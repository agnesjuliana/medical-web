const activeChildId = localStorage.getItem('activeChildId');
if (!activeChildId) {
    alert("⚠️ Bunda harus memilih Profil Anak/Kehamilan di Dashboard terlebih dahulu!");
    window.location.href = "../dashboardgrowlife.html";
}

let childrenProfiles = JSON.parse(localStorage.getItem('childrenProfiles')) || [];
let activeChild = childrenProfiles.find(c => c.id == activeChildId);

if (activeChild) {
    document.getElementById('namaAnakHeader').innerText = activeChild.name + (activeChild.type === 'janin' ? " (Masa Kehamilan)" : " (Anak)");
    if (activeChild.type === 'janin') {
        document.getElementById('labelTgl').innerText = "HPL (Hari Perkiraan Lahir) Bunda";
    } else {
        document.getElementById('labelTgl').innerText = "Tanggal Kelahiran Anak";
    }
}

// ==========================================
// MOCK DATABASE & JENDELA WAKTU
// ==========================================
const IMMUNI_MASTER = [
    { nama: "Hepatitis B (HB-0)", bulan: 0 },
    { nama: "BCG, Polio 1", bulan: 1 },
    { nama: "DPT-HB-Hib 1, Polio 2, PCV 1", bulan: 2 },
    { nama: "DPT-HB-Hib 2, Polio 3", bulan: 3 },
    { nama: "DPT-HB-Hib 3, Polio 4, IPV", bulan: 4 },
    { nama: "Campak (MR) 1", bulan: 9 },
    { nama: "DPT-HB-Hib Lanjutan", bulan: 18 },
    { nama: "Campak (MR) Lanjutan", bulan: 24 }
];

const PRENATAL_MASTER = [
    { nama: "USG Trimester 1 (Skrining Awal & Detak Jantung)", bulan: -7 },
    { nama: "Cek Trimester 2 (Skrining Organ & Fetomaternal)", bulan: -5 },
    { nama: "USG Trimester 3 (Cek Posisi Kepala Bayi)", bulan: -2 },
    { nama: "Persiapan Persalinan Dekat HPL", bulan: 0 }
];

// State Global
let schedules = null;

async function loadData() {
    try {
        const res = await fetch(`../api.php?action=get_reminder&child_id=${activeChildId}`);
        const result = await res.json();
        
        if(result.status === 'success' && result.data) {
            schedules = result.data;
            document.getElementById('tglLahir').value = schedules.tgl;
            checkNotifications(); 
            renderTimeline();
        } else {
            document.getElementById('scheduleTimeline').innerHTML = "<p style='color:#888; text-align:center;'>Belum ada jadwal. Silakan set tanggal patokan (Lahir/HPL) di samping lalu klik Buat Kalender.</p>";
        }
    } catch(e) {
        console.error("Gagal load data reminder", e);
    }
}

async function saveToDB() {
    if(!schedules) return;
    
    const fd = new FormData();
    fd.append('action', 'simpan_reminder');
    fd.append('child_id', activeChildId);
    fd.append('tgl', schedules.tgl);
    fd.append('items', JSON.stringify(schedules.items));

    try {
        const res = await fetch('../api.php', { method: 'POST', body: fd });
        const result = await res.json();
        if(result.status !== 'success') {
            console.error("Gagal simpan:", result.message);
        }
    } catch(e) {
        console.error("Server Error saat menyimpan");
    }
}

async function generateSchedule() {
    const tgl = document.getElementById('tglLahir').value;
    if(!tgl) return alert("⚠️ Mohon isi tanggal patokan (Lahir/HPL) terlebih dahulu!");

    const masterBasis = (activeChild && activeChild.type === 'janin') ? PRENATAL_MASTER : IMMUNI_MASTER;
    
    let generated = [];
    const baseDate = new Date(tgl);

    masterBasis.forEach(master => {
        let idealStart = new Date(baseDate);
        idealStart.setMonth(idealStart.getMonth() + master.bulan);
        
        let idealEnd = new Date(idealStart);
        idealEnd.setDate(idealEnd.getDate() + 14); 

        generated.push({
            tugas: master.nama,
            startDateStr: idealStart.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }),
            endDateStr: idealEnd.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }),
            startDateObj: idealStart.toISOString(),
            endDateObj: idealEnd.toISOString(),
            userPlannedDate: null 
        });
    });

    schedules = { tgl: tgl, items: generated };
    
    await saveToDB();

    alert("✅ Berhasil! Jadwal telah disimpan di Database.");
    checkNotifications();
    renderTimeline();
}

function checkNotifications() {
    if(!schedules || !schedules.items) return;
    
    const bannerBox = document.getElementById('notificationBanner');
    if(!bannerBox) return; 
    
    let messages = [];
    const today = new Date();
    today.setHours(0,0,0,0);
    
    schedules.items.forEach(it => {
        if(it.userPlannedDate) {
            const pDate = new Date(it.userPlannedDate);
            pDate.setHours(0,0,0,0);
            
            const diffTime = pDate.getTime() - today.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 3600 * 24));
            
            if(diffDays === 0) {
                messages.push(`<div style="margin-bottom:8px; color: #b71c1c; font-size: 1.1rem;">🚨 <b>HARI INI:</b> Jadwal layanan <b>${it.tugas}</b>. Segera ke Faskes ya!</div>`);
            } else if (diffDays === 1) {
                messages.push(`<div style="margin-bottom:8px; color: #c62828;">🔔 <b>BESOK:</b> Ingat jadwal <b>${it.tugas}</b>. Persiapkan Buku KIA Bunda.</div>`);
            }
        }
    });
    
    if(messages.length > 0) {
        bannerBox.style.display = "block";
        bannerBox.innerHTML = messages.join("");
    } else {
        bannerBox.style.display = "none";
    }
}

window.syncToGCal = function(tugas, dateStr) {
    if(!dateStr) return;
    const judul = encodeURIComponent(`🩺 GrowLife: ${tugas}`);
    const catatan = encodeURIComponent(`Pengingat otomatis dari platform GrowLife.\nTarget Profil: ${activeChild ? activeChild.name : ''}`);
    let d = new Date(dateStr);
    let day = ("0" + d.getDate()).slice(-2);
    let month = ("0" + (d.getMonth() + 1)).slice(-2);
    let dateStamp = `${d.getFullYear()}${month}${day}`;
    let startDate = dateStamp + "T020000Z";
    let endDate = dateStamp + "T040000Z";
    let url = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${judul}&details=${catatan}&dates=${startDate}/${endDate}`;
    window.open(url, '_blank');
}

async function setUserPlan(index, value) {
    if(!schedules) return;
    
    const chosenDate = new Date(value);
    const startDate = new Date(schedules.items[index].startDateObj);
    const endDate = new Date(schedules.items[index].endDateObj);
    
    chosenDate.setHours(0,0,0,0);
    startDate.setHours(0,0,0,0);
    endDate.setHours(0,0,0,0);

    if(chosenDate < startDate || chosenDate > endDate) {
        alert("⚠️ PERHATIAN BUNDA:\n\nTanggal yang Bunda pilih berada DI LUAR rentang optimal/ideal medis. Jika terpaksa, komunikasikan dengan Faskes pilihan Anda.");
    }

    schedules.items[index].userPlannedDate = value;
    
    // Update ke Database Server (Baris Spesifik)
    const fd = new FormData();
    fd.append('action', 'update_rencana_reminder');
    fd.append('child_id', activeChildId);
    fd.append('tugas', schedules.items[index].tugas);
    fd.append('planned_date', value);

    try {
        const res = await fetch('../api.php', { method: 'POST', body: fd });
        const result = await res.json();
        if(result.status !== 'success') console.error("Update gagal:", result.message);
    } catch(e) {
        console.error("Server Error update rencana");
    }
    
    checkNotifications();
    renderTimeline();
}

function renderTimeline() {
    const box = document.getElementById('scheduleTimeline');
    if(!box) return;
    box.innerHTML = "";
    if(!schedules || !schedules.items || schedules.items.length === 0) return;

    schedules.items.forEach((it, idx) => {
        let div = document.createElement('div');
        div.className = "timeline-item";
        
        let hasPlan = it.userPlannedDate ? true : false;
        
        const chosenDate = hasPlan ? new Date(it.userPlannedDate) : null;
        const startDate = new Date(it.startDateObj);
        const endDate = new Date(it.endDateObj);
        
        if (chosenDate) { chosenDate.setHours(0,0,0,0); }
        startDate.setHours(0,0,0,0);
        endDate.setHours(0,0,0,0);
        
        let isOutBounds = hasPlan && (chosenDate < startDate || chosenDate > endDate);
        const isPassedEndDate = new Date() > endDate && !hasPlan;

        // Visual Colors
        let statusColor = hasPlan ? (isOutBounds ? '#f57c00' : '#4caf50') : (isPassedEndDate ? '#e57373' : '#fb6f92');
        let statusIcon = hasPlan ? (isOutBounds ? '<i class="fas fa-exclamation-triangle"></i>' : '<i class="fas fa-check-circle"></i>') : '<i class="fas fa-calendar-alt"></i>';
        
        let bgStyle = '';
        if (hasPlan && !isOutBounds) bgStyle = 'background: #e8f5e9; border-left-color: #4caf50;';
        else if (hasPlan && isOutBounds) bgStyle = 'background: #fff3e0; border-left-color: #f57c00;';
        else if (isPassedEndDate) bgStyle = 'background: #ffebee; border-left-color: #e57373;';
        if(bgStyle) div.style.cssText = bgStyle;

        let warningPill = isOutBounds ? `<div style="font-size: 0.75rem; background: #ffe0b2; color: #e65100; padding: 4px 8px; border-radius: 6px; display: inline-block; margin-top:5px; font-weight:bold;">⚠️ Luar Rekomendasi Medis</div>` : '';

        // Teks Notif Internal
        let guideHTML = hasPlan ? 
            `<strong style="color:${isOutBounds ? '#f57c00' : '#2e7d32'};"><i class="fas fa-bookmark"></i> Rencana Bunda: ${new Date(it.userPlannedDate).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'})}</strong><br> ${warningPill}`
            : `<i class="fas fa-info-circle"></i> Rentang Waktu Terbagus/Aman: <br><b>${it.startDateStr} s/d ${it.endDateStr}</b>`;

        // Tombol Sinkronisasi ke Calendar
        let gcalBtn = hasPlan ?
            `<button class="btn btn-outline" style="margin-top:15px; background: white; border: 1px solid #4285F4; border-radius:8px; color: #4285F4; font-size: 0.85rem; padding: 8px 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items:center; gap: 8px;" onclick="syncToGCal('${it.tugas}', '${it.userPlannedDate}')"><img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Google_Calendar_icon_%282020%29.svg" width="16"> Simpan ke G-Calendar</button>` : '';

        div.innerHTML = `
            <div style="flex-grow: 1;">
                <h4 style="color: ${statusColor}">${statusIcon} ${it.tugas}</h4>
                <p class="timeline-desc" style="color: ${hasPlan ? '#2e7d32' : '#666'}">${guideHTML} ${gcalBtn}</p>
                
                <div class="user-action-box">
                    <label style="font-size: 0.75rem; font-weight: 600; color:#888;">Ubah/Isi waktu rencana Bunda:</label><br>
                    <input type="date" class="user-date-picker" value="${it.userPlannedDate || ''}" onchange="setUserPlan(${idx}, this.value)">
                </div>
            </div>
        `;
        box.appendChild(div);
    });
}

window.onload = loadData;
