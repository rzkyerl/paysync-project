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
        $this->demoUser = User::where('email', 'hr@paysync.test')->firstOrFail();
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
        // demoUser adalah hr_manager — hanya bisa akses pages yang diizinkan rolenya
        // hr_manager bisa akses: dashboard-hr, employees, attendance, payroll, payslips, reports
        // Tidak bisa: dashboard-finance (finance_manager only), dashboard-employee (employee only),
        //             approval (finance_manager only), disbursement (finance_manager only),
        //             reconciliation (finance_manager only), settings (super_admin only),
        //             audit (super_admin only)
        $hrPaths = [
            '/app/dashboard-hr',
            '/employees',
            '/app/attendance',
            '/app/payroll',
            '/app/payslips',
            '/app/reports',
        ];

        foreach ($hrPaths as $path) {
            $response = $this->actingAs($this->demoUser)->get($path);
            $this->assertSame(
                200,
                $response->getStatusCode(),
                "Path '{$path}' should return 200 for hr_manager demo user, got {$response->getStatusCode()}"
            );
        }

        // dashboard-finance hanya untuk finance_manager/super_admin — redirect
        $this->actingAs($this->demoUser)->get('/app/dashboard-finance')->assertRedirect();

        // settings hanya untuk super_admin — redirect
        $this->actingAs($this->demoUser)->get('/app/settings')->assertRedirect();

        // audit hanya untuk super_admin — redirect
        $this->actingAs($this->demoUser)->get('/app/audit')->assertRedirect();

        // invalid page — 404 karena tidak ada catch-all route
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
