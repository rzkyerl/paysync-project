import { chromium } from 'playwright-core';

const browser = await chromium.launch({
    executablePath: process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
});
const baseURL = process.env.PAYSYNC_BASE_URL || 'http://127.0.0.1:8765';

function assert(condition, message) {
    if (!condition) throw new Error(message);
}

try {
    const demoContext = await browser.newContext();
    const demo = await demoContext.newPage();
    await demo.goto(`${baseURL}/login`);
    await demo.locator('#email').fill('rina.hr@paysync.test');
    await demo.locator('#password').fill('password');
    await Promise.all([
        demo.waitForURL('**/app/dashboard-hr'),
        demo.getByRole('button', { name: 'Masuk ke Workspace' }).click(),
    ]);

    let body = await demo.locator('body').textContent();
    assert(body.includes('Mode Demo'), 'Demo banner is missing');
    assert(body.includes('PT Pay Sync'), 'Dynamic company name is missing');

    await demo.goto(`${baseURL}/employees`);
    body = await demo.locator('body').textContent();
    assert(body.includes('DEMO-0001') && body.includes('Rina Maharani'), 'Demo employees are not rendered from the database');

    await demo.goto(`${baseURL}/app/payroll`);
    body = await demo.locator('body').textContent();
    assert(body.includes('Needs Review') && body.includes('Juli 2026'), 'Demo review payroll is not rendered');
    await demoContext.close();

    const newContext = await browser.newContext();
    const fresh = await newContext.newPage();
    const email = `phase4-${Date.now()}@example.test`;
    await fresh.goto(`${baseURL}/register`);
    await fresh.locator('[name="name"]').fill('New User Phase Four');
    await fresh.locator('[name="email"]').fill(email);
    await fresh.locator('[name="company"]').fill('Perusahaan Baru');
    await fresh.locator('[name="company_size"]').selectOption({ index: 1 });
    await fresh.locator('[name="password"]').fill('password123');
    await fresh.locator('[name="password_confirmation"]').fill('password123');
    await fresh.locator('[name="terms"]').check();
    await Promise.all([
        fresh.waitForURL('**/onboarding'),
        fresh.getByRole('button', { name: 'Buat Akun Perusahaan' }).click(),
    ]);

    for (const [path, expected] of [
        ['/app/dashboard-hr', 'Belum ada karyawan'],
        ['/app/dashboard-finance', 'Belum ada payroll yang perlu disetujui'],
        ['/app/dashboard-employee', 'Profil karyawan belum terhubung'],
        ['/employees', 'Belum ada karyawan'],
        ['/app/payroll', 'Belum ada payroll'],
    ]) {
        const response = await fresh.goto(`${baseURL}${path}`);
        assert(response?.status() === 200, `${path} returned HTTP ${response?.status()}`);
        assert((await fresh.locator('body').textContent()).includes(expected), `${path} missing empty state: ${expected}`);
    }

    await newContext.close();
    console.log(JSON.stringify({ status: 'passed', demo: true, newUserEmptyStates: 5 }, null, 2));
} catch (error) {
    console.error(JSON.stringify({ status: 'failed', error: error.message }, null, 2));
    process.exitCode = 1;
} finally {
    await browser.close();
}
