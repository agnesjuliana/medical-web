// script.js - HANYA BACKEND (UI aman di index.html)
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔗 Backend Kelompok 10 Active');
    
    // GANTI form submit (tambah backend, UI tetap)
    const originalSubmit = document.getElementById('calorieForm').onsubmit;
    
    document.getElementById('calorieForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            usia: parseFloat(document.getElementById('age').value),
            gender: document.getElementById('gender').value,
            berat: parseFloat(document.getElementById('weight').value),
            tinggi: parseFloat(document.getElementById('height').value),
            aktivitas: parseFloat(document.getElementById('activity').value)
        };
        
        console.log('📤 Simpan ke DB Kelompok 10:', formData);
        
        try {
            // Backend kelompok 10
            const response = await fetch('simpan_kalori.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            console.log('✅ Tersimpan!', result);
            
            // Jalankan UI asli (dari index.html)
            if (typeof originalSubmit === 'function') {
                originalSubmit.call(this, e);
            } else {
                // Fallback UI
                const age = formData.usia, gender = formData.gender;
                const weight = formData.berat, height = formData.tinggi;
                const activity = formData.aktivitas;
                
                let bmr = gender === 'male' 
                    ? 88.362 + (13.397 * weight) + (4.799 * height) - (5.677 * age)
                    : 447.593 + (9.247 * weight) + (3.098 * height) - (4.330 * age);
                
                const totalCalories = Math.round(bmr * activity);
                const protein = Math.round((totalCalories * 0.25) / 4);
                const carbs = Math.round((totalCalories * 0.50) / 4);
                const fat = Math.round((totalCalories * 0.25) / 9);
                
                document.getElementById('calorieResult').textContent = totalCalories;
                document.getElementById('proteinResult').textContent = protein + 'g';
                document.getElementById('carbResult').textContent = carbs + 'g';
                document.getElementById('fatResult').textContent = fat + 'g';
                document.getElementById('result').classList.add('show');
            }
            
            // Load history
            loadHistory();
            
        } catch (error) {
            console.error('❌ Backend error:', error);
            // UI tetap jalan (fallback)
            if (typeof originalSubmit === 'function') {
                originalSubmit.call(this, e);
            }
        }
    });
    
    // History loader
    async function loadHistory() {
        try {
            const res = await fetch('ambil_data.php');
            const data = await res.json();
            console.log('📚 History:', data.data?.length || 0, 'items');
            
            // ✅ FIXED forEach error
            if (Array.isArray(data.data)) {
                data.data.forEach(item => console.log('📋', item.kalori));
            }
        } catch (e) {
            console.log('📭 No history');
        }
    }
    
    loadHistory();
});