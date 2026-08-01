<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_logger_records_actor_company_subject_metadata_and_ip(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $request = Request::create('/activity', 'POST', server: ['REMOTE_ADDR' => '10.20.30.40']);
        $request->setUserResolver(fn () => $user);

        ActivityLogger::record(
            $request,
            'payroll',
            'payroll.approved',
            'Payroll Juli 2026 disetujui',
            'payroll',
            42,
            ['period' => '2026-07'],
        );

        $log = ActivityLog::firstOrFail();

        $this->assertSame($company->id, $log->company_id);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('payroll.approved', $log->event);
        $this->assertSame('payroll', $log->subject_type);
        $this->assertSame(42, $log->subject_id);
        $this->assertSame('2026-07', $log->meta['period']);
        $this->assertSame('10.20.30.40', $log->ip_address);
        $this->assertNotNull($log->occurred_at);
    }

    public function test_system_activity_can_take_company_from_metadata(): void
    {
        $company = Company::create(['name' => 'Acme']);

        ActivityLogger::record(
            null,
            'team',
            'team.activated',
            'Anggota tim mengaktifkan akun',
            'user',
            10,
            ['company_id' => $company->id],
        );

        $this->assertDatabaseHas('activity_logs', [
            'company_id' => $company->id,
            'user_id' => null,
            'event' => 'team.activated',
        ]);
    }
}
