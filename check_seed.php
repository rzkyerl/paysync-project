<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'PayrollItems: ' . App\Models\PayrollItem::count() . PHP_EOL;
echo 'AttendanceRecords: ' . App\Models\AttendanceRecord::count() . PHP_EOL;
echo PHP_EOL;

$payrolls = App\Models\Payroll::with('payrollItems')->get();
foreach ($payrolls as $p) {
    echo $p->period . ' | ' . $p->status
        . ' | items=' . $p->payrollItems->count()
        . ' | gross=Rp ' . number_format($p->gross_total, 0, ',', '.')
        . ' | net=Rp ' . number_format($p->net_total, 0, ',', '.')
        . ' | anomaly=' . $p->anomaly_count
        . PHP_EOL;
}

echo PHP_EOL . '--- PayrollItems sample (periode terbaru) ---' . PHP_EOL;
$latest = App\Models\Payroll::latest('period')->first();
if ($latest) {
    foreach ($latest->payrollItems()->with('employee')->get() as $item) {
        echo $item->employee->name
            . ' | gross=Rp ' . number_format($item->gross_pay, 0, ',', '.')
            . ' | net=Rp ' . number_format($item->net_pay, 0, ',', '.')
            . ' | anomaly=' . ($item->has_anomaly ? json_decode($item->anomaly_type, true)[0] : '-')
            . ' | status=' . $item->status
            . PHP_EOL;
    }
}
