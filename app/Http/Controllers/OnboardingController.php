<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    private const COMPANY_SIZES = ['1–20', '21–50', '51–100', '101–500', '> 500'];

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isDemoUser() || $user->company_id !== null) {
            return redirect('/app/'.$user->defaultDashboard());
        }

        $prefill = array_merge(
            (array) session('onboarding_prefill', []),
            (array) session('onboarding_data', []),
        );

        return view('payflow.onboarding', [
            'prefill' => $prefill,
            'user' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isDemoUser() || $user->company_id !== null) {
            return redirect('/app/'.$user->defaultDashboard());
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'industry' => ['nullable', 'string', 'max:100'],
            'size' => ['required', 'in:'.implode(',', self::COMPANY_SIZES)],
            'payroll_cut_off' => ['required', 'integer', 'min:1', 'max:28'],
            'pay_date' => ['required', 'integer', 'min:1', 'max:31'],
        ]);

        try {
            DB::transaction(function () use ($user, $validated): void {
                $company = Company::create([
                    'name' => $validated['name'],
                    'industry' => $validated['industry'] ?? null,
                    'size' => $validated['size'],
                    'payroll_cut_off' => $validated['payroll_cut_off'],
                    'pay_date' => $validated['pay_date'],
                    'is_demo' => false,
                ]);

                $user->update([
                    'company_id' => $company->id,
                    'role' => 'super_admin',
                ]);

                $this->attachSeededTeam($user->email, $company->id);
            });
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('onboarding')
                ->withInput()
                ->with('error', 'Setup perusahaan gagal disimpan. Silakan coba lagi.');
        }

        session()->forget(['onboarding_data', 'onboarding_prefill', 'onboarding_step']);

        return redirect('/app/dashboard-hr');
    }

    private function attachSeededTeam(string $ownerEmail, int $companyId): void
    {
        if ($ownerEmail !== config('paysync.seeded_team.owner')) {
            return;
        }

        $memberEmails = config('paysync.seeded_team.members', []);

        if (! is_array($memberEmails) || $memberEmails === []) {
            return;
        }

        User::query()
            ->whereIn('email', $memberEmails)
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);
    }
}
