# PaySync

PaySync is a payroll management web application that helps companies manage employee data, attendance, payroll calculations, approvals, disbursements, and reports in one workflow. It provides dedicated interfaces and permissions for HR, Finance, employees, and administrators.

## Key features

- Dedicated dashboards for HR, Finance, and employees
- Employee records and bank account verification
- CSV imports for employee and attendance data
- Salary, allowance, overtime, lateness, and absence calculations
- End-to-end payroll workflow from draft and calculation to approval and disbursement
- Payroll anomaly detection and acknowledgement
- Payroll reconciliation and bank transfer file generation
- Employee payslip access
- Payroll reports and detailed breakdowns
- Email-based team invitations and role-based access control
- Company onboarding and settings audit logs

## User roles

| Role | Primary access |
| --- | --- |
| `super_admin` | Manage the company, team, settings, and the entire payroll process |
| `hr_manager` | Manage employees, attendance, and payroll preparation |
| `finance_manager` | Review, approve, reconcile, and disburse payroll |
| `employee` | View a personal dashboard and payslips |

## Tech stack

- PHP 8.3 and Laravel 13
- SQLite for local development, with MySQL support through environment configuration
- Blade, Alpine.js, and Tailwind CSS 4
- Vite 8 for frontend asset builds
- PHPUnit for automated testing
- Resend for email delivery

## Local development

Make sure PHP 8.3, Composer, Node.js, and npm are installed.

```bash
git clone https://github.com/rzkyerl/paysync-project.git
cd paysync-project
composer run setup
php artisan db:seed
composer run dev
```

Open the application at `http://localhost:8000`.

The `composer run setup` command installs dependencies, creates the `.env` file, generates the application key, runs database migrations, and builds the frontend assets. The default local configuration uses SQLite.

## Development accounts

After running `php artisan db:seed`, the following accounts are available with the password `password`:

| Role | Email |
| --- | --- |
| Super Admin | `ceo@paysync.test` |
| HR Manager | `hr@paysync.test` |
| Finance Manager | `finance@paysync.test` |
| Employee | `employee@paysync.test` |

> These accounts are intended for local development only. Never use the default credentials in production.

## Email configuration

By default, outgoing emails are written to the application log. To send team invitations through an email provider, configure the `MAIL_*` variables and provider credentials in `.env`.

## Useful commands

```bash
# Run the application, queue worker, log viewer, and Vite
composer run dev

# Run the automated test suite
composer test

# Build production frontend assets
npm run build

# Run database migrations
php artisan migrate
```

## Payroll workflow

1. HR adds or imports employee records.
2. HR creates a payroll period and imports attendance data.
3. PaySync calculates earnings and deductions and flags anomalies.
4. The payroll is submitted to Finance for review and approval.
5. Finance reconciles the payroll, downloads the bank transfer file, and records the disbursement.
6. Employees can access their payslips after payroll is completed.

## License

This project is available under the [MIT License](https://opensource.org/licenses/MIT).
