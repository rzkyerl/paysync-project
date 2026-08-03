<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature tests untuk LoginController dan RegisterController.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // LoginController::show
    // -----------------------------------------------------------------------

    public function test_login_page_is_accessible_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('payflow.auth.login');
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $user    = User::factory()->create([
            'role'       => 'hr_manager',
            'company_id' => $company->id,
            'status'     => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect('/app/dashboard-hr');
    }

    // -----------------------------------------------------------------------
    // LoginController::store
    // -----------------------------------------------------------------------

    public function test_user_can_login_with_valid_credentials(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $user    = User::factory()->create([
            'password'   => Hash::make('password123'),
            'role'       => 'hr_manager',
            'status'     => 'active',
            'company_id' => $company->id,
        ]);

        $this->post(route('login.attempt'), [
            'email'    => $user->email,
            'password' => 'password123',
        ])->assertRedirect('/app/dashboard-hr');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
            'status'   => 'active',
        ]);

        $response = $this->post(route('login.attempt'), [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_fails_for_invited_status_user(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status'   => 'invited', // bukan 'active'
        ]);

        $response = $this->post(route('login.attempt'), [
            'email'    => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_requires_email_field(): void
    {
        $this->post(route('login.attempt'), ['password' => 'password123'])
            ->assertSessionHasErrors('email');
    }

    public function test_login_requires_password_field(): void
    {
        $this->post(route('login.attempt'), ['email' => 'test@test.com'])
            ->assertSessionHasErrors('password');
    }

    public function test_new_user_without_company_redirected_to_onboarding_after_login(): void
    {
        $user = User::factory()->create([
            'password'   => Hash::make('password123'),
            'status'     => 'active',
            'company_id' => null,
            'is_demo'    => false,
        ]);

        $this->post(route('login.attempt'), [
            'email'    => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('onboarding'));
    }

    public function test_finance_manager_redirected_to_finance_dashboard(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $user    = User::factory()->create([
            'password'   => Hash::make('password123'),
            'role'       => 'finance_manager',
            'status'     => 'active',
            'company_id' => $company->id,
        ]);

        $this->post(route('login.attempt'), [
            'email'    => $user->email,
            'password' => 'password123',
        ])->assertRedirect('/app/dashboard-finance');
    }

    // -----------------------------------------------------------------------
    // LoginController::destroy
    // -----------------------------------------------------------------------

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    // -----------------------------------------------------------------------
    // RegisterController::show
    // -----------------------------------------------------------------------

    public function test_register_page_accessible_to_guests(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertViewIs('payflow.auth.register');
    }

    public function test_authenticated_user_redirected_from_register_page(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $user    = User::factory()->create([
            'role'       => 'hr_manager',
            'company_id' => $company->id,
            'status'     => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirect('/app/dashboard-hr');
    }

    // -----------------------------------------------------------------------
    // RegisterController::store
    // -----------------------------------------------------------------------

    public function test_user_can_register_and_is_redirected_to_onboarding(): void
    {
        $this->post(route('register.store'), [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'company'               => 'PT Contoh',
            'company_size'          => '1–20',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'terms'                 => '1',
        ])->assertRedirect(route('onboarding'));

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertAuthenticated();
    }

    public function test_registration_stores_onboarding_prefill_in_session(): void
    {
        $response = $this->post(route('register.store'), [
            'name'                  => 'Jane',
            'email'                 => 'jane@example.com',
            'company'               => 'PT Baru',
            'company_size'          => '21–50',
            'industry'              => 'Teknologi',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'terms'                 => '1',
        ]);

        $response->assertSessionHas('onboarding_prefill.company_name', 'PT Baru');
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->post(route('register.store'), [
            'name'                  => 'New User',
            'email'                 => 'existing@example.com',
            'company'               => 'PT Test',
            'company_size'          => '1–20',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'terms'                 => '1',
        ])->assertSessionHasErrors('email');
    }

    public function test_register_fails_with_short_password(): void
    {
        $this->post(route('register.store'), [
            'name'                  => 'New User',
            'email'                 => 'new@example.com',
            'company'               => 'PT Test',
            'company_size'          => '1–20',
            'password'              => 'short',
            'password_confirmation' => 'short',
            'terms'                 => '1',
        ])->assertSessionHasErrors('password');
    }

    public function test_register_fails_without_terms_acceptance(): void
    {
        $this->post(route('register.store'), [
            'name'                  => 'New User',
            'email'                 => 'new@example.com',
            'company'               => 'PT Test',
            'company_size'          => '1–20',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            // 'terms' sengaja tidak diisi
        ])->assertSessionHasErrors('terms');
    }

    public function test_register_fails_with_password_mismatch(): void
    {
        $this->post(route('register.store'), [
            'name'                  => 'New User',
            'email'                 => 'new@example.com',
            'company'               => 'PT Test',
            'company_size'          => '1–20',
            'password'              => 'password123',
            'password_confirmation' => 'different999',
            'terms'                 => '1',
        ])->assertSessionHasErrors('password');
    }

    public function test_register_requires_company_name(): void
    {
        $this->post(route('register.store'), [
            'name'                  => 'New User',
            'email'                 => 'new@example.com',
            'company'               => '',
            'company_size'          => '1–20',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'terms'                 => '1',
        ])->assertSessionHasErrors('company');
    }
}
