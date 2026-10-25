const {spawnSync}=require('node:child_process');
const assert=require('node:assert/strict');
const {chromium}=require('playwright');
const base='http://127.0.0.1:8089/index.php';
function fixture(mode){const r=spawnSync('C:/php/php.exe',['tests/ui-fixture.php',mode],{encoding:'utf8'});assert.equal(r.status,0,r.stderr);}
(async()=>{
fixture('populate');const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
let checks=0;
try {
 const page=await browser.newPage({viewport:{width:1024,height:600}});
 for(const lang of ['zh-Hant','ja','en']){
  await page.goto(base+'?lang='+lang+'&status=active&category=reading');
  assert.equal(await page.locator('.save-card').count(),12);checks++;
  await page.locator('.pagination a').last().click();
  assert.equal(await page.locator('.save-card').count(),1);checks++;
  const u=new URL(page.url());for(const [k,v] of Object.entries({lang,status:'active',category:'reading',page:'2'})){assert.equal(u.searchParams.get(k),v);checks++;}
  await page.goto(base+'?lang='+lang+'&status=paused&category=game');
  assert.equal(await page.locator('.save-card h3').textContent(),'Paused game');checks++;
  await page.goto(base+'?lang='+lang+'&status=completed&category=reading');
  assert.equal(await page.locator('.filter-empty').count(),1);checks++;
  await page.locator('.sidebar-bottom a').scrollIntoViewIfNeeded();
  await page.locator('.sidebar-bottom a').click();
  assert.equal(new URL(page.url()).searchParams.get('view'),'help');checks++;
  assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));checks++;
 }
 console.log('PASS browser filters, pagination, short window: '+checks+' checks');
}finally{await browser.close();fixture('cleanup');}
})().catch(e=>{console.error(e);process.exitCode=1;});
