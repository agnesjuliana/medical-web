/* ============================================
   HEALTHEDU — script.js
   ✅ Terhubung ke MySQL via PHP API
   ============================================ */

// ─────────────────────────────────────────────
// CONFIG API
// Sesuaikan BASE_URL dengan lokasi folder api/ di server kamu
// Contoh lokal:  'api'
// Contoh server: 'https://domain.com/healthedu/api'
// ─────────────────────────────────────────────
const API = 'api';   // ← ubah sesuai path server kamu

// ─────────────────────────────────────────────
// STATE
// ─────────────────────────────────────────────
let bmiGender  = 'male';
let tdeeGender = 'male';
let calGoal    = 2000;

// Data disimpan di memori (sync dari server saat login)
let foodLog = [];
let bmiLog  = [];
let weekLog = {};

// Auth state — token disimpan di localStorage
let authToken = localStorage.getItem('he_token') || null;
let authUser  = JSON.parse(localStorage.getItem('he_user') || 'null');

// ─────────────────────────────────────────────
// MENU DATA (statis, tidak berubah)
// ─────────────────────────────────────────────
const MENUS = [
  { emoji:'🥣', name:'Oatmeal Buah',          cat:'sarapan', cal:320, p:12, k:58, l:6,  desc:'Oat, pisang, stroberi, madu, susu rendah lemak' },
  { emoji:'🍳', name:'Telur + Roti Gandum',   cat:'sarapan', cal:385, p:22, k:35, l:16, desc:'2 telur rebus, 2 lembar roti gandum, selai kacang' },
  { emoji:'🥛', name:'Greek Yogurt Parfait',  cat:'sarapan', cal:295, p:18, k:42, l:5,  desc:'Yogurt Yunani, granola, blueberry, madu' },
  { emoji:'🍱', name:'Nasi Merah + Ayam Bakar',cat:'siang',  cal:510, p:38, k:62, l:10, desc:'100g nasi merah, 150g ayam bakar tanpa kulit, lalapan' },
  { emoji:'🥗', name:'Salad Tuna Mediterania',cat:'siang',   cal:340, p:32, k:18, l:14, desc:'Tuna kalengan, selada, tomat cherry, lemon dressing' },
  { emoji:'🍜', name:'Soto Ayam Bening',       cat:'siang',  cal:420, p:28, k:48, l:9,  desc:'Ayam suwir, bihun, telur, sayuran, kaldu bening' },
  { emoji:'🐟', name:'Ikan Panggang + Sayur',  cat:'malam',  cal:380, p:42, k:22, l:12, desc:'200g ikan kembung panggang, brokoli, wortel, jeruk nipis' },
  { emoji:'🥩', name:'Tempe Tahu Bacem',       cat:'malam',  cal:310, p:20, k:30, l:11, desc:'Tempe, tahu, kecap rendah garam, bawang, rempah' },
  { emoji:'🥦', name:'Cap Cay Sayur + Tahu',   cat:'malam',  cal:265, p:14, k:35, l:7,  desc:'Aneka sayuran, tahu, saus tiram rendah sodium' },
  { emoji:'🍌', name:'Pisang + Selai Kacang',  cat:'snack',  cal:190, p:5,  k:30, l:7,  desc:'1 pisang sedang + 1 sdm selai kacang alami' },
  { emoji:'🥜', name:'Mix Kacang Panggang',    cat:'snack',  cal:175, p:5,  k:8,  l:15, desc:'Almond, walnut, kacang mede tanpa garam (30g)' },
  { emoji:'🍎', name:'Buah Segar Campur',      cat:'snack',  cal:95,  p:1,  k:24, l:0,  desc:'Apel, pepaya, melon, semangka potong (150g)' },
];

const CAT_TAG  = { sarapan:'Sarapan', siang:'Makan Siang', malam:'Makan Malam', snack:'Snack' };
const CAT_CSS  = { sarapan:'tag-sarapan', siang:'tag-siang', malam:'tag-malam', snack:'tag-snack' };
const TYPE_EMOJI = { Sarapan:'🌅', 'Makan Siang':'☀️', 'Makan Malam':'🌙', Snack:'🍎' };

// ─────────────────────────────────────────────
// API HELPER
// ─────────────────────────────────────────────
async function apiPost(endpoint, data = {}) {
  if (authToken) data.token = authToken;
  const res = await fetch(`${API}/${endpoint}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });
  return res.json();
}

async function apiGet(endpoint, params = {}) {
  if (authToken) params.token = authToken;
  const qs  = new URLSearchParams(params).toString();
  const res = await fetch(`${API}/${endpoint}${qs ? '?' + qs : ''}`);
  return res.json();
}

// ─────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
  updateNavAuth();
  renderMenuGrid();
  animateCounters();
  navScrollEffect();

  if (authToken) {
    await loadAllDataFromServer();
  } else {
    // Fallback ke localStorage jika belum login
    foodLog = JSON.parse(localStorage.getItem('he_food') || '[]');
    bmiLog  = JSON.parse(localStorage.getItem('he_bmi')  || '[]');
    weekLog = JSON.parse(localStorage.getItem('he_week') || '{}');
  }

  renderFoodLog();
  renderBMILog();
  updateLogSummary();
});

// ─────────────────────────────────────────────
// LOAD DATA DARI SERVER
// ─────────────────────────────────────────────
async function loadAllDataFromServer() {
  try {
    // Food log hari ini
    const foodRes = await apiGet('food.php', { date: todayISO() });
    if (foodRes.success) {
      foodLog = foodRes.data.map(f => ({
        id:   f.id,
        name: f.name,
        cal:  parseInt(f.calories),
        type: f.meal_type,
        p:    parseInt(f.protein_g),
        k:    parseInt(f.carbs_g),
        l:    parseInt(f.fat_g),
        time: new Date(f.recorded_at).toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' }),
      }));
    }

    // BMI log
    const bmiRes = await apiGet('bmi.php');
    if (bmiRes.success) {
      bmiLog = bmiRes.data.map(b => ({
        id:    b.id,
        bmi:   b.bmi,
        cat:   b.category,
        color: bmiColor(parseFloat(b.bmi)),
        bg:    bmiBg(parseFloat(b.bmi)),
        w:     b.weight, h: b.height, age: b.age, gender: b.gender,
        date:  new Date(b.recorded_at).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' }),
        time:  new Date(b.recorded_at).toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' }),
      }));
    }

    // Week log untuk bar chart
    const weekRes = await apiGet('food.php', { action: 'week' });
    if (weekRes.success) {
      weekLog = {};
      weekRes.data.forEach(r => { weekLog[r.log_date] = parseInt(r.total_cal); });
    }

    // TDEE tersimpan (restore calGoal)
    const tdeeRes = await apiGet('tdee.php');
    if (tdeeRes.success && tdeeRes.data && tdeeRes.data.tdee) {
      calGoal = parseInt(tdeeRes.data.tdee);
    }
  } catch (e) {
    console.warn('Gagal muat data dari server, pakai lokal.', e);
  }
}

// ─────────────────────────────────────────────
// NAVIGATION (SPA)
// ─────────────────────────────────────────────
function navigate(page) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  const target = document.getElementById('page-' + page);
  if (target) {
    target.classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  if (page === 'log') {
    setTimeout(() => {
      updateLogSummary();
      renderBarChart();
      renderLineChart();
    }, 50);
  }
}

function scrollToFAQ() {
  navigate('home');
  setTimeout(() => {
    const el = document.getElementById('faq-anchor');
    if (el) el.scrollIntoView({ behavior: 'smooth' });
  }, 100);
}

// ─────────────────────────────────────────────
// NAVBAR
// ─────────────────────────────────────────────
function navScrollEffect() {
  window.addEventListener('scroll', () => {
    const nav = document.getElementById('navbar');
    nav.style.boxShadow = window.scrollY > 40
      ? '0 4px 24px rgba(0,0,0,.1)' : 'none';
  });
}

function toggleNav() {
  const m = document.getElementById('navMobile');
  m.classList.toggle('open');
}

// Tampilkan nama user / tombol logout jika sudah login
function updateNavAuth() {
  const navAuth = document.getElementById('navAuth');
  if (!navAuth) return;

  if (authUser) {
    navAuth.innerHTML = `
      <span style="font-weight:600;color:#064e3b;font-size:.9rem">
        <i class="fas fa-user-circle"></i> ${authUser.name.split(' ')[0]}
      </span>
      <button class="nav-btn-login" onclick="handleLogout()">
        <i class="fas fa-sign-out-alt"></i> Keluar
      </button>`;
  }
  // Jika belum login, HTML default dari index.html sudah benar
}

// ─────────────────────────────────────────────
// ANIMATED COUNTERS
// ─────────────────────────────────────────────
function animateCounters() {
  document.querySelectorAll('.count-up').forEach(el => {
    const target = parseInt(el.getAttribute('data-to'));
    const dur  = 1600;
    const step = target / (dur / 16);
    let cur    = 0;
    const t = setInterval(() => {
      cur = Math.min(cur + step, target);
      el.textContent = target >= 1000
        ? (cur / 1000).toFixed(1).replace('.0','') + 'K'
        : Math.floor(cur);
      if (cur >= target) clearInterval(t);
    }, 16);
  });
}

// ─────────────────────────────────────────────
// FAQ
// ─────────────────────────────────────────────
function toggleFAQ(btn) {
  const ans    = btn.nextElementSibling;
  const isOpen = btn.classList.contains('open');
  document.querySelectorAll('.fq').forEach(b => {
    b.classList.remove('open');
    b.nextElementSibling.classList.remove('open');
  });
  if (!isOpen) { btn.classList.add('open'); ans.classList.add('open'); }
}

// ─────────────────────────────────────────────
// CALC TABS
// ─────────────────────────────────────────────
function switchCalcTab(tab) {
  ['bmi','tdee'].forEach(t => {
    document.getElementById('tab-' + t).classList.toggle('active', t === tab);
    document.getElementById('cpanel-' + t).classList.toggle('active', t === tab);
  });
}

// ─────────────────────────────────────────────
// GENDER
// ─────────────────────────────────────────────
function setGender(g, mode) {
  if (mode === 'bmi') {
    bmiGender = g;
    document.getElementById('gbtn-male').classList.toggle('active',   g === 'male');
    document.getElementById('gbtn-female').classList.toggle('active', g === 'female');
  } else {
    tdeeGender = g;
    document.getElementById('tgbtn-male').classList.toggle('active',   g === 'male');
    document.getElementById('tgbtn-female').classList.toggle('active', g === 'female');
  }
}

// ─────────────────────────────────────────────
// BMI CALCULATOR
// ─────────────────────────────────────────────
async function calcBMI() {
  const w  = parseFloat(document.getElementById('b-weight').value);
  const hc = parseFloat(document.getElementById('b-height').value);
  const a  = parseFloat(document.getElementById('b-age').value);

  if (!w || !hc || !a)              { showToast('⚠️ Lengkapi semua data!'); return; }
  if (w < 20 || w > 300)            { showToast('⚠️ Berat tidak valid (20–300 kg)'); return; }
  if (hc < 50 || hc > 250)          { showToast('⚠️ Tinggi tidak valid (50–250 cm)'); return; }

  const h   = hc / 100;
  const bmi = w / (h * h);
  const bmiR = bmi.toFixed(1);

  let cat, color, bg, tip;
  if      (bmi < 18.5) { cat='Kekurangan Berat Badan'; color='#2563eb'; bg='#eff6ff'; tip='💡 Tingkatkan asupan kalori dengan makanan bergizi seperti kacang-kacangan, alpukat, dan protein berkualitas.'; }
  else if (bmi < 25)   { cat='Berat Badan Ideal';       color='#059669'; bg='#f0fdf4'; tip='✅ Pertahankan pola makan seimbang dan olahraga rutin. Anda dalam kondisi ideal!'; }
  else if (bmi < 30)   { cat='Kelebihan Berat Badan';   color='#d97706'; bg='#fffbeb'; tip='💡 Kurangi asupan 300–500 kkal/hari dan tingkatkan aktivitas fisik 150 menit/minggu.'; }
  else                 { cat='Obesitas';                 color='#dc2626'; bg='#fff1f2'; tip='⚠️ Disarankan berkonsultasi dengan dokter untuk program penurunan berat badan yang aman.'; }

  const base = bmiGender === 'male' ? 50 : 45.5;
  const iMin = (base + 0.91 * (hc - 152.4)).toFixed(1);
  const iMax = (base + 0.91 * (hc - 152.4) + 10).toFixed(1);

  document.getElementById('bmi-res').innerHTML = `
    <div class="bmi-res-content">
      <div class="bmi-circle" style="background:${bg};border-color:${color}">
        <span class="bmi-circle-val" style="color:${color}">${bmiR}</span>
        <span class="bmi-circle-lbl" style="color:${color}">BMI</span>
      </div>
      <span class="bmi-cat-badge" style="background:${bg};color:${color}">${cat}</span>
      <div class="bmi-detail-grid">
        <div class="bdi"><strong>${w} kg</strong><small>Berat Badan</small></div>
        <div class="bdi"><strong>${hc} cm</strong><small>Tinggi Badan</small></div>
        <div class="bdi"><strong>${iMin}–${iMax} kg</strong><small>Berat Ideal</small></div>
        <div class="bdi"><strong>${a} thn</strong><small>Usia</small></div>
      </div>
      <div class="bmi-tip">${tip}</div>
    </div>`;

  // Needle
  const needle = document.getElementById('bmi-needle');
  needle.style.display = 'block';
  let pct = bmi < 18.5 ? (bmi/18.5)*25
           : bmi < 25   ? 25 + ((bmi-18.5)/6.5)*25
           : bmi < 30   ? 50 + ((bmi-25)/5)*25
                        : 75 + Math.min(((bmi-30)/10)*25, 23);
  pct = Math.max(2, Math.min(97, pct));
  setTimeout(() => { needle.style.left = pct + '%'; }, 80);

  // Simpan ke server (atau localStorage jika belum login)
  await saveBMILog(bmiR, cat, color, bg, w, hc, a);
  showToast('✅ BMI berhasil dihitung!');
}

// ─────────────────────────────────────────────
// TDEE CALCULATOR
// ─────────────────────────────────────────────
async function calcTDEE() {
  const w  = parseFloat(document.getElementById('t-weight').value);
  const h  = parseFloat(document.getElementById('t-height').value);
  const a  = parseFloat(document.getElementById('t-age').value);
  const af = parseFloat(document.getElementById('t-activity').value);

  if (!w || !h || !a) { showToast('⚠️ Lengkapi semua data!'); return; }

  const bmr  = tdeeGender === 'male'
    ? 88.362 + (13.397*w) + (4.799*h) - (5.677*a)
    : 447.593 + (9.247*w) + (3.098*h) - (4.330*a);

  const tdee  = Math.round(bmr * af);
  const lose  = Math.round(tdee - 500);
  const gain  = Math.round(tdee + 300);
  const prot  = Math.round((tdee * 0.30) / 4);
  const carbs = Math.round((tdee * 0.40) / 4);
  const fat   = Math.round((tdee * 0.30) / 9);

  const actNames = { '1.2':'Tidak Aktif','1.375':'Ringan','1.55':'Sedang','1.725':'Aktif','1.9':'Sangat Aktif' };

  document.getElementById('tdee-res').innerHTML = `
    <div class="tdee-res-content">
      <div class="tdee-big">${tdee.toLocaleString('id-ID')}</div>
      <div class="tdee-sub">kkal/hari · Aktivitas ${actNames[af.toString()] || 'Sedang'}</div>
      <div class="tdee-goals">
        <div class="tg-item"><strong style="color:#dc2626">${lose.toLocaleString('id-ID')}</strong><small>📉 Turun Berat</small></div>
        <div class="tg-item"><strong style="color:#059669">${tdee.toLocaleString('id-ID')}</strong><small>⚖️ Jaga Berat</small></div>
        <div class="tg-item"><strong style="color:#2563eb">${gain.toLocaleString('id-ID')}</strong><small>📈 Naik Berat</small></div>
      </div>
      <div class="bmi-detail-grid">
        <div class="bdi"><strong>${prot}g</strong><small>Protein/hari</small></div>
        <div class="bdi"><strong>${carbs}g</strong><small>Karbo/hari</small></div>
        <div class="bdi"><strong>${fat}g</strong><small>Lemak/hari</small></div>
        <div class="bdi"><strong>${Math.round(bmr)}</strong><small>BMR Dasar</small></div>
      </div>
      <div class="bmi-tip">💡 Gunakan angka TDEE ini sebagai acuan asupan kalori harian sesuai tujuanmu. Target sudah diperbarui di Log!</div>
    </div>`;

  calGoal = tdee;
  updateLogSummary();

  // Simpan TDEE ke server
  if (authToken) {
    try {
      await apiPost('tdee.php', {
        tdee, bmr: Math.round(bmr), activity: af,
        weight: w, height: h, age: a, gender: tdeeGender,
      });
    } catch(e) { console.warn('Gagal simpan TDEE', e); }
  }

  showToast('🔥 TDEE berhasil dihitung!');
}

// ─────────────────────────────────────────────
// MENU GRID
// ─────────────────────────────────────────────
function renderMenuGrid() {
  const grid = document.getElementById('menuGrid');
  grid.innerHTML = MENUS.map(m => `
    <div class="mcard" data-cat="${m.cat}">
      <div class="mcard-emoji">${m.emoji}</div>
      <div class="mcard-body">
        <span class="mcard-tag ${CAT_CSS[m.cat]}">${CAT_TAG[m.cat]}</span>
        <h4>${m.name}</h4>
        <p>${m.desc}</p>
        <div class="mcard-macros">
          <span class="mcal"><i class="fas fa-fire"></i> ${m.cal} kkal</span>
          <span class="mmacro">P: ${m.p}g</span>
          <span class="mmacro">K: ${m.k}g</span>
          <span class="mmacro">L: ${m.l}g</span>
        </div>
      </div>
      <button class="mcard-btn" onclick="addFoodFromMenu('${m.name}',${m.cal},'${CAT_TAG[m.cat]}',${m.p},${m.k},${m.l})" title="Tambah ke log">
        <i class="fas fa-plus"></i>
      </button>
    </div>`).join('');
}

function filterMenu(cat, btn) {
  document.querySelectorAll('.mf').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.mcard').forEach(c => {
    c.classList.toggle('hidden', cat !== 'all' && c.dataset.cat !== cat);
  });
}

// ─────────────────────────────────────────────
// FOOD LOG
// ─────────────────────────────────────────────
async function addFoodFromMenu(name, cal, type, p, k, l) {
  await addFoodEntry(name, cal, type, p, k, l);
  showToast(`✅ ${name} ditambahkan ke log!`);
}

async function addFoodEntry(name, cal, type, p=0, k=0, l=0) {
  const entry = {
    id:   Date.now(),   // temp id, akan diganti id dari server jika login
    name, cal: parseInt(cal), type, p, k, l,
    time: new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' }),
  };

  // Simpan ke server jika login
  if (authToken) {
    try {
      const res = await apiPost('food.php', {
        name, calories: cal, meal_type: type,
        protein_g: p, carbs_g: k, fat_g: l,
        log_date: todayISO(),
      });
      if (res.success) entry.id = res.data.id;
    } catch(e) { console.warn('Gagal simpan makanan ke server', e); }
  } else {
    // Fallback localStorage
    const local = JSON.parse(localStorage.getItem('he_food') || '[]');
    local.push(entry);
    localStorage.setItem('he_food', JSON.stringify(local));
  }

  foodLog.push(entry);
  renderFoodLog();
  updateLogSummary();
  renderBarChart();
  renderDonut();
  updateWeekLog();
}

function renderFoodLog() {
  const el = document.getElementById('logList');
  if (!el) return;
  if (!foodLog.length) {
    el.innerHTML = `<div class="log-empty-state"><i class="fas fa-clipboard-list"></i><p>Belum ada catatan. Tambah dari Menu atau klik "+ Tambah Manual".</p></div>`;
    return;
  }
  el.innerHTML = foodLog.map(f => `
    <div class="log-item">
      <div class="li-emoji">${TYPE_EMOJI[f.type] || '🍽️'}</div>
      <div class="li-info">
        <div class="li-name">${f.name}</div>
        <div class="li-meta">${f.type} · ${f.time}</div>
      </div>
      <span class="li-cal">${f.cal} kkal</span>
      <button class="li-del" onclick="removeFoodItem(${f.id})"><i class="fas fa-times"></i></button>
    </div>`).join('');
}

async function removeFoodItem(id) {
  if (authToken) {
    try {
      await apiPost('food.php', { action: 'delete', id });
    } catch(e) { console.warn('Gagal hapus makanan', e); }
  } else {
    const local = JSON.parse(localStorage.getItem('he_food') || '[]').filter(f => f.id !== id);
    localStorage.setItem('he_food', JSON.stringify(local));
  }

  foodLog = foodLog.filter(f => f.id !== id);
  renderFoodLog();
  updateLogSummary();
  renderBarChart();
  renderDonut();
  showToast('🗑️ Dihapus dari log.');
}

async function clearFoodLog() {
  if (!foodLog.length) { showToast('Log sudah kosong!'); return; }
  if (!confirm('Hapus semua log makanan hari ini?')) return;

  if (authToken) {
    try {
      await apiPost('food.php', { action: 'clear', date: todayISO() });
    } catch(e) { console.warn('Gagal hapus semua', e); }
  } else {
    localStorage.setItem('he_food', '[]');
  }

  foodLog = [];
  renderFoodLog();
  updateLogSummary();
  renderBarChart();
  renderDonut();
  showToast('🗑️ Semua log dihapus.');
}

function updateLogSummary() {
  const total  = foodLog.reduce((s, f) => s + f.cal, 0);
  const remain = Math.max(0, calGoal - total);
  const pct    = Math.min(100, Math.round((total / calGoal) * 100));

  const ls = id => document.getElementById(id);
  if (ls('ls-total'))  ls('ls-total').textContent  = total.toLocaleString('id-ID');
  if (ls('ls-meals'))  ls('ls-meals').textContent  = foodLog.length;
  if (ls('ls-goal'))   ls('ls-goal').textContent   = calGoal.toLocaleString('id-ID');
  if (ls('ls-remain')) ls('ls-remain').textContent = remain.toLocaleString('id-ID');

  const fill  = ls('calProgFill');
  const pctEl = ls('calProgPct');
  if (fill) {
    fill.style.width      = pct + '%';
    fill.style.background = pct >= 100
      ? 'linear-gradient(to right, #f59e0b, #ef4444)'
      : 'linear-gradient(to right, #10b981, #3b82f6)';
  }
  if (pctEl) pctEl.textContent = `${pct}% tercapai`;

  renderDonut();
}

// ─────────────────────────────────────────────
// MODAL: TAMBAH MANUAL
// ─────────────────────────────────────────────
function openAddFood() {
  document.getElementById('addFoodModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  document.getElementById('addFoodModal').classList.remove('open');
  document.body.style.overflow = '';
}
function closeModalBg(e) {
  if (e.target === e.currentTarget) closeModal();
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeModal();
});

async function submitAddFood() {
  const name = document.getElementById('mf-name').value.trim();
  const cal  = parseInt(document.getElementById('mf-cal').value);
  const type = document.getElementById('mf-type').value;
  if (!name)          { showToast('⚠️ Nama makanan harus diisi!'); return; }
  if (!cal || cal<=0) { showToast('⚠️ Kalori tidak valid!'); return; }

  const k = Math.round((cal * .40) / 4);
  const p = Math.round((cal * .30) / 4);
  const l = Math.round((cal * .30) / 9);

  await addFoodEntry(name, cal, type, p, k, l);
  closeModal();
  document.getElementById('mf-name').value = '';
  document.getElementById('mf-cal').value  = '';
  showToast(`✅ ${name} ditambahkan!`);
}

// ─────────────────────────────────────────────
// BMI LOG
// ─────────────────────────────────────────────
async function saveBMILog(bmi, cat, color, bg, w, h, age) {
  const entry = {
    id: Date.now(), bmi, cat, color, bg, w, h, age,
    date: new Date().toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' }),
    time: new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' }),
  };

  if (authToken) {
    try {
      const res = await apiPost('bmi.php', {
        bmi, category: cat, weight: w, height: h, age, gender: bmiGender,
      });
      if (res.success) entry.id = res.data.id;
    } catch(e) { console.warn('Gagal simpan BMI ke server', e); }
  } else {
    const local = JSON.parse(localStorage.getItem('he_bmi') || '[]');
    local.unshift(entry);
    if (local.length > 15) local.splice(15);
    localStorage.setItem('he_bmi', JSON.stringify(local));
  }

  bmiLog.unshift(entry);
  if (bmiLog.length > 15) bmiLog = bmiLog.slice(0, 15);
  renderBMILog();
  renderLineChart();
}

function renderBMILog() {
  const el = document.getElementById('bmiLogList');
  if (!el) return;
  if (!bmiLog.length) {
    el.innerHTML = `<div class="log-empty-state"><i class="fas fa-weight-scale"></i><p>Belum ada riwayat. Hitung BMI di halaman Kalkulator.</p></div>`;
    return;
  }
  el.innerHTML = bmiLog.map(b => `
    <div class="bmi-log-item">
      <div class="bmi-badge" style="background:${b.bg};color:${b.color}">
        <span class="bmi-badge-val">${b.bmi}</span>
        <span class="bmi-badge-lbl">BMI</span>
      </div>
      <div class="bmi-info">
        <div class="bmi-info-name" style="color:${b.color}">${b.cat}</div>
        <div class="bmi-info-meta">${b.w}kg · ${b.h}cm · ${b.age}thn · ${b.date} ${b.time}</div>
      </div>
      <button class="bmi-del" onclick="removeBMIEntry(${b.id})"><i class="fas fa-times"></i></button>
    </div>`).join('');
}

async function removeBMIEntry(id) {
  if (authToken) {
    try {
      await apiPost('bmi.php', { action: 'delete', id });
    } catch(e) { console.warn('Gagal hapus BMI', e); }
  } else {
    const local = JSON.parse(localStorage.getItem('he_bmi') || '[]').filter(b => b.id !== id);
    localStorage.setItem('he_bmi', JSON.stringify(local));
  }

  bmiLog = bmiLog.filter(b => b.id !== id);
  renderBMILog();
  renderLineChart();
  showToast('🗑️ Riwayat dihapus.');
}

async function clearBMILog() {
  if (!bmiLog.length) { showToast('Riwayat sudah kosong!'); return; }
  if (!confirm('Hapus semua riwayat BMI?')) return;

  if (authToken) {
    try {
      await apiPost('bmi.php', { action: 'clear' });
    } catch(e) { console.warn('Gagal hapus BMI', e); }
  } else {
    localStorage.setItem('he_bmi', '[]');
  }

  bmiLog = [];
  renderBMILog();
  renderLineChart();
  showToast('🗑️ Riwayat BMI dihapus.');
}

// ─────────────────────────────────────────────
// WEEK LOG
// ─────────────────────────────────────────────
function updateWeekLog() {
  const today = todayISO();
  const total = foodLog.reduce((s, f) => s + f.cal, 0);
  weekLog[today] = total;

  if (!authToken) {
    // Simpan lokal jika belum login
    const keys = Object.keys(weekLog).sort();
    if (keys.length > 30) keys.slice(0, keys.length - 30).forEach(k => delete weekLog[k]);
    localStorage.setItem('he_week', JSON.stringify(weekLog));
  }
  // Jika login, server sudah update otomatis saat add/remove food
}

// ─────────────────────────────────────────────
// DONUT CHART
// ─────────────────────────────────────────────
function renderDonut() {
  const totCal = foodLog.reduce((s,f) => s+f.cal, 0);
  const totK   = foodLog.reduce((s,f) => s+f.k, 0);
  const totP   = foodLog.reduce((s,f) => s+f.p, 0);
  const totL   = foodLog.reduce((s,f) => s+f.l, 0);

  const cCenter = document.getElementById('donut-center');
  if (cCenter) cCenter.textContent = totCal.toLocaleString('id-ID');

  const legC = document.getElementById('leg-carb');
  const legP = document.getElementById('leg-prot');
  const legF = document.getElementById('leg-fat');
  if (legC) legC.textContent = totK + 'g';
  if (legP) legP.textContent = totP + 'g';
  if (legF) legF.textContent = totL + 'g';

  const circ      = 439.82;
  const totMacro  = totK * 4 + totP * 4 + totL * 9 || 1;
  const kLen      = (totK * 4 / totMacro) * circ;
  const pLen      = (totP * 4 / totMacro) * circ;
  const lLen      = (totL * 9 / totMacro) * circ;

  const dC = document.getElementById('donut-carb');
  const dP = document.getElementById('donut-prot');
  const dF = document.getElementById('donut-fat');
  if (!dC || !dP || !dF) return;

  if (totCal === 0) {
    [dC,dP,dF].forEach(el => el.setAttribute('stroke-dasharray','0 439.82'));
    return;
  }

  dC.setAttribute('stroke-dasharray', `${kLen} ${circ - kLen}`);
  dC.setAttribute('stroke-dashoffset', '109.96');
  const pOffset = 109.96 - kLen;
  dP.setAttribute('stroke-dasharray', `${pLen} ${circ - pLen}`);
  dP.setAttribute('stroke-dashoffset', pOffset);
  const lOffset = pOffset - pLen;
  dF.setAttribute('stroke-dasharray', `${lLen} ${circ - lLen}`);
  dF.setAttribute('stroke-dashoffset', lOffset);
}

// ─────────────────────────────────────────────
// BAR CHART
// ─────────────────────────────────────────────
function renderBarChart() {
  const container = document.getElementById('barChartArea');
  if (!container) return;

  const days = [];
  for (let i = 6; i >= 0; i--) {
    const d = new Date();
    d.setDate(d.getDate() - i);
    const key   = d.toISOString().split('T')[0];
    const label = d.toLocaleDateString('id-ID', { weekday:'short' });
    days.push({ label, cal: weekLog[key] || 0, key });
  }

  const maxCal = Math.max(...days.map(d => d.cal), calGoal, 500);

  container.innerHTML = days.map(day => {
    const heightPct = (day.cal / maxCal) * 100;
    const targetPct = (calGoal / maxCal) * 100;
    const barClass  = day.cal === 0 ? 'bar-low' : day.cal > calGoal ? 'bar-over' : 'bar-ok';
    const isToday   = day.key === todayISO();

    return `
      <div class="bar-col">
        <div class="bar-val">${day.cal > 0 ? day.cal.toLocaleString('id-ID') : ''}</div>
        <div class="bar-stack ${barClass}" style="height:${heightPct}%;min-height:${day.cal>0?'4px':'0'}">
          <div class="bar-target-line" style="bottom:${(targetPct / heightPct) * 100}%;display:${heightPct > 0 ? 'block':'none'}"></div>
        </div>
        <div class="bar-label" style="font-weight:${isToday?'800':'600'};color:${isToday?'#10b981':'#94a3b8'}">${day.label}${isToday?' ●':''}</div>
      </div>`;
  }).join('');
}

// ─────────────────────────────────────────────
// LINE CHART (BMI trend)
// ─────────────────────────────────────────────
function renderLineChart() {
  const area  = document.getElementById('lineChartArea');
  const empty = document.getElementById('lineEmpty');
  if (!area) return;

  if (bmiLog.length < 2) {
    if (empty) empty.style.display = 'flex';
    const old = area.querySelector('svg');
    if (old) old.remove();
    return;
  }

  if (empty) empty.style.display = 'none';

  const data = [...bmiLog].reverse();
  const vals = data.map(d => parseFloat(d.bmi));
  const minV = Math.max(10, Math.min(...vals) - 2);
  const maxV = Math.max(...vals) + 2;

  const W = area.clientWidth || 600;
  const H = 200;
  const PAD = { t:20, r:20, b:40, l:40 };
  const plotW = W - PAD.l - PAD.r;
  const plotH = H - PAD.t - PAD.b;

  const xScale = i => PAD.l + (i / (data.length - 1)) * plotW;
  const yScale = v => PAD.t + plotH - ((v - minV) / (maxV - minV)) * plotH;

  const pts      = data.map((d, i) => [xScale(i), yScale(parseFloat(d.bmi))]);
  const linePath = pts.map((p, i) => (i === 0 ? `M${p[0]},${p[1]}` : `L${p[0]},${p[1]}`)).join(' ');
  const fillPath = linePath + ` L${pts[pts.length-1][0]},${PAD.t+plotH} L${pts[0][0]},${PAD.t+plotH} Z`;

  const ySteps  = 4;
  const yLabels = Array.from({length: ySteps+1}, (_,i) => {
    const v = minV + (i/ySteps) * (maxV - minV);
    const y = yScale(v);
    return `<text x="${PAD.l - 6}" y="${y+4}" text-anchor="end" font-size="10" fill="#94a3b8" font-family="Plus Jakarta Sans" font-weight="600">${v.toFixed(1)}</text>
            <line x1="${PAD.l}" y1="${y}" x2="${PAD.l+plotW}" y2="${y}" stroke="#f1f5f9" stroke-width="1"/>`;
  }).join('');

  const xLabels = data.map((d, i) => `
    <text x="${xScale(i)}" y="${H - 10}" text-anchor="middle" font-size="10" fill="#94a3b8" font-family="Plus Jakarta Sans" font-weight="600">${d.date}</text>`).join('');

  const dots = pts.map((p, i) => `
    <circle cx="${p[0]}" cy="${p[1]}" r="6" fill="#10b981" stroke="white" stroke-width="2.5"/>
    <text x="${p[0]}" y="${p[1] - 12}" text-anchor="middle" font-size="11" fill="#064e3b" font-family="Plus Jakarta Sans" font-weight="800">${data[i].bmi}</text>`).join('');

  const zoneTop    = yScale(24.9);
  const zoneBottom = yScale(18.5);

  const svg = `
    <svg width="${W}" height="${H}" viewBox="0 0 ${W} ${H}" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="lineGrad" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="#10b981" stop-opacity="0.2"/>
          <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
        </linearGradient>
      </defs>
      <rect x="${PAD.l}" y="${zoneTop}" width="${plotW}" height="${zoneBottom - zoneTop}" fill="rgba(16,185,129,0.07)" rx="2"/>
      <text x="${PAD.l + plotW - 4}" y="${zoneTop + 14}" text-anchor="end" font-size="10" fill="#10b981" font-family="Plus Jakarta Sans" font-weight="700" opacity="0.7">Normal</text>
      ${yLabels}
      <path d="${fillPath}" fill="url(#lineGrad)"/>
      <path d="${linePath}" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
      ${xLabels}
      ${dots}
    </svg>`;

  const old = area.querySelector('svg');
  if (old) old.remove();
  area.insertAdjacentHTML('afterbegin', svg);
}

// ─────────────────────────────────────────────
// TOAST
// ─────────────────────────────────────────────
let toastTimer;
function showToast(msg) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 3000);
}

// ─────────────────────────────────────────────
// AUTH HANDLERS
// ─────────────────────────────────────────────
async function handleLogin() {
  const email = document.getElementById('login-email').value.trim();
  const pass  = document.getElementById('login-pass').value;

  if (!email || !pass)           { showToast('⚠️ Email dan password harus diisi!'); return; }
  if (!email.includes('@'))      { showToast('⚠️ Format email tidak valid!'); return; }
  if (pass.length < 6)           { showToast('⚠️ Password minimal 6 karakter!'); return; }

  showToast('⏳ Memproses...');

  try {
    const res = await apiPost('login.php', { email, password: pass });
    if (!res.success) { showToast('❌ ' + res.message); return; }

    // Simpan token & user
    authToken = res.data.token;
    authUser  = res.data.user;
    localStorage.setItem('he_token', authToken);
    localStorage.setItem('he_user', JSON.stringify(authUser));

    // Muat data dari server
    await loadAllDataFromServer();
    renderFoodLog();
    renderBMILog();
    updateLogSummary();
    updateNavAuth();

    showToast(`✅ Login berhasil! Halo, ${authUser.name.split(' ')[0]}!`);
    setTimeout(() => navigate('home'), 800);
  } catch(e) {
    showToast('❌ Gagal terhubung ke server.');
  }
}

async function handleSignup() {
  const name    = document.getElementById('signup-name').value.trim();
  const email   = document.getElementById('signup-email').value.trim();
  const pass    = document.getElementById('signup-pass').value;
  const confirm = document.getElementById('signup-confirm').value;
  const agree   = document.getElementById('signup-agree').checked;

  if (!name)                          { showToast('⚠️ Nama lengkap harus diisi!'); return; }
  if (!email || !email.includes('@')) { showToast('⚠️ Format email tidak valid!'); return; }
  if (pass.length < 8)                { showToast('⚠️ Password minimal 8 karakter!'); return; }
  if (pass !== confirm)               { showToast('⚠️ Konfirmasi password tidak cocok!'); return; }
  if (!agree)                         { showToast('⚠️ Setujui syarat & ketentuan terlebih dahulu!'); return; }

  showToast('⏳ Membuat akun...');

  try {
    const res = await apiPost('signup.php', { name, email, password: pass });
    if (!res.success) { showToast('❌ ' + res.message); return; }

    authToken = res.data.token;
    authUser  = res.data.user;
    localStorage.setItem('he_token', authToken);
    localStorage.setItem('he_user', JSON.stringify(authUser));

    updateNavAuth();
    showToast(`🎉 Akun berhasil dibuat! Selamat datang, ${name}!`);
    setTimeout(() => navigate('home'), 800);
  } catch(e) {
    showToast('❌ Gagal terhubung ke server.');
  }
}

async function handleLogout() {
  if (authToken) {
    try { await apiPost('logout.php', { token: authToken }); } catch(e) {}
  }
  authToken = null;
  authUser  = null;
  localStorage.removeItem('he_token');
  localStorage.removeItem('he_user');

  foodLog = [];
  bmiLog  = [];
  weekLog = {};

  updateNavAuth();
  renderFoodLog();
  renderBMILog();
  updateLogSummary();
  showToast('👋 Kamu telah keluar.');
  navigate('home');
}

function togglePass(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}

// ─────────────────────────────────────────────
// UTILS
// ─────────────────────────────────────────────
function todayISO() {
  return new Date().toISOString().split('T')[0];
}

function bmiColor(bmi) {
  if (bmi < 18.5) return '#2563eb';
  if (bmi < 25)   return '#059669';
  if (bmi < 30)   return '#d97706';
  return '#dc2626';
}
function bmiBg(bmi) {
  if (bmi < 18.5) return '#eff6ff';
  if (bmi < 25)   return '#f0fdf4';
  if (bmi < 30)   return '#fffbeb';
  return '#fff1f2';
}
