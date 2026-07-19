<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_is_idempotent_and_keeps_valid_company_links(): void
    {
        $this->seed(DemoDataSeeder::class);
        $this->seed(DemoDataSeeder::class);

        $company = Company::where('name', 'PT Pay Sync')->firstOrFail();

        $this->assertSame(1, Company::where('name', 'PT Pay Sync')->count());
        $this->assertTrue($company->is_demo);
        $this->assertSame(3, User::where('is_demo', true)->count());
        $this->assertSame(5, Employee::where('company_id', $company->id)->count());
        $this->assertSame(1, Payroll::where('company_id', $company->id)->where('status', 'needs_review')->count());

        User::where('is_demo', true)->each(function (User $user) use ($company): void {
            $this->assertSame($company->id, $user->company_id);
            $this->assertTrue($user->isDemoUser());
        });
    }
}
