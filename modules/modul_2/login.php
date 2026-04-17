<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - GrowLife</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --pink-dark: #fb6f92;
            --pink-light: #ffafcc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #fcf4f8;
            /* Soft pink-white base */
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            /* Prevent scrollbar from blobs */
        }

        /* Abstract Floating Shapes (Membentuk Mesh/Blurry Orbs) */
        .shape {
            position: absolute;
            filter: blur(60px);
            z-index: -1;
            animation: floatShape 10s ease-in-out infinite alternate;
        }

        .shape-1 {
            width: 450px;
            height: 450px;
            background: #ffafcc;
            top: -10%;
            left: -10%;
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            animation-duration: 12s;
        }

        .shape-2 {
            width: 350px;
            height: 350px;
            background: #bde0fe;
            bottom: -5%;
            right: -5%;
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            animation-duration: 15s;
            animation-delay: -2s;
        }

        .shape-3 {
            width: 250px;
            height: 250px;
            background: #ffc8dd;
            bottom: 20%;
            left: 15%;
            border-radius: 50%;
            animation-duration: 9s;
            animation-delay: -5s;
        }

        @keyframes floatShape {
            0% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }

            50% {
                transform: translate(30px, 30px) rotate(15deg) scale(1.05);
            }

            100% {
                transform: translate(-20px, -30px) rotate(-15deg) scale(0.95);
            }
        }

        /* Premium Glassmorphism Form */
        .login-box {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            padding: 50px 45px;
            border-radius: 35px;
            box-shadow: 0 15px 35px rgba(251, 111, 146, 0.15), inset 0 0 0 1px rgba(255, 255, 255, 0.5);
            text-align: center;
            width: 100%;
            max-width: 380px;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            position: relative;
            overflow: hidden;
        }

        @keyframes fadeUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .logo {
            font-size: 2.6rem;
            font-weight: 700;
            color: var(--pink-dark);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-shadow: 0 4px 15px rgba(251, 111, 146, 0.2);
        }

        .logo i {
            animation: pulse 2s infinite ease-in-out;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .subtitle {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 35px;
            line-height: 1.5;
            font-weight: 500;
        }

        .input-group {
            margin-bottom: 25px;
            text-align: left;
            position: relative;
        }

        .input-group i {
            position: absolute;
            top: 46px;
            left: 16px;
            color: #fb6f92;
            font-size: 1.1rem;
            opacity: 0.8;
        }

        label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 16px 15px 16px 48px;
            margin-top: 8px;
            border: 2px solid transparent;
            border-radius: 18px;
            outline: none;
            font-family: inherit;
            font-size: 1rem;
            color: #444;
            font-weight: 600;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.7);
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }

        input::placeholder {
            color: #aaa;
            font-weight: 400;
        }

        input:focus {
            border-color: rgba(251, 111, 146, 0.5);
            background: #ffffff;
            box-shadow: 0 5px 15px rgba(251, 111, 146, 0.1);
        }

        .btn {
            background: linear-gradient(135deg, var(--pink-dark), #ff8fab);
            color: white;
            border: none;
            padding: 18px;
            width: 100%;
            border-radius: 18px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.4s;
            box-shadow: 0 10px 20px rgba(251, 111, 146, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(251, 111, 146, 0.5);
        }

        .form-switch {
            font-size: 0.95rem;
            color: #666;
            margin-top: 25px;
            font-weight: 500;
        }

        .form-switch a {
            color: var(--pink-dark);
            transition: 0.3s;
            text-decoration: none;
        }

        .form-switch a:hover {
            color: #e05e80;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <!-- Dekorasi Belakang (Floating Blurred Shapes) -->
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <div class="login-box" id="loginForm">
        <div class="logo"><i class="fas fa-leaf"></i> GrowLife</div>
        <p class="subtitle">Platform Kehamilan & Tumbuh Kembang</p>

        <div class="input-group">
            <label>Alamat Email</label>
            <i class="fas fa-envelope"></i>
            <input type="email" id="loginEmail" placeholder="Bunda@gmail.com" autocomplete="off">
        </div>

        <div class="input-group">
            <label>Password</label>
            <i class="fas fa-lock"></i>
            <input type="password" id="loginPass" placeholder="****">
        </div>

        <button class="btn" onclick="doLogin()">Masuk</button>
        <p class="form-switch">
            Belum punya akun? <a href="#" onclick="toggleForm()">Buat Akun Baru</a>
        </p>
    </div>

    <!-- Form Register -->
    <div class="login-box" id="registerForm" style="display: none;">
        <div class="logo"><i class="fas fa-user-plus"></i> Buat Akun</div>
        <p class="subtitle">Daftar untuk memantau tumbuh kembang si kecil.</p>

        <div class="input-group">
            <label>Nama Panggilan Bunda</label>
            <i class="fas fa-user-circle"></i>
            <input type="text" id="regName" placeholder="Contoh: Bunda Laras" autocomplete="off">
        </div>

        <div class="input-group">
            <label>Alamat Email</label>
            <i class="fas fa-envelope"></i>
            <input type="email" id="regEmail" placeholder="Ketik email Bunda" autocomplete="off">
        </div>

        <div class="input-group">
            <label>Password</label>
            <i class="fas fa-lock"></i>
            <input type="password" id="regPass" placeholder="Masukkan Sandi Rahasia">
        </div>

        <button class="btn" style="background: linear-gradient(135deg, #4caf50, #81c784);" onclick="doRegister()">Daftar
            Sekarang</button>
        <p class="form-switch">
            Sudah ada akun? <a href="#" onclick="toggleForm()">Masuk di sini</a>
        </p>
    </div>

    <script>
        function toggleForm() {
            const lf = document.getElementById('loginForm');
            const rf = document.getElementById('registerForm');
            if (lf.style.display === "none") {
                lf.style.display = "block";
                rf.style.display = "none";
            } else {
                lf.style.display = "none";
                rf.style.display = "block";
            }
        }

        async function doRegister() {
            const name = document.getElementById('regName').value;
            const email = document.getElementById('regEmail').value;
            const pass = document.getElementById('regPass').value;

            if (!name || !email || !pass) {
                return alert("⚠️ Maaf Bunda, semua form wajib diisi!");
            }

            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('name', name);
            formData.append('email', email);
            formData.append('pass', pass);

            try {
                const response = await fetch('api_auth.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.status === 'success') {
                    alert(data.message);
                    document.getElementById('regName').value = "";
                    document.getElementById('regEmail').value = "";
                    document.getElementById('regPass').value = "";
                    toggleForm();
                } else {
                    alert("❌ " + data.message);
                }
            } catch (err) {
                alert("❌ Terjadi kesalahan server saat mendaftar.");
            }
        }

        async function doLogin() {
            const email = document.getElementById('loginEmail').value;
            const pass = document.getElementById('loginPass').value;

            if (!email || !pass) {
                return alert("⚠️ Tolong isi Alamat Email dan Password!");
            }

            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', email);
            formData.append('pass', pass);

            try {
                const response = await fetch('api_auth.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.status === 'success') {
                    // Berhasil Login!
                    localStorage.setItem("motherName", data.name); // Tetap simpan visual name, tapi Backend udah aman dengan session

                    // Animasi sesaat sebelum pindah
                    document.getElementById('loginForm').innerHTML = `<h2 style="color:var(--pink-dark)"><i class="fas fa-spinner fa-spin"></i><br>Sedang Masuk...</h2>`;
                    setTimeout(() => {
                        window.location.href = "dashboardgrowlife.php"; // Update ke PHP
                    }, 800);
                } else {
                    alert("❌ " + data.message);
                }
            } catch (err) {
                alert("❌ Koneksi ke server gagal.");
            }
        }
    </script>
</body>

</html>