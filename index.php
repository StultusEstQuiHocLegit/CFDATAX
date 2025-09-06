<?php
// index.php: single file app that reads CSVs and renders modern UI
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

$main = array_map(function($r){ $r['solvent'] = false; return $r; }, read_csv_assoc(__DIR__ . '/main.csv'));
$financials = array_map(function($r){ $r['solvent'] = false; return $r; }, read_csv_assoc(__DIR__ . '/financials.csv'));
$reports = array_map(function($r){ $r['solvent'] = false; return $r; }, read_csv_assoc(__DIR__ . '/reports.csv'));

$main_solvent = array_map(function($r){ $r['solvent'] = true; return $r; }, read_csv_assoc(__DIR__ . '/main_solvent.csv'));
$financials_solvent = array_map(function($r){ $r['solvent'] = true; return $r; }, read_csv_assoc(__DIR__ . '/financials_solvent.csv'));
$reports_solvent = array_map(function($r){ $r['solvent'] = true; return $r; }, read_csv_assoc(__DIR__ . '/reports_solvent.csv'));

$main = array_merge($main, $main_solvent);
$financials = array_merge($financials, $financials_solvent);
$reports = array_merge($reports, $reports_solvent);

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
    .status-dot{
      display:inline-block;
      width:.5rem; height:.5rem;
      border-radius:50%;
      margin-right:4px;
      vertical-align:middle;
      position:relative;
      top:-.1em;
    }
    .status-dot.good{ background:var(--good) }
    .status-dot.bad{ background:var(--bad) }
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
    .card.skeleton{
      pointer-events:none;
      position:relative;
      overflow:hidden;
      color:transparent;
      min-height:80px;
    }
    .card.skeleton:hover{ opacity:1; transform:none }
    .card.skeleton::before{
      content:"";
      position:absolute;
      inset:0;
      background:linear-gradient(120deg, rgba(255,255,255,0), rgba(255,255,255,.1), rgba(255,255,255,0));
      animation:shimmer 1.2s infinite;
    }
    @keyframes shimmer{
      0%{transform:translateX(-100%)}
      100%{transform:translateX(100%)}
    }
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
    .fin-item{ background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:10px; display:flex; flex-direction:column }
    .fin-item .k{ color:var(--muted); font-size:12px }
    .fin-item .v{ font-weight:600; font-size:14px; margin-top:auto; display:flex; align-items:center }
    .fin-item .pct{ font-weight:400; margin-left:auto; text-align:right }
    .fin-item.empty{ opacity: 0.3 }
    .fin-item.zero{ opacity: 0.7 }
    .fin-item.clickable{ cursor:pointer }
    .chart-card{ background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:10px; height:260px; }
    .chart-card canvas{ width:100%; height:100%; }
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
          <button id="go" class="btn hidden" title="search">search</button>
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

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    const PAGE_SIZE = 50;
    let currentResults = [];
    let loadedCount = 0;
    let loading = false;

    let lastQuery = "";

    let activeChart = null;
    function hideActiveChart(){
      if(activeChart){
        if(activeChart.chart) activeChart.chart.destroy();
        activeChart.card.remove();
        activeChart.container.removeEventListener('click', activeChart.listener);
        if(activeChart.item) activeChart.item.style.borderColor = '';
        activeChart = null;
      }
    }

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

    // Sentiment for financial metrics: whether a higher value is generally good or bad
    const METRIC_SENTIMENT = {
      'assets':'good',
      'CurrentAssets':'good',
      'NoncurrentAssets':'good',
      'liabilities':'bad',
      'CurrentLiabilities':'bad',
      'NoncurrentLiabilities':'bad',
      'LiabilitiesAndStockholdersEquity':'neutral',
      'equity':'good',
      'CommonStockValue':'neutral',
      'RetainedEarningsAccumulatedDeficit':'good',
      'AccumulatedOtherComprehensiveIncomeLoss':'neutral',
      'MinorityInterest':'neutral',
      'revenues':'good',
      'SalesRevenueNet':'good',
      'CostOfGoodsSold':'bad',
      'GrossProfit':'good',
      'OperatingExpenses':'bad',
      'SellingGeneralAndAdministrativeExpense':'bad',
      'ResearchAndDevelopmentExpense':'neutral',
      'OperatingIncomeLoss':'good',
      'InterestExpense':'bad',
      'IncomeBeforeIncomeTaxes':'good',
      'IncomeTaxExpenseBenefit':'bad',
      'NetIncomeLoss':'good',
      'PreferredStockDividendsAndOtherAdjustments':'bad',
      'NetIncomeLossAvailableToCommonStockholdersBasic':'good',
      'EarningsPerShareBasic':'good',
      'EarningsPerShareDiluted':'good',
      'WeightedAverageNumberOfSharesOutstandingBasic':'neutral',
      'WeightedAverageNumberOfDilutedSharesOutstanding':'neutral',
      'NetCashProvidedByUsedInOperatingActivities':'good',
      'NetCashProvidedByUsedInInvestingActivities':'neutral',
      'NetCashProvidedByUsedInFinancingActivities':'neutral',
      'CashAndCashEquivalentsPeriodIncreaseDecrease':'good',
      'CashAndCashEquivalentsAtCarryingValue':'good',
      'PaymentsToAcquirePropertyPlantAndEquipment':'neutral',
      'ProceedsFromIssuanceOfCommonStock':'neutral',
      'PaymentsOfDividends':'neutral',
      'RepaymentsOfDebt':'good',
      'ProceedsFromIssuanceOfDebt':'bad',
      'DepreciationAndAmortization':'neutral',
      'InventoryNet':'neutral',
      'AccountsReceivableNetCurrent':'neutral',
      'AccountsPayableCurrent':'bad',
      'Goodwill':'neutral',
      'IntangibleAssetsNetExcludingGoodwill':'neutral',
      'PropertyPlantAndEquipmentNet':'good',
      'LongTermDebtNoncurrent':'bad',
      'ShortTermBorrowings':'bad',
      'IncomeTaxesPayableCurrent':'bad'
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

    const METRIC_UNITS = {
      'EarningsPerShareBasic': 'USD/share',
      'EarningsPerShareDiluted': 'USD/share',
      'WeightedAverageNumberOfSharesOutstandingBasic': 'shares',
      'WeightedAverageNumberOfDilutedSharesOutstanding': 'shares'
    };

    function formatAmount(val, unit='USD'){
      const num = Number(String(val).replace(/,/g, ''));
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
      const fadedHTML = faded ? `<span style="opacity:.5">${faded}</span>` : '';
      const decimal = fracPart ? '.' + abs.toFixed(2).split('.')[1] : '';
      const decimalHTML = decimal ? `<span style="opacity:.7">${decimal}</span>` : '';
      return `${sign}${prefix}${fadedHTML}${decimalHTML} <span style="opacity:.3">${unit}</span>`;
    }

    function formatAmountPlain(val, unit='USD'){
      const num = Number(String(val).replace(/,/g, ''));
      if(isNaN(num)) return String(val);
      const sign = num < 0 ? '-' : '';
      const abs = Math.abs(num);
      return `${sign}${abs.toLocaleString('en-US', {maximumFractionDigits:2})} ${unit}`;
    }

    function renderResults(list){
      currentResults = list;
      loadedCount = 0;
      resultsEl.innerHTML = "";
      window.removeEventListener('scroll', handleScroll);
      loadMore();
      window.addEventListener('scroll', handleScroll);
    }

    function handleScroll(){
      if(loading) return;
      if(window.innerHeight + window.scrollY >= document.body.offsetHeight - 100){
        loadMore();
      }
    }

    function loadMore(){
      if(loadedCount >= currentResults.length) return;
      loading = true;
      resultsEl.querySelectorAll('.card.skeleton').forEach(el=>el.remove());
      const frag = document.createDocumentFragment();
      const chunk = currentResults.slice(loadedCount, loadedCount + PAGE_SIZE);
      for(const r of chunk){
        const card = document.createElement('div');
        card.className = 'card';
        const cik = String(r.CIK||"").trim();
        const sym = symbolByCIK.get(cik) || "";
        const statusDot = `<span class="status-dot ${r.solvent ? 'good' : 'bad'}" title="${r.solvent ? 'solvent' : 'bankrupt'}"></span>`;
        card.innerHTML = `
          <div class="meta">
            <span class="pill">CIK: ${cik||"-"}</span>
            ${sym ? `<span class="pill">Ticker: ${sym}</span>` : ``}
            ${r.exchange ? `<span class="pill">${fmt(r.exchange)}</span>` : ``}
          </div>
          <h3>${statusDot}${fmt(r.CompanyName)||"Unknown Company"}</h3>
          <div class="meta">
            ${r.SICDescription ? `<span>${fmt(r.SICDescription)}</span>`:""}
            ${r.BusinessAddressCity ? `<span> · ${fmt(r.BusinessAddressCity)}${r.BusinessAddressState? ", "+fmt(r.BusinessAddressState):""}</span>`:""}
          </div>
        `;
        card.addEventListener('click', ()=> openModal(cik));
        frag.appendChild(card);
      }
      resultsEl.appendChild(frag);
      loadedCount += chunk.length;

      const remaining = currentResults.length - loadedCount;
      if(remaining > 0){
        const pfrag = document.createDocumentFragment();
        const count = Math.min(PAGE_SIZE, remaining);
        for(let i=0;i<count;i++){
          const ph = document.createElement('div');
          ph.className = 'card skeleton';
          pfrag.appendChild(ph);
        }
        resultsEl.appendChild(pfrag);
      }else{
        window.removeEventListener('scroll', handleScroll);
      }
      loading = false;
    }

    function openModal(cik){
      const base = byCIK.get(cik) || {};
      const sym  = symbolByCIK.get(cik) || "";
      modalContent.innerHTML = "";
      const title = document.createElement('div');
      title.className = 'title';
      const statusDot = `<span class="status-dot ${base.solvent ? 'good' : 'bad'}" title="${base.solvent ? 'solvent' : 'bankrupt'}"></span>`;
      title.innerHTML = `
        <div>
          <h2 style="margin:0 0 2px 0">${statusDot}${fmt(base.CompanyName)||"Company"}</h2>
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
        if(k === 'solvent'){
          vEl.textContent = sv;
          vEl.style.color = v ? 'var(--good)' : 'var(--bad)';
        } else if(k === 'FilingURL'){
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

    function toggleChart(cik, key, item){
      if(activeChart && activeChart.key === key){
        hideActiveChart();
        return;
      }
      hideActiveChart();
      const years = Array.from((yearsByCIK.get(cik)||new Set())).sort();
      const labels = [];
      const data = [];
      let nonZeroCount = 0;
      for(const y of years){
        const rec = finByCIKYear.get(cik+'|'+y);
        const raw = rec ? fmt(rec[key]).trim() : '';
        const num = Number(String(raw).replace(/,/g,''));
        if(raw === '' || Number.isNaN(num)) continue;
        labels.push(y);
        data.push(num);
        if(num !== 0) nonZeroCount++;
      }
      if(nonZeroCount < 2) return;
      const grid = item.closest('.fin-grid');
      if(!grid) return;
      const chartCard = document.createElement('div');
      chartCard.className = 'chart-card';
      chartCard.style.gridColumn = '1 / -1';
      const canvas = document.createElement('canvas');
      chartCard.appendChild(canvas);
      const children = Array.from(grid.children);
      const idx = children.indexOf(item);
      const cols = getComputedStyle(grid).gridTemplateColumns.split(' ').length || 1;
      const rowEnd = Math.min(children.length - 1, Math.floor(idx / cols) * cols + cols - 1);
      const anchor = children[rowEnd];
      anchor.insertAdjacentElement('afterend', chartCard);
      item.style.borderColor = 'var(--blue)';
      const ctx = canvas.getContext('2d');
      const unit = METRIC_UNITS[key] || 'USD';
      const chart = new Chart(ctx, {
        type:'line',
        data:{ labels, datasets:[{ label: formatKV(key), data, borderColor:'#00aeef', backgroundColor:'rgba(0,174,239,0.2)', tension:0.1 }] },
        options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{ ticks:{ callback:(v)=>formatAmountPlain(v, unit) }}} }
      });
      chartCard.addEventListener('click', (ev)=>{ ev.stopPropagation(); hideActiveChart(); });
      const container = modal;
      const listener = (ev)=>{
        if(ev.target.closest('.chart-card') || ev.target.closest('.fin-item') === item) return;
        hideActiveChart();
      };
      container.addEventListener('click', listener);
      activeChart = {card: chartCard, chart, key, container, listener, item};
    }

    function renderYear(cik, year){
      // remove previous fin + links section if present
      const old = modalContent.querySelector('#year-section');
      if(old) old.remove();

      const sec = document.createElement('section');
      sec.id = 'year-section';
      sec.style.marginTop = '16px';

      const fin = finByCIKYear.get(cik+'|'+year) || null;
      const prevYear = String(Number(year) - 1);
      const prevFin = finByCIKYear.get(cik+'|'+prevYear) || null;
      if(fin){
        const grid = document.createElement('div'); grid.className='fin-grid';
        // show everything except a few metadata keys
        const omit = new Set(['idpk','CIK','year','EntityRegistrantName','EntityCentralIndexKey','TradingSymbol','EntityIncorporationStateCountryCode','EntityFilerCategory','DocumentType','AmendmentFlag','DocumentPeriodEndDate','DocumentFiscalPeriodFocus','DocumentFiscalYearFocus','CurrentFiscalYearEndDate','solvent']);
        for(const [k,v] of Object.entries(fin)){
          if(omit.has(k)) continue;
          const val = fmt(v).trim();
          const num = Number(String(val).replace(/,/g,''));
          const item = document.createElement('div');
          item.className = 'fin-item' + (val === '' ? ' empty' : (!Number.isNaN(num) && num === 0 ? ' zero' : ''));
          const unit = METRIC_UNITS[k] || 'USD';
          const formatted = formatAmount(val, unit);
          const sentiment = METRIC_SENTIMENT[k] || 'neutral';
          let pctHtml = '';
          if(prevFin && Object.prototype.hasOwnProperty.call(prevFin, k)){
            const prevRaw = fmt(prevFin[k]).trim();
            const currNum = num;
            const prevNum = Number(String(prevRaw).replace(/,/g,''));
            if(prevRaw !== '' && !Number.isNaN(currNum) && !Number.isNaN(prevNum) && prevNum !== 0){
              const pct = Math.round((currNum - prevNum) / Math.abs(prevNum) * 100);
              const absPct = Math.abs(pct);
              let op = 0.5;
              if(absPct === 0) op = 0.3;
              else if(absPct < 10) op = 0.4;
              const sign = pct > 0 ? '+' : '';
              let pctColor = '';
              if(pct !== 0 && (sentiment === 'good' || sentiment === 'bad')){
                const pctPositive = pct > 0;
                pctColor = sentiment === 'good'
                  ? (pctPositive ? 'var(--good)' : 'var(--bad)')
                  : (pctPositive ? 'var(--bad)' : 'var(--good)');
              }
              const colorAttr = pctColor ? `; color:${pctColor}` : '';
              pctHtml = `<span class="pct" style="opacity:${op}${colorAttr}" title="percentage change relative to previous year">${sign}${pct}%</span>`;
            }
          }
          const amountHTML = formatted !== null ? formatted : val;
          item.innerHTML = `<div class="k">${formatKV(k)}</div><div class="v"><span class="amt">${amountHTML}</span>${pctHtml}</div>`;
          if(sentiment !== 'neutral' && !Number.isNaN(num) && num !== 0){
            const amtPositive = num > 0;
            const amtColor = sentiment === 'good'
              ? (amtPositive ? 'var(--good)' : 'var(--bad)')
              : (amtPositive ? 'var(--bad)' : 'var(--good)');
            const amtSpan = item.querySelector('.amt');
            if(amtSpan) amtSpan.style.color = amtColor;
          }
          const allYears = Array.from((yearsByCIK.get(cik)||new Set()));
          const history = [];
          for(const y of allYears){
            const rec = finByCIKYear.get(cik+'|'+y);
            const raw = rec ? fmt(rec[k]).trim() : '';
            const numv = Number(String(raw).replace(/,/g,''));
            if(raw !== '' && !Number.isNaN(numv) && numv !== 0) history.push(numv);
          }
          if(history.length > 1){
            item.classList.add('clickable');
            item.addEventListener('click', (e)=>{ e.stopPropagation(); toggleChart(cik, k, item); });
          }
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
      hideActiveChart();
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
      lastQuery = query;
      goEl.classList.add('hidden');
      if(!query){
        resultsEl.innerHTML = "";
        statsEl.style.display = "none";
        hero.classList.remove('compact');
        window.removeEventListener('scroll', handleScroll);
        currentResults = [];
        loadedCount = 0;
        return;
      }
      const ranked = DATA.main
        .map(r => ({ rec:r, s:score(r, query) }))
        .filter(x => x.s>0)
        .sort((a,b)=> b.s - a.s)
        .map(x=>x.rec);
      renderResults(ranked);
      statsEl.textContent = `${ranked.length} result${ranked.length === 1 ? '' : 's'}`;
      statsEl.style.display = "inline-flex";
      hero.classList.add('compact');
    }

    // Show/hide search button depending on input content and last query; submit on Enter
    qEl.addEventListener('input', ()=>{
      const value = qEl.value.trim();
      const has = value.length>0 && value !== lastQuery;
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
