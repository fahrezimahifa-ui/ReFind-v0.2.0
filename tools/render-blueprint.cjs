// Render/inspect local documentation only. No app requests or database access.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const {pathToFileURL} = require('node:url');
const {spawnSync} = require('node:child_process');
const root = path.resolve(__dirname, '..');
const docs = path.join(root, 'docs');
const diagrams = path.join(docs, 'diagrams', 'exports');
const wireframes = path.join(docs, 'wireframes', 'exports');
const modulePath = process.env.REFIND_PLAYWRIGHT_MODULE || path.join(os.tmpdir(), 'opencode', 'refind-multipage-check', 'node_modules', 'playwright-core');
const {chromium} = require(modulePath);
const chrome = process.env.REFIND_CHROME || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

(async () => {
  assert.ok(fs.existsSync(diagrams), 'Run build-blueprint.py first');
  assert.ok(fs.existsSync(wireframes), 'Run build-blueprint.py first');
  const manifest = JSON.parse(fs.readFileSync(path.join(docs, 'diagrams', 'manifest.json'), 'utf8'));
  const browser = await chromium.launch({executablePath: chrome, headless: true});
  const context = await browser.newContext({viewport: {width: 1480, height: 1100}, deviceScaleFactor: 1, offline: true});
  const page = await context.newPage();
  const browserErrors = [];
  const networkRequests = [];
  page.on('pageerror', error => browserErrors.push(String(error)));
  page.on('request', request => { if (/^https?:/.test(request.url())) networkRequests.push(request.url()); });
const result = {scope: 'B06 as-built diagram and conceptual wireframe render; application E2E is in separate runtime suites', browserVersion: browser.version(), diagrams: [], wireframes: [], browserErrors, networkRequests};
  try {
    for (const artifact of manifest.artifacts) {
      for (const key of artifact.sheets) {
        let svg = fs.readFileSync(path.join(diagrams, key + '.svg'), 'utf8');
        svg = svg.replace(/<\?xml[\s\S]*?\?>/g, '').replace(/<!DOCTYPE[\s\S]*?>/g, '');
        await page.setContent('<!doctype html><html><head><meta charset="utf-8"><style>html,body{margin:0;padding:0;background:white}svg{display:block}</style></head><body>' + svg + '</body></html>');
        await page.evaluate(() => document.fonts.ready);
        const size = await page.locator('svg').evaluate(el => {
          const rect = el.getBoundingClientRect();
          const clippedTexts = [...el.querySelectorAll('text')].filter(text => {
            const r = text.getBoundingClientRect();
            return r.left < rect.left - 1 || r.top < rect.top - 1 || r.right > rect.right + 1 || r.bottom > rect.bottom + 1;
          }).map(text => text.textContent);
          return {width: Math.ceil(rect.width), height: Math.ceil(rect.height), textCount: el.querySelectorAll('text').length, clippedTexts};
        });
        assert.ok(size.width > 300 && size.height > 200, key + ' dimensions');
        assert.ok(size.width < 14000 && size.height < 14000, key + ' export dimensions exceed supported range');
        assert.deepEqual(size.clippedTexts, [], key + ' text clipped by canvas');
        await page.setViewportSize({width: size.width, height: size.height});
        await page.screenshot({path: path.join(diagrams, key + '.png'), fullPage: true, timeout: 120000});
        await page.pdf({path: path.join(diagrams, key + '.pdf'), width: (size.width + 1) + 'px', height: (size.height + 1) + 'px', margin: {top: 0, right: 0, bottom: 0, left: 0}, printBackground: true, timeout: 120000});
        result.diagrams.push({id: key, ...size});
        console.log('Rendered diagram ' + key + ': ' + size.width + 'x' + size.height);
      }
    }

    await page.setViewportSize({width: 1480, height: 1100});
    await page.goto(pathToFileURL(path.join(docs, 'wireframes', 'index.html')).href);
    await page.evaluate(() => document.fonts.ready);
    assert.equal(await page.locator('.board').count(), 8);
    assert.equal(await page.locator('.phone input,.phone button,.phone form').count(), 0, 'Wireframes must remain clearly static illustrations');
    const xmlEscape=value=>String(value).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    const wireframePages=[];
    for (const board of await page.locator('.board').all()) {
      const key = await board.getAttribute('data-board');
      const geometry = await board.evaluate(el => {
        const b = el.getBoundingClientRect();
        const frames = [...el.querySelectorAll('.phone,.desktop')].map(frame => {
          const r = frame.getBoundingClientRect();
          const content = frame.querySelector('.screen,.desktop-main');
          const last = content?.lastElementChild;
          if (content) content.scrollTop = content.scrollHeight;
          const after = last?.getBoundingClientRect();
          const area = content?.getBoundingClientRect();
          const reachable = !last || after.bottom <= area.bottom + 2;
          if (content) content.scrollTop = 0;
          return {type: frame.classList.contains('phone') ? 'mobile' : 'desktop', width: Math.round(r.width), height: Math.round(r.height),
            withinBoard: r.left >= b.left && r.right <= b.right && r.top >= b.top && r.bottom <= b.bottom,
            horizontalOverflow: frame.scrollWidth > frame.clientWidth + 1,
            contentScrollable: !!content && getComputedStyle(content).overflowY === 'auto', lastElementReachable: reachable};
        });
        return {width: Math.round(b.width), height: Math.round(b.height), frames};
      });
      for (const frame of geometry.frames) {
        assert.ok(frame.withinBoard, key + ' frame is clipped by board');
        assert.equal(frame.horizontalOverflow, false, key + ' frame horizontal overflow');
        assert.ok(frame.lastElementReachable, key + ' last control is unreachable');
      }
      await board.screenshot({path: path.join(wireframes, key + '.png'), timeout: 120000});
      const components=await board.evaluate(el=>{
        const base=el.getBoundingClientRect();
        const selector='.board-head h2,.board-head p,.target,.phone,.desktop,.topnav,.screen,.desktop-main,.card,.mini-card,.activity,.detail-panel,.callout,.upload,.photo,.mini-photo,.evidence > div,h1,h2,h3,p,label,.eyebrow,.small,.meta,.button,.input,.touch,.tab,.chip,.search-box,.brand,.desktop-nav,.text-link,.avatar';
        return [...el.querySelectorAll(selector)].flatMap((node,index)=>{
          const r=node.getBoundingClientRect(),clip=node.closest('.screen,.desktop-main')?.getBoundingClientRect();
          const x=Math.max(r.left,base.left,clip?.left??base.left),y=Math.max(r.top,base.top,clip?.top??base.top);
          const right=Math.min(r.right,base.right,clip?.right??base.right),bottom=Math.min(r.bottom,base.bottom,clip?.bottom??base.bottom);
          if(right-x<2||bottom-y<2)return[];
          const style=getComputedStyle(node),isContainer=node.matches('.phone,.desktop,.topnav,.screen,.desktop-main,.card,.mini-card,.activity,.detail-panel,.callout,.upload');
          return [{id:index,x:x-base.left,y:y-base.top,width:right-x,height:bottom-y,text:isContainer?'':node.innerText.trim(),background:style.backgroundColor,color:style.color,border:style.borderTopWidth==='0px'?'none':style.borderTopColor,rounded:style.borderTopLeftRadius!=='0px',font:parseFloat(style.fontSize),bold:Number(style.fontWeight)>=600}];
        });
      });
      const hex=colour=>{const parts=colour.match(/[\d.]+/g);return !parts||parts.length===4&&Number(parts[3])===0?'none':'#'+parts.slice(0,3).map(n=>Math.round(Number(n)).toString(16).padStart(2,'0')).join('');};
      const cells=components.map(c=>`<mxCell id="component-${c.id}" value="${xmlEscape(c.text)}" vertex="1" parent="1" data-kind="wireframe-component" style="rounded=${c.rounded?1:0};whiteSpace=wrap;html=0;fillColor=${hex(c.background)};strokeColor=${hex(c.border)};fontColor=${hex(c.color)};fontSize=${c.font};fontStyle=${c.bold?1:0};align=left;verticalAlign=middle;spacing=5;"><mxGeometry x="${c.x.toFixed(2)}" y="${c.y.toFixed(2)}" width="${c.width.toFixed(2)}" height="${c.height.toFixed(2)}" as="geometry"/></mxCell>`).join('');
      wireframePages.push(`<diagram id="${key}" name="${key} · Conceptual UI wireframe"><mxGraphModel grid="1" page="1" pageWidth="1420" pageHeight="1040"><root><mxCell id="0"/><mxCell id="1" parent="0"/>${cells}</root></mxGraphModel></diagram>`);
      const sourceDirectory=path.join(docs,'wireframes','sources');fs.mkdirSync(sourceDirectory,{recursive:true});
      fs.writeFileSync(path.join(sourceDirectory,key+'.drawio'),'<mxfile host="app.diagrams.net" agent="ReFind B06 editable wireframe components">'+wireframePages.at(-1)+'</mxfile>\n');
      geometry.editableComponents=components.length;
      result.wireframes.push({id: key, ...geometry});
      console.log('Rendered wireframe ' + key);
    }
    await page.pdf({path: path.join(wireframes, 'WIREFRAMES.pdf'), preferCSSPageSize: true, printBackground: true, timeout: 120000});
    fs.writeFileSync(path.join(docs,'wireframes','sources','WIREFRAMES.drawio'),'<mxfile host="app.diagrams.net" agent="ReFind B06 editable wireframe components">'+wireframePages.join('')+'</mxfile>\n');
    assert.deepEqual(browserErrors, []);
    assert.deepEqual(networkRequests, []);
    fs.writeFileSync(path.join(docs, 'verification', 'render-results.json'), JSON.stringify(result, null, 2) + '\n');
    const assembled = spawnSync(process.env.REFIND_PYTHON || 'python', [path.join(__dirname, 'build-blueprint.py'), '--assemble'], {encoding: 'utf8'});
    if (assembled.stdout) process.stdout.write(assembled.stdout);
    if (assembled.stderr) process.stderr.write(assembled.stderr);
    assert.equal(assembled.status, 0, 'PDF assembly failed');
    console.log('Documentation render complete; this tool does not execute application E2E.');
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
