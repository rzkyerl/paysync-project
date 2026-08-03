<?php

namespace Tests\Feature;

use App\Mail\TeamInvitationMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Tests untuk TeamInvitationMail.
 */
class TeamInvitationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_envelope_has_correct_subject(): void
    {
        $company = Company::create(['name' => 'Acme Corp']);
        $user    = User::factory()->create([
            'company_id' => $company->id,
            'status'     => 'invited',
        ]);

        $mail     = new TeamInvitationMail($user);
        $envelope = $mail->envelope();

        $this->assertSame('Undangan bergabung ke PaySync', $envelope->subject);
    }

    public function test_content_uses_correct_view(): void
    {
        $company = Company::create(['name' => 'Acme Corp']);
        $user    = User::factory()->create([
            'company_id' => $company->id,
            'status'     => 'invited',
        ]);

        $mail    = new TeamInvitationMail($user);
        $content = $mail->content();

        $this->assertSame('emails.team-invitation', $content->view);
    }

    public function test_mail_is_sent_when_team_member_invited(): void
    {
        Mail::fake();

        $company    = Company::create(['name' => 'Acme Corp']);
        $superAdmin = User::factory()->create([
            'role'       => 'super_admin',
            'status'     => 'active',
            'company_id' => $company->id,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('team.invite'), [
                'name'  => 'New HR',
                'email' => 'newhr@example.com',
                'role'  => 'hr_manager',
            ]);

        Mail::assertSent(TeamInvitationMail::class, function ($mail) {
            return $mail->hasTo('newhr@example.com')
                && $mail->member->email === 'newhr@example.com';
        });
    }

    public function test_mail_member_property_is_set_correctly(): void
    {
        $company = Company::create(['name' => 'Acme Corp']);
        $user    = User::factory()->create([
            'name'       => 'Budi HR',
            'email'      => 'budi@example.com',
            'company_id' => $company->id,
            'status'     => 'invited',
        ]);

        $mail = new TeamInvitationMail($user);

        $this->assertSame($user->id, $mail->member->id);
        $this->assertSame('budi@example.com', $mail->member->email);
    }
}
