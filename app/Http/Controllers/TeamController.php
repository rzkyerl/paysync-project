<?php

namespace App\Http\Controllers;

use App\Mail\TeamInvitationMail;
use App\Models\User;
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

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('payflow.team.index', [
            'company' => $user->company,
            'members' => User::query()
                ->where('company_id', $user->company_id)
                ->where('id', '!=', $user->getKey())
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->orderBy('name')
                ->get(),
            'roles' => self::INVITABLE_ROLES,
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', 'in:'.implode(',', self::INVITABLE_ROLES)],
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

        $user->delete();

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

        return back()->with('status', 'Undangan berhasil dikirim ulang ke '.$user->email.'.');
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
