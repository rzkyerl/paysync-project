<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests tambahan untuk NotificationController.
 * Melengkapi NotificationTest.php yang sudah ada.
 */
class NotificationControllerAdvancedTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Acme Corp']);
        $this->user    = User::factory()->create([
            'role'       => 'hr_manager',
            'status'     => 'active',
            'company_id' => $this->company->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // read — idempotency
    // -----------------------------------------------------------------------

    public function test_reading_already_read_notification_does_not_update_read_at(): void
    {
        $readAt = now()->subHour();
        $notification = UserNotification::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
            'type'       => 'test',
            'title'      => 'Test',
            'message'    => 'Already read',
            'action_url' => '/employees',
            'read_at'    => $readAt,
        ]);

        $this->actingAs($this->user)
            ->patch(route('notifications.read', $notification))
            ->assertRedirect('/employees');

        // read_at tidak boleh berubah
        $this->assertEquals(
            $readAt->timestamp,
            $notification->fresh()->read_at->timestamp
        );
    }

    public function test_null_action_url_falls_back_to_dashboard(): void
    {
        $notification = UserNotification::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
            'type'       => 'test',
            'title'      => 'Test',
            'message'    => 'No URL',
            'action_url' => null,
        ]);

        $this->actingAs($this->user)
            ->patch(route('notifications.read', $notification))
            ->assertRedirect('/app/dashboard-hr');
    }

    public function test_double_slash_action_url_falls_back_to_dashboard(): void
    {
        $notification = UserNotification::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
            'type'       => 'test',
            'title'      => 'Test',
            'message'    => 'Double slash',
            'action_url' => '//evil.com/phishing',
        ]);

        $this->actingAs($this->user)
            ->patch(route('notifications.read', $notification))
            ->assertRedirect('/app/dashboard-hr');
    }

    // -----------------------------------------------------------------------
    // readAll
    // -----------------------------------------------------------------------

    public function test_read_all_marks_all_unread_notifications_as_read(): void
    {
        UserNotification::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
            'type'       => 'test1',
            'title'      => 'Notif 1',
            'message'    => 'Message 1',
        ]);
        UserNotification::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
            'type'       => 'test2',
            'title'      => 'Notif 2',
            'message'    => 'Message 2',
        ]);

        $this->actingAs($this->user)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $this->user->unreadNotifications()->count());
    }

    public function test_read_all_does_not_affect_already_read_notifications(): void
    {
        $readAt = now()->subHour();
        $read = UserNotification::create([
            'company_id' => $this->company->id,
            'user_id'    => $this->user->id,
            'type'       => 'already_read',
            'title'      => 'Already Read',
            'message'    => 'Read notification',
            'read_at'    => $readAt,
        ]);

        $this->actingAs($this->user)
            ->post(route('notifications.read-all'));

        // read_at tidak berubah (nilainya masih $readAt)
        $this->assertEquals(
            $readAt->timestamp,
            $read->fresh()->read_at->timestamp
        );
    }
}
