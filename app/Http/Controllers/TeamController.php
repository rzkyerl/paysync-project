<?php

namespace App\Http\Controllers;

use App\Mail\TeamInvitationMail;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeamController extends Controller
{
    private const INVITABLE_ROLES = ['hr_manager', 'finance_manager', 'employee'];

    // Role yang tampil di halaman Manajemen Tim (admin roles saja)
    private const ADMIN_ROLES = ['hr_manager', 'finance_manager'];

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('payflow.team.index', [
            'company' => $user->company,
            'members' => User::query()
                ->where('company_id', $user->company_id)
                ->where('id', '!=', $user->getKey())
                ->whereIn('role', ['super_admin', 'hr_manager', 'finance_manager'])
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->orderBy('name')
                ->get(),
            'roles' => self::ADMIN_ROLES, // employee diundang dari halaman detail karyawan
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', 'in:'.implode(',', self::ADMIN_ROLES)],
        ]);

        $token = Str::random(64);
        $member = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => null,
            'role' => $data['role'],
            'status' => 'invited',
            'invitation_token' => $token,
            'invitation_expires_at' => now()->addDays(7),
            'is_demo' => false,
            'company_id' => $request->user()->company_id,
        ]);

        Mail::to($member)->send(new TeamInvitationMail($member));

        ActivityLogger::record($request, 'team', 'team.invited',
            "Undangan tim dikirim ke {$member->email}",
            'user', $member->id,
            ['name' => $member->name, 'email' => $member->email, 'role' => $member->role]
        );

        return back()->with('status', 'Undangan berhasil dikirim ke '.$member->email.'.');
    }

    public function showActivation(string $token): View
    {
        $member = $this->findInvitation($token);

        return view('payflow.team.activate', [
            'member' => $member,
            'token' => $token,
        ]);
    }

    public function activate(Request $request, string $token): RedirectResponse
    {
        $member = $this->findInvitation($token);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $member->forceFill([
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'invitation_token' => null,
            'invitation_expires_at' => null,
            'email_verified_at' => $member->email_verified_at ?? now(),
        ])->save();

        ActivityLogger::record($request, 'team', 'team.activated',
            "{$member->name} mengaktifkan akun tim",
            'user', $member->id,
            ['company_id' => $member->company_id, 'email' => $member->email, 'role' => $member->role]
        );

        app(NotificationService::class)->notifyRoles(
            (int) $member->company_id,
            ['super_admin'],
            'team.activated',
            'Anggota tim telah aktif',
            "{$member->name} ({$member->role}) telah mengaktifkan akunnya.",
            route('team.index', absolute: false),
            ['user_id' => $member->id, 'role' => $member->role],
        );

        Auth::login($member);
        $request->session()->regenerate();

        return redirect('/app/'.$member->defaultDashboard());
    }

    public function remove(Request $request, User $user): RedirectResponse
    {
        abort_if(
            $request->user()->company_id === null
            || $user->company_id !== $request->user()->company_id
            || $user->isSuperAdmin(),
            403,
        );

        $memberId = $user->id;
        $metadata = ['name' => $user->name, 'email' => $user->email, 'role' => $user->role];
        $user->delete();

        ActivityLogger::record($request, 'team', 'team.deactivated',
            "Anggota tim {$metadata['name']} dinonaktifkan",
            'user', $memberId, $metadata
        );

        return back()->with('status', 'Anggota tim berhasil dinonaktifkan.');
    }

    public function resend(Request $request, User $user): RedirectResponse
    {
        abort_if(
            $request->user()->company_id === null
            || $user->company_id !== $request->user()->company_id
            || $user->status !== 'invited',
            403,
        );

        $user->forceFill([
            'invitation_token' => Str::random(64),
            'invitation_expires_at' => now()->addDays(7),
        ])->save();

        Mail::to($user)->send(new TeamInvitationMail($user));

        ActivityLogger::record($request, 'team', 'team.invitation_resent',
            "Undangan tim dikirim ulang ke {$user->email}",
            'user', $user->id,
            ['name' => $user->name, 'email' => $user->email, 'role' => $user->role]
        );

        return back()->with('status', 'Undangan berhasil dikirim ulang ke '.$user->email.'.');
    }

    /**
     * Kirim undangan ESS (Employee Self-Service) dari halaman detail karyawan.
     * Hanya HR Manager yang bisa melakukan ini.
     */
    public function inviteEmployee(Request $request, \App\Models\Employee $employee): RedirectResponse
    {
        abort_if(! $request->user()->isHrManager(), 403);
        abort_if((int) $employee->company_id !== (int) $request->user()->company_id, 404);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
        ]);

        // Cek apakah karyawan sudah punya akun
        if ($employee->user()->exists()) {
            return back()->with('error', 'Karyawan ini sudah memiliki akun sistem.');
        }

        $token  = Str::random(64);
        $member = User::create([
            'name'                    => $employee->name,
            'email'                   => $data['email'],
            'password'                => null,
            'role'                    => 'employee',
            'status'                  => 'invited',
            'invitation_token'        => $token,
            'invitation_expires_at'   => now()->addDays(7),
            'is_demo'                 => false,
            'company_id'              => $employee->company_id,
            'employee_id'             => $employee->id,
        ]);

        Mail::to($member)->send(new TeamInvitationMail($member));

        ActivityLogger::record($request, 'team', 'employee.invited',
            "Undangan akun ESS dikirim ke {$employee->name} ({$data['email']})",
            'employee', $employee->id,
            ['email' => $data['email'], 'employee_nip' => $employee->nip]
        );

        return back()->with('status', "Undangan berhasil dikirim ke {$data['email']}.");
    }

    private function findInvitation(string $token): User
    {
        return User::query()
            ->where('invitation_token', $token)
            ->where('status', 'invited')
            ->where('invitation_expires_at', '>', now())
            ->firstOrFail();
    }
}
