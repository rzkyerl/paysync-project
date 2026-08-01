<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_notification_is_company_scoped(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $otherCompany = Company::create(['name' => 'Other']);
        $finance = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'finance_manager',
            'status' => 'active',
        ]);
        User::factory()->create([
            'company_id' => $company->id,
            'role' => 'hr_manager',
            'status' => 'active',
        ]);
        User::factory()->create([
            'company_id' => $otherCompany->id,
            'role' => 'finance_manager',
            'status' => 'active',
        ]);

        app(NotificationService::class)->notifyRoles(
            $company->id,
            ['finance_manager'],
            'payroll.awaiting_approval',
            'Payroll menunggu persetujuan',
            'Payroll Juli siap ditinjau.',
            '/app/payroll/10',
        );

        $this->assertSame(1, UserNotification::count());
        $this->assertDatabaseHas('user_notifications', [
            'company_id' => $company->id,
            'user_id' => $finance->id,
            'type' => 'payroll.awaiting_approval',
        ]);
    }

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'hr_manager',
            'status' => 'active',
        ]);
        $notification = app(NotificationService::class)->notifyUser(
            $user,
            'employee.bank_unverified',
            'Rekening perlu diverifikasi',
            'Rekening Budi belum diverifikasi.',
            '/employees',
        );

        $response = $this->actingAs($user)->patch(route('notifications.read', $notification));

        $response->assertRedirect('/employees');
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notification_is_rendered_in_header_with_unread_count(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'hr_manager',
            'status' => 'active',
        ]);
        app(NotificationService::class)->notifyUser(
            $user,
            'employee.bank_unverified',
            'Rekening perlu diverifikasi',
            'Satu rekening menunggu verifikasi.',
            '/employees',
        );

        $this->actingAs($user)
            ->get(route('employees.index'))
            ->assertOk()
            ->assertSeeText('Notifikasi (1)')
            ->assertSeeText('Rekening perlu diverifikasi')
            ->assertSeeText('Satu rekening menunggu verifikasi.');
    }

    public function test_user_cannot_read_another_users_notification(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $owner = User::factory()->create(['company_id' => $company->id, 'status' => 'active']);
        $other = User::factory()->create(['company_id' => $company->id, 'status' => 'active']);
        $notification = app(NotificationService::class)->notifyUser(
            $owner,
            'team.role_changed',
            'Role diperbarui',
            'Role Anda diperbarui.',
        );

        $this->actingAs($other)
            ->patch(route('notifications.read', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
