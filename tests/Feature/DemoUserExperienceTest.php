<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoUserExperienceTest extends TestCase
{
    use RefreshDatabase;

    private User $demoUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoDataSeeder::class);
        $this->demoUser = User::where('email', 'rina.hr@paysync.test')->firstOrFail();
    }

    public function test_demo_user_receives_populated_hr_dashboard_data(): void
    {
        $this->actingAs($this->demoUser)
            ->get(route('dashboard.hr'))
            ->assertOk()
            ->assertViewHas('isDemoUser', true)
            ->assertViewHas('isEmpty', false);
    }

    public function test_demo_user_can_access_all_main_pages(): void
    {
        $paths = [
            '/app/dashboard-hr',
            '/app/dashboard-finance',
            '/app/dashboard-employee',
            '/employees',
            '/app/attendance',
            '/app/payroll',
            '/app/approval',
            '/app/payslips',
            '/app/disbursement',
            '/app/reconciliation',
            '/app/reports',
            '/app/settings',
            '/app/audit',
        ];

        foreach ($paths as $path) {
            $this->actingAs($this->demoUser)->get($path)->assertOk();
        }

        $this->actingAs($this->demoUser)->get('/app/invalid-page')->assertNotFound();
    }

    public function test_demo_hr_dashboard_contains_mode_demo_banner(): void
    {
        $this->actingAs($this->demoUser)
            ->get(route('dashboard.hr'))
            ->assertOk()
            ->assertSee('Mode Demo');
    }
}
