import { chromium } from 'playwright-core';

const browser = await chromium.launch({
    executablePath: process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
});

const page = await browser.newPage();
const baseURL = process.env.PAYSYNC_BASE_URL || 'http://127.0.0.1:8765';

try {
    await page.goto(`${baseURL}/login`);
    await page.locator('#email').fill('rina.hr@paysync.test');
    await page.locator('#password').fill('password');
    await Promise.all([
        page.waitForURL('**/app/dashboard-hr'),
        page.getByRole('button', { name: 'Masuk ke Workspace' }).click(),
    ]);

    const paths = [
        '/app/dashboard-hr',
        '/app/dashboard-finance',
        '/app/dashboard-employee',
        '/employees',
        '/app/attendance',
        '/app/payroll',
        '/app/approval',
        '/app/payslips',
        '/app/disbursement',
        '/app/reconciliation',
        '/app/reports',
        '/app/settings',
        '/app/audit',
    ];

    const results = [];
    for (const path of paths) {
        const response = await page.goto(`${baseURL}${path}`);
        const status = response?.status() ?? 0;
        if (status !== 200) throw new Error(`${path} returned HTTP ${status}`);
        results.push({ path, status });
    }

    const invalid = await page.goto(`${baseURL}/app/invalid-page`);
    if (invalid?.status() !== 404) throw new Error('Invalid page did not return HTTP 404');

    console.log(JSON.stringify({ status: 'passed', pages: results, invalidPage: 404 }, null, 2));
} catch (error) {
    console.error(JSON.stringify({ status: 'failed', error: error.message }, null, 2));
    process.exitCode = 1;
} finally {
    await browser.close();
}
