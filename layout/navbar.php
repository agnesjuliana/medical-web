<?php
/**
 * Navbar Layout
 * 
 * Top navigation bar with responsive mobile menu.
 * Requires core/auth.php to be included before this file.
 */

$currentUser = getCurrentUser();
$initials = getUserInitials();
?>

<!-- Navbar -->
<nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 backdrop-blur-sm bg-white/95 dark:bg-gray-900/95 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-cyan-500 to-cyan-700 rounded-xl flex items-center justify-center shadow-sm group-hover:shadow-md group-hover:scale-105 transition-all duration-200">
                        <span class="text-white text-sm font-bold">M</span>
                    </div>
                    <span class="text-lg font-semibold text-gray-800 dark:text-white hidden sm:block">MedWeb</span>
                </a>
            </div>

            <!-- Desktop Nav Links -->
            <div class="hidden md:flex items-center gap-1">
                <!-- <a href="<?= BASE_URL ?>/index.php" class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-cyan-600 hover:bg-cyan-50 rounded-lg transition-colors">
                    Dashboard
                </a> -->
                <!-- Add module links here -->
            </div>

            <!-- User Menu (Desktop) -->
            <div class="hidden md:flex items-center gap-3">
                <div class="flex items-center gap-3 pl-3 border-l border-gray-200 dark:border-gray-700">
                    
                    <!-- User Dropdown -->
                    <div class="relative" id="userDropdown">
                        <button onclick="toggleDropdown('userDropdownMenu')" 
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 focus-ring transition-colors">
                            <div class="w-8 h-8 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-full flex items-center justify-center shadow-sm">
                                <span class="text-white text-xs font-semibold"><?= htmlspecialchars($initials) ?></span>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200 leading-none"><?= htmlspecialchars($currentUser['name'] ?? '') ?></p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="userDropdownMenu" 
                             class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1.5 z-50 animate-in">
                            <div class="px-4 py-2.5 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200"><?= htmlspecialchars($currentUser['name'] ?? '') ?></p>
                                <p class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                            </div>
                            <a href="<?= BASE_URL ?>/auth/logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign out
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button onclick="toggleMobileMenu()" 
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 focus-ring transition-colors">
                    <svg id="menuIconOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="menuIconClose" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu panel -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-gray-100 dark:border-gray-700">
        <div class="px-4 py-3 space-y-1">
            <a href="<?= BASE_URL ?>/index.php" class="block px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-900/20 rounded-lg transition-colors">
                Dashboard
            </a>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-700 px-4 py-3">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-semibold"><?= htmlspecialchars($initials) ?></span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200"><?= htmlspecialchars($currentUser['name'] ?? '') ?></p>
                    <p class="text-xs text-gray-400 dark:text-gray-500"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="flex items-center gap-2 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Sign out
            </a>
        </div>
    </div>
</nav>
