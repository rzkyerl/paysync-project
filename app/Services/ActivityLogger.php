<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;

/**
 * Pencatatan aktivitas terpusat.
 *
 * Cara pakai di controller:
 *
 *   app(ActivityLogger::class)->log($request, 'payroll', 'payroll.approved',
 *       'Payroll Juli 2026 disetujui', 'payroll', $payroll->id,
 *       ['period' => $payroll->period, 'net_total' => $payroll->net_total]
 *   );
 *
 * Atau lewat shortcut statis:
 *   ActivityLogger::record($request, 'employee', 'employee.created', 'Karyawan Budi ditambahkan');
 */
class ActivityLogger
{
    /**
     * Catat satu entri log aktivitas.
     *
     * @param  Request|null  $request  Request saat ini (untuk user & IP); null = system
     * @param  string  $category  Kategori: employee | payroll | attendance | team | settings
     * @param  string  $event  Kode event: employee.created, payroll.approved, …
     * @param  string  $desc  Deskripsi human-readable
     * @param  string|null  $subjType  Tipe subject model (e.g. "payroll", "employee")
     * @param  int|null  $subjId  ID subject model
     * @param  array|null  $meta  Data tambahan (JSON)
     */
    public function log(
        ?Request $request,
        string $category,
        string $event,
        string $desc,
        ?string $subjType = null,
        ?int $subjId = null,
        ?array $meta = null,
    ): ActivityLog {
        $user = $request?->user();
        $companyId = $user?->company_id;

        // Fallback: ambil company_id dari meta jika user tidak ada
        if (! $companyId && isset($meta['company_id'])) {
            $companyId = (int) $meta['company_id'];
        }

        return ActivityLog::create([
            'company_id' => $companyId,
            'user_id' => $user?->id,
            'category' => $category,
            'event' => $event,
            'description' => $desc,
            'subject_type' => $subjType,
            'subject_id' => $subjId,
            'meta' => $meta,
            'ip_address' => $request?->ip() ?? RequestFacade::ip(),
            'occurred_at' => now(),
        ]);
    }

    /**
     * Shortcut statis agar bisa dipanggil tanpa inject service.
     */
    public static function record(
        ?Request $request,
        string $category,
        string $event,
        string $desc,
        ?string $subjType = null,
        ?int $subjId = null,
        ?array $meta = null,
    ): ActivityLog {
        return app(self::class)->log($request, $category, $event, $desc, $subjType, $subjId, $meta);
    }
}
