const {spawnSync}=require('node:child_process');
const fs=require('node:fs');
const path=require('node:path');
const {chromium}=require('playwright');
const assert=require('node:assert/strict');
const base=process.env.RESTART_TEST_URL||'http://127.0.0.1:8089/index.php';
(async()=>{
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage();let checks=0;
for(const lang of ['zh-Hant','ja','en']) {
await page.goto(base+'?lang='+lang);
assert.equal(await page.locator('.welcome-empty').count(),1,'empty database should show welcome');checks++;
await page.goto(base+'?view=new&lang='+lang);
await page.locator('#title').fill('😀'.repeat(100));await page.locator('#progress').fill('進度 <b>安全</b> '+lang);
assert.equal(await page.locator('#title').inputValue(),'😀'.repeat(100),'100 Unicode title characters must fit');checks++;
await Promise.all([page.waitForURL(/view=detail/),page.locator('button[type=submit]').first().click()]);
const id=new URL(page.url()).searchParams.get('id');assert.ok(id);checks++;
await page.goto(base+'?lang='+lang);assert.equal(await page.locator('.resume-panel h3').textContent(),'😀'.repeat(100));checks++;
for(const width of [1440,1024,768,390,320]) {
await page.setViewportSize({width,height:900});
for(const view of ['list','detail','edit','help','delete']) {
await page.goto(base+'?lang='+lang+'&view='+view+'&id='+id);
assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),`${lang} ${view} ${width}: no horizontal overflow`);checks++;
assert.equal(await page.locator('script').count(),0);checks++;
if(width===390) {assert.ok(await page.locator('.main-nav .nav-item').first().evaluate(e=>parseFloat(getComputedStyle(e).fontSize)>=12),'navigation text at least12px');checks++;assert.ok(await page.locator('.sidebar-bottom a').isVisible(),'help visible on narrow window');checks++;}
}
}
await page.setViewportSize({width:1440,height:900});
await page.goto(base+'?view=detail&lang='+lang+'&id='+id);
await Promise.all([page.waitForNavigation(),page.locator('form:has(input[value="paused"]) button').click()]);
await page.goto(base+'?lang='+lang);assert.equal(await page.locator('.no-active').count(),1,'paused excluded from resume');checks++;
await page.goto(base+'?view=delete&lang='+lang+'&id='+id);
await Promise.all([page.waitForNavigation(),page.locator('button[type=submit]').click()]);
assert.equal(await page.locator('.welcome-empty').count(),1,'deleting last item returns welcome');checks++;
}
await browser.close();console.log('PASS browser: '+checks+' checks');
})().catch(e=>{console.error('FAIL browser:',e.message);process.exitCode=1;process.exit()});

