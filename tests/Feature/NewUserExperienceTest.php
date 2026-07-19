<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewUserExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_redirects_new_user_to_onboarding(): void
    {
        $response = $this->post(route('register.store'), $this->registrationData());

        $response->assertRedirect(route('onboarding'));
        $this->assertAuthenticated();

        $user = User::where('email', 'new.user@example.test')->firstOrFail();
        $this->assertFalse($user->isDemoUser());
        $this->assertNull($user->company_id);
    }

    public function test_new_user_receives_empty_hr_dashboard(): void
    {
        $user = User::create([
            'name' => 'New Empty User',
            'email' => 'empty.user@example.test',
            'password' => 'password123',
            'role' => 'employee',
            'is_demo' => false,
            'company_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.hr'))
            ->assertOk()
            ->assertViewHas('isDemoUser', false)
            ->assertViewHas('isEmpty', true)
            ->assertSee('Belum ada karyawan');
    }

    public function test_registration_ignores_injected_demo_flag_and_company_id(): void
    {
        $companyId = \App\Models\Company::create(['name' => 'Injected Company'])->id;

        $this->post(route('register.store'), array_merge($this->registrationData(), [
            'email' => 'injection@example.test',
            'is_demo' => true,
            'company_id' => $companyId,
        ]))->assertRedirect(route('onboarding'));

        $user = User::where('email', 'injection@example.test')->firstOrFail();
        $this->assertFalse($user->isDemoUser());
        $this->assertNull($user->company_id);
    }

    /**
     * @return array<string, mixed>
     */
    private function registrationData(): array
    {
        return [
            'name' => 'New User',
            'email' => 'new.user@example.test',
            'company' => 'Perusahaan Baru',
            'company_size' => '1-10',
            'industry' => 'Technology',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => '1',
        ];
    }
}
