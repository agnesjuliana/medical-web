document.addEventListener('DOMContentLoaded', () => {

    // --- 1. FITUR MODE GELAP / TERANG ---
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    // Cek preferensi sebelumnya
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
    } else {
        document.documentElement.classList.remove('dark');
    }

    themeToggle.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        if (document.documentElement.classList.contains('dark')) {
            localStorage.setItem('theme', 'dark');
            // Icon Matahari
            themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
        } else {
            localStorage.setItem('theme', 'light');
            // Icon Bulan
            themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>';
        }
    });


    // --- 2. FITUR UBAH BAHASA (EN / ID) ---
    let currentLang = 'en';
    const langToggle = document.getElementById('langToggle');
    const translatableElements = document.querySelectorAll('.translatable');

    function updateLanguage() {
        translatableElements.forEach(el => {
            if (currentLang === 'en') {
                el.innerHTML = el.getAttribute('data-en');
            } else {
                el.innerHTML = el.getAttribute('data-id');
            }
        });
        langToggle.innerText = currentLang === 'en' ? 'ID' : 'EN';
    }

    langToggle.addEventListener('click', () => {
        currentLang = currentLang === 'en' ? 'id' : 'en';
        updateLanguage();
        // Sembunyikan hasil form jika bahasa diganti agar tidak bingung
        document.getElementById('resultBox').classList.add('hidden');
    });


    // --- 3. ANIMASI SCROLL (UI) ---
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = entry.target.classList.contains('scale-in') ? 'scale(1)' : 'translateY(0)';
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.slide-up, .scale-in, .fade-in').forEach(el => observer.observe(el));


    // --- 4. LOGIKA KALKULATOR KALORI (Dengan Goal & Bahasa) ---
    const form = document.getElementById('calcForm');
    const resultBox = document.getElementById('resultBox');
    const btnReset = document.getElementById('btnReset');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Ambil Nilai
        const weight = parseFloat(document.getElementById('weight').value);
        const duration = parseFloat(document.getElementById('duration').value);
        const activitySelect = document.getElementById('activity');
        const metValue = parseFloat(activitySelect.value);

        // Ambil nama aktivitas berdasarkan bahasa
        const activityOption = activitySelect.options[activitySelect.selectedIndex];
        const activityName = currentLang === 'en' ? activityOption.getAttribute('data-en') : activityOption.getAttribute('data-id');

        // Ambil Tujuan / Goal
        const goalValue = document.getElementById('goal').value;

        // Perhitungan
        const burnedCalories = Math.round(((metValue * 3.5 * weight) / 200) * duration);
        const waterNeeded = Math.round((duration / 30) * 250);

        // Siapkan Teks Rekomendasi berdasarkan Goal & Bahasa
        let recommendation = "";
        if (currentLang === 'en') {
            if (goalValue === 'lose') {
                recommendation = `This creates a solid caloric deficit to help your weight loss journey! Pair this with a protein-rich meal to maintain muscle.`;
            } else if (goalValue === 'bulk') {
                recommendation = `Great cardio session! Since you are bulking, make sure to eat an extra <b>${burnedCalories + 300} kcal</b> today to stay in a caloric surplus for muscle growth.`;
            } else {
                recommendation = `Excellent work keeping your body active! Consistent activity like this is perfect for maintaining your healthy lifestyle.`;
            }

            document.getElementById('narrativeResult').innerHTML = `
          You just burned approximately <span class="text-4xl font-bold block my-3 text-green-200">${burnedCalories} kcal</span> 
          from <b>${duration} minutes</b> of ${activityName.toLowerCase()}. <br><br>
          ${recommendation}
        `;
            document.getElementById('hydrationTip').innerText = `Recovery Tip: Drink ~${waterNeeded}ml of water to replenish lost fluids.`;
        } else {
            if (goalValue === 'lose') {
                recommendation = `Aktivitas ini menciptakan defisit kalori yang bagus untuk program dietmu! Imbangi dengan makanan tinggi protein untuk menjaga massa otot.`;
            } else if (goalValue === 'bulk') {
                recommendation = `Kardio yang mantap! Karena kamu sedang bulking, pastikan kamu makan ekstra <b>${burnedCalories + 300} kkal</b> hari ini agar tetap surplus kalori untuk ototmu.`;
            } else {
                recommendation = `Kerja bagus! Aktivitas fisik yang konsisten seperti ini sangat sempurna untuk menjaga pola hidup sehat dan berat badan idealmu.`;
            }

            document.getElementById('narrativeResult').innerHTML = `
          Kamu berhasil membakar sekitar <span class="text-4xl font-bold block my-3 text-green-200">${burnedCalories} kkal</span> 
          dari <b>${duration} menit</b> melakukan ${activityName.toLowerCase()}. <br><br>
          ${recommendation}
        `;
            document.getElementById('hydrationTip').innerText = `Tips Pemulihan: Minum sekitar ${waterNeeded}ml air putih untuk mengganti cairan tubuh.`;
        }

        // Tampilkan Hasil dengan animasi
        resultBox.classList.remove('hidden');
        resultBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    // Fitur Tombol Reset
    btnReset.addEventListener('click', () => {
        form.reset();
        resultBox.classList.add('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Setel bahasa awal
    updateLanguage();
});