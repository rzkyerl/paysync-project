<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('onboarding'))->assertRedirect(route('login'));
    }

    public function test_new_user_sees_onboarding_with_registration_prefill(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $response = $this->actingAs($user)
            ->withSession([
                'onboarding_prefill' => [
                    'company_name' => 'PT Prefill',
                    'company_size' => '21–50',
                    'industry' => 'Teknologi',
                ],
            ])
            ->get(route('onboarding'));

        $response->assertOk()
            ->assertViewIs('payflow.onboarding')
            ->assertViewHas('user', $user)
            ->assertViewHas('prefill.company_name', 'PT Prefill');
    }

    public function test_user_with_company_is_redirected_to_role_dashboard(): void
    {
        $company = Company::create(['name' => 'Existing Company']);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'finance_manager',
            'is_demo' => false,
        ]);

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertRedirect('/app/dashboard-finance');
    }

    public function test_demo_user_is_redirected_to_default_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
            'is_demo' => true,
            'company_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertRedirect('/app/dashboard-hr');
    }

    public function test_valid_submission_creates_company_and_promotes_owner(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'name' => 'PT Maju Bersama',
            'industry' => 'Teknologi',
            'size' => '21–50',
            'payroll_cut_off' => 25,
            'pay_date' => 30,
        ]);

        $response->assertRedirect('/app/dashboard-hr');
        $this->assertDatabaseHas('companies', [
            'name' => 'PT Maju Bersama',
            'size' => '21–50',
            'payroll_cut_off' => 25,
            'pay_date' => 30,
        ]);
        $user->refresh();
        $this->assertNotNull($user->company_id);
        $this->assertSame('super_admin', $user->role);
    }

    public function test_invalid_submission_does_not_create_company(): void
    {
        $user = User::factory()->create(['company_id' => null, 'is_demo' => false]);

        $this->actingAs($user)
            ->from(route('onboarding'))
            ->post(route('onboarding.store'), [
                'name' => '',
                'size' => '21–50',
                'payroll_cut_off' => 29,
                'pay_date' => 32,
            ])
            ->assertRedirect(route('onboarding'))
            ->assertSessionHasErrors(['name', 'payroll_cut_off', 'pay_date']);

        $this->assertDatabaseCount('companies', 0);
        $user->refresh();
        $this->assertNull($user->company_id);
        $this->assertNotSame('super_admin', $user->role);
    }
}
