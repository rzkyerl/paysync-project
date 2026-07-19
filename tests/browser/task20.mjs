import { chromium } from 'playwright-core';

const baseURL = process.env.PAYSYNC_BASE_URL || 'http://127.0.0.1:8765';
const executablePath = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const results = [];

function assert(condition, message) {
    if (!condition) throw new Error(message);
}

async function check(name, callback) {
    await callback();
    results.push({ name, status: 'passed' });
}

async function login(browser, email, dashboardPath) {
    const context = await browser.newContext({ baseURL, viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();
    await page.goto('/login');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill('password');
    await Promise.all([
        page.waitForURL(url => url.pathname === dashboardPath),
        page.getByRole('button', { name: 'Masuk ke Workspace' }).click(),
    ]);
    return { context, page };
}

const browser = await chromium.launch({ executablePath, headless: true });

try {
    const hr = await login(browser, 'hr@paysync.test', '/app/dashboard-hr');

    await check('20.2 sidebar collapse persists after reload', async () => {
        const before = await hr.page.evaluate(() => localStorage.getItem('sidebar-collapsed'));
        await hr.page.locator('.sidebar-toggle').click();
        const after = await hr.page.evaluate(() => localStorage.getItem('sidebar-collapsed'));
        assert(before !== after, 'Sidebar state did not change in localStorage');
        await hr.page.reload();
        await hr.page.waitForSelector('aside.sidebar');
        const collapsed = await hr.page.locator('aside.sidebar').evaluate(el => el.classList.contains('collapsed'));
        assert(collapsed === (after === 'true'), 'Sidebar state was not restored after reload');
    });

    await check('20.3 role dashboards and isolation', async () => {
        assert((await hr.page.locator('h1').first().textContent()).includes('Dashboard HR'), 'HR dashboard not rendered');
        assert((await hr.page.request.get('/app/dashboard-finance')).status() === 403, 'HR accessed finance dashboard');

        const finance = await login(browser, 'finance@paysync.test', '/app/dashboard-finance');
        assert((await finance.page.locator('h1').first().textContent()).includes('Dashboard Finance'), 'Finance dashboard not rendered');
        assert((await finance.page.request.get('/app/dashboard-hr')).status() === 403, 'Finance accessed HR dashboard');
        await finance.context.close();

        const employee = await login(browser, 'employee@paysync.test', '/app/dashboard-employee');
        assert((await employee.page.locator('body').textContent()).includes('Budi Santoso'), 'Employee-owned data not rendered');
        assert((await employee.page.request.get('/employees')).status() === 403, 'Employee accessed employee management');
        await employee.context.close();
    });

    await check('20.4 search, filter, sort, and pagination preserve query state', async () => {
        await hr.page.goto('/employees?search=Budi&department=Engineering&status=active&sort=name&dir=desc&per_page=15');
        const url = new URL(hr.page.url());
        for (const [key, value] of Object.entries({ search: 'Budi', department: 'Engineering', status: 'active', sort: 'name', dir: 'desc', per_page: '15' })) {
            assert(url.searchParams.get(key) === value, `Missing query state: ${key}`);
        }
        assert((await hr.page.locator('tbody').textContent()).includes('Budi'), 'Search result did not contain Budi');
    });

    await check('20.5, 20.6, 20.9 confirm modal, Escape, Enter, and success toast', async () => {
        await hr.page.goto('/employees');
        const rowsBefore = await hr.page.locator('tbody tr').count();
        await hr.page.getByRole('button', { name: 'Hapus' }).first().click();
        await hr.page.locator('.modal-overlay').waitFor({ state: 'visible' });
        await hr.page.keyboard.press('Escape');
        await hr.page.locator('.modal-overlay').waitFor({ state: 'hidden' });
        assert(await hr.page.locator('tbody tr').count() === rowsBefore, 'Escape unexpectedly deleted an employee');

        await hr.page.getByRole('button', { name: 'Hapus' }).first().click();
        await hr.page.locator('.modal-overlay').waitFor({ state: 'visible' });
        await hr.page.keyboard.press('Enter');
        await hr.page.waitForURL(url => url.pathname === '/employees');
        await hr.page.locator('.toast-success').waitFor({ state: 'visible' });
        assert((await hr.page.locator('.toast-success').textContent()).includes('berhasil'), 'Success toast was not shown');

        await hr.page.evaluate(() => Alpine.store('toast').add('error', 'Simulasi aksi gagal', 1000));
        await hr.page.locator('.toast-error').waitFor({ state: 'visible' });
        assert((await hr.page.locator('.toast-error').textContent()).includes('gagal'), 'Error toast was not shown');
    });

    await check('20.7 no-results empty state', async () => {
        await hr.page.goto('/employees?search=__tidak_ada_hasil__');
        const body = (await hr.page.locator('body').textContent()).toLowerCase();
        assert(body.includes('tidak ditemukan') || body.includes('tidak ada hasil'), 'No-results empty state was not shown');
    });

    await check('20.8 responsive layout at 375px', async () => {
        await hr.page.setViewportSize({ width: 375, height: 812 });
        await hr.page.goto('/employees');
        const layout = await hr.page.evaluate(() => {
            const shell = document.querySelector('.app-shell');
            const tableWrap = document.querySelector('.table-wrap');
            return {
                columns: getComputedStyle(shell).gridTemplateColumns,
                viewport: document.documentElement.clientWidth,
                bodyWidth: document.body.scrollWidth,
                tableOverflow: tableWrap ? getComputedStyle(tableWrap).overflowX : null,
                overflowElements: [...document.body.querySelectorAll('*')]
                    .map(element => ({
                        element: `${element.tagName.toLowerCase()}.${element.className || ''}`,
                        right: Math.round(element.getBoundingClientRect().right),
                        width: Math.round(element.getBoundingClientRect().width),
                    }))
                    .filter(item => item.right > document.documentElement.clientWidth + 1)
                    .slice(0, 8),
            };
        });
        assert(!layout.columns.includes('260px'), 'Desktop sidebar column remained active at 375px');
        assert(layout.bodyWidth <= layout.viewport, `Sidebar/layout caused horizontal page overflow: ${JSON.stringify(layout)}`);
        assert(layout.tableOverflow === 'auto', 'Table wrapper is not horizontally scrollable');
    });

    await hr.context.close();
    console.log(JSON.stringify({ status: 'passed', checks: results }, null, 2));
} catch (error) {
    console.error(JSON.stringify({ status: 'failed', checks: results, error: error.message }, null, 2));
    process.exitCode = 1;
} finally {
    await browser.close();
}
