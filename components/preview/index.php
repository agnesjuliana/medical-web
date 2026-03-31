<?php
/**
 * Dashboard — Index Page
 * 
 * Protected page (requires login).
 * Shows welcome card and demonstrates component kit usage.
 */

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Dashboard';
?>
<?php require_once __DIR__ . '/../../layout/header.php'; ?>
<?php require_once __DIR__ . '/../../layout/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Welcome back, <?= htmlspecialchars($user['name'] ?? 'User') ?> 👋</h1>
        <p class="text-gray-500 mt-1">Here's an overview of your MedWeb dashboard.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <?= component_stat('Total Modules', '12', [
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
            'trend' => '+2 this month',
            'trendDir' => 'up',
        ]) ?>

        <?= component_stat('Active Users', '1,247', [
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
            'trend' => '+18%',
            'trendDir' => 'up',
        ]) ?>

        <?= component_stat('Records', '8,453', [
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
            'trend' => '+342',
            'trendDir' => 'up',
        ]) ?>

        <?= component_stat('System Health', '99.9%', [
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
            'trend' => 'Operational',
            'trendDir' => 'up',
        ]) ?>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <!-- Recent Activity (spans 2 cols) -->
        <div class="lg:col-span-2">
            <?= component_card([
                'title' => 'Recent Activity',
                'subtitle' => 'Latest actions across all modules',
                'padding' => false,
                'content' => component_table(
                    ['User', 'Action', 'Module', 'Date'],
                    [
                        [
                            '<div class="flex items-center gap-2.5">' . component_avatar('Agnes Maria', 'sm') . '<span class="font-medium">Agnes Maria</span></div>',
                            'Updated record',
                            component_badge('Patients', 'primary'),
                            '<span class="text-gray-500">2 min ago</span>'
                        ],
                        [
                            '<div class="flex items-center gap-2.5">' . component_avatar('John Smith', 'sm') . '<span class="font-medium">John Smith</span></div>',
                            'Created new entry',
                            component_badge('Lab Results', 'info'),
                            '<span class="text-gray-500">15 min ago</span>'
                        ],
                        [
                            '<div class="flex items-center gap-2.5">' . component_avatar('Sarah Lee', 'sm') . '<span class="font-medium">Sarah Lee</span></div>',
                            'Approved request',
                            component_badge('Pharmacy', 'success'),
                            '<span class="text-gray-500">1 hour ago</span>'
                        ],
                        [
                            '<div class="flex items-center gap-2.5">' . component_avatar('Mike Chen', 'sm') . '<span class="font-medium">Mike Chen</span></div>',
                            'Deleted record',
                            component_badge('Archive', 'error'),
                            '<span class="text-gray-500">3 hours ago</span>'
                        ],
                    ],
                    ['striped' => true]
                ),
            ]) ?>
        </div>

        <!-- Quick Actions -->
        <div>
            <?= component_card([
                'title' => 'Quick Actions',
                'content' => '<div class="space-y-3">'
                    . component_button('New Module', ['variant' => 'primary', 'fullWidth' => true, 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'])
                    . component_button('View Reports', ['variant' => 'outline', 'fullWidth' => true, 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'])
                    . component_button('Settings', ['variant' => 'ghost', 'fullWidth' => true, 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'])
                    . '</div>',
            ]) ?>

            <!-- Alerts -->
            <div class="mt-5 space-y-3">
                <?= component_alert('System update v2.1 deployed successfully.', 'success', ['dismissible' => true]) ?>
                <?= component_alert('3 pending approvals require your attention.', 'warning', ['dismissible' => true]) ?>
            </div>
        </div>
    </div>

    <!-- Component Showcase (Demo) -->
    <?= component_card([
        'title' => 'Component Kit Preview',
        'subtitle' => 'Available UI components for all modules',
        'content' => '
            <div class="space-y-8">
                <!-- Buttons -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Buttons</h4>
                    <div class="flex flex-wrap items-center gap-3">
                        ' . component_button('Primary', ['variant' => 'primary']) . '
                        ' . component_button('Secondary', ['variant' => 'secondary']) . '
                        ' . component_button('Outline', ['variant' => 'outline']) . '
                        ' . component_button('Ghost', ['variant' => 'ghost']) . '
                        ' . component_button('Destructive', ['variant' => 'destructive']) . '
                        ' . component_button('Small', ['variant' => 'primary', 'size' => 'sm']) . '
                        ' . component_button('Large', ['variant' => 'primary', 'size' => 'lg']) . '
                    </div>
                </div>

                <!-- Badges -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Badges</h4>
                    <div class="flex flex-wrap items-center gap-2">
                        ' . component_badge('Default') . '
                        ' . component_badge('Primary', 'primary') . '
                        ' . component_badge('Success', 'success') . '
                        ' . component_badge('Warning', 'warning') . '
                        ' . component_badge('Error', 'error') . '
                        ' . component_badge('Info', 'info') . '
                    </div>
                </div>

                <!-- Alerts -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Alerts</h4>
                    <div class="space-y-3">
                        ' . component_alert('This is an informational message.', 'info', ['title' => 'Information']) . '
                        ' . component_alert('Operation completed successfully!', 'success') . '
                        ' . component_alert('Please check your input data.', 'warning') . '
                        ' . component_alert('An error occurred while processing.', 'error', ['dismissible' => true]) . '
                    </div>
                </div>

                <!-- Avatars -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Avatars</h4>
                    <div class="flex items-center gap-3">
                        ' . component_avatar('Agnes Maria', 'sm') . '
                        ' . component_avatar('John Smith', 'md') . '
                        ' . component_avatar('Sarah Lee', 'lg') . '
                        ' . component_avatar('Mike Chen', 'xl') . '
                    </div>
                </div>

                <!-- Modal Demo -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Modal</h4>
                    ' . component_button('Open Modal', ['onclick' => "openModal('demoModal')", 'variant' => 'outline']) . '
                </div>

                <!-- Inputs -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Form Inputs</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
                        ' . component_input('demo_name', ['label' => 'Name', 'placeholder' => 'Enter your name', 'required' => true]) . '
                        ' . component_input('demo_email', ['label' => 'Email', 'type' => 'email', 'placeholder' => 'you@example.com']) . '
                        ' . component_input('demo_error', ['label' => 'With Error', 'placeholder' => 'Invalid input', 'error' => 'This field has an error.']) . '
                        ' . component_input('demo_disabled', ['label' => 'Disabled', 'placeholder' => 'Cannot edit', 'disabled' => true, 'hint' => 'This field is disabled']) . '
                    </div>
                </div>
            </div>
        ',
    ]) ?>

</main>

<!-- Demo Modal -->
<?= component_modal('demoModal', [
    'title' => 'Demo Modal',
    'size' => 'md',
    'content' => '<p class="text-sm text-gray-600">This is a reusable modal component. You can use it for confirmations, forms, or any dialog content across your modules.</p>'
        . '<div class="mt-4">' . component_input('modal_input', ['label' => 'Example Field', 'placeholder' => 'Type something...']) . '</div>',
    'footer' => component_button('Cancel', ['variant' => 'outline', 'onclick' => "closeModal('demoModal')"])
        . ' ' . component_button('Confirm', ['variant' => 'primary']),
]) ?>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
