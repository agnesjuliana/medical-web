<?php
/**
 * Login Page
 */

require_once __DIR__ . '/../core/auth.php';
requireGuest();
startSession();

$pageTitle = 'Sign In';
$error   = getFlash('error');
$success = getFlash('success');
$oldEmail = getFlash('old_email');
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-cyan-500 to-cyan-700 rounded-2xl shadow-lg shadow-cyan-500/20 mb-4">
                <span class="text-white text-xl font-bold">M</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Welcome back</h1>
            <p class="text-gray-500 text-sm mt-1">Sign in to your MedWeb account</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

            <?php if ($error): ?>
            <div class="mb-6 flex items-center gap-2 px-4 py-3 bg-red-50 text-red-700 text-sm rounded-xl border border-red-100">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="mb-6 flex items-center gap-2 px-4 py-3 bg-green-50 text-green-700 text-sm rounded-xl border border-green-100">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/auth/process_login.php" method="POST" id="loginForm" novalidate>
                
                <!-- Email -->
                <div class="mb-5">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($oldEmail ?? '') ?>"
                           placeholder="you@example.com"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 focus:bg-white transition-all"
                           required>
                    <p class="mt-1 text-xs text-red-500 hidden" id="email-error"></p>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    </div>
                    <div class="relative">
                        <input type="password" id="password" name="password" 
                               placeholder="••••••••"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 focus:bg-white transition-all pr-10"
                               required>
                        <button type="button" onclick="togglePassword('password', this)" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-red-500 hidden" id="password-error"></p>
                </div>

                <!-- Submit -->
                <button type="submit" 
                        class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-medium py-2.5 px-4 rounded-xl text-sm shadow-sm shadow-cyan-500/20 hover:shadow-md hover:shadow-cyan-500/25 focus-ring transition-all active:scale-[0.98]">
                    Sign in
                </button>
            </form>
        </div>

        <!-- Register link -->
        <p class="text-center text-sm text-gray-500 mt-6">
            Don't have an account?
            <a href="<?= BASE_URL ?>/auth/register.php" class="text-cyan-600 hover:text-cyan-700 font-medium transition-colors">Create account</a>
        </p>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<script>
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
