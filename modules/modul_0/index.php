<?php
/**
 * Modul 0 — Component Usage Examples
 * 
 * Demonstrates how to use the shared component kit
 * from components/components.php in any module.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Modul 0 — Button Examples';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-cyan-600 transition-colors">Module Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Modul 0 — Button Examples</span>
    </nav>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Button Component Examples</h1>
        <p class="text-gray-500 mt-1">How to use <code class="bg-gray-100 px-1.5 py-0.5 rounded text-sm font-mono">component_button()</code> in your modules.</p>
    </div>

    <!-- ===================== -->
    <!-- EXAMPLE 1: Basic      -->
    <!-- ===================== -->
    <?= component_card([
        'title' => '1. Basic Buttons',
        'subtitle' => 'Just pass a label — that\'s it',
        'content' => '
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    ' . component_button('Click Me') . '
                </div>
                <pre class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm font-mono text-gray-700 overflow-x-auto">&lt;?= component_button(\'Click Me\') ?&gt;</pre>
            </div>
        ',
    ]) ?>

    <div class="h-5"></div>

    <!-- ===================== -->
    <!-- EXAMPLE 2: Variants   -->
    <!-- ===================== -->
    <?= component_card([
        'title' => '2. Button Variants',
        'subtitle' => 'primary | secondary | outline | ghost | destructive',
        'content' => '
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    ' . component_button('Primary', ['variant' => 'primary']) . '
                    ' . component_button('Secondary', ['variant' => 'secondary']) . '
                    ' . component_button('Outline', ['variant' => 'outline']) . '
                    ' . component_button('Ghost', ['variant' => 'ghost']) . '
                    ' . component_button('Destructive', ['variant' => 'destructive']) . '
                </div>
                <pre class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm font-mono text-gray-700 overflow-x-auto">' . htmlspecialchars("<?= component_button('Primary', ['variant' => 'primary']) ?>
<?= component_button('Secondary', ['variant' => 'secondary']) ?>
<?= component_button('Outline', ['variant' => 'outline']) ?>
<?= component_button('Ghost', ['variant' => 'ghost']) ?>
<?= component_button('Destructive', ['variant' => 'destructive']) ?>") . '</pre>
            </div>
        ',
    ]) ?>

    <div class="h-5"></div>

    <!-- ===================== -->
    <!-- EXAMPLE 3: Sizes      -->
    <!-- ===================== -->
    <?= component_card([
        'title' => '3. Button Sizes',
        'subtitle' => 'sm | md (default) | lg',
        'content' => '
            <div class="space-y-4">
                <div class="flex flex-wrap items-end gap-3">
                    ' . component_button('Small', ['size' => 'sm']) . '
                    ' . component_button('Medium', ['size' => 'md']) . '
                    ' . component_button('Large', ['size' => 'lg']) . '
                </div>
                <pre class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm font-mono text-gray-700 overflow-x-auto">' . htmlspecialchars("<?= component_button('Small', ['size' => 'sm']) ?>
<?= component_button('Medium', ['size' => 'md']) ?>
<?= component_button('Large', ['size' => 'lg']) ?>") . '</pre>
            </div>
        ',
    ]) ?>

    <div class="h-5"></div>

    <!-- ===================== -->
    <!-- EXAMPLE 4: With Icon  -->
    <!-- ===================== -->
    <?= component_card([
        'title' => '4. Buttons with Icons',
        'subtitle' => 'Pass an SVG string via the icon option',
        'content' => '
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    ' . component_button('Add New', [
                        'variant' => 'primary',
                        'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
                    ]) . '
                    ' . component_button('Download', [
                        'variant' => 'outline',
                        'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>'
                    ]) . '
                    ' . component_button('Delete', [
                        'variant' => 'destructive',
                        'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                    ]) . '
                </div>
                <pre class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm font-mono text-gray-700 overflow-x-auto">' . htmlspecialchars("<?= component_button('Add New', [
    'variant' => 'primary',
    'icon' => '<svg class=\"w-4 h-4\" ...>...</svg>'
]) ?>") . '</pre>
            </div>
        ',
    ]) ?>

    <div class="h-5"></div>

    <!-- ===================== -->
    <!-- EXAMPLE 5: As Link    -->
    <!-- ===================== -->
    <?= component_card([
        'title' => '5. Button as Link',
        'subtitle' => 'Use the href option to render as <a> tag',
        'content' => '
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    ' . component_button('Go to Module Hub', [
                        'variant' => 'primary',
                        'href' => BASE_URL . '/index.php',
                        'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>'
                    ]) . '
                    ' . component_button('External Link', [
                        'variant' => 'outline',
                        'href' => '#'
                    ]) . '
                </div>
                <pre class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm font-mono text-gray-700 overflow-x-auto">' . htmlspecialchars("<?= component_button('Go to Hub', [
    'variant' => 'primary',
    'href' => BASE_URL . '/index.php'
]) ?>") . '</pre>
            </div>
        ',
    ]) ?>

    <div class="h-5"></div>

    <!-- ===================== -->
    <!-- EXAMPLE 6: Full Width -->
    <!-- ===================== -->
    <?= component_card([
        'title' => '6. Full Width & Disabled',
        'subtitle' => 'fullWidth and disabled options',
        'content' => '
            <div class="space-y-4 max-w-md">
                ' . component_button('Full Width Button', ['variant' => 'primary', 'fullWidth' => true]) . '
                <div class="h-1"></div>
                ' . component_button('Disabled Button', ['variant' => 'primary', 'disabled' => true, 'fullWidth' => true]) . '
                <pre class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm font-mono text-gray-700 overflow-x-auto">' . htmlspecialchars("<?= component_button('Submit', [
    'variant' => 'primary',
    'fullWidth' => true
]) ?>

<?= component_button('Disabled', [
    'disabled' => true
]) ?>") . '</pre>
            </div>
        ',
    ]) ?>

    <div class="h-5"></div>

    <!-- ===================== -->
    <!-- EXAMPLE 7: onclick    -->
    <!-- ===================== -->
    <?= component_card([
        'title' => '7. With JavaScript onclick',
        'subtitle' => 'Attach JS handlers directly',
        'content' => '
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-3">
                    ' . component_button('Show Alert', [
                        'variant' => 'outline',
                        'onclick' => "alert('Hello from Modul 0!')"
                    ]) . '
                    ' . component_button('Open Modal', [
                        'variant' => 'primary',
                        'onclick' => "openModal('exampleModal')"
                    ]) . '
                </div>
                <pre class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm font-mono text-gray-700 overflow-x-auto">' . htmlspecialchars("<?= component_button('Show Alert', [
    'onclick' => \"alert('Hello!')\"
]) ?>

<?= component_button('Open Modal', [
    'onclick' => \"openModal('myModal')\"
]) ?>") . '</pre>
            </div>
        ',
    ]) ?>

    <div class="h-5"></div>

    <!-- ===================== -->
    <!-- EXAMPLE 8: Submit     -->
    <!-- ===================== -->
    <?= component_card([
        'title' => '8. Form Submit Button',
        'subtitle' => 'Set type to "submit" for form usage',
        'content' => '
            <div class="space-y-4">
                <form onsubmit="event.preventDefault(); alert(\'Form submitted!\')">
                    <div class="flex items-center gap-3">
                        ' . component_button('Cancel', ['variant' => 'outline']) . '
                        ' . component_button('Save Changes', ['variant' => 'primary', 'type' => 'submit']) . '
                    </div>
                </form>
                <pre class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm font-mono text-gray-700 overflow-x-auto">' . htmlspecialchars("<?= component_button('Cancel', ['variant' => 'outline']) ?>
<?= component_button('Save', [
    'variant' => 'primary',
    'type' => 'submit'
]) ?>") . '</pre>
            </div>
        ',
    ]) ?>

</main>

<!-- Example Modal -->
<?= component_modal('exampleModal', [
    'title' => 'Example Modal',
    'content' => '<p class="text-sm text-gray-600">This modal was opened using <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">component_button</code> with an onclick handler.</p>',
    'footer' => component_button('Close', ['variant' => 'outline', 'onclick' => "closeModal('exampleModal')"])
        . ' ' . component_button('Got it', ['variant' => 'primary', 'onclick' => "closeModal('exampleModal')"]),
]) ?>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
