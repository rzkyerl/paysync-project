<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SettingsAuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const EDITABLE_FIELDS = ['name', 'industry', 'size', 'payroll_cut_off', 'pay_date'];

    public function index(Request $request): View
    {
        $company = $this->company($request);

        return view('payflow.settings.index', [
            'company' => $company,
            'users' => $company->users()->where('role', '!=', 'super_admin')->orderBy('name')->get(),
            'auditLogs' => $company->settingsAuditLogs()->with('user')->latest('changed_at')->limit(50)->get(),
        ]);
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $company = $this->company($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'industry' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:50'],
            'payroll_cut_off' => ['nullable', 'integer', 'between:1,28'],
            'pay_date' => ['nullable', 'integer', 'between:1,31'],
        ]);

        DB::transaction(function () use ($company, $validated, $request): void {
            foreach (self::EDITABLE_FIELDS as $field) {
                $old = $company->getAttribute($field);
                $new = $validated[$field] ?? null;
                if ((string) $old === (string) $new) {
                    continue;
                }
                SettingsAuditLog::create([
                    'company_id' => $company->id,
                    'user_id' => $request->user()->id,
                    'field_changed' => $field,
                    'old_value' => $old,
                    'new_value' => $new,
                    'changed_at' => now(),
                ]);
            }
            $company->update($validated);
        });

        return back()->with('status', 'Profil perusahaan berhasil diperbarui.');
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $this->assertTarget($request, $user);
        $validated = $request->validate(['role' => ['required', 'in:hr_manager,finance_manager,employee']]);
        $user->update(['role' => $validated['role']]);

        return back()->with('status', 'Role anggota berhasil diperbarui.');
    }

    public function deactivateUser(Request $request, User $user): RedirectResponse
    {
        $this->assertTarget($request, $user);
        $user->delete();

        return back()->with('status', 'Anggota berhasil dinonaktifkan.');
    }

    private function company(Request $request): Company
    {
        abort_if($request->user()->company_id === null, 404);

        return Company::whereKey($request->user()->company_id)->firstOrFail();
    }

    private function assertTarget(Request $request, User $user): void
    {
        abort_if(
            (int) $user->company_id !== (int) $request->user()->company_id
            || $user->isSuperAdmin()
            || (int) $user->id === (int) $request->user()->id,
            403,
        );
    }
}
