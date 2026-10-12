// Meaningful B06 seed/persistence verification, exact-owned fixture cleanup on _test only.
const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path');
const {execFileSync}=require('node:child_process');
const root=path.resolve(__dirname,'..'),target=path.resolve(root,'..','ReFind_0.2.0_Test'),php='C:\\xampp\\php\\php.exe';
const out=path.join(root,'docs','verification','b06');
assert.equal(JSON.parse(fs.readFileSync(path.join(target,'.refind-b02-test.json'))).source,root);
function driver(input){return JSON.parse(execFileSync(php,[path.join(target,'tools','b02-fixture.php')],{input:JSON.stringify(input),encoding:'utf8'}));}
function q(query,values=[]){return driver({action:'query',query,values}).rows;}
function run(tool){return JSON.parse(execFileSync(php,[path.join(target,'tools',tool)],{encoding:'utf8'}));}
function snapshot(){return Object.fromEntries(['users','items','claims','handovers','notifications','item_photos','claim_photos','item_matches','item_status_history'].map(t=>[t,JSON.stringify(q('SELECT * FROM '+t))]));}
const results=[];let ids=[],media=[],complete=false;
const receipts=['student-demo.json','expanded-demo.json'];
assert.equal(Number(q('SELECT COUNT(*) AS n FROM items')[0].n),0,'Seed QA needs an empty owned _test report dataset');
for(const name of receipts)assert.ok(!fs.existsSync(path.join(target,'storage','setup',name)),'Do not overwrite existing demo bookmarks');
const before=snapshot();
fs.mkdirSync(out,{recursive:true});
try{
    run('seed-student-demo.php');
    const first=run('seed-expanded-demo.php');
    ids=q('SELECT item_id FROM items').map(r=>Number(r.item_id));
    media=q('SELECT file_path FROM item_photos UNION ALL SELECT file_path FROM claim_photos').map(r=>r.file_path);
    assert.equal(first.reports_created,37);assert.equal(first.summary.database,'refind_db_0_2_0_test');
    assert.equal(first.summary.counts.items,40);assert.equal(first.summary.counts.claims,12);assert.equal(first.summary.counts.handovers,6);
    results.push('Real report/claim/review/pickup functions produce 40 reports, 12 claims and 6 handovers on isolated _test');
    for(const type of ['LOST','FOUND'])assert.equal(Number(first.summary.report_types.find(r=>r.item_type===type).count),20);
    assert.equal(first.summary.report_states.length,7);assert.equal(first.summary.claim_states.length,4);assert.equal(first.summary.pickup_states.length,3);
    results.push('20 Lost/20 Found, all seven report states, four claim states and all three pickup states are demonstrable');
    assert.equal(first.summary.counts.item_photos,60);assert.equal(first.summary.counts.claim_photos,12);
    assert.ok(first.summary.counts.notifications>50&&first.summary.counts.item_status_history>40);
    assert.equal(run('check-workflow.php').passed,true);
    results.push('Public/private stock collections, claimant proof, notifications/history and 17 invariants remain consistent');
    const seeded=snapshot(),repeat=run('seed-expanded-demo.php');assert.equal(repeat.reports_created,0);assert.deepEqual(snapshot(),seeded);
    results.push('Repeated seed adds no records/media/events and preserves the entire business snapshot');
    const editable=first.bookmarks['C19-lost'];q('UPDATE items SET title=? WHERE item_id=?',['User-edited synthetic example',editable]);
    const edited=snapshot();run('seed-expanded-demo.php');assert.deepEqual(snapshot(),edited);
    results.push('Explicitly edited bookmarked example is preserved on reseed');
    const removed=q('SELECT file_path FROM item_photos WHERE item_id=?',[editable]).map(r=>r.file_path);
    q('DELETE FROM notifications WHERE item_id=?',[editable]);q('DELETE FROM items WHERE item_id=?',[editable]);
    for(const relative of removed){assert.match(relative,/^(items|evidence)\/[a-f0-9]{32}\.jpg$/);fs.unlinkSync(path.join(target,'storage',relative));}
    const deleted=snapshot();run('seed-expanded-demo.php');assert.deepEqual(snapshot(),deleted);
    assert.equal(q('SELECT item_id FROM items WHERE item_id=?',[editable]).length,0);
    results.push('Deliberately deleted bookmarked report is not silently recreated');
    complete=true;
}finally{
    // Identity checking prevents cleanup touching later/manual fixtures.
    if(!ids.length)ids=q('SELECT item_id FROM items WHERE title LIKE ?',['Demo%']).map(r=>Number(r.item_id));
    for(const id of ids){const r=q('SELECT title,reporter_user_id FROM items WHERE item_id=?',[id])[0];if(r){assert.match(r.title,/^(Demo|User-edited synthetic example)/);assert.ok([1,2].includes(Number(r.reporter_user_id)));}}
    if(ids.length){const marks=ids.map(()=>'?').join(',');media.push(...q('SELECT file_path FROM item_photos WHERE item_id IN ('+marks+') UNION ALL SELECT p.file_path FROM claim_photos p JOIN claims c ON c.claim_id=p.claim_id WHERE c.item_id IN ('+marks+')',[...ids,...ids]).map(r=>r.file_path));q('DELETE FROM notifications WHERE item_id IN ('+marks+')',ids);q('DELETE FROM items WHERE item_id IN ('+marks+')',ids);}
    for(const relative of new Set(media)){assert.match(relative,/^(items|evidence|claims)\/[a-f0-9]{32}\.jpg$/);const file=path.join(target,'storage',relative);if(fs.existsSync(file))fs.unlinkSync(file);}
    for(const name of receipts){const file=path.join(target,'storage','setup',name);if(fs.existsSync(file)){assert.equal(JSON.parse(fs.readFileSync(file)).database,'refind_db_0_2_0_test');fs.unlinkSync(file);}}
    assert.deepEqual(snapshot(),before);const invariants=run('check-workflow.php');assert.equal(invariants.passed,true);
    results.push('Exact-ID/media cleanup restores the pre-seed test dataset and account/password snapshot');
    fs.writeFileSync(path.join(out,'seed-results.json'),JSON.stringify({scope:'B06 synthetic seed integration/idempotency on isolated _test only',results,passed:results.length,complete,invariants},null,2)+'\n');
}
assert.equal(complete,true);console.log('B06 demo seed: '+results.length+' scenario groups PASS; test dataset restored.');
