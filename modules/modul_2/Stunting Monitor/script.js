const activeChildId = localStorage.getItem('activeChildId');
if(!activeChildId) {
    alert("⚠️ Bunda belum memilih Profil Anak di Dashboard!");
    window.location.href = "../dashboardgrowlife.php";
}

let childrenProfiles = JSON.parse(localStorage.getItem('childrenProfiles')) || [];
let activeChild = childrenProfiles.find(c => c.id == activeChildId);

let dataBayiAnda = new Array(25).fill(null);

// =====================================
// DATA STANDAR WHO / Z-SCORE (0 - 24 BULAN)
// =====================================
const labelsBulan = Array.from({length: 25}, (_, i) => i); 
const whoIdealBB = [3.3, 4.5, 5.6, 6.4, 7.0, 7.5, 7.9, 8.3, 8.6, 8.9, 9.2, 9.4, 9.6, 9.9, 10.1, 10.3, 10.5, 10.7, 10.9, 11.1, 11.3, 11.5, 11.8, 12.0, 12.2];
const whoMinBB   = [2.5, 3.4, 4.3, 5.0, 5.6, 6.0, 6.4, 6.7, 6.9, 7.1, 7.3, 7.5, 7.7, 7.9, 8.1, 8.3, 8.4, 8.6, 8.8, 8.9, 9.1, 9.3, 9.4, 9.6, 9.7];

const whoIdealTB = [50.5, 54.7, 58.4, 61.4, 63.9, 65.9, 67.6, 69.2, 70.6, 72.0, 73.3, 74.5, 75.7, 76.9, 78.0, 79.1, 80.2, 81.2, 82.3, 83.2, 84.2, 85.1, 86.0, 86.9, 87.8];
const whoMinTB   = [46.3, 50.8, 54.4, 57.3, 59.7, 61.7, 63.3, 64.8, 66.2, 67.5, 68.7, 69.9, 71.0, 72.1, 73.2, 74.2, 75.2, 76.2, 77.2, 78.1, 79.1, 80.0, 80.9, 81.7, 82.5];

const whoIdealLK = [34.5, 37.3, 39.1, 40.5, 41.5, 42.4, 43.2, 43.8, 44.3, 44.8, 45.3, 45.7, 46.1, 46.4, 46.7, 47.0, 47.3, 47.5, 47.7, 47.9, 48.1, 48.3, 48.5, 48.7, 48.8];
const whoMinLK   = [31.9, 34.6, 36.4, 37.8, 38.9, 39.7, 40.4, 41.0, 41.5, 42.0, 42.4, 42.8, 43.2, 43.5, 43.8, 44.1, 44.3, 44.5, 44.8, 45.0, 45.2, 45.4, 45.5, 45.7, 45.8];

let stuntingChart = null;
let currentTabMode = 'BB';

async function initPage() {
    const monthSelect = document.getElementById('monthInput');
    for(let i=0; i<=24; i++){
        let opt = document.createElement('option');
        opt.value = i;
        opt.text = i === 0 ? "Baru Lahir (0 Bulan)" : `Usia ${i} Bulan`;
        monthSelect.appendChild(opt);
    }
    
    if(activeChild && activeChild.type === 'janin') {
        alert("Modul ini dikhususkan bagi bayi/anak yang sudah lahir. Tapi Anda tetap bisa bereksperimen!");
    }

    // Mengambil data real dari MySQL saat halaman di-load
    try {
        const res = await fetch(`../api.php?action=get_stunting&child_id=${activeChildId}`);
        const result = await res.json();
        if(result.status === 'success') {
            result.data.forEach(dbRow => {
                if(dbRow.month >= 0 && dbRow.month <= 24) {
                    dataBayiAnda[dbRow.month] = { 
                        bb: parseFloat(dbRow.bb) || null, 
                        tb: parseFloat(dbRow.tb) || null, 
                        lk: parseFloat(dbRow.lk) || null 
                    };
                }
            });
        }
    } catch(e) {
        console.error("Gagal load data stunting dari server", e);
    }

    renderChart();
    
    // Auto analisis bulan terakhir yang terisi
    for(let m=24; m>=0; m--){
        if(dataBayiAnda[m] && (dataBayiAnda[m].bb || dataBayiAnda[m].tb)) {
            analisisCerdas(m, dataBayiAnda[m].bb, dataBayiAnda[m].tb);
            break; // cukup analisis record terakhir
        }
    }
}

// --------------------------------
// GRAPHIC LOGIC
// --------------------------------
function switchTab(mode) {
    currentTabMode = mode;
    ['btnTabBB', 'btnTabTB', 'btnTabLK'].forEach(btn => document.getElementById(btn).className = 'tab-btn');
    document.getElementById(`btnTab${mode}`).classList.add('active');
    renderChart();
}

function renderChart() {
    if(stuntingChart) stuntingChart.destroy();
    const ctx = document.getElementById('stuntingChart').getContext('2d');
    
    let arrUser = [], arrIdeal = [], arrMin = [], chartYTitle = '', maxChart = 0;
    
    if (currentTabMode === 'BB') {
        arrUser = dataBayiAnda.map(x => x ? x.bb : null);
        arrIdeal = whoIdealBB; arrMin = whoMinBB;
        chartYTitle = 'Berat Badan (Kg)'; maxChart = 15;
    } else if (currentTabMode === 'TB') {
        arrUser = dataBayiAnda.map(x => x ? x.tb : null);
        arrIdeal = whoIdealTB; arrMin = whoMinTB;
        chartYTitle = 'Panjang/Tinggi Badan (cm)'; maxChart = 100;
    } else if (currentTabMode === 'LK') {
        arrUser = dataBayiAnda.map(x => x ? x.lk : null);
        arrIdeal = whoIdealLK; arrMin = whoMinLK;
        chartYTitle = 'Lingkar Kepala (cm)'; maxChart = 55;
    }

    stuntingChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labelsBulan,
            datasets: [
                {
                    label: `Titik Rekaman Anak`,
                    data: arrUser,
                    borderColor: '#fb6f92', backgroundColor: '#fb6f92',
                    borderWidth: 3, pointRadius: 6, pointHoverRadius: 8, spanGaps: true 
                },
                {
                    label: 'Normal Ideal WHO', data: arrIdeal,
                    borderColor: '#4caf50', borderDash: [5, 5], borderWidth: 2, pointRadius: 0          
                },
                {
                    label: 'Batas Bawah Waspada', data: arrMin,
                    borderColor: '#e57373', backgroundColor: 'rgba(229, 115, 115, 0.1)', 
                    borderWidth: 2, pointRadius: 0, fill: 'origin' 
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { title: { display: true, text: 'Umur (Bulan)' }, grid: { color: '#f1f1f1' } },
                y: { title: { display: true, text: chartYTitle }, grid: { color: '#f1f1f1' }, min: 0, max: maxChart }
            }
        }
    });
}

// --------------------------------
// DATA RECORDING & INSIGHT ENGINE
// --------------------------------
async function recordData() {
    const month = parseInt(document.getElementById('monthInput').value);
    const w = parseFloat(document.getElementById('weightInput').value) || null;
    const h = parseFloat(document.getElementById('heightInput').value) || null;
    const lkc = parseFloat(document.getElementById('headInput').value) || null;

    if(!w && !h) return alert("Bunda belum mengisikan Berat atau Tinggi Badan!");

    // Update real-time UI
    dataBayiAnda[month] = { bb: w, tb: h, lk: lkc };
    
    // Save to MYSQL via api.php
    const fd = new FormData();
    fd.append('action', 'simpan_stunting');
    fd.append('child_id', activeChildId);
    fd.append('month', month);
    fd.append('bb', w || '');
    fd.append('tb', h || '');
    fd.append('lk', lkc || '');

    try {
        const res = await fetch('../api.php', { method: 'POST', body: fd });
        const json = await res.json();
        if(json.status !== 'success') {
            alert('Gagal menyimpan ke database server!');
            return;
        }
        // Sukses save
    } catch(e) {
        alert('Server Error!');
        return;
    }
    
    renderChart();
    analisisCerdas(month, w, h);
}

function analisisCerdas(month, bb, tb) {
    let box = document.getElementById('conclusionBox');
    let nutBox = document.getElementById('nutritionBox');
    
    box.className = "conclusion-box"; // reset
    box.innerHTML = ""; nutBox.style.display = "none";
    
    // VARIABEL AI/Insight
    let isStunting = false;
    let isUnderweight = false;
    let warningTren = "";
    
    // 1. Z-Score Checking vs WHO Minimal Array
    if (tb && tb < whoMinTB[month]) isStunting = true;
    if (bb && bb < whoMinBB[month]) isUnderweight = true;
    
    // 2. Trend Analysis (Memeriksa bulan sebelumnya)
    if (month > 0 && dataBayiAnda[month-1]) {
        let prevBB = dataBayiAnda[month-1].bb;
        if (bb && prevBB) {
            if (bb < prevBB) {
                warningTren = `📉 <b>Growth Trend Berbahaya:</b> Berat badan anak malah <b>turun</b> dibandingkan bulan ke-${month-1}.`;
                box.classList.add("trend-warn");
            } else if (bb === prevBB) {
                warningTren = `⚠️ <b>Growth Trend Melambat:</b> Berat badan stagnan (tidak naik) sejak bulan ke-${month-1}.`;
                box.classList.add("trend-warn");
            }
        }
    }

    // 3. Rekayasa Output Kesimpulan Klinis
    if (isStunting) {
        box.classList.add("warning");
        box.innerHTML = `🚨 <b>INDIKASI STUNTING TINGGI:</b> Tinggi Anak (${tb}cm) berada di bawah garis standar perawakan pendek WHO. Harus dirujuk untuk menghindari disfungsi kognitif permanen! <br><br>${warningTren}`;
    } 
    else if (isUnderweight) {
        box.classList.add("warning");
        box.innerHTML = `⚠️ <b>RISIKO MALNUTRISI:</b> Berat badan (${bb}kg) tidak mencukupi target kurva ideal hijau WHO. Segera intervensi gizi agar tidak menjadi stunting.<br><br>${warningTren}`;
    } 
    else {
        box.classList.add("safe");
        box.innerHTML = `✅ <b>Tumbuh Kembang Optimal!</b> Rasio BB dan TB anak masuk dalam kurva hijau WHO (Aman). Pertahankan ritme asupan Gizinya. <br><br><span style="color:#e65100">${warningTren}</span>`;
    }

    // 4. Sistem Rekomendasi Nutrisi Pribadi
    let saranText = "";
    if (isStunting) {
        saranText = `<i class="fas fa-apple-alt"></i> <b>Rekomendasi Nutrisi:</b> Jangan hanya fokus kalori, perbanyak asupan penyusun tulang dan hormon (Protein Hewani & Zinc). Berikan: Telur 2 butir/hari, Ikan Laut, dan Hati Daging Murni.`;
    } else if (isUnderweight || warningTren !== "") {
        saranText = `<i class="fas fa-drumstick-bite"></i> <b>Rekomendasi Nutrisi:</b> Terjadi perlambatan BB. Fokuskan pada peningkatan Lemak dan Kalori padat seperti: Penambahan keju mentega, alpukat, dan susui minimal tiap 2-3 jam.`;
    }

    if(saranText !== "") {
        nutBox.innerHTML = saranText;
        nutBox.style.display = "block";
    }
}

async function hapusBulanIni() {
    const month = parseInt(document.getElementById('monthInput').value);
    
    // Cek apakah data ada di memori lokal
    if(!dataBayiAnda[month] || (!dataBayiAnda[month].bb && !dataBayiAnda[month].tb)) {
        return alert("Belum ada data di usia " + month + " bulan untuk dihapus.");
    }
    
    if(confirm("Apakah Bunda yakin ingin menghapus catatan usia " + month + " bulan?")) {
        const fd = new FormData();
        fd.append('action', 'hapus_bulan_stunting');
        fd.append('child_id', activeChildId);
        fd.append('month', month);
        
        try {
            const res = await fetch('../api.php', { method: 'POST', body: fd });
            const json = await res.json();
            
            if(json.status === 'success') {
                dataBayiAnda[month] = null;
                
                document.getElementById('weightInput').value = '';
                document.getElementById('heightInput').value = '';
                document.getElementById('headInput').value = '';

                document.getElementById('conclusionBox').className = "conclusion-box"; 
                document.getElementById('conclusionBox').innerHTML = `Data usia ${month} bulan berhasil dihapus.`;
                document.getElementById('nutritionBox').style.display = "none";
                
                renderChart();
                alert(`✅ Data usia ${month} bulan berhasil dihapus dari Database!`);
            } else {
                alert("Gagal menghapus data di server!");
            }
        } catch(e) {
            alert("Gagal menyambung ke server database");
        }
    }
}

async function resetGraphic() {
    if(confirm("Apakah Bunda yakin ingin menghapus SELURUH jejak catatan grafik anak ini dari Database Utama?")){
        
        const fd = new FormData();
        fd.append('action', 'hapus_stunting');
        fd.append('child_id', activeChildId);
        
        try {
            await fetch('../api.php', { method: 'POST', body: fd });
            
            dataBayiAnda.fill(null);
            document.getElementById('conclusionBox').className = "conclusion-box"; 
            document.getElementById('conclusionBox').innerHTML = "Record Kosong.";
            document.getElementById('nutritionBox').style.display = "none";
            
            renderChart();
            alert("✅ Seluruh jejak grafik berhasil dikosongkan dari Database!");
        } catch(e) {
            alert("Gagal menghapus data dari server");
        }
    }
}

window.onload = initPage;



