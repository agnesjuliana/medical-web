<?php
/**
 * Register Page
 */

require_once __DIR__ . '/../core/auth.php';
requireGuest();
startSession();

$pageTitle = 'Create Account';
$error  = getFlash('error');
$errors = $_SESSION['validation_errors'] ?? [];
$old    = $_SESSION['old_input'] ?? [];
unset($_SESSION['validation_errors'], $_SESSION['old_input']);
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-cyan-500 to-cyan-700 rounded-2xl shadow-lg shadow-cyan-500/20 mb-4">
                <span class="text-white text-xl font-bold">M</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Create your account</h1>
            <p class="text-gray-500 text-sm mt-1">Get started with MedWeb</p>
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

            <form action="<?= BASE_URL ?>/auth/process_register.php" method="POST" id="registerForm" novalidate>
                
                <!-- Name -->
                <div class="mb-5">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                           placeholder="John Doe"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 focus:bg-white transition-all <?= isset($errors['name']) ? 'border-red-300 bg-red-50/50' : '' ?>"
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <p class="mt-1 text-xs text-red-500"><?= htmlspecialchars($errors['name']) ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-red-500 hidden" id="name-error"></p>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="mb-5">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                           placeholder="you@example.com"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 focus:bg-white transition-all <?= isset($errors['email']) ? 'border-red-300 bg-red-50/50' : '' ?>"
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <p class="mt-1 text-xs text-red-500"><?= htmlspecialchars($errors['email']) ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-red-500 hidden" id="email-error"></p>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="mb-5">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" 
                               placeholder="••••••••"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 focus:bg-white transition-all pr-10 <?= isset($errors['password']) ? 'border-red-300 bg-red-50/50' : '' ?>"
                               required>
                        <button type="button" onclick="togglePassword('password', this)" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <p class="mt-1 text-xs text-red-500"><?= htmlspecialchars($errors['password']) ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-red-500 hidden" id="password-error"></p>
                    <?php endif; ?>
                    <p class="mt-1 text-xs text-gray-400">Minimum 6 characters</p>
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                    <div class="relative">
                        <input type="password" id="password_confirm" name="password_confirm" 
                               placeholder="••••••••"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 focus:bg-white transition-all pr-10 <?= isset($errors['password_confirm']) ? 'border-red-300 bg-red-50/50' : '' ?>"
                               required>
                        <button type="button" onclick="togglePassword('password_confirm', this)" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
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
                        class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-medium py-2.5 px-4 rounded-xl text-sm shadow-sm shadow-cyan-500/20 hover:shadow-md hover:shadow-cyan-500/25 focus-ring transition-all active:scale-[0.98]">
                    Create account
                </button>
            </form>
        </div>

        <!-- Login link -->
        <p class="text-center text-sm text-gray-500 mt-6">
            Already have an account?
            <a href="<?= BASE_URL ?>/auth/login.php" class="text-cyan-600 hover:text-cyan-700 font-medium transition-colors">Sign in</a>
        </p>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        let valid = true;
        valid = validateRequired('name', 'Full name') && valid;
        valid = validateRequired('email', 'Email') && valid;
        valid = validateEmail('email') && valid;
        valid = validateMinLength('password', 6, 'Password') && valid;
        valid = validateMatch('password', 'password_confirm', 'Password confirmation') && valid;
        if (!valid) e.preventDefault();
    });
</script>
</body>
</html>
