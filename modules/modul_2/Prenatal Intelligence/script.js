let nutritionDatabase = []; // Dinamis dari mySQL!

const fetalMilestones = {
    1: { phase: "Germinal", title: "Minggu 1-4: Implantasi", desc: "Sel mulai membelah diri menjadi embrio.", size: "0.1 mm" },
    5: { phase: "Embrionik", title: "Minggu 5-8: Organogenesis", desc: "Tabung saraf menutup. Jantung mulai berdetak.", size: "2 - 5 mm" },
    9: { phase: "Fetal", title: "Minggu 9-13: Pergerakan Refleks", desc: "Janin mulai bergerak dan organ vital mulai berfungsi.", size: "6 - 7 cm" },
    14: { phase: "Fetal", title: "Minggu 14-27: Gerak Aktif", desc: "Sistem saraf matang, janin aktif menendang.", size: "25 - 35 cm" },
    28: { phase: "Fetal", title: "Minggu 28-40: Persiapan Lahir", desc: "Paru-paru matang, posisi kepala berputar ke bawah.", size: "45 - 50 cm" }
};

// State Global
let currentFolic = 0;
let currentIron = 0;
let targetIronHarian = 18; 
const targetFolicHarian = 600;

// History State Connected to Dashboard
const activeChildId = localStorage.getItem('activeChildId');
if(!activeChildId) {
    alert("⚠️ Anda belum memilih/membuat Profil Anak di Dashboard!");
    window.location.href = "../dashboardgrowlife.php";
}

let nutritionHistory = {};
let currentDate = new Date().toISOString().split('T')[0];
let editingId = null;

async function init() {
    // Populate Selector Minggu
    const weekSel = document.getElementById('weekSelector');
    for(let i=1; i<=40; i++) {
        weekSel.options[weekSel.options.length] = new Option("Minggu ke-" + i, i);
    }

    // Setup Date Picker
    const dateInput = document.getElementById('historyDate');
    if(dateInput) dateInput.value = currentDate;

    try {
        // 1. Ambil List Makanan dari Database
        const resFood = await fetch('../api.php?action=get_foods');
        const jsonFood = await resFood.json();
        if(jsonFood.status === 'success') {
            nutritionDatabase = jsonFood.data;
            const foodSel = document.getElementById('foodSelect');
            foodSel.innerHTML = '<option value="-1">-- Pilih Menu --</option>';
            nutritionDatabase.forEach((item, index) => {
                let option = document.createElement("option");
                option.value = index;
                option.text = item.name + ` (Folat:${item.folic}mcg, Besi:${item.iron}mg)`;
                foodSel.appendChild(option);
            });
        }
        
        // 2. Ambil Riwayat dari Database aseli!
        await fetchNutritionFromDB();
        
    } catch(e) {
        console.error("Gagal koneksi ke server database!", e);
    }

    updateFetalInfo(); 
    loadCurrentDateData();
}

async function fetchNutritionFromDB() {
    nutritionHistory = {}; // reset
    const res = await fetch(`../api.php?action=get_nutrisi&child_id=${activeChildId}`);
    const json = await res.json();
    if(json.status === 'success') {
        json.data.forEach(row => {
            if(!nutritionHistory[row.record_date]) nutritionHistory[row.record_date] = [];
            nutritionHistory[row.record_date].push({
                id: parseInt(row.id),
                foodIndex: parseInt(row.food_index),
                name: row.name,
                folic: parseFloat(row.folic),
                iron: parseFloat(row.iron)
            });
        });
    }
}

function updateFetalInfo() {
    const week = parseInt(document.getElementById('weekSelector').value) || 1;
    targetIronHarian = (week >= 14) ? 27 : 18;

    const m = week >= 28 ? fetalMilestones[28] : 
             (week >= 14 ? fetalMilestones[14] : 
             (week >= 9 ? fetalMilestones[9] : 
             (week >= 5 ? fetalMilestones[5] : fetalMilestones[1])));

    document.getElementById('fetalPhase').innerText = m.phase;
    document.getElementById('fetalTitle').innerText = m.title;
    document.getElementById('fetalDesc').innerText = m.desc;
    document.getElementById('fetalSize').innerText = "Estimasi Ukuran: " + m.size;

    updateNutritionUI();
}

function changeDate() {
    const dateInput = document.getElementById('historyDate');
    if(dateInput && dateInput.value) {
        currentDate = dateInput.value;
        cancelEdit();
        loadCurrentDateData();
    }
}

function loadCurrentDateData() {
    currentFolic = 0;
    currentIron = 0;
    
    const todayLog = nutritionHistory[currentDate] || [];
    const listEl = document.getElementById('historyList');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    
    if(todayLog.length === 0) {
        listEl.innerHTML = '<li class="history-item" style="color:#aaa; justify-content:center;">Belum ada catatan makanan.</li>';
    } else {
        todayLog.forEach(log => {
            currentFolic += log.folic;
            currentIron += log.iron;
            
            listEl.innerHTML += `
                <li class="history-item">
                    <div class="item-details">
                        <b>${log.name}</b>
                        <small style="color:#777">Folat: ${log.folic}mcg | Besi: ${log.iron}mg</small>
                    </div>
                    <div class="item-actions">
                        <button class="btn-small btn-edit" title="Edit Konsumsi" onclick="editNutrition(${log.id})"><i class="fas fa-pen"></i></button>
                        <button class="btn-small btn-delete" title="Hapus Konsumsi" onclick="deleteNutrition(${log.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </li>
            `;
        });
    }

    updateNutritionUI();
}

function showMessage(text, isError = false) {
    const msg = document.getElementById('actionMessage');
    if(msg) {
        msg.innerText = text;
        msg.style.color = isError ? '#d00000' : '#2e7d32'; 
        msg.style.display = 'block';
        setTimeout(() => { msg.style.display = 'none'; }, 2500);
    }
}

async function submitNutrition() {
    const foodIndex = document.getElementById('foodSelect').value;
    if(foodIndex == -1) return showMessage("⚠️ Pilih menu makanan dulu ya, Bun!", true);

    const selectedFood = nutritionDatabase[foodIndex];
    let isUpdate = (editingId !== null);

    // Save to Database via POST
    const fd = new FormData();
    fd.append('action', 'simpan_nutrisi');
    fd.append('child_id', activeChildId);
    fd.append('record_date', currentDate);
    fd.append('food_index', foodIndex);
    fd.append('name', selectedFood.name);
    fd.append('folic', selectedFood.folic);
    fd.append('iron', selectedFood.iron);
    if(isUpdate) fd.append('editing_id', editingId);

    try {
        const res = await fetch('../api.php', { method: 'POST', body: fd });
        const json = await res.json();
        
        if(json.status === 'success') {
            showMessage(isUpdate ? "✅ Berhasil diupdate ke Database!" : "✅ Berhasil masuk ke Database!");
            cancelEdit();
            await fetchNutritionFromDB(); // Sinkron ulang dengan database terbaru!
            loadCurrentDateData();
        } else {
            showMessage("Gagal menyimpan server SQL!", true);
        }
    } catch(e) {
        showMessage("Server Error", true);
    }
}

function editNutrition(id) {
    const todayLog = nutritionHistory[currentDate] || [];
    const log = todayLog.find(item => item.id === id);
    if(log) {
        document.getElementById('foodSelect').value = log.foodIndex;
        document.getElementById('submitBtn').innerText = "Update Konsumsi via Server";
        editingId = id;
    }
}

function cancelEdit() {
    editingId = null;
    const foodSel = document.getElementById('foodSelect');
    if (foodSel) foodSel.value = "-1";
    const subBtn = document.getElementById('submitBtn');
    if (subBtn) subBtn.innerText = "Tambah Konsumsi";
}

async function deleteNutrition(id) {
    if(confirm("Yakin ingin menghapus catatan ini dari Database Server?")) {
        const fd = new FormData();
        fd.append('action', 'hapus_nutrisi');
        fd.append('id', id);
        try {
            await fetch('../api.php', { method: 'POST', body: fd });
            cancelEdit();
            await fetchNutritionFromDB();
            loadCurrentDateData();
            showMessage("🗑️ Catatan berhasil dihapus permanen.");
        } catch(e) {
            showMessage("ERROR Hapus DB!", true);
        }
    }
}

async function resetDay() {
    if(confirm("Hapus semua baris catatan untuk tanggal " + currentDate + " dari Database?")) {
        const fd = new FormData();
        fd.append('action', 'hapus_hari_nutrisi');
        fd.append('child_id', activeChildId);
        fd.append('record_date', currentDate);
        try {
            await fetch('../api.php', { method: 'POST', body: fd });
            cancelEdit();
            await fetchNutritionFromDB();
            loadCurrentDateData();
            showMessage("🗑️ Hari ini dikosongkan.", true);
        } catch(e) {
            alert("Error Server");
        }
    }
}

function updateNutritionUI() {
    const folicPercent = Math.min((currentFolic / targetFolicHarian) * 100, 100);
    const ironP = isNaN(targetIronHarian) ? 1 : targetIronHarian; 
    const ironPercent = Math.min((currentIron / ironP) * 100, 100);

    document.getElementById('folicBar').style.width = folicPercent + "%";
    document.getElementById('ironBar').style.width = ironPercent + "%";
    
    const formattedFolic = Number.isInteger(currentFolic) ? currentFolic : currentFolic.toFixed(1);
    const formattedIron = Number.isInteger(currentIron) ? currentIron : currentIron.toFixed(1);

    document.getElementById('folicStatus').innerText = `${formattedFolic} / ${targetFolicHarian} mcg`;
    document.getElementById('ironStatus').innerText = `${formattedIron} / ${targetIronHarian} mg`;
}

function checkRisk() {
    const sys = parseInt(document.getElementById('systolic').value);
    const glu = parseInt(document.getElementById('glucose').value);
    const res = document.getElementById('riskResult');

    if(isNaN(sys) || isNaN(glu)) {
        res.innerHTML = `<div class="alert alert-danger">⚠️ Mohon masukkan angka tekanan darah dan gula darah.</div>`;
        return;
    }

    let errors = [];
    if(sys >= 140) errors.push("⚠️ Tekanan darah tinggi (Sistolik ≥ 140 mmHg)");
    if(glu >= 200) errors.push("⚠️ Gula darah tinggi (GDS ≥ 200 mg/dL)");

    if(errors.length > 0) {
        res.innerHTML = `<div class="alert alert-danger"><b>Analisis Medis:</b><br>${errors.join('<br>')}<br><br><small>Mohon segera konsultasikan ke tenaga medis terdekat.</small></div>`;
    } else {
        res.innerHTML = `<div class="alert alert-success">✅ Kondisi saat ini terpantau normal. Tetap jaga kesehatan ya, Bun!</div>`;
    }
}

window.onload = init;


