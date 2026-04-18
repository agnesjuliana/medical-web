// 1. Fitur Smooth Scrolling (Navigasi Halus)
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// 2. Fitur Kalkulator Kalori (Harris-Benedict Formula)
const calorieForm = document.getElementById('calorieForm');
if (calorieForm) {
    calorieForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const age = parseFloat(document.getElementById('age').value);
        const gender = document.getElementById('gender').value;
        const weight = parseFloat(document.getElementById('weight').value);
        const height = parseFloat(document.getElementById('height').value);
        const activity = parseFloat(document.getElementById('activity').value);
        
        let bmr;
        // Rumus Harris-Benedict
        if (gender === 'male') {
            bmr = 88.362 + (13.397 * weight) + (4.799 * height) - (5.677 * age);
        } else {
            bmr = 447.593 + (9.247 * weight) + (3.098 * height) - (4.330 * age);
        }
        
        const totalCalories = Math.round(bmr * activity);
        const totalCalories = Math.round(bmr * activity);
        fetch('simpan_kalori.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                usia: age,
                gender: gender,
                berat: weight,
                tinggi: height,
                aktivitas: activity,
                kalori: totalCalories
            })
        })
        .then(res => res.json())
        .then(data => {
            console.log("Data berhasil disimpan:", data);
        })
        .catch(err => console.error("Error:", err));
        // Estimasi Makronutrisi
        const protein = Math.round((totalCalories * 0.25) / 4); // 25% Protein
        const carbs = Math.round((totalCalories * 0.50) / 4);   // 50% Karbohidrat
        const fat = Math.round((totalCalories * 0.25) / 9);     // 25% Lemak
        
        // Menampilkan Hasil ke UI
        document.getElementById('calorieResult').textContent = totalCalories;
        document.getElementById('proteinResult').textContent = protein + 'g';
        document.getElementById('carbResult').textContent = carbs + 'g';
        document.getElementById('fatResult').textContent = fat + 'g';
        
        const resultSection = document.getElementById('result');
        resultSection.classList.add('show');
        
        // Scroll otomatis ke hasil perhitungan
        resultSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

// 3. Fitur Pencarian Database Makanan (Live Search)
const foodSearch = document.getElementById('foodSearch');
if (foodSearch) {
    foodSearch.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const foodCards = document.querySelectorAll('.food-card');
        
        foodCards.forEach(card => {
            const foodName = card.querySelector('.food-name').textContent.toLowerCase();
            if (foodName.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
}

// 4. Animasi Muncul Saat Scroll (Intersection Observer)
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // Memberikan efek animasi fade in up
            entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
            // Berhenti mengamati setelah animasi jalan sekali
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Mengamati elemen kartu fitur dan makanan
document.querySelectorAll('.feature-card, .food-card').forEach(el => {
    observer.observe(el);
});