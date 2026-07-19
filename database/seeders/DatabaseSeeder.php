<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * All demo data (company, employees, users, payrolls + payroll items)
     * is managed exclusively by DemoDataSeeder — single source of truth
     * for multi-tenant correctness.
     */
    public function run(): void
    {
        $this->call(DemoDataSeeder::class);
    }
}
