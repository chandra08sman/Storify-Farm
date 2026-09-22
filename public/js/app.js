let users = [];
let products = [];
let batches = [];
let transactions = [];
let appNotifications = [];
let currentUser = null;

function ymd(d){return d.toISOString().slice(0,10)}

let CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
async function apiFetch(url, options = {}, retried = false) {
  const opts = {
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': CSRF_TOKEN,
      ...(options.body instanceof FormData ? {} : {'Content-Type': 'application/json'}),
      ...(options.headers || {}),
    },
    ...options,
  };
  let res;
  try {
    res = await fetch(url, opts);
  } catch (err) {
    return {ok:false, status:0, data:{ok:false, message:'Tidak bisa terhubung ke server. Periksa koneksi Anda.'}};
  }
  let data = null;
  try { data = await res.json(); } catch (e) { data = null; }
  if(res.status===419 && !retried){
    const page=await fetch('/', {credentials:'same-origin', headers:{'Accept':'text/html'}});
    if(page.ok){
      const html=await page.text();
      const freshToken=html.match(/<meta name="csrf-token" content="([^"]+)"/);
      if(freshToken) CSRF_TOKEN=freshToken[1];
      return apiFetch(url, options, true);
    }
  }
  return {ok: res.ok, status: res.status, data};
}

async function syncFromServer(){
  const {ok, data} = await apiFetch('/api/bootstrap');
  if(!ok || !data) return false;
  currentUser = data.user;
  loadAIHistory();
  products = data.products || [];
  batches = data.batches || [];
  transactions = data.transactions || [];
  appNotifications = data.notifications || [];
  if(data.users) users = data.users;
  if(typeof refreshAll === 'function') refreshAll();
  return true;
}
async function loadBootstrapOrGuest(){
  const {ok, data} = await apiFetch('/api/bootstrap');
  if(ok && data){
    currentUser = data.user;
    loadAIHistory();
    products = data.products || [];
    batches = data.batches || [];
    transactions = data.transactions || [];
    appNotifications = data.notifications || [];
    if(data.users) users = data.users;
  } else {
    currentUser = null;
    loadAIHistory();
  }
}

function showToast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');clearTimeout(window._toastT);window._toastT=setTimeout(()=>t.classList.remove('show'),2500)}

function productBatches(productId){return batches.filter(b=>b.product_id===productId)}
function productStock(productId){return productBatches(productId).reduce((s,b)=>s+Number(b.quantity),0)}
function capacityStatus(product){
 const stock=productStock(product.id);
 const capacity=Number(product.capacity)||0;
 const usedPct = capacity ? Math.min(100,(stock/capacity)*100) : 0;
 const remaining = capacity ? Math.max(0,100-usedPct) : 100;
 if(capacity && stock>=capacity) return {label:'Penuh',pct:usedPct,remaining:0,cls:'bg-danger/15 text-danger'};
 if(remaining<=20) return {label:'Hampir Penuh',pct:usedPct,remaining,cls:'bg-amber/30 text-[#6b4a1c]'};
 return {label:'Normal',pct:usedPct,remaining,cls:'bg-primary/10 text-primary'};
}

async function storeIn({product_id,quantity,batch,received_at}){
 if(!quantity||quantity<1) return {ok:false,msg:'Jumlah tidak valid.'};
 if(!batch) return {ok:false,msg:'Nomor batch wajib diisi.'};
 if(!received_at) return {ok:false,msg:'Tanggal diterima wajib diisi.'};
 const {ok, data} = await apiFetch('/api/transactions/in', {
   method:'POST',
   body: JSON.stringify({product_id, quantity:Number(quantity), batch, received_at}),
 });
 if(ok && data?.ok){ await syncFromServer(); return {ok:true, msg:data.message}; }
 return {ok:false, msg: data?.message || 'Gagal mencatat barang masuk.'};
}
async function storeOut({product_id,quantity}){
 quantity=Number(quantity);
 if(!quantity||quantity<1) return {ok:false,msg:'Jumlah tidak valid.'};
 const {ok, data} = await apiFetch('/api/transactions/out', {
   method:'POST',
   body: JSON.stringify({product_id, quantity}),
 });
 if(ok && data?.ok){ await syncFromServer(); return {ok:true, msg:data.message}; }
 return {ok:false, msg: data?.message || 'Gagal mencatat barang keluar.'};
}

function normalizeText(s){
  return (s||'').toLowerCase().normalize('NFKD').replace(/[^\w\s]/g,' ').replace(/\s+/g,' ').trim();
}
function levenshtein(a,b){
  if(a===b) return 0;
  const al=a.length, bl=b.length;
  if(!al) return bl; if(!bl) return al;
  let prev=Array.from({length:bl+1},(_,i)=>i);
  for(let i=1;i<=al;i++){
    const cur=[i];
    for(let j=1;j<=bl;j++){
      cur[j]=a[i-1]===b[j-1] ? prev[j-1] : 1+Math.min(prev[j-1],prev[j],cur[j-1]);
    }
    prev=cur;
  }
  return prev[bl];
}
function fuzzyTolerance(len){ return len<=3?0 : len<=5?1 : len<=8?2 : 3; }
function wordFuzzyMatch(word, tokens){
  for(const t of tokens){
    if(t===word || t.includes(word) || word.includes(t)) return true;
    if(levenshtein(t,word) <= fuzzyTolerance(Math.max(t.length,word.length))) return true;
  }
  return false;
}
function phraseFuzzyMatch(normalizedInput, tokens, phrase){
  const p=normalizeText(phrase);
  if(normalizedInput.includes(p)) return true;
  return p.split(' ').every(w=>wordFuzzyMatch(w,tokens));
}


function calculateWarehouse({length,width,rackLength,rackWidth,aisle}){
 const area=length*width;
 const rackArea=rackLength*rackWidth;
 const racks=Math.max(0,Math.floor((area*0.6)/rackArea));
 return {area,racks,temperature:'20–25°C',humidity:'60–70%'};
}

const ROLE_RESTRICTED={planner:['admin','supervisor'],reports:['admin','supervisor'],users:['admin'],barcode:['admin','petugas']};function roleAllowed(page){
 if(!ROLE_RESTRICTED[page]) return true;
 return currentUser && ROLE_RESTRICTED[page].includes(currentUser.role);
}
function applyNavRoles(){
 document.querySelectorAll('#nav [data-roles]').forEach(el=>{
   const roles=el.getAttribute('data-roles').split(',');
   el.classList.toggle('role-hidden', !(currentUser && roles.includes(currentUser.role)));
 });
}

const APP_PAGES=['dashboard','ai','produk','transaksi','barcode','warehouse','fifo','kapasitas','planner','reports','users','settings'];

let lastShownView=null;
function showView(v){
  const viewChanged = v!==lastShownView;
  document.getElementById('view-landing').style.display = v==='landing' ? '' : 'none';
  document.getElementById('view-login').style.display = v==='login' ? 'grid' : 'none';
  document.getElementById('view-register').style.display = v==='register' ? 'grid' : 'none';
  document.getElementById('view-app').style.display = v==='app' ? 'block' : 'none';
  const preLogin = v!=='app';
  const mascotEl = document.getElementById('mascot');
  const chatEl = document.getElementById('aiChatPanel');
  if(mascotEl){
    mascotEl.style.display = 'flex';
    mascotEl.classList.toggle('mascot-mini', !preLogin);
    mascotEl.title = preLogin ? 'Tanya Storify Assistant' : 'Tanya Storify AI';
  }
  if(chatEl){
    if(!preLogin) chatEl.classList.remove('open');
    chatEl.style.display = preLogin ? 'flex' : 'none';
  }
  setMascotParallaxActive(false);
  if(v==='landing'){
    if(window.__lastMascotView!=='landing') typeMascotGreeting();
  } else {
    hideMascotGreeting();
  }
  window.__lastMascotView = v;
  if(viewChanged) window.scrollTo(0,0);
  lastShownView=v;
}
function renderView(){
  const key=(location.hash||'#/').replace('#','');
  const authed = !!currentUser;
  if(APP_PAGES.includes(key)){
    if(!authed){ location.hash='#login'; return; }
    showView('app'); applyUserInfo(); applyNavRoles(); applyTheme(); route(); return;
  }
  if(key==='login'){
    if(authed){ location.hash='#dashboard'; return; }
    showView('login'); return;
  }
  if(key==='register'){
    if(authed){ location.hash='#dashboard'; return; }
    showView('register'); return;
  }
  if(authed){ location.hash='#dashboard'; return; }
  showView('landing');
}
function goDashboardOrLogin(){
  location.hash = currentUser ? '#dashboard' : '#login';
}
window.addEventListener('hashchange', renderView);

function togglePassword(){
  const inp=document.getElementById('loginPassword'), icon=document.getElementById('pwIcon');
  const show = inp.type==='password';
  inp.type = show?'text':'password';
  icon.textContent = show?'visibility_off':'visibility';
}
function toggleRegisterPassword(inputId, iconId){
  const inp=document.getElementById(inputId), icon=document.getElementById(iconId);
  if(!inp) return;
  const show=inp.type==='password';
  inp.type=show?'text':'password';
  if(icon) icon.textContent=show?'visibility_off':'visibility';
}
async function handleRegister(e){
  e.preventDefault();
  const name=document.getElementById('regName').value.trim();
  const email=document.getElementById('regEmail').value.trim();
  const password=document.getElementById('regPassword').value;
  const confirm=document.getElementById('regPasswordConfirm').value;
  const errBox=document.getElementById('registerError');
  const fail=(msg)=>{ if(errBox){errBox.textContent=msg;errBox.classList.remove('hidden');} showToast(msg); return false; };
  if(!name||!email||!password) return fail('Lengkapi semua kolom.');
  if(password.length<6) return fail('Password minimal 6 karakter.');
  if(password!==confirm) return fail('Konfirmasi password tidak sama.');
  if(errBox) errBox.classList.add('hidden');

  const {ok, data} = await apiFetch('/api/register', {
    method:'POST',
    body: JSON.stringify({name, email, password, password_confirmation: confirm}),
  });
  if(!ok || !data?.ok){
    const msg = data?.errors ? Object.values(data.errors).flat().join(' ') : (data?.message || 'Gagal membuat akun.');
    return fail(msg);
  }
  await syncFromServer();
  showToast(data.message || ('Akun berhasil dibuat. Selamat datang, '+name+'!'));
  setTimeout(()=>{location.hash='#dashboard';},300);
  return false;
}
async function handleLogin(e){
  e.preventDefault();
  const email=document.getElementById('loginEmail').value.trim();
  const pass=document.getElementById('loginPassword').value;
  const errBox=document.getElementById('loginError');
  const {ok, data} = await apiFetch('/api/login', {
    method:'POST',
    body: JSON.stringify({email, password: pass}),
  });
  if(!ok || !data?.ok){
    const msg = data?.errors ? Object.values(data.errors).flat().join(' ') : (data?.message || 'Email atau password salah.');
    if(errBox){errBox.textContent=msg;errBox.classList.remove('hidden');}
    showToast(msg);
    return false;
  }
  if(errBox) errBox.classList.add('hidden');
  await syncFromServer();
  showToast('Login berhasil');
  setTimeout(()=>{location.hash='#dashboard';},300);
  return false;
}
function avatarUrlWithCacheBust(url){
  if(!url) return url;
  const separator = url.includes('?') ? '&' : '?';
  return `${url}${separator}v=${Date.now()}`;
}
function applyUserInfo(){
  if(!currentUser) return;
  document.querySelectorAll('.userNameSlot').forEach(el=>el.textContent=currentUser.name);
  document.querySelectorAll('.userRoleSlot').forEach(el=>el.textContent=currentUser.role.charAt(0).toUpperCase()+currentUser.role.slice(1));
  const img=document.getElementById('sidebarAvatarImg'), icon=document.getElementById('sidebarAvatarIcon');
  if(img && icon){
    if(currentUser.avatar){ img.src=avatarUrlWithCacheBust(currentUser.avatar); img.classList.remove('hidden'); icon.classList.add('hidden'); }
    else { img.classList.add('hidden'); icon.classList.remove('hidden'); }
  }
}
function hexToRgbString(hex){
  const value = String(hex || '#3d4b3c').trim();
  const clean = value.startsWith('#') ? value.slice(1) : value;
  if(clean.length===3){
    return clean.split('').map(ch => ch + ch).join(', ');
  }
  const normalized = clean.length===6 ? clean : '3d4b3c';
  const r = parseInt(normalized.slice(0,2),16);
  const g = parseInt(normalized.slice(2,4),16);
  const b = parseInt(normalized.slice(4,6),16);
  if(Number.isNaN(r) || Number.isNaN(g) || Number.isNaN(b)) return '61 75 60';
  return `${r} ${g} ${b}`;
}
function shadeHexColor(hex, percent){
  const value = String(hex || '#3d4b3c').trim();
  const clean = value.startsWith('#') ? value.slice(1) : value;
  const normalized = clean.length===3 ? clean.split('').map(ch => ch + ch).join('') : clean;
  const num = Number.parseInt(normalized, 16);
  if(Number.isNaN(num)) return '#3d4b3c';
  const amt = Math.round(2.55 * percent);
  const r = Math.min(255, Math.max(0, (num >> 16) + amt));
  const g = Math.min(255, Math.max(0, ((num >> 8) & 0x00FF) + amt));
  const b = Math.min(255, Math.max(0, (num & 0x0000FF) + amt));
  return `#${[r,g,b].map(v => v.toString(16).padStart(2,'0')).join('')}`;
}
function resolveAccentPalette(accent){
  const accentKey = String(accent || 'green');
  const accentValues={green:'44 74 59',blue:'29 78 216',orange:'194 65 12',purple:'124 58 237'};
  const accent2Values={green:'71 98 79',blue:'30 64 175',orange:'154 52 18',purple:'109 40 217'};
  if(accentKey in accentValues){
    return {
      accent: accentValues[accentKey],
      accent2: accent2Values[accentKey],
      ink: accentKey === 'blue' ? '29 78 216' : accentKey === 'orange' ? '194 65 12' : accentKey === 'purple' ? '124 58 237' : '44 74 59',
      preset: true,
    };
  }
  const baseHex = accentKey.startsWith('#') ? accentKey : `#${accentKey}`;
  const accentRgb = hexToRgbString(baseHex);
  const accent2Hex = shadeHexColor(baseHex, -18);
  return {
    accent: accentRgb,
    accent2: hexToRgbString(accent2Hex),
    ink: accentRgb,
    preset: false,
  };
}
function applyTheme(){
  if(!currentUser) return;
  const el=document.getElementById('view-app');
  if(!el) return;
  el.classList.remove('theme-dark');
  let theme=currentUser.theme||'light';
  if(theme==='system') theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  if(theme==='dark') el.classList.add('theme-dark');
  el.classList.remove('accent-green','accent-blue','accent-orange','accent-purple','accent-custom');
  const accentPalette = resolveAccentPalette(currentUser.accent || 'green');
  if(accentPalette.preset) el.classList.add('accent-'+(currentUser.accent||'green'));
  else el.classList.add('accent-custom');

  const rootVars = {
    '--accent': accentPalette.accent,
    '--accent2': accentPalette.accent2,
    '--accent-ink': accentPalette.ink,
    '--custom-accent': accentPalette.accent,
    '--custom-accent2': accentPalette.accent2,
    '--custom-accent-ink': accentPalette.ink,
    '--surface': theme==='dark'?'28 34 29':'255 255 255',
    '--surface-tint': theme==='dark'?'35 42 36':'238 236 226',
    '--surface-tint2': theme==='dark'?'44 52 45':'220 223 208',
    '--page-bg': theme==='dark'?'19 23 20':'246 244 238',
  };
  Object.entries(rootVars).forEach(([name, value]) => {
    document.documentElement.style.setProperty(name, value);
    el.style.setProperty(name, value);
  });

  const chatPanels=[document.getElementById('aiChatPanel'),document.getElementById('aiMiniPanel')].filter(Boolean);
  chatPanels.forEach(panel=>{
    panel.classList.toggle('theme-dark', theme==='dark');
    panel.classList.remove('accent-green','accent-blue','accent-orange','accent-purple','accent-custom');
    if(accentPalette.preset) panel.classList.add('accent-'+(currentUser.accent||'green'));
    else panel.classList.add('accent-custom');
    panel.style.setProperty('--accent', accentPalette.accent);
    panel.style.setProperty('--accent2', accentPalette.accent2);
    panel.style.setProperty('--accent-ink', accentPalette.ink);
    panel.style.setProperty('--custom-accent', accentPalette.accent);
    panel.style.setProperty('--custom-accent2', accentPalette.accent2);
    panel.style.setProperty('--custom-accent-ink', accentPalette.ink);
  });
}
function resetGlobalTheme(){
  const defaults={
    '--accent':'44 74 59', '--accent2':'71 98 79', '--accent-ink':'44 74 59',
    '--surface':'255 255 255', '--surface2':'250 249 244', '--surface-tint':'238 236 226', '--surface-tint2':'220 223 208',
    '--page-bg':'246 244 238', '--ink':'28 33 29', '--ink-muted':'92 98 89', '--border-c':'215 217 204',
  };
  Object.entries(defaults).forEach(([name,value])=>document.documentElement.style.setProperty(name,value));
}
async function logout(){
  await apiFetch('/api/logout', {method:'POST'});
  currentUser=null;
  loadAIHistory();
  resetGlobalTheme();
  document.querySelectorAll('#aiChatPanel,#aiMiniPanel').forEach(panel=>panel.classList.remove('theme-dark','accent-green','accent-blue','accent-orange','accent-purple'));
  document.getElementById('loginForm')?.reset();
  document.getElementById('loginError')?.classList.add('hidden');
  const pwInp=document.getElementById('loginPassword'), pwIcon=document.getElementById('pwIcon');
  if(pwInp){ pwInp.value=''; pwInp.defaultValue=''; pwInp.type='password'; }
  if(pwIcon) pwIcon.textContent='visibility';
  document.getElementById('userMenu')?.classList.add('hidden');
  document.getElementById('view-app')?.classList.remove('theme-dark','accent-green','accent-blue','accent-orange','accent-purple');
  showToast('Anda telah keluar');
  setTimeout(()=>{ location.hash='#/'; renderView(); },300);
}
function toggleUserMenu(e){
  e.stopPropagation();
  document.getElementById('notifPanel')?.classList.add('hidden');
  document.getElementById('userMenu').classList.toggle('hidden');
}
document.addEventListener('click', ()=>{ document.getElementById('userMenu')?.classList.add('hidden'); });
function toggleNotifPanel(e){
 e.stopPropagation();
 document.getElementById('userMenu')?.classList.add('hidden');
 document.getElementById('notifPanel')?.classList.toggle('hidden');
}
document.addEventListener('click', ()=>{ document.getElementById('notifPanel')?.classList.add('hidden'); });

function go(page){location.hash=page}
let currentAppPage='dashboard';
let lastPageBeforeAI='dashboard';
function backFromAI(){ go(lastPageBeforeAI||'dashboard'); }
function route(){
 let page=(location.hash||'#dashboard').slice(1);
 if(!roleAllowed(page)){ showToast('Akses ditolak untuk role '+currentUser.role); page='dashboard'; location.hash='dashboard'; }
 const target = document.getElementById('page-'+page) ? page : 'dashboard';
 if(target==='ai' && currentAppPage!=='ai') lastPageBeforeAI=currentAppPage;
 currentAppPage=target;
 document.querySelectorAll('#view-app .page').forEach(x=>x.classList.remove('active'));
 document.getElementById('page-'+target).classList.add('active');
 document.querySelectorAll('#view-app .nav-item').forEach(x=>x.classList.toggle('active',x.dataset.page===target));
 document.querySelector('#view-app aside')?.classList.remove('open');
 document.getElementById('drawerOverlay')?.classList.remove('show');
 if(target==='produk')renderProducts();
if(target==='transaksi'){
  const viewOnly = currentUser && currentUser.role==='supervisor';
  document.getElementById('trxEditView')?.classList.toggle('hidden', viewOnly);
  document.getElementById('trxViewOnlyWrap')?.classList.toggle('hidden', !viewOnly);
  document.getElementById('trxHistoryBtn')?.classList.toggle('hidden', viewOnly);
  const subtitle=document.getElementById('trxSubtitle');
  if(subtitle) subtitle.textContent = viewOnly ? 'Lihat riwayat pergerakan stok (khusus tampilan).' : 'Catat pergerakan stok secara akurat.';
  if(viewOnly){ renderHistoryTable(); } else { fillProductSelect('trxProduct');onTrxTypeChange();onTrxProductChange(); }
} if(target==='barcode'){fillProductSelect('barcodeProduct');document.getElementById('barcodeSearch').value='';}
 if(target==='fifo')renderFIFO();
 if(target==='warehouse')renderStorifyView();
 if(target==='kapasitas')renderCapacity();
 if(target==='reports')renderReports();
 if(target==='users')renderUsers();
 if(target==='settings')fillSettingsForm();
 if(target==='dashboard')renderDashboard();
 if(target==='ai')renderAIPage();
}
function fillProductSelect(id){
 const s=document.getElementById(id);if(!s)return;
 s.innerHTML=products.map(p=>`<option value="${p.id}">${p.name} — ${productStock(p.id).toLocaleString()} kg</option>`).join('');
}

function renderDashboard(){
 const totalStock=products.reduce((s,p)=>s+productStock(p.id),0);
 const today=ymd(new Date());
 const todayIn=transactions.filter(t=>t.type==='in'&&t.created_at.slice(0,10)===today).reduce((s,t)=>s+t.quantity,0);
 const todayOut=transactions.filter(t=>t.type==='out'&&t.created_at.slice(0,10)===today).reduce((s,t)=>s+t.quantity,0);
 document.getElementById('statProducts').textContent=products.length;
 document.getElementById('statTotalStock').textContent=totalStock.toLocaleString();
 document.getElementById('statTodayIn').textContent=todayIn.toLocaleString();
 document.getElementById('statTodayOut').textContent=todayOut.toLocaleString();
 document.getElementById('activityList').innerHTML = transactions.length ? transactions.slice(0,5).map(t=>{
   const p=products.find(x=>x.id===t.product_id);
   return `<div class="flex items-center gap-3 py-3 border-b border-line"><div class="w-9 h-9 rounded-full ${t.type==='in'?'bg-sage':'bg-amber/30'} flex items-center justify-center"><span class="material-symbols-outlined">${t.type==='in'?'south_west':'north_east'}</span></div><div class="flex-1"><b>${p?p.name:'-'}</b><div class="text-sm text-muted">${t.notes||(t.type==='in'?'Barang masuk':'Barang keluar')} • ${t.created_at.slice(0,10)}</div></div><strong>${t.type==='in'?'+':'-'}${Number(t.quantity).toLocaleString()} kg</strong></div>`;
 }).join('') : `<p class="text-muted text-sm py-3">Belum ada aktivitas.</p>`;
 document.getElementById('dashboardCapacity').innerHTML = products.map(p=>{
   const st=capacityStatus(p);
   return `<div><div class="flex justify-between"><b>${p.name}</b><span>${Math.min(100,Math.round(st.pct))}%</span></div><div class="h-2 bg-sage2 rounded-full mt-2"><div class="h-full bg-primary rounded-full" style="width:${Math.min(100,st.pct)}%"></div></div></div>`;
 }).join('');
}

function renderProducts(){
 const q=(document.getElementById('productSearch')?.value||'').toLowerCase();
 const canManage = currentUser && ['admin','supervisor'].includes(currentUser.role);
  document.getElementById('btnAddProduct')?.classList.toggle('hidden', !canManage);
 const filtered=products.filter(p=>p.name.toLowerCase().includes(q)||p.location.toLowerCase().includes(q)||(p.sku||'').toLowerCase().includes(q));
 if(!filtered.length){ document.getElementById('productTable').innerHTML=`<tr><td colspan="8" class="text-muted text-center py-6">${products.length?'Produk tidak ditemukan.':'Belum ada produk. Klik "Tambah Produk" untuk memulai.'}</td></tr>`; return; }
 document.getElementById('productTable').innerHTML=filtered.map(p=>{
   const st=capacityStatus(p), stock=productStock(p.id);
  return `<tr><td><b>${p.name}</b></td><td class="font-mono text-sm">${p.sku||'-'}</td><td>${p.location}</td><td class="font-mono">${stock.toLocaleString()} kg</td><td class="font-mono">${Number(p.capacity).toLocaleString()} kg</td><td><span class="badge ${st.cls}">${st.label}</span></td><td class="text-sm text-muted">${p.temperature} · ${p.humidity}</td><td>${canManage?`<div class="flex gap-2"><button onclick="editProduct(${p.id})" class="text-primary">Edit</button><button onclick="deleteProduct(${p.id})" class="text-danger">Hapus</button></div>`:'-'}</td></tr>`;
 }).join('');
}
function suggestNextRackLocation(){
 const letters=products.map(p=>{
   const m=(p.location||'').match(/([A-Za-z])\s*\d/);
   return m ? m[1].toUpperCase() : null;
 }).filter(Boolean);
 let nextCode=65;
 if(letters.length) nextCode=Math.max(...letters.map(l=>l.charCodeAt(0)))+1;
 const letter=String.fromCharCode(nextCode);
 return `Rak ${letter}1 – ${letter}2`;
}

async function openProductForm(){
 if(currentUser && currentUser.role==='petugas'){ showToast('Petugas tidak memiliki akses untuk menambah produk.'); return; }
 const name=prompt('Nama produk:');
 if(name===null || !name.trim()) return;
 const suggestedLocation=suggestNextRackLocation();
 const location=prompt('Lokasi rak:', suggestedLocation);
 if(location===null) return;
 const capacityInput=prompt('Kapasitas (kg):','100000');
 if(capacityInput===null) return;
 const capacity=Number(capacityInput)||100000;

 const {ok, data} = await apiFetch('/api/products', {
   method:'POST',
   body: JSON.stringify({name: name.trim(), location: location.trim()||suggestedLocation, capacity}),
 });
 if(!ok || !data?.ok){
   showToast(data?.errors ? Object.values(data.errors).flat().join(' ') : (data?.message || 'Gagal menambah produk.'));
   return;
 }
 await syncFromServer();renderProducts();showToast(data.message);
}

async function editProduct(productId){
 const product=products.find(p=>p.id===productId);
 if(!product || !currentUser || !['admin','supervisor'].includes(currentUser.role)) return;
 const name=prompt('Nama produk:',product.name);
 if(name===null || !name.trim()) return;
 const location=prompt('Lokasi rak:',product.location);
 if(location===null || !location.trim()) return;
 const capacityInput=prompt('Kapasitas (kg):',String(product.capacity));
 if(capacityInput===null) return;
 const capacity=Number(capacityInput);
 if(!capacity || capacity<1){showToast('Kapasitas harus lebih dari 0 kg.');return;}
 const {ok,data}=await apiFetch(`/api/products/${productId}`,{
   method:'PUT',
   body:JSON.stringify({name:name.trim(),location:location.trim(),capacity}),
 });
 if(!ok || !data?.ok){showToast(data?.message||'Gagal mengubah produk.');return;}
 await syncFromServer();
 renderProducts();
 showToast(data.message||'Produk berhasil diperbarui.');
}

async function deleteProduct(productId){
 const product=products.find(p=>p.id===productId);
 if(!product || !confirm(`Arsipkan produk "${product.name}"? Riwayat transaksi tetap tersimpan.`)) return;
 const {ok, data}=await apiFetch(`/api/products/${productId}`, {method:'DELETE'});
 if(!ok || !data?.ok){
   showToast(data?.message || 'Gagal mengarsipkan produk.');
   return;
 }
 await syncFromServer();
 renderProducts();
 showToast(data.message);
}

function onTrxTypeChange(){
 const type=document.getElementById('trxType').value;
 document.getElementById('trxBatchWrap').style.display = type==='in' ? '' : 'none';
 document.getElementById('trxDateWrap').style.display = type==='in' ? '' : 'none';
 document.getElementById('trxHint').textContent = type==='in'
   ? 'Barang masuk akan membuat batch baru dengan nomor & tanggal terima.'
   : 'Barang keluar otomatis mengambil batch tertua terlebih dahulu (FIFO) — tidak perlu memilih batch manual.';
}
function onTrxProductChange(){
 const pid=Number(document.getElementById('trxProduct').value);
 const p=products.find(x=>x.id===pid);
 if(!p)return;
 document.getElementById('trxLocation').value=p.location;
 document.getElementById('trxCurrentStock').textContent=productStock(p.id).toLocaleString();
 document.getElementById('trxCurrentLoc').textContent='kg • '+p.location;
}
async function saveTransaction(){
 const pid=Number(document.getElementById('trxProduct').value);
 const type=document.getElementById('trxType').value;
 const qty=Number(document.getElementById('trxQty').value);
 let res;
 if(type==='in'){
   res=await storeIn({product_id:pid,quantity:qty,batch:document.getElementById('trxBatch').value.trim(),received_at:document.getElementById('trxDate').value});
 }else{
   res=await storeOut({product_id:pid,quantity:qty});
 }
 if(res.ok){
   const p=products.find(x=>x.id===pid);
   const st=p?capacityStatus(p):null;
   if(st && st.label==='Penuh'){
     showToast('Kapasitas penyimpanan penuh. Tidak tersedia ruang penyimpanan yang tersisa.');
   }else if(st && st.label==='Hampir Penuh'){
     showToast('Peringatan: Kapasitas penyimpanan hampir penuh. Sisa kapasitas hanya '+Math.round(st.remaining)+'%.');
   }else{
     showToast(res.msg);
   }
   resetTrx();
   onTrxProductChange();
 }else{
   showToast(res.msg);
 }
}

function refreshAll(){
 renderDashboard();
 renderProducts();
 renderCapacity();
 renderFIFO();
 renderReports();
 fillProductSelect('trxProduct');
 fillProductSelect('barcodeProduct');
 renderNotifications();
 renderHistoryTable();
}

function computeNotifications(){
 const notifs=[];
 products.forEach(p=>{
   const stock=productStock(p.id);
   const st=capacityStatus(p);
   if(stock<=0){
     notifs.push({level:'danger',icon:'error',title:'Stok habis',message:`${p.name} kehabisan stok.`});
   }
   if(st.label==='Penuh'){
     notifs.push({level:'danger',icon:'error',title:'Kapasitas penuh',message:`${p.name}: kapasitas penyimpanan penuh. Tidak tersedia ruang penyimpanan yang tersisa.`});
   }else if(st.label==='Hampir Penuh'){
     notifs.push({level:'warning',icon:'warning',title:'Kapasitas hampir penuh',message:`${p.name}: sisa kapasitas hanya ${Math.round(st.remaining)}%.`});
   }
 });
 return notifs;
}
async function markNotificationRead(notificationId){
 const notification=appNotifications.find(n=>n.id===notificationId);
 if(!notification || notification.read_at) return;
 const {ok, data}=await apiFetch(`/api/notifications/${notificationId}/read`,{method:'POST'});
 if(!ok || !data?.ok){showToast(data?.message || 'Notifikasi gagal ditandai sudah dibaca.');return;}
 appNotifications=appNotifications.filter(n=>n.id!==notificationId);
 renderNotifications();
}
function renderNotifications(){
 const list=[...appNotifications.filter(n=>!n.read_at).map(n=>({id:n.id,level:n.type==='security'?'info':'warning',icon:n.type==='security'?'security':'notifications',title:n.title,message:n.message,read_at:n.read_at})),...computeNotifications()];
 const badge=document.getElementById('notifBadge');
 const box=document.getElementById('notifList');
 if(badge){
   const unreadCount=list.filter(n=>!n.read_at).length;
   if(unreadCount){badge.textContent=unreadCount>9?'9+':String(unreadCount);badge.classList.remove('hidden');}
   else badge.classList.add('hidden');
 }
 if(box){
   box.innerHTML = list.length ? list.map(n=>
     `<div class="notification-item flex items-start gap-3 p-3 border-b border-line last:border-b-0 ${n.read_at?'':'is-new'}"><span class="material-symbols-outlined ${n.level==='danger'?'text-danger':n.level==='info'?'text-primary':'text-amber'}">${n.icon}</span><div class="flex-1 min-w-0"><b class="text-sm">${n.title}</b><div class="text-xs text-muted mt-0.5">${n.message}</div>${n.id?`<button type="button" onclick="markNotificationRead(${n.id})" class="mt-2 text-xs font-semibold text-primary hover:underline">Sudah dibaca</button>`:''}</div></div>`
  ).join('') : `<div class="notification-empty p-6 text-center text-muted text-sm"><span class="material-symbols-outlined block mb-2 text-[28px]">notifications_none</span>Tidak ada notifikasi baru</div>`;
 }
}
function resetTrx(){document.querySelectorAll('#page-transaksi input').forEach(x=>{if(x.id!=='trxLocation')x.value=''});document.getElementById('trxDate').value=ymd(new Date())}

function formatDateTime(iso){
 const d=new Date(iso);
 if(isNaN(d)) return '-';
 const tgl=d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
 const jam=d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
 return `${tgl} ${jam}`;
}
function renderHistoryTable(){
 const html = transactions.length ? transactions.map(t=>{
   const p=products.find(x=>x.id===t.product_id);
   const u=users.find(x=>x.id===t.user_id);
   return `<tr>
     <td class="font-mono text-sm">${formatDateTime(t.created_at)}</td>
     <td><b>${p?p.name:'-'}</b></td>
     <td><span class="badge ${t.type==='in'?'bg-primary/10 text-primary':'bg-amber/30 text-[#6b4a1c]'}">${t.type==='in'?'Barang Masuk':'Barang Keluar'}</span></td>
     <td class="font-mono">${t.type==='in'?'+':'-'}${Number(t.quantity).toLocaleString()} kg</td>
     <td class="text-sm text-muted">${t.notes||'-'}</td>
     <td class="text-sm">${u?u.name:'-'}</td>
   </tr>`;
 }).join('') : `<tr><td colspan="6" class="text-muted text-center py-6">Belum ada riwayat transaksi.</td></tr>`;
 const box=document.getElementById('historyTable');
 if(box) box.innerHTML = html;
 const inlineBox=document.getElementById('historyTableInline');
 if(inlineBox) inlineBox.innerHTML = html;
}
function openHistoryModal(){
 renderHistoryTable();
 document.getElementById('historyModal').classList.remove('hidden');
}
function closeHistoryModal(){
 document.getElementById('historyModal').classList.add('hidden');
}

function generateBarcode(){
 const s=document.getElementById('barcodeProduct'),p=products.find(x=>x.id===Number(s.value));if(!p)return;
 renderBarcodeFor(p);
}
function renderBarcodeFor(p){
 const sku=p.sku||('SF-'+String(p.id).padStart(5,'0'));
 const box=document.getElementById('barcodeBox');
 box.innerHTML=`<svg id="barcodeSvg"></svg>`;
 try{
   if(typeof JsBarcode==='undefined') throw new Error('lib not loaded');
   JsBarcode('#barcodeSvg', sku, {format:'CODE128', lineColor:'#1c211d', width:2, height:70, displayValue:true, fontSize:14, margin:8});
 }catch(err){
   box.innerHTML=`<div class="text-danger text-sm p-4">Gagal membuat barcode. Periksa koneksi internet lalu coba lagi.</div>`;
   showToast('Gagal membuat barcode.');
   return;
 }
 document.getElementById('barcodeLabel').innerHTML=`<b>${p.name}</b><br><span class="text-muted">${p.location}</span><br><span>${sku}</span>`;
 document.getElementById('barcodePrintBtn').classList.remove('hidden');
 document.getElementById('barcodeProduct').value=String(p.id);
}
function searchByBarcode(q){
 q=q.trim().toLowerCase();
 if(!q)return;
 const p=products.find(x=>(x.sku||'').toLowerCase()===q || (x.sku||'').toLowerCase().includes(q) || x.name.toLowerCase().includes(q));
 if(p) renderBarcodeFor(p);
}
let cameraScanReader=null;
let cameraScanContext='barcode';
async function openCameraScan(context){
 cameraScanContext=context||'barcode';
 const modal=document.getElementById('cameraScanModal');
 const status=document.getElementById('cameraScanStatus');
 modal.classList.remove('hidden');modal.classList.add('flex');
 status.textContent='Meminta izin kamera...';

 const isSecure = window.isSecureContext || location.protocol==='https:' || ['localhost','127.0.0.1'].includes(location.hostname);
 if(!isSecure){
   status.textContent='Kamera diblokir browser karena halaman ini tidak dibuka lewat HTTPS.';
   return;
 }
 if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){
   status.textContent='Kamera tidak didukung di browser ini. Coba pakai Chrome/Safari versi terbaru.';
   return;
 }
 if(typeof ZXing==='undefined'){
   status.textContent='Modul pemindai gagal dimuat. Periksa koneksi internet lalu buka ulang.';
   return;
 }
 try{
   cameraScanReader=new ZXing.BrowserMultiFormatReader();
   status.textContent='Arahkan kamera ke barcode produk...';
   await cameraScanReader.decodeFromVideoDevice(undefined,'cameraScanVideo',(result,err)=>{
     if(result) handleScannedBarcode(result.getText());
   });
 }catch(err){
   console.error('Camera scan error:', err);
   if(err && err.name==='OverconstrainedError'){
     try{
       status.textContent='Kamera belakang tidak tersedia, mencoba kamera lain...';
       await cameraScanReader.decodeFromConstraints({video:true},'cameraScanVideo',(result)=>{
         if(result) handleScannedBarcode(result.getText());
       });
       status.textContent='Arahkan kamera ke barcode produk...';
       return;
     }catch(err2){ console.error('Camera scan fallback error:', err2); }
   }
   if(err && (err.name==='NotAllowedError' || err.name==='PermissionDeniedError')){
     status.textContent='Akses kamera ditolak. Izinkan akses kamera untuk situs ini lewat pengaturan browser/HP.';
   } else if(err && err.name==='NotFoundError'){
     status.textContent='Tidak ada kamera terdeteksi di perangkat ini.';
   } else if(err && err.name==='NotReadableError'){
     status.textContent='Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.';
   } else {
     status.textContent='Tidak bisa mengakses kamera. Pastikan izin kamera untuk situs ini sudah diizinkan.';
   }
 }
}
function handleScannedBarcode(text){
 const q=String(text||'').trim();
 if(!q) return;
 const p=products.find(x=>(x.sku||'').toLowerCase()===q.toLowerCase());
 if(!p){
   const status=document.getElementById('cameraScanStatus');
   if(status) status.textContent='Kode "'+q+'" tidak cocok dengan produk manapun. Coba lagi.';
   return;
 }
 const scanContext=cameraScanContext;
 closeCameraScan();
 if(scanContext==='transaksi'){
   const sel=document.getElementById('trxProduct');
   if(sel){
     sel.value=String(p.id);
     sel.dispatchEvent(new Event('change', {bubbles:true}));
   }
   showToast('Produk terisi otomatis lewat scan: '+p.name);
   document.getElementById('trxQty').focus();
 } else {
   renderBarcodeFor(p);
   document.getElementById('barcodeSearch').value=p.sku;
   showToast('Produk ditemukan lewat scan: '+p.name);
 }
}
function closeCameraScan(){
 const modal=document.getElementById('cameraScanModal');
 modal.classList.add('hidden');modal.classList.remove('flex');
 try{ cameraScanReader && cameraScanReader.reset(); }catch(e){}
 cameraScanReader=null;
 cameraScanContext='barcode';
}
function printBarcode(){
 const box=document.getElementById('barcodeBox');
 if(!box.querySelector('svg')){showToast('Generate barcode terlebih dahulu');return;}
 const label=document.getElementById('barcodeLabel').innerHTML;
 const w=window.open('','_blank');
 if(!w){showToast('Izinkan pop-up untuk mencetak barcode');return;}
 w.document.write(`<html><head><title>Cetak Barcode</title></head><body style="text-align:center;padding:40px;font-family:sans-serif">${box.innerHTML}<p>${label}</p></body></html>`);
 w.document.close();w.focus();w.print();
}

let draggedRackId=null;
function rackOrderKey(){return `storify_rack_order_${currentUser?.id||'guest'}`;}
function orderedStorifyProducts(){
 const saved=JSON.parse(localStorage.getItem(rackOrderKey())||'[]');
 const byId=new Map(products.map(p=>[p.id,p]));
 const ordered=saved.map(id=>byId.get(Number(id))).filter(Boolean);
 products.forEach(p=>{if(!ordered.includes(p)) ordered.push(p);});
 return ordered;
}
function renderStorifyView(){
 const canMove=currentUser && currentUser.role!=='petugas';
 document.getElementById('storifyRacks').innerHTML=orderedStorifyProducts().map(p=>
  `<button draggable="${canMove}" data-product-id="${p.id}" ondragstart="dragRackStart(event,${p.id})" ondragend="dragRackEnd(event)" onclick="showLocationPath(${p.id})" class="card p-5 text-left hover:scale-[1.02] transition storify-rack ${canMove?'':'storify-rack-readonly'}"><b>${p.name}</b><div class="text-sm text-muted mt-1">${p.location}</div><div class="text-xs font-mono text-primary mt-2">${productStock(p.id).toLocaleString()} kg</div></button>`
 ).join('');
}
function dragRackStart(event,id){
 if(!currentUser || currentUser.role==='petugas'){event.preventDefault();return;}
 draggedRackId=id;
 event.currentTarget.classList.add('dragging');
 event.dataTransfer.effectAllowed='move';
 event.dataTransfer.setData('text/plain',String(id));
}
function dragRackEnd(event){
 event.currentTarget.classList.remove('dragging');
 draggedRackId=null;
}
function dragRackOver(event){event.preventDefault();event.dataTransfer.dropEffect='move';}
function dropRack(event){
 event.preventDefault();
 if(!currentUser || currentUser.role==='petugas') return;
 const target=event.target.closest('.storify-rack');
 const targetId=target?Number(target.dataset.productId):null;
 if(!draggedRackId || !targetId || draggedRackId===targetId) return;
 const order=orderedStorifyProducts().map(p=>p.id);
 const from=order.indexOf(draggedRackId),to=order.indexOf(targetId);
 if(from<0 || to<0) return;
 order.splice(from,1);order.splice(to,0,draggedRackId);
 localStorage.setItem(rackOrderKey(),JSON.stringify(order));
 renderStorifyView();
}
function showLocationPath(id){
 const p=products.find(x=>x.id===id);if(!p)return;
 showToast(`Jalur: Loading Area → Lorong Utama → ${p.location}`);
}

function priorityInfo(p){
 const stock=productStock(p.id);
 const pct = p.capacity? (stock/p.capacity)*100 : 0;
 if(stock===0 || pct>=95) return {label:'Sangat Tinggi',cls:'bg-danger/15 text-danger',note:stock===0?'Stok habis':'Kapasitas kritis'};
 if(pct>=80) return {label:'Tinggi',cls:'bg-amber/40 text-[#6b4a1c]',note:'< 20% kapasitas tersisa'};
 if(pct>=50) return {label:'Sedang',cls:'bg-amber/20 text-[#6b4a1c]',note:'Mulai mendekati batas'};
 return {label:'Rendah',cls:'bg-sage text-primary',note:'Kondisi normal'};
}

function renderFIFO(){
 const rows=[];
 products.forEach(p=>{
   const pool=productBatches(p.id).filter(b=>b.quantity>0).sort((a,b)=>a.received_at<b.received_at?-1:(a.received_at>b.received_at?1:a.id-b.id));
   pool.forEach((b,i)=>rows.push({p,b,i}));
 });
 document.getElementById('fifoTable').innerHTML = rows.length ? rows.map(({p,b,i})=>{
   const pr=priorityInfo(p);
   return `<tr><td class="font-mono">#${i+1}</td><td><b>${p.name}</b></td><td class="font-mono">${b.number}</td><td>${b.received_at}</td><td class="font-mono">${Number(b.quantity).toLocaleString()} kg</td><td><span class="badge ${pr.cls}">${pr.label}</span><div class="text-xs text-muted mt-1">${i===0?'Keluarkan lebih dulu (FIFO) · ':''}${pr.note}</div></td></tr>`;
 }).join('') : `<tr><td colspan="6" class="text-muted text-center py-6">Belum ada batch tersedia.</td></tr>`;
}

function renderCapacity(){
 document.getElementById('capacityCards').innerHTML=products.map(p=>{
   const st=capacityStatus(p), stock=productStock(p.id), pct=Math.min(100,st.pct);
   return `<div class="card p-6"><div class="flex justify-between items-center"><h3 class="text-xl font-semibold">${p.name}</h3><span class="badge ${st.cls}">${st.label}</span></div><div class="h-3 bg-sage2 rounded-full mt-5"><div class="h-full bg-primary rounded-full" style="width:${pct}%"></div></div><div class="flex justify-between mt-3 text-sm text-muted"><span>${stock.toLocaleString()} kg terpakai</span><span>${Number(p.capacity).toLocaleString()} kg kapasitas</span></div></div>`;
 }).join('');
}

function plannerInputsChanged(){
 lastPlannerResult=null;
 document.getElementById('plannerResult')?.classList.add('hidden');
}
function calculatePlanner(){
 const length=Number(document.getElementById('plLength').value);
 const width=Number(document.getElementById('plWidth').value);
 const rackLength=Number(document.getElementById('plRackLength').value);
 const rackWidth=Number(document.getElementById('plRackWidth').value);
 const aisle=Number(document.getElementById('plAisle').value);
 if(!length||!width||!rackLength||!rackWidth||!aisle){showToast('Lengkapi semua ukuran terlebih dahulu');return;}
 const r=calculateWarehouse({length,width,rackLength,rackWidth,aisle});
 document.getElementById('plArea').textContent=r.area.toLocaleString();
 document.getElementById('plRacks').textContent=r.racks.toLocaleString();
 document.getElementById('plannerResult').classList.remove('hidden');
 lastPlannerResult={...r,length,width,rackLength,rackWidth,aisle};
}

let reportRows=[];
function fillReportDefaults(){
 const fromEl=document.getElementById('repFrom'), toEl=document.getElementById('repTo');
 if(fromEl && !fromEl.value){
   const d=new Date(); d.setDate(1);
   fromEl.value=ymd(d);
 }
 if(toEl && !toEl.value) toEl.value=ymd(new Date());
}
function renderReports(){
 fillReportDefaults();
 const from=document.getElementById('repFrom').value;
 const to=document.getElementById('repTo').value;
 reportRows = transactions.filter(t=>{
   const d=t.created_at.slice(0,10);
   return (!from||d>=from) && (!to||d<=to);
 }).sort((a,b)=>a.created_at<b.created_at?1:-1);

 const totalIn=reportRows.filter(t=>t.type==='in').reduce((s,t)=>s+t.quantity,0);
 const totalOut=reportRows.filter(t=>t.type==='out').reduce((s,t)=>s+t.quantity,0);
 document.getElementById('repIn').textContent=totalIn.toLocaleString()+' kg';
 document.getElementById('repOut').textContent=totalOut.toLocaleString()+' kg';
 const net=totalIn-totalOut;
 document.getElementById('repNet').textContent=(net>=0?'+':'')+net.toLocaleString()+' kg';
 document.getElementById('repCount').textContent=reportRows.length.toLocaleString();

 document.getElementById('repProductTable').innerHTML = products.map(p=>{
   const rows=reportRows.filter(t=>t.product_id===p.id);
   const pin=rows.filter(t=>t.type==='in').reduce((s,t)=>s+t.quantity,0);
   const pout=rows.filter(t=>t.type==='out').reduce((s,t)=>s+t.quantity,0);
   const pnet=pin-pout;
   return `<tr><td><b>${p.name}</b></td><td class="font-mono">+${pin.toLocaleString()} kg</td><td class="font-mono">-${pout.toLocaleString()} kg</td><td class="font-mono ${pnet>=0?'':'text-danger'}">${pnet>=0?'+':''}${pnet.toLocaleString()} kg</td><td class="font-mono">${productStock(p.id).toLocaleString()} kg</td></tr>`;
 }).join('');

 document.getElementById('repTrxTable').innerHTML = reportRows.length ? reportRows.map(t=>{
   const p=products.find(x=>x.id===t.product_id);
   return `<tr><td class="font-mono">${t.created_at.slice(0,10)}</td><td><span class="badge ${t.type==='in'?'bg-primary/10 text-primary':'bg-amber/30 text-[#6b4a1c]'}">${t.type==='in'?'Masuk':'Keluar'}</span></td><td>${p?p.name:'-'}</td><td class="font-mono">${(batches.find(b=>b.id===t.batch_id)||{}).number||'-'}</td><td class="font-mono">${t.type==='in'?'+':'-'}${Number(t.quantity).toLocaleString()} kg</td><td>${t.location||'-'}</td><td class="text-sm text-muted">${t.notes||'-'}</td></tr>`;
 }).join('') : `<tr><td colspan="7" class="text-muted text-center py-6">Tidak ada transaksi pada periode ini.</td></tr>`;
}
function downloadReport(){
 if(!reportRows) reportRows=[];
 if(!reportRows.length){ showToast('Tidak ada data untuk diunduh pada periode ini.'); return; }
 const from=document.getElementById('repFrom').value, to=document.getElementById('repTo').value;
 const esc=v=>`"${String(v??'').replace(/"/g,'""')}"`;
 const lines=[['Tanggal','Jenis','Produk','Batch','Jumlah (kg)','Lokasi','Catatan'].map(esc).join(',')];
 reportRows.forEach(t=>{
   const p=products.find(x=>x.id===t.product_id);
   const b=batches.find(x=>x.id===t.batch_id);
   lines.push([t.created_at.slice(0,10), t.type==='in'?'Masuk':'Keluar', p?p.name:'-', b?b.number:'-', t.quantity, t.location||'-', t.notes||'-'].map(esc).join(','));
 });
 const csv='\uFEFF'+lines.join('\r\n');
 const blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});
 const url=URL.createObjectURL(blob);
 const a=document.createElement('a');
 a.href=url; a.download=`laporan-storify-farm_${from||'awal'}_sampai_${to||'sekarang'}.csv`;
 document.body.appendChild(a); a.click(); a.remove();
 URL.revokeObjectURL(url);
 showToast('Laporan berhasil diunduh.');
}

function renderUsers(){
 document.getElementById('usersTable').innerHTML=users.map(u=>
   `<tr><td>${u.name}</td><td>${u.email}</td><td><span class="badge bg-primary/15 text-primary">${u.role}</span></td><td><button onclick="resetUserPassword(${u.id})" class="text-primary font-semibold">Reset Password</button></td></tr>`
 ).join('');
}
async function resetUserPassword(userId){
 const user=users.find(u=>u.id===userId);
 if(!user || user.id===currentUser?.id) return;
 const password=prompt('Password baru untuk '+user.name+':');
 if(password===null) return;
 const confirmPassword=prompt('Ulangi password baru:');
 if(password!==confirmPassword){showToast('Konfirmasi password tidak sama.');return;}
 const {ok,data}=await apiFetch(`/api/users/${userId}/reset-password`,{method:'POST',body:JSON.stringify({password,password_confirmation:confirmPassword})});
 if(!ok || !data?.ok){showToast(data?.message||'Gagal mereset password.');return;}
 await syncFromServer();
 showToast(data.message||'Password berhasil direset.');
}
async function addUser(){
 const name=document.getElementById('userName').value.trim();
 const email=document.getElementById('userEmail').value.trim();
 const password=document.getElementById('userPassword').value;
 const role=document.getElementById('userRole').value;
 if(!name||!email||!password||password.length<6){showToast('Lengkapi data (password minimal 6 karakter)');return;}
 const {ok, data} = await apiFetch('/api/users', {
   method:'POST',
   body: JSON.stringify({name, email, password, role}),
 });
 if(!ok || !data?.ok){
   showToast(data?.errors ? Object.values(data.errors).flat().join(' ') : (data?.message || 'Gagal membuat user.'));
   return;
 }
 await syncFromServer();renderUsers();showToast(data.message || 'User dibuat.');
 ['userName','userEmail','userPassword'].forEach(id=>document.getElementById(id).value='');
}

function toggleCustomAccentInput(){
  const accentSelect = document.getElementById('setAccent');
  const accentCustomWrap = document.getElementById('customAccentWrap');
  const accentCustomInput = document.getElementById('setAccentCustom');
  const isCustom = accentSelect && accentSelect.value === 'custom';
  if(accentCustomWrap) accentCustomWrap.classList.toggle('hidden', !isCustom);
  if(accentCustomInput && !isCustom && accentCustomInput.value) accentCustomInput.value = accentCustomInput.value;
}
function fillSettingsForm(){
 if(!currentUser)return;
 const accentValue = currentUser.accent || 'green';
 document.getElementById('setName').value=currentUser.name;
 document.getElementById('setTheme').value=currentUser.theme||'light';
 const accentSelect = document.getElementById('setAccent');
 const accentCustomInput = document.getElementById('setAccentCustom');
 const presetValues=['green','blue','orange','purple'];
 accentSelect.value = presetValues.includes(accentValue) ? accentValue : 'custom';
 if(accentCustomInput){ accentCustomInput.value = presetValues.includes(accentValue) ? '#3d4b3c' : accentValue; }
 toggleCustomAccentInput();
 document.getElementById('setCurrentPassword').value='';
 document.getElementById('setPassword').value='';
 document.getElementById('setPasswordConfirm').value='';
 document.getElementById('passwordChangeFields')?.classList.add('hidden');
 document.getElementById('passwordChangeArrow')?.classList.remove('rotate-90');
 document.getElementById('setAvatar').value='';
 const img=document.getElementById('avatarPreview'), icon=document.getElementById('avatarPreviewIcon');
 if(currentUser.avatar){ img.src=avatarUrlWithCacheBust(currentUser.avatar); img.classList.remove('hidden'); icon.classList.add('hidden'); }
 else { img.classList.add('hidden'); icon.classList.remove('hidden'); }
}
function togglePasswordChange(){
 const fields=document.getElementById('passwordChangeFields');
 const arrow=document.getElementById('passwordChangeArrow');
 const toggle=document.getElementById('passwordChangeToggle');
 if(!fields)return;
 const hidden=fields.classList.toggle('hidden');
 arrow?.classList.toggle('rotate-90',!hidden);
 if(toggle) toggle.setAttribute('aria-expanded',String(!hidden));
}
function previewAvatar(){
 const file=document.getElementById('setAvatar').files[0];
 if(!file) return;
 const reader=new FileReader();
 reader.onload=()=>{
   const img=document.getElementById('avatarPreview'), icon=document.getElementById('avatarPreviewIcon');
   img.src=reader.result; img.classList.remove('hidden'); icon.classList.add('hidden');
 };
 reader.readAsDataURL(file);
}
async function saveSettings(){
 const name=document.getElementById('setName').value.trim();
 const theme=document.getElementById('setTheme').value;
 const accentSelect=document.getElementById('setAccent');
 const accentCustom=document.getElementById('setAccentCustom');
 const accent = accentSelect?.value === 'custom' ? (accentCustom?.value || '#3d4b3c') : (accentSelect?.value || 'green');
 if(!name){showToast('Nama wajib diisi');return;}
 const file=document.getElementById('setAvatar').files[0];
 const form=new FormData();
 form.append('name',name);form.append('theme',theme);form.append('accent',accent);
 const newPassword=document.getElementById('setPassword').value;
 const passwordConfirm=document.getElementById('setPasswordConfirm').value;
 const currentPassword=document.getElementById('setCurrentPassword').value;
 if(newPassword && (!currentPassword || newPassword!==passwordConfirm || newPassword.length<6)){showToast('Lengkapi password saat ini dan konfirmasi password baru.');return;}
 if(newPassword){form.append('current_password',currentPassword);form.append('password',newPassword);form.append('password_confirmation',passwordConfirm);}
 if(file) form.append('avatar',file);
 const {ok, data} = await apiFetch('/api/settings', {method:'POST', body:form});
 if(!ok || !data?.ok){
   showToast(data?.errors ? Object.values(data.errors).flat().join(' ') : (data?.message || 'Gagal menyimpan pengaturan.'));
   return;
 }
 await syncFromServer();
 applyUserInfo();applyTheme();showToast(data.message || 'Pengaturan tersimpan.');
}
async function deleteAccount(){
 const password=document.getElementById('deleteAccountPassword')?.value || '';
 if(!password){showToast('Masukkan password saat ini.');return;}
 if(!confirm('Hapus akun dan seluruh data workspace secara permanen? Tindakan ini tidak dapat dibatalkan.')) return;
 const {ok,data}=await apiFetch('/api/account',{method:'DELETE',body:JSON.stringify({current_password:password})});
 if(!ok || !data?.ok){showToast(data?.errors ? Object.values(data.errors).flat().join(' ') : (data?.message || 'Gagal menghapus akun.'));return;}
 currentUser=null;
 products=[];batches=[];transactions=[];users=[];appNotifications=[];
 resetGlobalTheme();
 showToast(data.message || 'Akun berhasil dihapus.');
 setTimeout(()=>{location.hash='#/';renderView();},500);
}

function mascotAction(){
  const inApp = document.getElementById('view-app').style.display !== 'none';
  if(inApp) toggleMiniAIPanel(); else toggleAIChat();
}

function syncMascotPanelPosition(){
  const mascot=document.getElementById('mascot');
  if(!mascot) return;
  const mascotRect=mascot.getBoundingClientRect();
  document.querySelectorAll('.ai-chat-panel.open').forEach(panel=>{
    const gap=12;
    const left=Math.max(8,Math.min(mascotRect.left+mascotRect.width-panel.offsetWidth,window.innerWidth-panel.offsetWidth-8));
    const above=mascotRect.top-panel.offsetHeight-gap;
    const top=above>=8 ? above : Math.min(window.innerHeight-panel.offsetHeight-8,mascotRect.bottom+gap);
    panel.style.left=left+'px';
    panel.style.top=Math.max(8,top)+'px';
    panel.style.right='auto';
    panel.style.bottom='auto';
  });
}

let aiAppHistory=[];
let activeAppAIContainerId='aiAppMessages';
let activeAppAIFormId='aiAppForm';
let aiAppRequestActive=false;

function appAIGreeting(){
  return `Halo${currentUser?', '+currentUser.name.split(' ')[0]:''}! 👋 Saya Storify AI. Tanyakan stok, lokasi barang, FIFO, kapasitas gudang, atau rencana Warehouse Planner.`;
}
function renderAppAIMessagesInto(containerId){
  const box=document.getElementById(containerId);
  if(!box) return;
  box.innerHTML='';
  if(!aiAppHistory.length){
    appendChatMessage(containerId,'bot', appAIGreeting());
    return;
  }
  aiAppHistory.forEach(m=>appendChatMessage(containerId, m.role==='user'?'user':'bot', m.text));
}
function renderAIPage(){
  activeAppAIContainerId='aiAppMessages';
  activeAppAIFormId='aiAppForm';
  renderAppAIMessagesInto('aiAppMessages');
  setTimeout(()=>document.getElementById('aiAppInput')?.focus(),50);
}
function toggleMiniAIPanel(){
  const panel=document.getElementById('aiMiniPanel');
  const opening=!panel.classList.contains('open');
  panel.classList.toggle('open');
  if(opening){
    activeAppAIContainerId='aiMiniMessages';
    activeAppAIFormId='aiMiniForm';
    renderAppAIMessagesInto('aiMiniMessages');
    setTimeout(()=>document.getElementById('aiMiniInput')?.focus(),50);
  }
  requestAnimationFrame(syncMascotPanelPosition);
}
function openFullAIPage(){
  document.getElementById('aiMiniPanel').classList.remove('open');
  go('ai');
}

async function sendAppAIMessage(e, forcedText){
  if(e && e.preventDefault) e.preventDefault();
  if(aiAppRequestActive) return false;
  const containerId=activeAppAIContainerId;
  const form=document.getElementById(activeAppAIFormId);
  const input=form ? form.querySelector('input.input') : null;
  const text=(forcedText!=null ? forcedText : (input?input.value:'')||'').trim();
  if(!text) return false;
  aiAppRequestActive=true;
  const btn=form ? form.querySelector('.ai-send-btn') : null;
  appendChatMessage(containerId,'user',text);
  aiAppHistory.push({role:'user',text});
  saveAIHistory();
  if(forcedText==null && input) input.value='';
  if(input) input.disabled=true; if(btn) btn.disabled=true;
  appendTyping(containerId);

  try{
    const reply=await getSmartAIReply(text, aiAppHistory.slice(0,-1), buildAppAISystemPrompt());
    removeTyping(containerId);
    appendChatMessage(containerId,'bot', reply);
    aiAppHistory.push({role:'model',text:reply});
    saveAIHistory();
    aiAppRequestActive=false;
    if(input){ input.disabled=false; input.focus(); } if(btn) btn.disabled=false;
  }catch(error){
    removeTyping(containerId);
    appendChatMessage(containerId,'bot', 'Maaf, Storify AI sedang tidak dapat menjawab. Silakan coba lagi.');
    aiAppRequestActive=false;
    if(input){ input.disabled=false; input.focus(); } if(btn) btn.disabled=false;
  }

  return false;
}
function consultAIAboutPlanner(){
  const hasPlannerResult=!!lastPlannerResult;
  document.getElementById('aiMiniPanel')?.classList.remove('open');
  location.hash='ai';
  setTimeout(()=>{
    renderAIPage();
    if(hasPlannerResult) sendAppAIMessage(null,'Saya sudah menghitung rencana Warehouse Planner. Tolong bantu konsultasikan hasilnya dan beri saran jika masih ada yang perlu diperbaiki.');
  }, 60);
}

function globalSearch(q){if(q.trim()){go('produk');setTimeout(()=>{document.getElementById('productSearch').value=q;renderProducts()},50)}}
function toggleMobile(){document.querySelector('#view-app aside').classList.toggle('open');document.getElementById('drawerOverlay').classList.toggle('show')}

document.getElementById('trxDate').value=ymd(new Date());
function updateMascotEyes(clientX, clientY){
  const svg = document.getElementById('mascotSvg');
  if(!svg || svg.getBoundingClientRect().width===0) return;
  const rect = svg.getBoundingClientRect();
  const vb = svg.viewBox.baseVal;
  const scaleX = vb.width/rect.width, scaleY = vb.height/rect.height;
  const localX = (clientX - rect.left) * scaleX;
  const localY = (clientY - rect.top) * scaleY;
  svg.querySelectorAll('.pupil').forEach(p=>{
    const bx = parseFloat(p.dataset.bx), by = parseFloat(p.dataset.by);
    const dx = localX - bx, dy = localY - by;
    const dist = Math.min(3.2, Math.hypot(dx,dy)/10);
    const ang = Math.atan2(dy,dx);
    p.setAttribute('cx', bx + Math.cos(ang)*dist);
    p.setAttribute('cy', by + Math.sin(ang)*dist);
  });
  const body = document.getElementById('mascotBody');
  if(body){
    const lean = Math.max(-6, Math.min(6, (localX-60)/8));
    body.style.transform = `rotate(${lean}deg)`;
    body.style.transformOrigin = '60px 118px';
  }
}
document.addEventListener('mousemove', e=>{ updateMascotEyes(e.clientX, e.clientY); updateMascotParallax(e.clientX, e.clientY); });
document.addEventListener('touchmove', e=>{ if(e.touches[0]) updateMascotEyes(e.touches[0].clientX, e.touches[0].clientY); }, {passive:true});

const MASCOT_PARALLAX_MAX_OFFSET = 14;
const MASCOT_PARALLAX_MAX_TILT   = 4;
const MASCOT_PARALLAX_EASE       = 0.10;
let mascotParallaxActive = false;
let mascotTargetX=0, mascotTargetY=0, mascotTargetRot=0;
let mascotCurX=0, mascotCurY=0, mascotCurRot=0;
let mascotParallaxRAF = null;
function prefersReducedMotion(){
  return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}
function setMascotParallaxActive(active){
  mascotParallaxActive = active;
  if(!active){
    mascotTargetX = mascotTargetY = mascotTargetRot = 0;
    mascotCurX = mascotCurY = mascotCurRot = 0;
    if(mascotParallaxRAF){ cancelAnimationFrame(mascotParallaxRAF); mascotParallaxRAF = null; }
    const mascotEl = document.getElementById('mascot');
    if(mascotEl) mascotEl.style.transform = '';
  }
}
function updateMascotParallax(clientX, clientY){
  if(!mascotParallaxActive || prefersReducedMotion()) return;
  const cx = window.innerWidth/2, cy = window.innerHeight/2;
  const dx = Math.max(-1, Math.min(1, (clientX-cx)/cx));
  const dy = Math.max(-1, Math.min(1, (clientY-cy)/cy));
  mascotTargetX = dx * MASCOT_PARALLAX_MAX_OFFSET;
  mascotTargetY = dy * MASCOT_PARALLAX_MAX_OFFSET;
  mascotTargetRot = dx * MASCOT_PARALLAX_MAX_TILT;
  startMascotParallaxLoop();
}
function startMascotParallaxLoop(){
  if(mascotParallaxRAF) return;
  const mascotEl = document.getElementById('mascot');
  const step = ()=>{
    mascotCurX += (mascotTargetX - mascotCurX) * MASCOT_PARALLAX_EASE;
    mascotCurY += (mascotTargetY - mascotCurY) * MASCOT_PARALLAX_EASE;
    mascotCurRot += (mascotTargetRot - mascotCurRot) * MASCOT_PARALLAX_EASE;
    if(mascotEl) mascotEl.style.transform = `translate3d(${mascotCurX.toFixed(2)}px, ${mascotCurY.toFixed(2)}px, 0) rotate(${mascotCurRot.toFixed(2)}deg)`;
    const settled = Math.abs(mascotTargetX-mascotCurX)<0.05 && Math.abs(mascotTargetY-mascotCurY)<0.05 && Math.abs(mascotTargetRot-mascotCurRot)<0.05;
    if(!settled){ mascotParallaxRAF = requestAnimationFrame(step); }
    else { mascotParallaxRAF = null; }
  };
  mascotParallaxRAF = requestAnimationFrame(step);
}
document.documentElement.addEventListener('mouseleave', ()=>{
  if(!mascotParallaxActive) return;
  mascotTargetX = mascotTargetY = mascotTargetRot = 0;
  startMascotParallaxLoop();
});

const MASCOT_POSITION_KEY='storify_mascot_position_v3';
let mascotDragState=null;
let mascotSuppressClick=false;
function clampMascotPosition(left, top, el){
  const margin=8;
  return {
    left:Math.max(margin,Math.min(left,window.innerWidth-el.offsetWidth-margin)),
    top:Math.max(margin,Math.min(top,window.innerHeight-el.offsetHeight-margin)),
  };
}
function applySavedMascotPosition(){
  const mascot=document.getElementById('mascot');
  if(!mascot) return;
  try{
    const saved=JSON.parse(localStorage.getItem(MASCOT_POSITION_KEY)||'null');
    if(!saved || !Number.isFinite(saved.left) || !Number.isFinite(saved.top)) return;
    const position=clampMascotPosition(saved.left,saved.top,mascot);
    mascot.style.left=position.left+'px';
    mascot.style.top=position.top+'px';
    mascot.style.right='auto';
    mascot.style.bottom='auto';
  }catch(e){}
}
function initMascotDrag(){
  const mascot=document.getElementById('mascot');
  const handle=mascot?.querySelector('.mascot-float');
  if(!mascot || !handle) return;
  applySavedMascotPosition();
  handle.addEventListener('pointerdown', e=>{
    if(e.button!==0) return;
    const rect=mascot.getBoundingClientRect();
    mascotDragState={pointerId:e.pointerId,startX:e.clientX,startY:e.clientY,left:rect.left,top:rect.top,moved:false};
    handle.setPointerCapture?.(e.pointerId);
    setMascotParallaxActive(false);
  });
  handle.addEventListener('pointermove', e=>{
    const state=mascotDragState;
    if(!state || state.pointerId!==e.pointerId) return;
    const distance=Math.hypot(e.clientX-state.startX,e.clientY-state.startY);
    if(distance>4) state.moved=true;
    if(!state.moved) return;
    const position=clampMascotPosition(state.left+e.clientX-state.startX,state.top+e.clientY-state.startY,mascot);
    mascot.style.left=position.left+'px';
    mascot.style.top=position.top+'px';
    mascot.style.right='auto';
    mascot.style.bottom='auto';
    mascot.style.transform='';
  });
  const finishDrag=e=>{
    const state=mascotDragState;
    if(!state || state.pointerId!==e.pointerId) return;
    if(state.moved){
      const rect=mascot.getBoundingClientRect();
      localStorage.setItem(MASCOT_POSITION_KEY,JSON.stringify({left:rect.left,top:rect.top}));
    }
    mascotSuppressClick=state.moved;
    mascotDragState=null;
    syncMascotPanelPosition();
  };
  handle.addEventListener('pointerup',finishDrag);
  handle.addEventListener('pointercancel',finishDrag);
  mascot.addEventListener('click',e=>{
    if(mascotSuppressClick){
      mascotSuppressClick=false;
      e.preventDefault();
      e.stopPropagation();
    }
  },true);
  window.addEventListener('resize',()=>{
    if(!mascot.style.left || !mascot.style.top) return;
    const rect=mascot.getBoundingClientRect();
    const position=clampMascotPosition(rect.left,rect.top,mascot);
    mascot.style.left=position.left+'px';
    mascot.style.top=position.top+'px';
    localStorage.setItem(MASCOT_POSITION_KEY,JSON.stringify(position));
    syncMascotPanelPosition();
  });
}
initMascotDrag();

const MASCOT_GREETING_TEXT = "Halo! Saya asisten AI Storify Farm. Ada yang bisa saya bantu?";
let mascotGreetingTimer = null;
function typeMascotGreeting(){
  const wrap = document.getElementById('mascotGreeting');
  const textEl = document.getElementById('mascotGreetingText');
  const caretEl = document.getElementById('mascotGreetingCaret');
  if(!wrap || !textEl) return;
  if(mascotGreetingTimer){ clearInterval(mascotGreetingTimer); mascotGreetingTimer = null; }
  wrap.classList.add('show');
  if(caretEl) caretEl.classList.remove('done');
  if(prefersReducedMotion()){
    textEl.textContent = MASCOT_GREETING_TEXT;
    if(caretEl) caretEl.classList.add('done');
    return;
  }
  let i = 0;
  mascotGreetingTimer = setInterval(()=>{
    i++;
    textEl.textContent = MASCOT_GREETING_TEXT.slice(0, i);
    if(i >= MASCOT_GREETING_TEXT.length){
      clearInterval(mascotGreetingTimer); mascotGreetingTimer = null;
      if(caretEl) caretEl.classList.add('done');
    }
  }, 32);
}
function hideMascotGreeting(){
  const wrap = document.getElementById('mascotGreeting');
  if(wrap) wrap.classList.remove('show');
  if(mascotGreetingTimer){ clearInterval(mascotGreetingTimer); mascotGreetingTimer = null; }
}

let aiHistory=[];
const AI_HISTORY_LIMIT=50;
let aiGuestRequestActive=false;
function aiHistoryKey(){return `storify_ai_history_${currentUser?.id||'guest'}`;}
function loadAIHistory(){
  try{
    const saved=JSON.parse(localStorage.getItem(aiHistoryKey())||'null');
    aiAppHistory=Array.isArray(saved?.app)?saved.app.slice(-AI_HISTORY_LIMIT):[];
    aiHistory=Array.isArray(saved?.guest)?saved.guest.slice(-AI_HISTORY_LIMIT):[];
  }catch(e){
    aiAppHistory=[];
    aiHistory=[];
  }
}
function saveAIHistory(){
  try{
    localStorage.setItem(aiHistoryKey(),JSON.stringify({app:aiAppHistory.slice(-AI_HISTORY_LIMIT),guest:aiHistory.slice(-AI_HISTORY_LIMIT)}));
  }catch(e){}
}
let lastPlannerResult=null;

const AI_STRICT_RULES=`ARAHAN UTAMA:
1. Kamu adalah asisten operasional Storify Farm. Prioritaskan bantuan terkait pergudangan, inventaris, margin, dan alur barang. Tetap responsif, ramah, dan nyambung saat merespons sapaan atau obrolan di luar topik; arahkan kembali ke konteks bisnis secara natural tanpa terkesan menolak atau kaku.
2. Selalu baca dan manfaatkan riwayat chat sebelum menjawab. Jika pengguna menanyakan kelanjutan fitur, cara kerja aplikasi seperti apakah prosesnya otomatis atau manual, atau memberi tanggapan singkat, jawab secara natural sesuai konteks percakapan tanpa meminta pengguna mengulang pertanyaan.
3. Jangan memutus obrolan dengan template penolakan kaku seperti "Saya hanya asisten gudang" jika pertanyaannya masih berkaitan dengan sistem, fitur, atau aplikasi Storify Farm.`;
const STORIFY_ASSISTANT_SYSTEM=`Kamu adalah "Storify Assistant", asisten AI untuk aplikasi Storify Farm.\n${AI_STRICT_RULES}`;
const WAREHOUSE_SEARCH_KEYWORDS='pergudangan, warehouse management, warehouse management system, WMS, manajemen gudang, stok, inventaris, inventory, produk, komoditas, harga pasar, harga komoditas, rantai pasok, supply chain, logistik, distribusi, pengadaan, barang masuk, barang keluar, FIFO, FEFO, batch, lot, SKU, barcode, lokasi rak, rak gudang, kapasitas gudang, layout gudang, tata letak gudang, pallet, palet, karung, cold storage, suhu penyimpanan, kelembapan, forklift, pallet jack, material handling, K3 gudang, keselamatan kerja, APD, kebakaran gudang, teknologi logistik';

function buildAppAISystemPrompt(){
  const stockLines = products.map(p=>{
    const st=productStock(p.id);
    const cap=capacityStatus(p);
    return `- ${p.name} (SKU ${p.sku||'-'}): lokasi ${p.location}, stok ${st.toLocaleString()} kg dari kapasitas maksimum ${Number(p.capacity||0).toLocaleString()} kg, status kapasitas ${cap.label}`;
  }).join('\n');
  let plannerLine='Belum ada hasil perhitungan Warehouse Planner untuk sesi ini.';
  if(lastPlannerResult){
    const r=lastPlannerResult;
    plannerLine=`Luas gudang ${r.area.toLocaleString()} m², estimasi ${r.racks.toLocaleString()} unit rak (ukuran rak ${r.rackLength}×${r.rackWidth} m, lebar lorong ${r.aisle} m), untuk mengelola ${products.length} produk.`;
  }
  return `${AI_STRICT_RULES}\n\nKamu adalah "Storify AI" di dalam aplikasi Storify Farm yang sedang dipakai oleh ${currentUser?currentUser.name:'user'}${currentUser?' (role: '+currentUser.role+')':''}.\nData produk & stok saat ini:\n${stockLines || '(belum ada produk terdaftar)'}\nWarehouse Planner: ${plannerLine}`;
}

async function callSmartAI(systemPrompt, history, userText){
  const messages=(history||[]).slice(-8).map(h=>({role:h.role==='user'?'user':'model',text:h.text}));
  const {ok, data} = await apiFetch('/api/ai/chat',{
    method:'POST',
    body:JSON.stringify({system:systemPrompt, history:messages, message:userText}),
  });
  if(!ok || !data?.ok || !data.reply) throw new Error(data?.message || 'Storify AI request failed');
  return data.reply.trim();
}

function aiSystemPromptForSearch(basePrompt){
  return `${basePrompt}\n\nGunakan Gemini/ChatGPT dan pencarian internet bila dibutuhkan. Gunakan kata kunci pergudangan yang paling relevan dari daftar ini saat mencari: ${WAREHOUSE_SEARCH_KEYWORDS}. Jawab langsung, beri solusi taktis, dan sertakan sumber atau waktu data jika tersedia. Fokus pada isu utama pengguna; jangan mengarang data. Jika informasi kurang, jelaskan batasnya dan ajukan satu pertanyaan klarifikasi yang paling menentukan.`;
}

async function getSmartAIReply(userText, history, systemPrompt){
  const localReply=getAppAIReply(userText);
  if(localReply) return localReply;
  if(!isStorifyContextQuestion(userText)) return getOutOfScopeReply(userText);
  try{
    return await callSmartAI(aiSystemPromptForSearch(systemPrompt), history, userText);
  }catch(error){
    return AI_FALLBACK;
  }
}

const STORIFY_CONTEXT_KEYWORDS = [
  'storify', 'farm', 'website', 'web', 'situs', 'aplikasi', 'sistem', 'fitur', 'halaman',
  'login', 'daftar', 'akun', 'password', 'barcode', 'sku', 'scan', 'pindai', 'produk',
  'stok', 'stock', 'inventaris', 'inventory', 'gudang', 'warehouse', 'barang', 'transaksi',
  'batch', 'fifo', 'kapasitas', 'rak', 'lokasi', 'planner', 'laporan', 'report', 'operasional',
  'margin', 'harga pasar', 'komoditas', 'logistik', 'distribusi', 'pengadaan', 'supply chain'
];
function isStorifyContextQuestion(userText){
  const norm=normalizeText(userText);
  return STORIFY_CONTEXT_KEYWORDS.some(keyword=>norm.includes(normalizeText(keyword)));
}
function getOutOfScopeReply(userText){
  const norm=normalizeText(userText);
  if(/resep|kue|masak|memasak|masakan|makanan|minuman|goreng|panggang|baking/.test(norm)){
    return 'Saya belum bisa membantu membuat resep atau memasak. Saya bisa membantu menjawab pertanyaan tentang website, fitur Storify Farm, stok, gudang, dan operasional bisnis.';
  }
  return 'Pertanyaan ini di luar konteks Storify Farm. Saya tetap bisa membantu membahas website, fitur aplikasi, stok, gudang, atau operasional bisnis.';
}

const AI_KB = [
  { keywords:['siapa kamu','kamu siapa','kamu apa','apa itu storify','kamu ai beneran','kamu chatgpt','kamu gemini','kamu manusia bukan'],
    answer:'Saya Storify AI/Assistant, asisten bawaan Storify Farm. Biasanya saya tersambung ke model AI penuh jadi bisa diajak ngobrol apa saja seperti ChatGPT/Gemini — kalau jawaban ini kamu terima, kemungkinan koneksi ke server AI sedang bermasalah, jadi saya jawab pakai basis pengetahuan bawaan dulu 🙂' },
  { keywords:['bisa bantu apa','kamu bisa apa','fitur apa saja','apa saja fiturnya','fitur storify'],
    answer:'Saya bisa bantu jelasin semua fitur Storify Farm: Barcode/SKU, FIFO otomatis, Warehouse Planner, Storify View (panorama gudang), Reports, Kapasitas, sampai cara Login/daftar. Kalau kamu sudah login, saya juga bisa cek stok, lokasi produk, dan status kapasitas secara langsung.' },
  { keywords:['halo','hai','hi','hei','pagi','siang','sore','malam','assalamualaikum'],
    answer:'Halo juga! 👋 Ada yang mau ditanyakan soal Storify Farm?' },
  { keywords:['apa kabar','apa kabarnya','how are you'],
    answer:'Baik, siap membantu! 😊 Ada yang bisa saya bantu soal Storify Farm hari ini?' },
  { keywords:['siapa nama kamu','nama kamu siapa','namamu siapa'],
    answer:'Nama saya Storify AI — maskot & asisten resmi Storify Farm.' },
  { keywords:['siapa yang bikin barcode','pembuat barcode','barcodenya siapa','barcode dibuat siapa'],
    answer:'Di Storify Farm, barcode dibuat otomatis dari SKU produk. Generator barcode menggunakan library JsBarcode, sedangkan pemindaian kamera menggunakan ZXing. Nama dan SKU produknya tetap dikelola dari data produk di aplikasi.' },
  { keywords:['barcode','scan','pindai','sku','qr code','kode batang'],
    answer:'Storify Farm mendukung Barcode/SKU per produk — tinggal cari atau pindai untuk mencatat barang masuk/keluar tanpa input manual.' },
  { keywords:['fifo','first in first out','batch tertua','urutan keluar'],
    answer:'FIFO di Storify Farm berjalan otomatis per batch, jadi batch tertua selalu diprioritaskan keluar duluan sesuai tanggal masuknya.' },
  { keywords:['planner','tata letak','layout gudang','rancang gudang','desain gudang','hitung rak','jumlah rak','luas gudang'],
    answer:'Warehouse Planner membantu menghitung luas gudang, estimasi jumlah rak, suhu & kelembapan ideal, sebelum gudang mulai dipakai. Isi ukuran gudang & rak di halaman Warehouse Planner, klik "Hitung & Beri Saran", lalu kamu juga bisa klik "Konsultasi dengan AI" untuk saya review hasilnya.' },
  { keywords:['konsultasi ai','konsultasi dengan ai','review rencana','minta saran planner','saran gudang'],
    answer:'Untuk konsultasi rencana gudang, buka Warehouse Planner, isi ukurannya, klik "Hitung & Beri Saran", lalu klik tombol "💬 Konsultasi dengan AI" — saya akan review hasil hitungannya dan kasih saran berdasarkan angka aslinya.' },
  { keywords:['storify view','panorama','street view','virtual','peta gudang','denah'],
    answer:'Storify View itu panorama virtual gudang mirip Google Street View, jadi petugas bisa cepat menemukan lorong dan rak yang dituju.' },
  { keywords:['laporan','report','csv','excel','unduh laporan','download laporan'],
    answer:'Laporan barang masuk, keluar, stok, dan kapasitas dibuat otomatis dari data transaksi asli dan bisa diunduh dalam bentuk CSV (bisa dibuka di Excel) kapan saja lewat menu Reports.' },
  { keywords:['harga','biaya','bayar','langganan','price','gratis','paket'],
    answer:'Untuk info harga/paket terbaru, silakan hubungi tim kami lewat hello@storifyfarm.id ya — saya belum punya data harga resmi.' },
  { keywords:['install','pwa','offline','aplikasi hp','download aplikasi'],
    answer:'Storify Farm bisa di-install sebagai PWA langsung dari browser (tanpa app store), jadi bisa dipakai seperti aplikasi biasa di HP/laptop.' },
  { keywords:['login','masuk akun','daftar','buat akun','lupa password','ganti password'],
    answer:'Untuk masuk, klik tombol "Login" di pojok kanan atas, lalu gunakan akun perusahaan kamu. Belum punya akun? Klik "Daftar" atau hubungi admin gudang kamu untuk didaftarkan.' },
  { keywords:['stok','stock','barang masuk','barang keluar','inventaris','inventory','sisa stok'],
    answer:'Pencatatan stok di Storify Farm real-time — begitu ada barang masuk/keluar yang dicatat (manual atau lewat Barcode), datanya langsung update di dashboard.' },
  { keywords:['kapasitas','penuh','hampir penuh','sisa ruang'],
    answer:'Status kapasitas dihitung otomatis dari stok dibanding kapasitas maksimum tiap produk — kalau sudah di atas 80% terpakai, statusnya berubah jadi "Hampir Penuh" supaya kamu bisa antisipasi.' },
  { keywords:['lokasi','ada dimana','di rak mana','posisi barang'],
    answer:'Setiap produk sudah tercatat lokasi raknya — buka menu Data Produk untuk lihat lokasi tiap produk, atau menu Storify View untuk penampakan visualnya.' },
  { keywords:['kontak','hubungi','customer service','cs','email','telepon'],
    answer:'Kamu bisa hubungi tim Storify Farm lewat hello@storifyfarm.id untuk pertanyaan lebih lanjut.' },
  { keywords:['siapa pembuat','dibuat siapa','developer','pengembang'],
    answer:'Storify Farm dikembangkan sebagai Smart Warehouse Management System untuk gudang hasil panen — saya sendiri adalah asisten bawaannya, bukan bagian dari tim developer, jadi untuk pertanyaan teknis di luar aplikasi baiknya hubungi hello@storifyfarm.id.' },
  { keywords:['lucu','bercanda','jokes','cerita dong','nyanyi'],
    answer:'Haha, saya lebih jago urusan gudang & stok dibanding jadi komedian 😄 tapi kalau butuh hiburan, tanya saja hal ringan — saya usahakan tetap seru untuk dijawab.' },
  { keywords:['terima kasih','makasih','thanks','thank you','oke sip','mantap'],
    answer:'Sama-sama! Kalau ada pertanyaan lain soal Storify Farm, tanya saja ya 😊' },
  { keywords:['bye','dadah','sampai jumpa','selesai','cukup dulu'],
    answer:'Sampai jumpa! Kalau butuh bantuan lagi, saya selalu ada di sini 👋' }
];
const AI_FALLBACK = 'Pencarian AI dan Google sedang tidak tersedia karena kuota provider habis atau layanan sedang membatasi request. Pertanyaan ini tetap termasuk konsultasi pergudangan; coba lagi setelah kuota aktif atau API key diganti.';

function getRuleBasedReply(userText, fallback=AI_FALLBACK){
  const calculationReply=getWarehouseCalculationReply(userText);
  if(calculationReply) return calculationReply;
  const norm = normalizeText(userText);
  const tokens = norm.split(' ').filter(Boolean);
  for(const item of AI_KB){
    if(item.keywords.some(k=>phraseFuzzyMatch(norm,tokens,k))) return item.answer;
  }
  return fallback;
}

function getDataOnlyReply(userText){
  const norm = normalizeText(userText);
  const tokens = norm.split(' ').filter(Boolean);

  const productMatch = products.find(p => normalizeText(p.name) === norm || norm.includes(normalizeText(p.name)) || [...normalizeText(p.name).split(' ')].some(word => tokens.includes(word)));
  if(productMatch){
    const st = productStock(productMatch.id);
    const cap = capacityStatus(productMatch);
    return `${productMatch.name} saat ini ada di ${productMatch.location}, stok ${st.toLocaleString()} kg, dengan status kapasitas ${cap.label}.`;
  }

  if(phraseFuzzyMatch(norm, tokens, 'stok') || phraseFuzzyMatch(norm, tokens, 'stock')){
    return 'Saya hanya bisa menampilkan data stok yang sudah tercatat di aplikasi. Pilih produk yang ingin dicek, lalu saya akan sebutkan lokasi dan jumlah stok aktualnya.';
  }

  if(phraseFuzzyMatch(norm, tokens, 'lokasi')){
    return 'Data lokasi produk bisa dilihat di menu Data Produk atau Storify View, sesuai produk yang Anda cari.';
  }

  if(lastPlannerResult){
    const r = lastPlannerResult;
    return `Data terkini planner: luas gudang ${r.area.toLocaleString()} m², estimasi ${r.racks.toLocaleString()} rak, ukuran rak ${r.rackLength}×${r.rackWidth} m, lorong ${r.aisle} m.`;
  }

  return 'Data yang diminta belum tersedia di basis data aplikasi saat ini.';
}

function getCriticalReply(userText){
  const norm = normalizeText(userText);
  const tokens = norm.split(' ').filter(Boolean);
  const productMatch = products.find(p => normalizeText(p.name) === norm || norm.includes(normalizeText(p.name)) || [...normalizeText(p.name).split(' ')].some(word => tokens.includes(word)));

  if(productMatch){
    const st = productStock(productMatch.id);
    const cap = capacityStatus(productMatch);
    const warning = st <= 0 || cap.label.includes('Tinggi') || cap.label.includes('Penuh') || cap.label.includes('Kritis');
    if(warning){
      return `Saya melihat ${productMatch.name} berstatus ${cap.label} dengan stok ${st.toLocaleString()} kg di ${productMatch.location}. Ini perlu perhatian lebih cepat karena bisa menandakan kapasitas mulai kritis atau stok sudah sangat rendah.`;
    }
    return `${productMatch.name} masih dalam kondisi cukup aman: stok ${st.toLocaleString()} kg dan status kapasitas ${cap.label}.`;
  }

  if(lastPlannerResult){
    const r = lastPlannerResult;
    return `Dari hasil planner terakhir, luas gudang ${r.area.toLocaleString()} m² dan estimasi ${r.racks.toLocaleString()} rak masih masuk akal. Saya tetap sarankan untuk mengecek apakah jumlah rak cukup untuk semua produk tanpa menumpuk zona dengan kapasitas tinggi.`;
  }

  return 'Saya menilai jawaban ini perlu ditinjau lebih hati-hati. Untuk keputusan penting, saya sarankan cek data stok, kapasitas, dan lokasi produk yang relevan dulu sebelum mengambil aksi.';
}

function isCriticalAIQuestion(userText){
  const norm=normalizeText(userText);
  return /(kritis|risiko|bahaya|darurat|aman|penuh|hampir penuh|stok habis|kehabisan|prioritas|peringatan|perlu perhatian)/.test(norm);
}

function getLocalAIReply(userText){ return getAppAIReply(userText); }

function getWarehouseCalculationReply(userText){
  const norm=normalizeText(userText);
  if(!/(karung|sak|kantong|rak|kapasitas)/.test(norm)) return null;
  const values=[...String(userText).toLowerCase().matchAll(/(\d+(?:[.,]\d+)?)\s*(cm|meter|m|kg|gram|g)\b/g)]
    .map(match=>({value:Number(match[1].replace(',','.')),unit:match[2]}));
  const lengthValue=values.find(item=>['cm','meter','m'].includes(item.unit));
  const weightValue=values.find(item=>['kg','gram','g'].includes(item.unit));
  if(!lengthValue || !weightValue || !/(karung|sak|kantong)/.test(norm)) return null;
  const bagLength=lengthValue.unit==='cm'?lengthValue.value/100:lengthValue.value;
  const bagWeight=weightValue.unit==='kg'?weightValue.value:weightValue.value/1000;
  const rackMatch=norm.match(/rak(?:nya|\s+sepanjang|\s+panjang)?\s+(\d+(?:[.,]\d+)?)\s*(cm|meter|m)/);
  const rackLength=rackMatch ? (rackMatch[2]==='cm'?Number(rackMatch[1].replace(',','.'))/100:Number(rackMatch[1].replace(',','.'))) : (lastPlannerResult?.rackLength||null);
  const capacityMatch=norm.match(/(?:kapasitas|beban)\s+(?:rak\s*)?(\d+(?:[.,]\d+)?)\s*(kg|ton)/);
  const rackCapacity=capacityMatch ? Number(capacityMatch[1].replace(',','.'))*(capacityMatch[2]==='ton'?1000:1) : null;
  if(!rackLength && !rackCapacity){
    return `Saya bisa hitung, tetapi ukuran rak belum ada. Dari data Anda: satu karung panjang ${lengthValue.value} ${lengthValue.unit} dan berat ${weightValue.value} ${weightValue.unit}. Beri panjang rak (cm/m) atau kapasitas beban rak (kg), serta jumlah lapis jika karung ditumpuk.`;
  }
  const byLength=rackLength?Math.floor(rackLength/bagLength):Infinity;
  const byWeight=rackCapacity?Math.floor(rackCapacity/bagWeight):Infinity;
  const total=Math.min(byLength,byWeight);
  const parts=[];
  if(rackLength) parts.push(`panjang: floor(${rackLength} m / ${bagLength} m) = ${byLength} karung`);
  if(rackCapacity) parts.push(`berat: floor(${rackCapacity} kg / ${bagWeight} kg) = ${byWeight} karung`);
  return `Hasil perhitungan: ${total.toLocaleString()} karung per baris/lapis. ${parts.join('; ')}. Batasnya memakai angka terkecil. Jika rak memiliki beberapa lapis, kalikan dengan jumlah lapis setelah memastikan tinggi dan beban aman.`;
}

function getAppAIReply(userText){
  const norm = normalizeText(userText);
  const tokens = norm.split(' ').filter(Boolean);
  const calculationReply=getWarehouseCalculationReply(userText);
  if(calculationReply) return calculationReply;

  if(/harga\s+pasar|harga komoditas|pasar/.test(norm)) return null;
  if(/harga|biaya|price/.test(norm)) return getRuleBasedReply(userText, null);

  if(lastPlannerResult && /(rak|lorong|panjang|lebar|ganti|ubah|tata letak|layout)/.test(norm)){
    const r=lastPlannerResult;
    if(/panjang.*rak|rak.*panjang|ganti.*rak|ubah.*rak/.test(norm)){
      return `Bisa. Jika panjang rak diubah menjadi 4 meter, hasil Warehouse Planner perlu dihitung ulang karena jumlah rak dan ruang lorong bisa berubah. Data terakhir memakai rak ${r.rackLength}×${r.rackWidth} m dengan lorong ${r.aisle} m. Masukkan panjang 4 m di Warehouse Planner lalu klik "Hitung & Beri Saran" agar estimasi barunya berdasarkan ukuran gudang yang sebenarnya.`;
    }
    return `Saya masih memakai hasil planner terakhir: rak ${r.rackLength}×${r.rackWidth} m, lorong ${r.aisle} m, dan estimasi ${r.racks} unit rak. Untuk menguji perubahan layout, ubah ukurannya di Warehouse Planner lalu hitung ulang.`;
  }

  let bestProduct=null, bestScore=Infinity;
  for(const p of products){
    const pn = normalizeText(p.name);
    if(norm.includes(pn)){ bestProduct=p; bestScore=0; break; }
    for(const w of pn.split(' ')){
      for(const t of tokens){
        const d=levenshtein(w,t);
        if(d<=fuzzyTolerance(Math.max(w.length,t.length)) && d<bestScore){ bestScore=d; bestProduct=p; }
      }
    }
  }
  if(bestProduct){
    const st=productStock(bestProduct.id);
    const cap=capacityStatus(bestProduct);
    return `${bestProduct.name} ada di ${bestProduct.location}, stok saat ini ${st.toLocaleString()} kg (status kapasitas: ${cap.label}).`;
  }

  if(['planner','rencana','gudang saya','review','konsultasi','rekomendasi rak','saran'].some(k=>phraseFuzzyMatch(norm,tokens,k))){
    if(lastPlannerResult){
      const r=lastPlannerResult;
      const tight = products.filter(p=>{const c=capacityStatus(p); return c.label!=='Normal';});
      let advice = `Dari hitungan terakhir kamu: luas gudang ${r.area.toLocaleString()} m² dengan estimasi ${r.racks.toLocaleString()} unit rak (lorong ${r.aisle} m, rak ${r.rackLength}×${r.rackWidth} m). `;
      if(r.racks<products.length){
        advice += `Jumlah rak ini lebih sedikit dari jumlah produk yang kamu kelola (${products.length} produk) — pertimbangkan perbesar area atau perkecil ukuran rak/lorong supaya tiap produk kebagian rak sendiri. `;
      } else {
        advice += `Jumlah rak ini sudah cukup untuk ${products.length} produk yang kamu kelola. `;
      }
      if(tight.length){
        advice += `Perhatikan juga: ${tight.map(p=>p.name).join(', ')} sudah berstatus "Hampir Penuh/Penuh" — prioritaskan rak tambahan atau percepat rotasi FIFO untuk produk itu.`;
      } else {
        advice += `Semua produk saat ini masih di status kapasitas Normal, jadi rencana ini cukup aman untuk jangka pendek.`;
      }
      return advice;
    }
    return 'Kamu belum menghitung Warehouse Planner. Buka menu Warehouse Planner, isi ukuran gudang & rak, klik "Hitung & Beri Saran", baru klik "💬 Konsultasi dengan AI" supaya saya bisa kasih saran berdasarkan angka aslinya.';
  }

  if(phraseFuzzyMatch(norm,tokens,'lokasi')) return 'Gunakan menu Storify View untuk melihat lokasi rak dan jalur menuju barang, atau menu Data Produk untuk daftar lokasi tiap produk.';
  if(phraseFuzzyMatch(norm,tokens,'fifo')) return 'FIFO mengambil batch yang masuk paling awal terlebih dahulu saat barang keluar — otomatis, tanpa perlu kamu pilih manual.';
  if(phraseFuzzyMatch(norm,tokens,'kapasitas')) return 'Status kapasitas dihitung otomatis dari stok dibanding kapasitas maksimum tiap produk. Buka menu Kapasitas Gudang untuk lihat semua produk sekaligus.';
  if(phraseFuzzyMatch(norm,tokens,'stok') || phraseFuzzyMatch(norm,tokens,'stock')) return 'Sebut nama produknya (misal "stok Premium berapa?") dan saya cek langsung dari data terbaru.';

  return getRuleBasedReply(userText, null);
}

const EMOJI_TO_ICON = {
  '👋':'waving_hand', '🧭':'explore', '🧠':'psychology', '💬':'chat_bubble',
  '🙂':'sentiment_satisfied', '😊':'sentiment_satisfied', '😄':'sentiment_very_satisfied',
  '🤔':'psychology_alt', '👇':'arrow_downward', '✅':'check_circle', '⚠️':'warning'
};
function escapeHtml(str){
  return String(str).replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
function emojiToIconHtml(escapedText){
  let out = escapedText;
  for(const [emoji, icon] of Object.entries(EMOJI_TO_ICON)){
    if(out.includes(emoji)){
      out = out.split(emoji).join(`<span class="material-symbols-outlined ai-inline-icon">${icon}</span>`);
    }
  }
  return out;
}
function appendChatMessage(containerId, role, text){
  const box = document.getElementById(containerId);
  const div = document.createElement('div');
  div.className = 'ai-msg ' + (role==='user' ? 'user' : 'bot');
  div.innerHTML = emojiToIconHtml(escapeHtml(text));
  box.appendChild(div);
  box.scrollTop = box.scrollHeight;
  return div;
}
function appendTyping(containerId){
  const box = document.getElementById(containerId);
  const div = document.createElement('div');
  div.className = 'ai-msg bot typing';
  div.id = containerId+'TypingIndicator';
  div.innerHTML = '<span></span><span></span><span></span>';
  box.appendChild(div);
  box.scrollTop = box.scrollHeight;
}
function removeTyping(containerId){ document.getElementById(containerId+'TypingIndicator')?.remove(); }

async function sendAIMessage(e){
  e.preventDefault();
  if(aiGuestRequestActive) return false;
  const input = document.getElementById('aiChatInput');
  const text = input.value.trim();
  if(!text) return false;
  aiGuestRequestActive=true;
  const btn = document.querySelector('#aiChatForm .ai-send-btn');

  appendChatMessage('aiMessages','user', text);
  aiHistory.push({role:'user', text});
  saveAIHistory();
  input.value='';
  input.disabled=true; if(btn) btn.disabled=true;

  appendTyping('aiMessages');
  try{
    const reply=await getSmartAIReply(text, aiHistory.slice(0,-1), STORIFY_ASSISTANT_SYSTEM);
    removeTyping('aiMessages');
    appendChatMessage('aiMessages','bot', reply);
    aiHistory.push({role:'model', text:reply});
    saveAIHistory();
    aiGuestRequestActive=false;
    input.disabled=false; if(btn) btn.disabled=false; input.focus();
  }catch(error){
    removeTyping('aiMessages');
    appendChatMessage('aiMessages','bot', 'Maaf, Storify AI sedang tidak dapat menjawab. Silakan coba lagi.');
    aiGuestRequestActive=false;
    input.disabled=false; if(btn) btn.disabled=false; input.focus();
  }

  return false;
}

function toggleAIChat(){
  const panel = document.getElementById('aiChatPanel');
  const opening = !panel.classList.contains('open');
  panel.classList.toggle('open');
  if(opening){
    if(!document.getElementById('aiMessages').childElementCount){
      appendChatMessage('aiMessages','bot', 'Halo! 👋 Saya Storify Assistant. Tanya apa saja soal fitur Storify Farm, atau cara mulai pakai aplikasinya.');
    }
    document.getElementById('aiChatInput')?.focus();
  }
  requestAnimationFrame(syncMascotPanelPosition);
}

(async function init(){
  await loadBootstrapOrGuest();
  renderView();
})();
