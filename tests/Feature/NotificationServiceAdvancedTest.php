<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests tambahan untuk NotificationService.
 * Melengkapi NotificationTest.php yang sudah ada.
 */
class NotificationServiceAdvancedTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationService::class);
        $this->company = Company::create(['name' => 'Acme Corp']);
    }

    // -----------------------------------------------------------------------
    // notifyRoles — exceptUserId
    // -----------------------------------------------------------------------

    public function test_notify_roles_excludes_except_user_id(): void
    {
        $hr1 = User::factory()->create([
            'company_id' => $this->company->id,
            'role'       => 'hr_manager',
            'status'     => 'active',
        ]);
        $hr2 = User::factory()->create([
            'company_id' => $this->company->id,
            'role'       => 'hr_manager',
            'status'     => 'active',
        ]);

        $this->service->notifyRoles(
            $this->company->id,
            ['hr_manager'],
            'test.type',
            'Test',
            'Test message',
            null,
            [],
            $hr1->id, // exclude hr1
        );

        $this->assertSame(1, UserNotification::count());
        $this->assertDatabaseHas('user_notifications', ['user_id' => $hr2->id]);
        $this->assertDatabaseMissing('user_notifications', ['user_id' => $hr1->id]);
    }

    public function test_notify_roles_only_sends_to_active_users(): void
    {
        User::factory()->create([
            'company_id' => $this->company->id,
            'role'       => 'hr_manager',
            'status'     => 'invited', // bukan active
        ]);

        $this->service->notifyRoles(
            $this->company->id,
            ['hr_manager'],
            'test.type',
            'Test',
            'Test message',
        );

        $this->assertSame(0, UserNotification::count());
    }

    public function test_notify_roles_does_nothing_when_no_matching_users(): void
    {
        $this->service->notifyRoles(
            $this->company->id,
            ['finance_manager'], // tidak ada finance_manager
            'test.type',
            'Test',
            'Message',
        );

        $this->assertSame(0, UserNotification::count());
    }

    // -----------------------------------------------------------------------
    // notifyUsers — deduplication
    // -----------------------------------------------------------------------

    public function test_notify_users_deduplicates_same_user(): void
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status'     => 'active',
        ]);

        // Kirim user yang sama dua kali dalam koleksi
        $users = collect([$user, $user]);

        $this->service->notifyUsers(
            $users,
            'test.type',
            'Test',
            'Message',
        );

        $this->assertSame(1, UserNotification::count());
    }

    public function test_notify_users_with_empty_collection_does_not_insert(): void
    {
        $this->service->notifyUsers(
            collect(),
            'test.type',
            'Test',
            'Message',
        );

        $this->assertSame(0, UserNotification::count());
    }

    // -----------------------------------------------------------------------
    // notifyUser — return value
    // -----------------------------------------------------------------------

    public function test_notify_user_returns_user_notification_with_correct_fields(): void
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status'     => 'active',
        ]);

        $notification = $this->service->notifyUser(
            $user,
            'payroll.approved',
            'Payroll Disetujui',
            'Payroll Juli 2026 telah disetujui.',
            '/app/payroll/1',
            ['payroll_id' => 1],
        );

        $this->assertInstanceOf(UserNotification::class, $notification);
        $this->assertSame($user->id, $notification->user_id);
        $this->assertSame('payroll.approved', $notification->type);
        $this->assertSame('Payroll Disetujui', $notification->title);
        $this->assertSame('/app/payroll/1', $notification->action_url);
        $this->assertSame(['payroll_id' => 1], $notification->data);
    }

    public function test_notify_user_with_null_action_url(): void
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status'     => 'active',
        ]);

        $notification = $this->service->notifyUser(
            $user,
            'test.type',
            'Test',
            'Message without URL',
        );

        $this->assertNull($notification->action_url);
    }
}
