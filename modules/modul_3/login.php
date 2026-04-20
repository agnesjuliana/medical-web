<?php
/**
 * Modul 3 Login Page
 * Menggunakan gaya PulmoAI (Glassmorphism)
 */

require_once __DIR__ . '/../../core/auth.php';
requireGuest(BASE_URL . '/modules/modul_3/index.php');
startSession();

$pageTitle = 'Masuk PulmoAI';
$error   = getFlash('error');
$success = getFlash('success');
$oldEmail = getFlash('old_email');
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
    <div class="w-full max-w-md animate-fade-in relative">
        
        <!-- Decorative blobs -->
        <div class="absolute -top-10 -left-10 h-32 w-32 rounded-full bg-accent/20 blur-2xl"></div>
        <div class="absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-primary/20 blur-2xl"></div>

        <!-- Logo -->
        <div class="text-center mb-8 relative z-10">
            <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl gradient-primary shadow-glow mb-4">
                <i data-lucide="activity" class="h-8 w-8 text-white stroke-[2.5px]"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">PulmoAI</h1>
            <p class="text-gray-500 text-sm mt-1 font-medium">Masuk untuk mengakses sistem Deteksi TBC</p>
        </div>

        <!-- Card -->
        <div class="glass rounded-3xl shadow-soft p-8 relative z-10 bg-white/80">

            <?php if ($error): ?>
            <div class="mb-6 flex items-center gap-2 px-4 py-3 bg-red-50 text-red-700 text-sm rounded-xl border border-red-100">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="mb-6 flex items-center gap-2 px-4 py-3 bg-green-50 text-green-700 text-sm rounded-xl border border-green-100">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/modules/modul_3/process_login.php" method="POST" id="loginForm" novalidate>
                
                <!-- Email -->
                <div class="mb-5">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i> Alamat Email
                    </label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($oldEmail ?? '') ?>"
                           placeholder="dokter@rs-example.com"
                           class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] focus:bg-white transition-all shadow-sm"
                           required>
                    <p class="mt-1 text-xs text-red-500 hidden" id="email-error"></p>
                </div>

                <!-- Password -->
                <div class="mb-8">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i> Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" 
                               placeholder="••••••••"
                               class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#5B88D6]/30 focus:border-[#5B88D6] focus:bg-white transition-all shadow-sm pr-10"
                               required>
                        <button type="button" onclick="togglePassword('password', this)" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-red-500 hidden" id="password-error"></p>
                </div>

                <!-- Submit -->
                <button type="submit" 
                        class="w-full gradient-primary hover:opacity-90 text-white font-bold py-3.5 px-4 rounded-xl text-sm shadow-glow transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                    Masuk Sekarang <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <!-- Register link -->
        <p class="text-center text-sm text-gray-500 mt-8 relative z-10 font-medium">
            Belum memiliki akses?
            <a href="<?= BASE_URL ?>/modules/modul_3/register.php" class="text-[#5B88D6] hover:text-[#7C9CE1] font-bold transition-colors">Daftar Akun Khusus</a>
        </p>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<script>
    lucide.createIcons();
    
    // Toggle Password Script (Incase validation.js toggle is different)
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

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        let valid = true;
        valid = validateRequired('email', 'Email') && valid;
        valid = validateEmail('email') && valid;
        valid = validateRequired('password', 'Password') && valid;
        if (!valid) e.preventDefault();
    });
</script>
</body>
</html>
