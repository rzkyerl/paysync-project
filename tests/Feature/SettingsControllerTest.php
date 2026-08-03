<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SettingsAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk SettingsController.
 */
class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $superAdmin;
    private User $hrManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name'            => 'Acme Corp',
            'industry'        => 'Teknologi',
            'size'            => '21–50',
            'payroll_cut_off' => 25,
            'pay_date'        => 28,
        ]);

        $this->superAdmin = User::factory()->create([
            'role'       => 'super_admin',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);

        $this->hrManager = User::factory()->create([
            'role'       => 'hr_manager',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // updateCompany
    // -----------------------------------------------------------------------

    public function test_super_admin_can_update_company_settings(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.company.update'), [
                'name'            => 'Acme Corp Updated',
                'industry'        => 'Keuangan',
                'size'            => '51–100',
                'payroll_cut_off' => 20,
                'pay_date'        => 25,
            ])->assertRedirect();

        $this->assertDatabaseHas('companies', [
            'id'              => $this->company->id,
            'name'            => 'Acme Corp Updated',
            'industry'        => 'Keuangan',
        ]);
    }

    public function test_update_company_logs_changed_fields_in_audit_log(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.company.update'), [
                'name'            => 'New Name',
                'industry'        => 'Teknologi', // tidak berubah
                'size'            => '21–50',       // tidak berubah
                'payroll_cut_off' => 25,             // tidak berubah
                'pay_date'        => 30,             // berubah
            ]);

        // Hanya field yang berubah yang masuk audit log
        $this->assertDatabaseHas('settings_audit_logs', [
            'company_id'    => $this->company->id,
            'field_changed' => 'name',
        ]);
        $this->assertDatabaseHas('settings_audit_logs', [
            'company_id'    => $this->company->id,
            'field_changed' => 'pay_date',
        ]);

        // industry tidak berubah → tidak ada di audit log
        $unchanged = SettingsAuditLog::where('company_id', $this->company->id)
            ->where('field_changed', 'industry')
            ->count();
        $this->assertSame(0, $unchanged);
    }

    public function test_update_company_fails_payroll_cut_off_over_28(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.company.update'), [
                'name'            => 'Acme',
                'payroll_cut_off' => 29,
                'pay_date'        => 28,
            ])->assertSessionHasErrors('payroll_cut_off');
    }

    public function test_non_super_admin_cannot_update_company(): void
    {
        $this->actingAs($this->hrManager)
            ->put(route('settings.company.update'), [
                'name'            => 'Hacked',
                'payroll_cut_off' => 25,
                'pay_date'        => 28,
            ])->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // updateUserRole
    // -----------------------------------------------------------------------

    public function test_super_admin_can_change_user_role(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.users.role', $this->hrManager), [
                'role' => 'finance_manager',
            ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'   => $this->hrManager->id,
            'role' => 'finance_manager',
        ]);
    }

    public function test_cannot_change_super_admin_role(): void
    {
        $otherAdmin = User::factory()->create([
            'role'       => 'super_admin',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->put(route('settings.users.role', $otherAdmin), [
                'role' => 'hr_manager',
            ])->assertForbidden();
    }

    public function test_cannot_change_own_role(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.users.role', $this->superAdmin), [
                'role' => 'hr_manager',
            ])->assertForbidden();
    }

    public function test_cannot_change_role_of_user_from_different_company(): void
    {
        $otherCompany = Company::create(['name' => 'Other Corp']);
        $otherUser    = User::factory()->create([
            'role'       => 'hr_manager',
            'company_id' => $otherCompany->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->put(route('settings.users.role', $otherUser), [
                'role' => 'finance_manager',
            ])->assertForbidden();
    }

    public function test_invalid_role_value_rejected(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.users.role', $this->hrManager), [
                'role' => 'super_admin', // tidak bisa diset ke super_admin
            ])->assertSessionHasErrors('role');
    }

    // -----------------------------------------------------------------------
    // deactivateUser
    // -----------------------------------------------------------------------

    public function test_super_admin_can_deactivate_team_member(): void
    {
        $this->actingAs($this->superAdmin)
            ->delete(route('settings.users.deactivate', $this->hrManager))
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $this->hrManager->id]);
    }

    public function test_cannot_deactivate_super_admin(): void
    {
        $otherAdmin = User::factory()->create([
            'role'       => 'super_admin',
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->delete(route('settings.users.deactivate', $otherAdmin))
            ->assertForbidden();
    }

    public function test_cannot_deactivate_self(): void
    {
        $this->actingAs($this->superAdmin)
            ->delete(route('settings.users.deactivate', $this->superAdmin))
            ->assertForbidden();
    }

    public function test_cannot_deactivate_user_from_different_company(): void
    {
        $otherCompany = Company::create(['name' => 'Other Corp']);
        $otherUser    = User::factory()->create([
            'role'       => 'hr_manager',
            'company_id' => $otherCompany->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->delete(route('settings.users.deactivate', $otherUser))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // index
    // -----------------------------------------------------------------------

    public function test_super_admin_can_view_settings_page(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('settings.index'))
            ->assertOk();
    }

    public function test_non_super_admin_cannot_view_settings_page(): void
    {
        // Middleware role pada GET request mengarahkan ke dashboard, bukan 403
        $this->actingAs($this->hrManager)
            ->get(route('settings.index'))
            ->assertRedirect('/app/dashboard-hr');
    }
}
