<?php

namespace Tests\Feature;

use App\Mail\TeamInvitationMail;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature tests untuk TeamController.
 */
class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $superAdmin;
    private User $hrManager;
    private User $financeManager;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->company = Company::create(['name' => 'Acme Corp']);

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

        $this->financeManager = User::factory()->create([
            'role'       => 'finance_manager',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // invite
    // -----------------------------------------------------------------------

    public function test_super_admin_can_invite_hr_manager(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('team.invite'), [
                'name'  => 'Finance Person',
                'email' => 'finance@example.com',
                'role'  => 'hr_manager',
            ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email'  => 'finance@example.com',
            'role'   => 'hr_manager',
            'status' => 'invited',
        ]);

        Mail::assertSent(TeamInvitationMail::class, fn ($mail) => $mail->hasTo('finance@example.com'));
    }

    public function test_invite_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($this->superAdmin)
            ->post(route('team.invite'), [
                'name'  => 'Duplicate User',
                'email' => 'existing@example.com',
                'role'  => 'hr_manager',
            ])->assertSessionHasErrors('email');

        Mail::assertNotSent(TeamInvitationMail::class);
    }

    public function test_invite_fails_with_employee_role(): void
    {
        // Employee role tidak bisa diundang via halaman tim, hanya dari halaman detail karyawan
        $this->actingAs($this->superAdmin)
            ->post(route('team.invite'), [
                'name'  => 'Some Employee',
                'email' => 'emp@example.com',
                'role'  => 'employee',
            ])->assertSessionHasErrors('role');
    }

    // -----------------------------------------------------------------------
    // showActivation
    // -----------------------------------------------------------------------

    public function test_valid_token_shows_activation_form(): void
    {
        $invited = User::factory()->create([
            'status'                => 'invited',
            'invitation_token'      => 'validtoken123',
            'invitation_expires_at' => now()->addDays(7),
            'company_id'            => $this->company->id,
        ]);

        $this->get(route('invite.show', 'validtoken123'))
            ->assertOk();
    }

    public function test_expired_token_returns_404(): void
    {
        User::factory()->create([
            'status'                => 'invited',
            'invitation_token'      => 'expiredtoken',
            'invitation_expires_at' => now()->subDay(), // sudah kedaluwarsa
            'company_id'            => $this->company->id,
        ]);

        $this->get(route('invite.show', 'expiredtoken'))
            ->assertNotFound();
    }

    public function test_non_invited_status_token_returns_404(): void
    {
        User::factory()->create([
            'status'                => 'active', // sudah aktif
            'invitation_token'      => 'activetoken',
            'invitation_expires_at' => now()->addDays(7),
            'company_id'            => $this->company->id,
        ]);

        $this->get(route('invite.show', 'activetoken'))
            ->assertNotFound();
    }

    // -----------------------------------------------------------------------
    // activate
    // -----------------------------------------------------------------------

    public function test_invited_user_can_activate_account(): void
    {
        $invited = User::factory()->create([
            'status'                => 'invited',
            'invitation_token'      => 'activatetoken',
            'invitation_expires_at' => now()->addDays(7),
            'company_id'            => $this->company->id,
            'role'                  => 'hr_manager',
        ]);

        $this->post(route('invite.activate', 'activatetoken'), [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect('/app/dashboard-hr');

        $this->assertDatabaseHas('users', [
            'id'               => $invited->id,
            'status'           => 'active',
            'invitation_token' => null,
        ]);
    }

    public function test_activate_fails_with_short_password(): void
    {
        User::factory()->create([
            'status'                => 'invited',
            'invitation_token'      => 'shortpwdtoken',
            'invitation_expires_at' => now()->addDays(7),
            'company_id'            => $this->company->id,
        ]);

        $this->post(route('invite.activate', 'shortpwdtoken'), [
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
    }

    public function test_activate_fails_with_password_confirmation_mismatch(): void
    {
        User::factory()->create([
            'status'                => 'invited',
            'invitation_token'      => 'mismatchtoken',
            'invitation_expires_at' => now()->addDays(7),
            'company_id'            => $this->company->id,
        ]);

        $this->post(route('invite.activate', 'mismatchtoken'), [
            'password'              => 'password123',
            'password_confirmation' => 'different456',
        ])->assertSessionHasErrors('password');
    }

    // -----------------------------------------------------------------------
    // resend
    // -----------------------------------------------------------------------

    public function test_super_admin_can_resend_invitation(): void
    {
        $invited = User::factory()->create([
            'status'                => 'invited',
            'invitation_token'      => 'oldtoken',
            'invitation_expires_at' => now()->addDays(1),
            'company_id'            => $this->company->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('team.resend', $invited))
            ->assertRedirect();

        // Token diperbarui
        $this->assertDatabaseMissing('users', ['invitation_token' => 'oldtoken']);
        Mail::assertSent(TeamInvitationMail::class);
    }

    public function test_cannot_resend_to_active_user(): void
    {
        $activeUser = User::factory()->create([
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('team.resend', $activeUser))
            ->assertForbidden();
    }

    public function test_cannot_resend_to_user_from_different_company(): void
    {
        $otherCompany = Company::create(['name' => 'Other Corp']);
        $otherInvited = User::factory()->create([
            'status'                => 'invited',
            'invitation_token'      => 'othertoken',
            'invitation_expires_at' => now()->addDays(7),
            'company_id'            => $otherCompany->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('team.resend', $otherInvited))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // remove
    // -----------------------------------------------------------------------

    public function test_super_admin_can_remove_team_member(): void
    {
        $member = User::factory()->create([
            'role'       => 'hr_manager',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->delete(route('team.remove', $member))
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $member->id]);
    }

    public function test_cannot_remove_super_admin(): void
    {
        $otherAdmin = User::factory()->create([
            'role'       => 'super_admin',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->delete(route('team.remove', $otherAdmin))
            ->assertForbidden();
    }

    public function test_cannot_remove_member_from_different_company(): void
    {
        $otherCompany = Company::create(['name' => 'Other Corp']);
        $otherMember  = User::factory()->create([
            'role'       => 'hr_manager',
            'status'     => 'active',
            'company_id' => $otherCompany->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->delete(route('team.remove', $otherMember))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // inviteEmployee (ESS)
    // -----------------------------------------------------------------------

    public function test_hr_manager_can_invite_employee_for_ess(): void
    {
        $employee = Employee::create([
            'company_id'   => $this->company->id,
            'nip'          => 'EMP-0001',
            'name'         => 'Karyawan Test',
            'department'   => 'IT',
            'position'     => 'Staff',
            'work_status'  => 'active',
            'join_date'    => '2022-01-01',
            'basic_salary' => 5_000_000,
        ]);

        $this->actingAs($this->hrManager)
            ->post(route('employees.invite', $employee), [
                'email' => 'karyawan@example.com',
            ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email'       => 'karyawan@example.com',
            'role'        => 'employee',
            'status'      => 'invited',
            'employee_id' => $employee->id,
        ]);

        Mail::assertSent(TeamInvitationMail::class);
    }

    public function test_cannot_invite_employee_who_already_has_account(): void
    {
        $employee = Employee::create([
            'company_id'   => $this->company->id,
            'nip'          => 'EMP-0002',
            'name'         => 'Existing User',
            'department'   => 'IT',
            'position'     => 'Staff',
            'work_status'  => 'active',
            'join_date'    => '2022-01-01',
            'basic_salary' => 5_000_000,
        ]);

        // Create existing user linked to this employee
        User::factory()->create([
            'email'       => 'linked@example.com',
            'employee_id' => $employee->id,
            'company_id'  => $this->company->id,
        ]);

        $this->actingAs($this->hrManager)
            ->post(route('employees.invite', $employee), [
                'email' => 'new@example.com',
            ])->assertSessionHas('error');
    }

    public function test_finance_manager_cannot_invite_employee_for_ess(): void
    {
        $employee = Employee::create([
            'company_id'   => $this->company->id,
            'nip'          => 'EMP-0003',
            'name'         => 'Test',
            'department'   => 'IT',
            'position'     => 'Staff',
            'work_status'  => 'active',
            'join_date'    => '2022-01-01',
            'basic_salary' => 5_000_000,
        ]);

        $this->actingAs($this->financeManager)
            ->post(route('employees.invite', $employee), [
                'email' => 'test@example.com',
            ])->assertForbidden();
    }
}
