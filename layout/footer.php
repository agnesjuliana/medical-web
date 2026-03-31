<?php
/**
 * Footer Layout
 * 
 * Shared footer section. Include at the bottom of every page.
 */
?>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-gradient-to-br from-cyan-500 to-cyan-700 rounded-lg flex items-center justify-center">
                        <span class="text-white text-xs font-bold">M</span>
                    </div>
                    <span class="text-sm text-gray-500">MedWeb &copy; <?= date('Y') ?></span>
                </div>
                <p class="text-xs text-gray-400">
                    Built with PHP &bull; TailwindCSS &bull; MySQL
                </p>
            </div>
        </div>
    </footer>

    <!-- Component JS -->
    <script src="<?= BASE_URL ?>/assets/js/components.js"></script>
</body>
</html>
