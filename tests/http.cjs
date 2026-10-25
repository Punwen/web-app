const assert=require('node:assert/strict');
const base=process.env.RESTART_TEST_URL||'http://127.0.0.1:8089/index.php';
let cookie='',checks=0;
async function req(query='',data=null){const r=await fetch(base+query,{method:data?'POST':'GET',headers:{cookie,...(data?{'Content-Type':'application/x-www-form-urlencoded'}:{})},body:data?new URLSearchParams(data):undefined,redirect:'manual'});const c=r.headers.get('set-cookie');if(c)cookie=c.split(';')[0];return {status:r.status,text:await r.text(),location:r.headers.get('location')};}
function ok(value,msg){assert.ok(value,msg);checks++;}
function hidden(text,name){return text.match(new RegExp('name="'+name+'" value="([^"]*)"'))?.[1]??'';}
function fields(text){return {token:hidden(text,'token'),submission:hidden(text,'submission'),version:hidden(text,'version')};}
const valid={title:'閱讀 📖 <script>alert(1)</script>',category:'reading',status:'active',progress:'第十頁',next_step:'十一頁',memo:'日本語'};
(async()=>{
let r=await req('?view=new&lang=zh-Hant');ok(r.status===200,'new form must render');const first=fields(r.text);
r=await req('?lang=zh-Hant',{...valid,...first,action:'create',title:''});ok(r.status===422,'blank title rejected');ok(r.text.includes('第十頁'),'validation keeps submitted progress');
r=await req('?lang=zh-Hant',{...valid,...first,action:'create','title[]':'array'});ok(r.status===422,'array field rejected');
r=await req('?lang=zh-Hant',{...valid,...first,action:'create',token:'bad'});ok(r.status===403,'CSRF rejected');
r=await req('?lang=zh-Hant',{...valid,...first,action:'create'});ok(r.status===303,'create redirects');const target=r.location;const id=new URL(target,base).searchParams.get('id');ok(id,'saved ID returned');
let retry=await req('?lang=zh-Hant',{...valid,...first,action:'create'});ok(retry.status===303&&retry.location===target,'repeat submission same record');
r=await req('?view=detail&id='+id+'&lang=en');ok(r.status===200,'detail accessible');ok(r.text.includes('&lt;script&gt;'),'saved HTML escaped');ok(!r.text.includes('<script>'),'no executable product script');ok(r.text.includes('第十頁'),'user content shared across languages');
r=await req('?view=edit&id='+id+'&lang=ja');const edit=fields(r.text);ok(edit.version==='1','initial revision');
r=await req('?lang=ja',{...valid,...edit,action:'update',id,title:'Updated',status:'paused'});ok(r.status===303&&r.location.includes('lang=ja'),'update keeps locale');
let stale=await req('?view=edit&id='+id+'&lang=en');const staleFields=fields(stale.text);
let fresh=await req('?view=edit&id='+id+'&lang=en');r=await req('?lang=en',{...valid,...fields(fresh.text),action:'update',id,title:'Newest'});ok(r.status===303,'newer edit saved');
r=await req('?lang=en',{...valid,...staleFields,action:'update',id,title:'Stale text'});ok(r.status===409,'stale edit blocked');ok(r.text.includes('Stale text'),'conflict retains submitted text');
r=await req('?view=delete&id='+id+'&lang=en');const del=fields(r.text);ok(r.status===200&&r.text.includes('Newest'),'delete confirmation identifies record');
r=await req('?action=delete&id='+id);ok(r.status===200,'GET remains read-only');
r=await req('?view=detail&id='+id);ok(r.status===200,'GET did not delete');
r=await req('?lang=en',{...del,action:'delete',id});ok(r.status===303,'confirmed delete redirects');
r=await req('?view=detail&id='+id);ok(r.status===404,'deleted entry not found');
r=await req('?view=detail&id[]=1');ok(r.status===400,'malformed ID rejected');
r=await req('?view=unknown');ok(r.status===404,'unknown route404');
r=await req('?lang[]=en');ok(r.status===400,'malformed locale rejected');
console.log('PASS HTTP: '+checks+' assertions');
})().catch(e=>{console.error('FAIL HTTP:',e.message);process.exitCode=1});
