<?php
/**
 * Modul 3 Register Page
 * Menggunakan gaya PulmoAI (Glassmorphism)
 */

require_once __DIR__ . '/../../core/auth.php';
requireGuest(BASE_URL . '/modules/modul_3/index.php');
startSession();

$pageTitle = 'Daftar Akses PulmoAI';
$error  = getFlash('error');
$errors = $_SESSION['validation_errors'] ?? [];
$old    = $_SESSION['old_input'] ?? [];
unset($_SESSION['validation_errors'], $_SESSION['old_input']);
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>

<style>
/* Reusing PulmoAI styles */
:root {
  --background: oklch(0.99 0.005 220);
  --primary: oklch(0.58 0.14 210);
  --accent: oklch(0.72 0.15 195);
  --gradient-primary: linear-gradient(135deg, oklch(0.58 0.14 210), oklch(0.72 0.15 195));
  --glass-bg: oklch(1 0 0 / 0.7);
  --glass-border: oklch(1 0 0 / 0.3);
  --shadow-soft: 0 4px 20px -8px oklch(0.4 0.1 220 / 0.15);
  --shadow-glow: 0 0 40px oklch(0.65 0.15 200 / 0.35);
}
.glass { background: var(--glass-bg); backdrop-filter: blur(16px) saturate(180%); border: 1px solid var(--glass-border); }
.gradient-primary { background: var(--gradient-primary); }
</style>

<div class="min-h-screen flex items-center justify-center px-4 py-12" style="background-color: var(--background);">
    <div class="w-full max-w-md animate-fade-in relative mt-10 mb-10">
        
        <!-- Decorative blobs -->
        <div class="absolute -top-10 -right-10 h-32 w-32 rounded-full bg-accent/20 blur-2xl"></div>
        <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-primary/20 blur-2xl"></div>

        <!-- Logo -->
        <div class="text-center mb-8 relative z-10">
            <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl gradient-primary shadow-glow mb-4">
                <i data-lucide="shield-plus" class="h-8 w-8 text-white stroke-[2.5px]"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Daftar Akun</h1>
            <p class="text-gray-500 text-sm mt-1 font-medium">Buat kredensial akses untuk PulmoAI Sistem</p>
        </div>

        <!-- Card -->
        <div class="glass rounded-3xl shadow-soft p-8 relative z-10 bg-white/80">

            <?php if ($error): ?>
            <div class="mb-6 flex items-center gap-2 px-4 py-3 bg-red-50 text-red-700 text-sm rounded-xl border border-red-100">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/modules/modul_3/process_register.php" method="POST" id="registerForm" novalidate>
                
                <!-- Name -->
                <div class="mb-5">
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i> Nama Lengkap
                    </label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                           placeholder="Dr. Andi"
                           class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] focus:bg-white transition-all shadow-sm <?= isset($errors['name']) ? 'border-red-300 bg-red-50/50' : '' ?>"
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <p class="mt-1 text-xs text-red-500"><?= htmlspecialchars($errors['name']) ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-red-500 hidden" id="name-error"></p>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="mb-5">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i> Alamat Email
                    </label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                           placeholder="dokter@rs-example.com"
                           class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] focus:bg-white transition-all shadow-sm <?= isset($errors['email']) ? 'border-red-300 bg-red-50/50' : '' ?>"
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <p class="mt-1 text-xs text-red-500"><?= htmlspecialchars($errors['email']) ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-red-500 hidden" id="email-error"></p>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="mb-5">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i> Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" 
                               placeholder="••••••••"
                               class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] focus:bg-white transition-all shadow-sm pr-10 <?= isset($errors['password']) ? 'border-red-300 bg-red-50/50' : '' ?>"
                               required>
                        <button type="button" onclick="togglePassword('password', this)" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <p class="mt-1 text-xs text-red-500"><?= htmlspecialchars($errors['password']) ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-red-500 hidden" id="password-error"></p>
                    <?php endif; ?>
                </div>

                <!-- Confirm Password -->
                <div class="mb-8">
                    <label for="password_confirm" class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i> Konfirmasi Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password_confirm" name="password_confirm" 
                               placeholder="••••••••"
                               class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] focus:bg-white transition-all shadow-sm pr-10 <?= isset($errors['password_confirm']) ? 'border-red-300 bg-red-50/50' : '' ?>"
                               required>
                        <button type="button" onclick="togglePassword('password_confirm', this)" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <p class="mt-1 text-xs text-red-500"><?= htmlspecialchars($errors['password_confirm']) ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-red-500 hidden" id="password_confirm-error"></p>
                    <?php endif; ?>
                </div>

                <!-- Submit -->
                <button type="submit" 
                        class="w-full gradient-primary hover:opacity-90 text-white font-bold py-3.5 px-4 rounded-xl text-sm shadow-glow transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                    Daftar Akun Baru <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <!-- Login link -->
        <p class="text-center text-sm text-gray-500 mt-8 relative z-10 font-medium">
            Sudah mendaftar akses?
            <a href="<?= BASE_URL ?>/modules/modul_3/login.php" class="text-[#5B88D6] hover:text-[#7C9CE1] font-bold transition-colors">Masuk di sini</a>
        </p>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<script>
    lucide.createIcons();
    
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }

    document.getElementById('registerForm').addEventListener('submit', function(e) {
        let valid = true;
        valid = validateRequired('name', 'Nama Lengkap') && valid;
        valid = validateRequired('email', 'Email') && valid;
        valid = validateEmail('email') && valid;
        valid = validateMinLength('password', 6, 'Password') && valid;
        valid = validateMatch('password', 'password_confirm', 'Konfirmasi Password') && valid;
        if (!valid) e.preventDefault();
    });
</script>
</body>
</html>
