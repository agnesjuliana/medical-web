document.addEventListener('DOMContentLoaded', function() {
    // 1. Panggil history saat pertama kali halaman dibuka
    loadHistory(); 

    const calorieForm = document.getElementById('calorieForm');
    
    if (calorieForm) {
        calorieForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Ambil data dari form
            const formData = {
                usia: parseFloat(document.getElementById('usia').value),
                gender: document.getElementById('gender').value,
                berat: parseFloat(document.getElementById('berat').value),
                tinggi: parseFloat(document.getElementById('tinggi').value),
                aktivitas: parseFloat(document.getElementById('aktivitas').value)
            };

            console.log('Data yang akan dikirim:', formData);

            try {
                const response = await fetch('simpan_kalori.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const rawResponse = await response.text();
                console.log('Respon mentah:', rawResponse);

                const result = JSON.parse(rawResponse);
                
                if (result.status === 'success') {
                    alert('✅ BERHASIL! Data tersimpan.');
                    updateUI(result.kalori);
                    
                    // PANGGIL LAGI biar tabel history langsung update otomatis
                    loadHistory(); 
                } else {
                    alert('❌ GAGAL: ' + result.msg);
                }

            } catch (error) {
                console.error('❌ Error parsing JSON:', error);
            }
        });
    }

    // Fungsi Update Tampilan Angka Kalori
    function updateUI(kalori) {
        const calRes = document.getElementById('calorieResult');
        const resDiv = document.getElementById('result');
        if(calRes) {
            calRes.textContent = kalori;
            resDiv.classList.add('show');
        }
    }

    // FUNGSI LOAD HISTORY (Ditaruh di dalam agar rapi)
    async function loadHistory() {
        console.log('Memuat history...');
        try {
            const response = await fetch('ambil_data.php');
            const result = await response.json();
            
            const tableBody = document.getElementById('historyTableBody');
            if (!tableBody) return;

            tableBody.innerHTML = ''; 

            if (result.status === 'success' && result.data.length > 0) {
                result.data.forEach(item => {
                    tableBody.innerHTML += `
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="py-4 px-6 text-sm text-gray-600">${item.waktu}</td>
                            <td class="py-4 px-6 text-sm">${item.usia} Thn / ${item.gender}</td>
                            <td class="py-4 px-6 text-sm">${item.berat}kg / ${item.tinggi}cm</td>
                            <td class="py-4 px-6 text-sm font-bold text-blue-600">${item.kalori} kkal</td>
                        </tr>
                    `;
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="4" class="py-4 text-center text-gray-500">Belum ada data riwayat.</td></tr>';
            }
        } catch (e) {
            console.error("Error load history:", e);
        }
    }
});