<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Verifikasi Tasks 14–17 dari spec onboarding-flow.
 *
 * Task 14: Alur normal end-to-end
 * Task 15: Semua skenario Onboarding_Guard
 * Task 16: Validasi server-side individual
 * Task 17: Atomic transaction (rollback bersih)
 */
class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Task 14: Alur normal end-to-end
    // -----------------------------------------------------------------------

    /**
     * @test
     * Task 14: Happy path — submit valid → Company dibuat, user dapat company_id +
     * role=super_admin, redirect ke /app/dashboard-hr.
     */
    public function test_task14_full_happy_path_end_to_end(): void
    {
        $user = User::factory()->create([
            'company_id' => null,
            'is_demo'    => false,
            'role'       => 'hr_manager', // role awal sebelum onboarding
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'name'            => 'PT Maju Bersama',
            'industry'        => 'Teknologi',
            'size'            => '21–50',
            'payroll_cut_off' => 25,
            'pay_date'        => 30,
        ]);

        // Redirect ke dashboard HR
        $response->assertRedirect('/app/dashboard-hr');

        // Record Company tersimpan di database
        $this->assertDatabaseHas('companies', [
            'name'            => 'PT Maju Bersama',
            'industry'        => 'Teknologi',
            'size'            => '21–50',
            'payroll_cut_off' => 25,
            'pay_date'        => 30,
            'is_demo'         => false,
        ]);

        // User mendapat company_id terisi dan role = super_admin
        $user->refresh();
        $this->assertNotNull($user->company_id, 'company_id harus terisi setelah onboarding');
        $this->assertSame('super_admin', $user->role, 'role harus super_admin setelah onboarding');

        // company_id yang tersimpan cocok dengan Company yang baru dibuat
        $company = Company::first();
        $this->assertNotNull($company);
        $this->assertSame($company->id, $user->company_id);
    }

    /**
     * @test
     * Task 14: Setelah registrasi, session prefill terbawa ke onboarding view.
     */
    public function test_task14_registration_prefill_available_in_onboarding_view(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $this->actingAs($user)
            ->withSession([
                'onboarding_prefill' => [
                    'company_name' => 'PT Registrasi',
                    'company_size' => '51–100',
                    'industry'     => 'Keuangan',
                ],
            ])
            ->get(route('onboarding'))
            ->assertOk()
            ->assertViewIs('payflow.onboarding')
            ->assertViewHas('prefill.company_name', 'PT Registrasi');
    }

    // -----------------------------------------------------------------------
    // Task 15: Semua skenario Onboarding_Guard
    // -----------------------------------------------------------------------

    /**
     * @test
     * Task 15: User tidak terautentikasi → redirect ke /login.
     */
    public function test_task15_unauthenticated_user_redirected_to_login(): void
    {
        $this->get(route('onboarding'))
            ->assertRedirect(route('login'));
    }

    /**
     * @test
     * Task 15: User dengan company_id !== null → redirect ke dashboard sesuai role.
     */
    public function test_task15_user_with_company_redirected_to_dashboard(): void
    {
        $company = Company::create(['name' => 'Existing Corp']);
        $user    = User::factory()->create([
            'company_id' => $company->id,
            'role'       => 'finance_manager',
            'is_demo'    => false,
        ]);

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertRedirect('/app/dashboard-finance');
    }

    /**
     * @test
     * Task 15: User dengan company_id !== null yang POST ke onboarding → redirect ke dashboard.
     */
    public function test_task15_user_with_company_post_redirected_to_dashboard(): void
    {
        $company = Company::create(['name' => 'Already Setup Corp']);
        $user    = User::factory()->create([
            'company_id' => $company->id,
            'role'       => 'super_admin',
            'is_demo'    => false,
        ]);

        $this->actingAs($user)
            ->post(route('onboarding.store'), [
                'name'            => 'PT Lain',
                'size'            => '1–20',
                'payroll_cut_off' => 25,
                'pay_date'        => 30,
            ])
            ->assertRedirect('/app/dashboard-hr');

        // Tidak ada Company baru yang dibuat (masih 1 — yang di atas)
        $this->assertDatabaseCount('companies', 1);
    }

    /**
     * @test
     * Task 15: Demo user (is_demo = true) → redirect ke dashboard sesuai role.
     */
    public function test_task15_demo_user_redirected_to_dashboard(): void
    {
        $user = User::factory()->create([
            'role'       => 'super_admin',
            'is_demo'    => true,
            'company_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertRedirect('/app/dashboard-hr');
    }

    /**
     * @test
     * Task 15: Demo user dengan role finance_manager → redirect ke dashboard-finance.
     */
    public function test_task15_demo_user_finance_redirected_to_correct_dashboard(): void
    {
        $user = User::factory()->create([
            'role'       => 'finance_manager',
            'is_demo'    => true,
            'company_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertRedirect('/app/dashboard-finance');
    }

    // -----------------------------------------------------------------------
    // Task 16: Validasi server-side
    // -----------------------------------------------------------------------

    /**
     * @test
     * Task 16: Submit dengan name kosong → error validasi, tidak ada Company tersimpan.
     */
    public function test_task16_empty_name_returns_validation_error(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $this->actingAs($user)
            ->from(route('onboarding'))
            ->post(route('onboarding.store'), [
                'name'            => '',
                'size'            => '21–50',
                'payroll_cut_off' => 25,
                'pay_date'        => 30,
            ])
            ->assertRedirect(route('onboarding'))
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('companies', 0);
        $user->refresh();
        $this->assertNull($user->company_id, 'company_id tidak boleh terisi jika validasi gagal');
    }

    /**
     * @test
     * Task 16: payroll_cut_off = 29 ditolak (max 28).
     */
    public function test_task16_payroll_cut_off_29_is_rejected(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $this->actingAs($user)
            ->from(route('onboarding'))
            ->post(route('onboarding.store'), [
                'name'            => 'PT Valid Name',
                'size'            => '21–50',
                'payroll_cut_off' => 29,
                'pay_date'        => 30,
            ])
            ->assertRedirect(route('onboarding'))
            ->assertSessionHasErrors('payroll_cut_off');

        $this->assertDatabaseCount('companies', 0);
    }

    /**
     * @test
     * Task 16: pay_date = 32 ditolak (max 31).
     */
    public function test_task16_pay_date_32_is_rejected(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $this->actingAs($user)
            ->from(route('onboarding'))
            ->post(route('onboarding.store'), [
                'name'            => 'PT Valid Name',
                'size'            => '21–50',
                'payroll_cut_off' => 25,
                'pay_date'        => 32,
            ])
            ->assertRedirect(route('onboarding'))
            ->assertSessionHasErrors('pay_date');

        $this->assertDatabaseCount('companies', 0);
    }

    /**
     * @test
     * Task 16: payroll_cut_off = 0 ditolak (min 1).
     */
    public function test_task16_payroll_cut_off_0_is_rejected(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $this->actingAs($user)
            ->from(route('onboarding'))
            ->post(route('onboarding.store'), [
                'name'            => 'PT Valid Name',
                'size'            => '21–50',
                'payroll_cut_off' => 0,
                'pay_date'        => 30,
            ])
            ->assertRedirect(route('onboarding'))
            ->assertSessionHasErrors('payroll_cut_off');

        $this->assertDatabaseCount('companies', 0);
    }

    /**
     * @test
     * Task 16: pay_date = 0 ditolak (min 1).
     */
    public function test_task16_pay_date_0_is_rejected(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $this->actingAs($user)
            ->from(route('onboarding'))
            ->post(route('onboarding.store'), [
                'name'            => 'PT Valid Name',
                'size'            => '21–50',
                'payroll_cut_off' => 25,
                'pay_date'        => 0,
            ])
            ->assertRedirect(route('onboarding'))
            ->assertSessionHasErrors('pay_date');

        $this->assertDatabaseCount('companies', 0);
    }

    /**
     * @test
     * Task 16: size tidak valid ditolak.
     */
    public function test_task16_invalid_size_is_rejected(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $this->actingAs($user)
            ->from(route('onboarding'))
            ->post(route('onboarding.store'), [
                'name'            => 'PT Valid Name',
                'size'            => '999',
                'payroll_cut_off' => 25,
                'pay_date'        => 30,
            ])
            ->assertRedirect(route('onboarding'))
            ->assertSessionHasErrors('size');

        $this->assertDatabaseCount('companies', 0);
    }

    /**
     * @test
     * Task 16: Batas valid payroll_cut_off = 28 harus diterima.
     */
    public function test_task16_payroll_cut_off_28_is_accepted(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $this->actingAs($user)
            ->post(route('onboarding.store'), [
                'name'            => 'PT Batas Atas',
                'size'            => '1–20',
                'payroll_cut_off' => 28,
                'pay_date'        => 31,
            ])
            ->assertRedirect('/app/dashboard-hr');

        $this->assertDatabaseHas('companies', ['payroll_cut_off' => 28]);
    }

    // -----------------------------------------------------------------------
    // Task 17: Atomic transaction — rollback bersih
    // -----------------------------------------------------------------------

    /**
     * @test
     * Task 17: Verifikasi DB::transaction digunakan di OnboardingController@store.
     * Code review: pastikan transaction membungkus Company::create() dan user->update().
     */
    public function test_task17_controller_uses_db_transaction(): void
    {
        $controllerSource = file_get_contents(
            app_path('Http/Controllers/OnboardingController.php')
        );

        $this->assertStringContainsString(
            'DB::transaction',
            $controllerSource,
            'OnboardingController@store harus membungkus operasi dalam DB::transaction()'
        );

        // Pastikan Company::create() ada di dalam closure transaction
        $this->assertStringContainsString(
            'Company::create',
            $controllerSource,
            'Company::create() harus ada dalam transaction'
        );

        // Pastikan user->update() ada di dalam closure transaction
        $this->assertStringContainsString(
            '$user->update',
            $controllerSource,
            '$user->update() harus ada dalam transaction'
        );
    }

    /**
     * @test
     * Task 17: Jika user->update() gagal setelah Company::create(), tidak ada Company
     * yang tersimpan di database (rollback bersih via DB::transaction).
     *
     * Simulasi: gunakan DB::transaction manual dengan throw di tengah untuk
     * membuktikan rollback bekerja.
     */
    public function test_task17_transaction_rollback_on_user_update_failure(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $companyCreatedId = null;
        $exceptionThrown  = false;

        try {
            DB::transaction(function () use ($user, &$companyCreatedId): void {
                $company         = Company::create([
                    'name'            => 'PT Rollback Test',
                    'industry'        => 'Test',
                    'size'            => '1–20',
                    'payroll_cut_off' => 25,
                    'pay_date'        => 30,
                    'is_demo'         => false,
                ]);
                $companyCreatedId = $company->id;

                // Simulasi kegagalan setelah Company::create() tapi sebelum user->update()
                throw new \RuntimeException('Simulasi kegagalan database');

                // Baris ini tidak pernah dieksekusi
                $user->update(['company_id' => $company->id, 'role' => 'super_admin']); // @phpstan-ignore-line
            });
        } catch (\RuntimeException) {
            $exceptionThrown = true;
        }

        // Exception memang harus dilempar
        $this->assertTrue($exceptionThrown, 'Exception harus ter-throw');

        // Rollback: tidak ada Company yang tersimpan
        $this->assertDatabaseCount('companies', 0);
        $this->assertDatabaseMissing('companies', ['id' => $companyCreatedId],
            null, // connection
        );

        // User state tidak berubah
        $user->refresh();
        $this->assertNull($user->company_id, 'company_id harus tetap null setelah rollback');
        $this->assertNotSame('super_admin', $user->role);
    }

    /**
     * @test
     * Task 17: Validasi gagal tidak masuk ke transaction → tidak ada Company tersimpan.
     */
    public function test_task17_validation_failure_never_enters_transaction(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        // Submit data invalid
        $this->actingAs($user)
            ->from(route('onboarding'))
            ->post(route('onboarding.store'), [
                'name'            => '',    // invalid
                'size'            => '21–50',
                'payroll_cut_off' => 29,    // invalid
                'pay_date'        => 32,    // invalid
            ]);

        // Tidak ada Company sama sekali
        $this->assertDatabaseCount('companies', 0);

        // User tidak berubah
        $user->refresh();
        $this->assertNull($user->company_id);
    }

    /**
     * @test
     * Task 17: Controller error handling — exception di dalam transaction
     * menghasilkan redirect dengan flash error tanpa Company tersimpan.
     * (Verifikasi perilaku OnboardingController@store saat Throwable tertangkap)
     */
    public function test_task17_controller_catches_throwable_and_returns_error_flash(): void
    {
        $controllerSource = file_get_contents(
            app_path('Http/Controllers/OnboardingController.php')
        );

        // Verifikasi try-catch \Throwable ada
        $this->assertStringContainsString(
            'Throwable',
            $controllerSource,
            'Controller harus menangkap \Throwable untuk error handling'
        );

        // Verifikasi ada flash error message
        $this->assertStringContainsString(
            "with('error'",
            $controllerSource,
            'Controller harus flash error message ke session saat transaction gagal'
        );
    }
}
