<?php
// index.php — single file app that reads CSVs and renders modern UI
header('Content-Type: text/html; charset=utf-8');

function read_csv_assoc($path){
  if (!file_exists($path)) return array();
  $out = array();
  if (($h = fopen($path, 'r')) !== false) {
    $headers = fgetcsv($h);
    if ($headers === false) { fclose($h); return array(); }
    while (($data = fgetcsv($h)) !== false) {
      $row = array();
      foreach($headers as $i => $key){
        if ($key === null || $key === '') continue;
        $row[$key] = array_key_exists($i, $data) ? $data[$i] : null;
      }
      $out[] = $row;
    }
    fclose($h);
  }
  return $out;
}

$main = read_csv_assoc(__DIR__ . '/main.csv');
$financials = read_csv_assoc(__DIR__ . '/financials.csv');
$reports = read_csv_assoc(__DIR__ . '/reports.csv');

$payload = array('main'=>$main, 'financials'=>$financials, 'reports'=>$reports);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>CFDATA</title>
  <style>
    :root{
      --bg:#000000;
      --card:#111218;
      --muted:#9aa0a6;
      --text:#f5f7fa;
      --good:#2ecc71;     /* green */
      --bad:#e74c3c;      /* red */
      --blue:#00aeef;     /* cristallblue */
      --violet:#8a2be2;   /* accents */
      --shadow:0 8px 24px rgba(0,0,0,.35);
      --radius:16px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: ui-sans-serif, -apple-system, Segoe UI, Roboto, Inter, Arial, sans-serif;
      background:var(--bg);
      color:var(--text);
    }
    a{color:var(--blue); text-decoration:none; transition:opacity .2s ease}
    a:hover{opacity:.8}
    .wrap{
      max-width:1200px;
      margin:0 auto;
      padding:24px;
    }
    header{
      position:sticky; top:0; z-index:10;
      backdrop-filter:saturate(120%) blur(6px);
      background:rgba(0,0,0,.5);
      border-bottom:1px solid rgba(255,255,255,.08);
    }
    .hero{
      min-height:calc(100vh - 60px);
      display:flex; align-items:center; justify-content:center;
      transition: min-height .3s ease;
    }
    .hero.compact{min-height:auto; justify-content:flex-start}
    .searchbar{
      width:100%;
      display:flex; align-items:center; justify-content:center; gap:10px;
    }
    .searchbar input{
      width:min(900px, 100%);
      padding:16px 18px;
      background:#0b0c10;
      color:var(--text);
      border:1px solid rgba(255,255,255,.08);
      border-radius:var(--radius);
      outline:none;
      font-size:18px;
      box-shadow:var(--shadow);
      transition:border .2s ease, box-shadow .2s ease;
    }
    .searchbar input:focus{
      border-color:var(--violet);
      box-shadow:0 0 0 4px rgba(138,43,226,.25);
    }
    .btn{
      padding:16px 20px;
      border-radius:var(--radius);
      border:1px solid rgba(255,255,255,.08);
      background:linear-gradient(135deg, #1b1c22, #0f0f14);
      color:var(--text);
      font-weight:600;
      letter-spacing:.2px;
      cursor:pointer;
      box-shadow:var(--shadow);
      transition:transform .06s ease, opacity .2s ease, border .2s ease;
    }
    .btn:hover{opacity:.9}
    .btn:active{transform:translateY(1px)}
    .btn.hidden{display:none}
    .pill{
      display:inline-flex; align-items:center; gap:8px;
      font-size:13px; padding:6px 10px; border-radius:999px;
      background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08);
      color:var(--muted);
    }
    .grid{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap:16px;
      padding-bottom:60px;
    }
    .card{
      background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
      border:1px solid rgba(255,255,255,.08);
      border-radius:var(--radius);
      padding:16px;
      box-shadow:var(--shadow);
      transition:opacity .2s ease, transform .2s ease, border .2s ease;
      cursor:pointer;
    }
    .card:hover{ opacity:.8; transform:translateY(-2px) }
    .card h3{ margin:4px 0 8px 0; font-size:18px }
    .meta{ color:var(--muted); font-size:12px; display:flex; flex-wrap:wrap; gap:10px }
    .kv{ display:grid; grid-template-columns: 160px 1fr; gap:6px 12px; font-size:14px; }
    .kv .k{ color:var(--muted) }
    .kv .v{ color:var(--text); word-break:break-word }
    /* Modal */
    .overlay{
      position:fixed; inset:0;
      background:rgba(0,0,0,.45);
      backdrop-filter: blur(8px);
      display:none; align-items:center; justify-content:center;
      z-index:50;
      padding:24px;
    }
    .overlay.show{ display:flex }
    .modal{
      max-width:1000px; width:min(100%, 1000px);
      max-height:85vh;
      background:linear-gradient(180deg, #101119, #0b0c10);
      border:1px solid rgba(255,255,255,.12);
      border-radius:24px;
      box-shadow:0 20px 60px rgba(0,0,0,.55);
      overflow:hidden;
      position:relative;
    }
    .modal-content{
      max-height:85vh;
      overflow:auto;
      padding:24px;
    }
    .modal .title{
      display:flex; flex-wrap:wrap; gap:10px; align-items:baseline; justify-content:space-between;
    }
    .years{ display:flex; flex-wrap:wrap; gap:8px; margin-top:36px }
    .year-btn{
      padding:8px 12px; border-radius:12px; border:1px solid rgba(255,255,255,.1);
      background:#0f1015; color:#dfe4ea; font-weight:600; cursor:pointer;
    }
    .year-btn.active{
      background:linear-gradient(135deg, rgba(138,43,226,.25), rgba(0,174,239,.25));
      border-color:var(--violet);
      color:#fff;
    }
    .fin-grid{ display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:10px; margin-top:12px }
    .fin-item{ background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:10px }
    .fin-item .k{ color:var(--muted); font-size:12px }
    .fin-item .v{ font-weight:600; font-size:14px }
    .fin-item.empty{ opacity: 0.3 }
    .fin-item.zero{ opacity: 0.7 }
    .links{
      display:flex; gap:10px; flex-wrap:wrap; margin-top:14px
    }
    .close-btn{
      position:absolute; top:10px; right:10px;
      background:rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.08);
      color:var(--text);
      border-radius:8px;
      padding:4px 8px;
      cursor:pointer;
      opacity:0.5;
    }
    .close-btn:hover{opacity:.8}
    .link-card{
      width:64px; height:64px; display:flex; align-items:center; justify-content:center;
      border-radius:14px;
      background:linear-gradient(180deg, rgba(0,174,239,.12), rgba(138,43,226,.12));
      border:1px solid rgba(255,255,255,.12);
      font-weight:800; font-size:20px; color:var(--text);
      text-align:center;
      transition:opacity .2s ease, transform .2s ease;
    }
    .link-card:hover{ opacity:.8; transform:translateY(-1px) }
    .badge{ padding:4px 8px; border-radius:10px; font-size:12px; }
    .badge.good{ background:rgba(46,204,113,.15); color:var(--good); border:1px solid rgba(46,204,113,.25) }
    .badge.bad{ background:rgba(231,76,60,.15); color:var(--bad); border:1px solid rgba(231,76,60,.25) }
    footer{ color:var(--muted); font-size:12px; text-align:center; padding:20px 0 60px }
  </style>
</head>
<body>
  <header>
    <div class="wrap">
      <div class="hero" id="hero">
        <div class="searchbar">
          <input id="q" type="search" placeholder="search..." autocomplete="off" autofocus />
          <button id="go" class="btn hidden" title="Search">Search</button>
        </div>
      </div>
    </div>
  </header>

  <main class="wrap">
    <div id="stats" class="pill" style="margin-bottom:14px; display:none"></div>
    <div id="results" class="grid" aria-live="polite"></div>
  </main>

  <div id="overlay" class="overlay">
    <div id="modal" class="modal" role="dialog" aria-modal="true">
      <div class="modal-content"></div>
    </div>
  </div>

  <footer>
    <span style="opacity: 0.3">Stultus est, qui hoc legit.</span>
  </footer>

  <script>
    // --- DATA PLACEHOLDER (replaced differently in index.php vs preview.html) ---
    const DATA = <?php echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    // Build quick maps for lookups
    const byCIK = new Map();
    for(const row of DATA.main){
      if(!row) continue;
      byCIK.set(String(row.CIK||"").trim(), row);
    }
    const finByCIKYear = new Map(); // key: CIK|year -> record
    const yearsByCIK = new Map();
    const symbolByCIK = new Map();
    for(const f of DATA.financials){
      const cik = String(f.CIK||"").trim();
      const year = String(f.year||"").trim();
      if(cik && year){
        finByCIKYear.set(cik+"|"+year, f);
        if(!yearsByCIK.has(cik)) yearsByCIK.set(cik, new Set());
        yearsByCIK.get(cik).add(year);
      }
      // capture a symbol if present
      const sym = (f.TradingSymbol||"").trim();
      if(cik && sym && !symbolByCIK.has(cik)) symbolByCIK.set(cik, sym);
    }

    const reportsByCIKYear = new Map();
    for(const r of DATA.reports){
      const cik = String(r.CIK||"").trim();
      const year = String(r.year||"").trim();
      if(cik && year) reportsByCIKYear.set(cik+"|"+year, r);
    }

    const qEl = document.getElementById('q');
    const goEl = document.getElementById('go');
    const hero = document.getElementById('hero');
    const resultsEl = document.getElementById('results');
    const statsEl = document.getElementById('stats');
    const overlay = document.getElementById('overlay');
    const modal = document.getElementById('modal');
    const modalContent = modal.querySelector('.modal-content');

    // Translation map for state of incorporation codes
    const stateNameMap = {
      'AL':'Alabama','AK':'Alaska','AZ':'Arizona','AR':'Arkansas','CA':'California','CO':'Colorado','CT':'Connecticut',
      'DE':'Delaware','FL':'Florida','GA':'Georgia','HI':'Hawaii','ID':'Idaho','IL':'Illinois','IN':'Indiana',
      'IA':'Iowa','KS':'Kansas','KY':'Kentucky','LA':'Louisiana','ME':'Maine','MD':'Maryland','MA':'Massachusetts',
      'MI':'Michigan','MN':'Minnesota','MS':'Mississippi','MO':'Missouri','MT':'Montana','NE':'Nebraska','NV':'Nevada',
      'NH':'New Hampshire','NJ':'New Jersey','NM':'New Mexico','NY':'New York','NC':'North Carolina','ND':'North Dakota',
      'OH':'Ohio','OK':'Oklahoma','OR':'Oregon','PA':'Pennsylvania','RI':'Rhode Island','SC':'South Carolina',
      'SD':'South Dakota','TN':'Tennessee','TX':'Texas','UT':'Utah','VT':'Vermont','VA':'Virginia','WA':'Washington',
      'WV':'West Virginia','WI':'Wisconsin','WY':'Wyoming','DC':'District of Columbia','PR':'Puerto Rico'
    };

    function normalize(s){
      return (s||"").toString().toLowerCase().replace(/[^a-z0-9\.\- ]+/g,' ').replace(/\s+/g,' ').trim();
    }

    // Basic scoring: prioritize (1) start-with on company, (2) contains in company/ticker, (3) other fields
    function score(record, query){
      const cik = String(record.CIK||"").trim();
      const company = record.CompanyName||"";
      const sicDesc = record.SICDescription||"";
      const city = record.BusinessAddressCity||"";
      const state = record.BusinessAddressState||"";
      const exch = record.exchange||"";
      const sym  = symbolByCIK.get(cik) || "";

      const nq = normalize(query);
      if(!nq) return 0;
      let s = 0;
      const nc = normalize(company);
      const ns = normalize(sym);
      const fields = [nc, ns, normalize(cik), normalize(sicDesc), normalize(city), normalize(state), normalize(exch)];

      if(nc.startsWith(nq)) s += 200;
      if(ns && ns === nq) s += 180;
      if(nc.includes(nq)) s += 120;
      if(ns && ns.includes(nq)) s += 100;
      if(String(cik) === query.trim()) s += 160;

      // token coverage
      const tokens = nq.split(' ');
      for(const t of tokens){
        for(const f of fields){
          if(t && f.includes(t)) { s += 20; break; }
        }
      }
      // Slight boost if in same SIC bucket
      if(String(record.IndustrySICCode||"").includes(query)) s += 10;

      return s;
    }

    function formatKV(k){
      // prettify key names a bit
      return String(k).replace(/([a-z0-9])([A-Z])/g,'$1 $2')
                      .replace(/_/g,' ')
                      .replace(/\bCik\b/i,'CIK')
                      .replace(/\bUrl\b/i,'URL');
    }

    function fmt(v){
      if(v === null || v === undefined) return "";
      const s = String(v);
      return s;
    }

    function formatUSD(val){
      const num = Number(val);
      if(isNaN(num)) return null;
      const sign = num < 0 ? '-' : '';
      const abs = Math.abs(num);
      const intPart = Math.trunc(abs);
      const fracPart = abs - intPart;
      const parts = intPart.toLocaleString('en-US').split(',');
      const digits = parts.join('').length;
      let mainParts = parts;
      let fadedParts = [];
      if(digits > 7){
        mainParts = parts.slice(0, -2);
        fadedParts = parts.slice(-2);
      } else if(digits > 3){
        mainParts = parts.slice(0, -1);
        fadedParts = parts.slice(-1);
      }
      const prefix = mainParts.join(',');
      const faded = fadedParts.length ? ',' + fadedParts.join(',') : '';
      const fadedHTML = faded ? `<span style="opacity:.7">${faded}</span>` : '';
      const decimal = fracPart ? '.' + abs.toFixed(2).split('.')[1] : '';
      return `${sign}${prefix}${fadedHTML}${decimal} USD`;
    }

    function renderResults(list){
      resultsEl.innerHTML = "";
      const frag = document.createDocumentFragment();
      for(const r of list){
        const card = document.createElement('div');
        card.className = 'card';
        const cik = String(r.CIK||"").trim();
        const sym = symbolByCIK.get(cik) || "";
        card.innerHTML = `
          <div class="meta">
            <span class="pill">CIK: ${cik||"-"}</span>
            ${sym ? `<span class="pill">Ticker: ${sym}</span>` : ``}
            ${r.exchange ? `<span class="pill">${fmt(r.exchange)}</span>` : ``}
          </div>
          <h3>${fmt(r.CompanyName)||"Unknown Company"}</h3>
          <div class="meta">
            ${r.SICDescription ? `<span>${fmt(r.SICDescription)}</span>`:""}
            ${r.BusinessAddressCity ? `<span> · ${fmt(r.BusinessAddressCity)}${r.BusinessAddressState? ", "+fmt(r.BusinessAddressState):""}</span>`:""}
          </div>
        `;
        card.addEventListener('click', ()=> openModal(cik));
        frag.appendChild(card);
      }
      resultsEl.appendChild(frag);
    }

    function openModal(cik){
      const base = byCIK.get(cik) || {};
      const sym  = symbolByCIK.get(cik) || "";
      modalContent.innerHTML = "";
      const title = document.createElement('div');
      title.className = 'title';
      title.innerHTML = `
        <div>
          <h2 style="margin:0 0 2px 0">${fmt(base.CompanyName)||"Company"}</h2>
          <div class="meta" style="margin-top:4px">
            <span class="pill">CIK: ${cik}</span>
            ${sym ? `<span class="pill">Ticker: ${sym}</span>`:""}
            ${base.exchange ? `<span class="pill">${fmt(base.exchange)}</span>`:""}
            ${base.SICDescription ? `<span class="pill">${fmt(base.SICDescription)}</span>`:""}
          </div>
        </div>
        <div class="meta">
          ${base.Website ? `<a class="pill" href="${fmt(base.Website)}" target="_blank" rel="noopener">Website</a>`:""}
          ${base.InvestorWebsite ? `<a class="pill" href="${fmt(base.InvestorWebsite)}" target="_blank" rel="noopener">IR</a>`:""}
        </div>
      `;
      modalContent.appendChild(title);

      const existingClose = modal.querySelector('.close-btn');
      if(existingClose) existingClose.remove();
      const closeBtn = document.createElement('button');
      closeBtn.className = 'close-btn';
      closeBtn.innerHTML = '<strong>X</strong>';
      closeBtn.title = 'close';
      closeBtn.addEventListener('click', () => closeModal());
      modal.appendChild(closeBtn);

      // Company details (from main.csv) — show all non-empty fields
      const kv = document.createElement('div');
      kv.className = 'kv';
      kv.style.marginTop = '16px';
      for(const [k,v] of Object.entries(base)){
        if(v===undefined || v===null) continue;
        let sv = String(v).trim();
        if(!sv) continue;
        if(k === 'StateOfIncorporation'){
          const full = stateNameMap[sv.toUpperCase()];
          if(full) sv = `${sv} (${full})`;
        }
        const kEl = document.createElement('div'); kEl.className='k'; kEl.textContent = formatKV(k);
        const vEl = document.createElement('div'); vEl.className='v';
        if(k === 'FilingURL'){
          const a = document.createElement('a');
          a.href = sv; a.textContent = sv; a.target='_blank'; a.rel='noopener';
          a.style.opacity = 0.5;
          vEl.appendChild(a);
        } else {
          vEl.textContent = sv;
        }
        kv.appendChild(kEl); kv.appendChild(vEl);
      }
      modalContent.appendChild(kv);

      // Years
      const years = Array.from((yearsByCIK.get(cik)||new Set())).sort((a,b)=> String(b).localeCompare(String(a)));
      if(years.length){
        const yearsWrap = document.createElement('div'); yearsWrap.className='years';
        years.forEach((y,idx)=>{
          const b = document.createElement('button');
          b.className = 'year-btn'+(idx===0?' active':'');
          b.textContent = y;
          b.addEventListener('click', ()=>{
            modalContent.querySelectorAll('.year-btn').forEach(x=>x.classList.remove('active'));
            b.classList.add('active');
            renderYear(cik, y);
          });
          yearsWrap.appendChild(b);
        });
        modalContent.appendChild(yearsWrap);
        // initial render
        renderYear(cik, years[0]);
      }

      overlay.classList.add('show');
      document.body.style.overflow='hidden';
    }

    function renderYear(cik, year){
      // remove previous fin + links section if present
      const old = modalContent.querySelector('#year-section');
      if(old) old.remove();

      const sec = document.createElement('section');
      sec.id = 'year-section';
      sec.style.marginTop = '16px';

      const fin = finByCIKYear.get(cik+'|'+year) || null;
      if(fin){
        const grid = document.createElement('div'); grid.className='fin-grid';
        // show everything except a few metadata keys
        const omit = new Set(['idpk','CIK','year','EntityRegistrantName','EntityCentralIndexKey','TradingSymbol','EntityIncorporationStateCountryCode','EntityFilerCategory','DocumentType','AmendmentFlag','DocumentPeriodEndDate','DocumentFiscalPeriodFocus','DocumentFiscalYearFocus','CurrentFiscalYearEndDate']);
        for(const [k,v] of Object.entries(fin)){
          if(omit.has(k)) continue;
          const val = fmt(v).trim();
          const num = Number(val);
          const item = document.createElement('div');
          item.className = 'fin-item' + (val === '' ? ' empty' : (!Number.isNaN(num) && num === 0 ? ' zero' : ''));
          const formatted = formatUSD(val);
          item.innerHTML = `<div class="k">${formatKV(k)}</div><div class="v">${formatted !== null ? formatted : val}</div>`;
          grid.appendChild(item);
        }
        sec.appendChild(grid);
      }

      // Links (Annual first, then Q1..Q4) as square cards
      const rep = reportsByCIKYear.get(cik+'|'+year) || null;
      const links = [];
      if(rep){
        if(rep.AnnualReportLink) links.push({label:'AR', url: rep.AnnualReportLink, title:'open annual report'});
        if(rep.QuarterlyReportLinkQ1) links.push({label:'Q1', url: rep.QuarterlyReportLinkQ1, title:'open quarterly report for quarter Q1'});
        if(rep.QuarterlyReportLinkQ2) links.push({label:'Q2', url: rep.QuarterlyReportLinkQ2, title:'open quarterly report for quarter Q2'});
        if(rep.QuarterlyReportLinkQ3) links.push({label:'Q3', url: rep.QuarterlyReportLinkQ3, title:'open quarterly report for quarter Q3'});
        if(rep.QuarterlyReportLinkQ4) links.push({label:'Q4', url: rep.QuarterlyReportLinkQ4, title:'open quarterly report for quarter Q4'});
      }
      if(links.length){
        const lwrap = document.createElement('div'); lwrap.className='links';
        for(const L of links){
          const a = document.createElement('a'); a.className='link-card'; a.href = L.url; a.target='_blank'; a.rel='noopener';
          a.textContent = L.label;
          if(L.title) a.title = L.title;
          lwrap.appendChild(a);
        }
        sec.appendChild(lwrap);
      }

      modalContent.appendChild(sec);
    }

    function closeModal(ev){
      if(ev && ev.target && ev.target.closest && ev.target.closest('.modal')) return;
      overlay.classList.remove('show');
      modalContent.innerHTML = "";
      const cb = modal.querySelector('.close-btn');
      if(cb) cb.remove();
      document.body.style.overflow='';
    }

    overlay.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal(e) });

    function doSearch(){
      const query = qEl.value.trim();
      if(!query){
        resultsEl.innerHTML = "";
        statsEl.style.display = "none";
        hero.classList.remove('compact');
        return;
      }
      const ranked = DATA.main
        .map(r => ({ rec:r, s:score(r, query) }))
        .filter(x => x.s>0)
        .sort((a,b)=> b.s - a.s)
        .slice(0,200)
        .map(x=>x.rec);
      renderResults(ranked);
      statsEl.textContent = `${ranked.length} result(s)`;
      statsEl.style.display = "inline-flex";
      hero.classList.add('compact');
    }

    // Show/hide search button depending on input content; submit on Enter
    qEl.addEventListener('input', ()=>{
      const has = qEl.value.trim().length>0;
      goEl.classList.toggle('hidden', !has);
    });
    qEl.addEventListener('keydown', (e)=>{
      if(e.key === 'Enter'){
        e.preventDefault();
        doSearch();
      }
    });
    goEl.addEventListener('click', doSearch);

    // Focus cursor on page load
    window.addEventListener('load', ()=> qEl.focus());
  </script>
</body>
</html>
