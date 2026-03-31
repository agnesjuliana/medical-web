<?php
/**
 * Component Kit — shadcn-inspired Reusable UI Components
 * 
 * Include this file in any page to use the component functions.
 * All components use Tailwind CSS classes with the project color scheme.
 * 
 * Usage: require_once __DIR__ . '/../components/components.php';
 */

// ─────────────────────────────────────────────
// BUTTON
// ─────────────────────────────────────────────

/**
 * Render a button component
 * 
 * @param string $label   Button text
 * @param array  $options {
 *   @type string $variant  'primary'|'secondary'|'outline'|'ghost'|'destructive' (default: 'primary')
 *   @type string $size     'sm'|'md'|'lg' (default: 'md')
 *   @type string $type     'button'|'submit'|'reset' (default: 'button')
 *   @type string $href     If set, renders as <a> instead of <button>
 *   @type string $id       Element ID
 *   @type string $class    Additional CSS classes
 *   @type string $icon     SVG icon HTML (prepended to label)
 *   @type string $onclick  JS onclick handler
 *   @type bool   $disabled Disabled state
 *   @type bool   $fullWidth Full width button
 * }
 * @return string HTML
 */
function component_button(string $label, array $options = []): string
{
    $variant   = $options['variant'] ?? 'primary';
    $size      = $options['size'] ?? 'md';
    $type      = $options['type'] ?? 'button';
    $href      = $options['href'] ?? null;
    $id        = $options['id'] ?? '';
    $class     = $options['class'] ?? '';
    $icon      = $options['icon'] ?? '';
    $onclick   = $options['onclick'] ?? '';
    $disabled  = $options['disabled'] ?? false;
    $fullWidth = $options['fullWidth'] ?? false;

    // Base classes
    $base = 'inline-flex items-center justify-center gap-2 font-medium rounded-xl transition-all duration-200 focus-ring active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed';

    // Size variants
    $sizes = [
        'sm' => 'text-xs px-3 py-1.5',
        'md' => 'text-sm px-4 py-2.5',
        'lg' => 'text-base px-6 py-3',
    ];

    // Style variants
    $variants = [
        'primary'     => 'bg-cyan-600 hover:bg-cyan-700 text-white shadow-sm shadow-cyan-500/20 hover:shadow-md hover:shadow-cyan-500/25',
        'secondary'   => 'bg-gray-100 hover:bg-gray-200 text-gray-700',
        'outline'     => 'border border-gray-200 bg-white hover:bg-gray-50 text-gray-700',
        'ghost'       => 'hover:bg-gray-100 text-gray-600',
        'destructive' => 'bg-red-500 hover:bg-red-600 text-white shadow-sm shadow-red-500/20',
    ];

    $classes = implode(' ', [
        $base,
        $sizes[$size] ?? $sizes['md'],
        $variants[$variant] ?? $variants['primary'],
        $fullWidth ? 'w-full' : '',
        $class,
    ]);

    $attrs = '';
    if ($id) $attrs .= " id=\"$id\"";
    if ($onclick) $attrs .= " onclick=\"$onclick\"";
    if ($disabled) $attrs .= " disabled";

    $content = ($icon ? "$icon " : '') . htmlspecialchars($label);

    if ($href) {
        return "<a href=\"$href\" class=\"$classes\"$attrs>$content</a>";
    }
    return "<button type=\"$type\" class=\"$classes\"$attrs>$content</button>";
}


// ─────────────────────────────────────────────
// CARD
// ─────────────────────────────────────────────

/**
 * Render a card component
 * 
 * @param array $options {
 *   @type string $title       Card title
 *   @type string $subtitle    Card subtitle
 *   @type string $content     Card body (HTML)
 *   @type string $footer      Card footer (HTML)
 *   @type string $headerRight HTML for right side of header (e.g. badge, button)
 *   @type string $id          Element ID
 *   @type string $class       Additional CSS classes
 *   @type bool   $padding     Whether to apply padding to content (default: true)
 *   @type bool   $hover       Enable hover lift effect
 * }
 * @return string HTML
 */
function component_card(array $options = []): string
{
    $title       = $options['title'] ?? '';
    $subtitle    = $options['subtitle'] ?? '';
    $content     = $options['content'] ?? '';
    $footer      = $options['footer'] ?? '';
    $headerRight = $options['headerRight'] ?? '';
    $id          = $options['id'] ?? '';
    $class       = $options['class'] ?? '';
    $padding     = $options['padding'] ?? true;
    $hover       = $options['hover'] ?? false;

    $hoverClass = $hover ? 'hover:shadow-md hover:-translate-y-0.5' : '';
    $idAttr     = $id ? " id=\"$id\"" : '';

    $html = "<div{$idAttr} class=\"bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden {$hoverClass} {$class}\">";

    // Header
    if ($title || $headerRight) {
        $html .= '<div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">';
        $html .= '<div>';
        if ($title) {
            $html .= '<h3 class="text-base font-semibold text-gray-800">' . htmlspecialchars($title) . '</h3>';
        }
        if ($subtitle) {
            $html .= '<p class="text-sm text-gray-500 mt-0.5">' . htmlspecialchars($subtitle) . '</p>';
        }
        $html .= '</div>';
        if ($headerRight) {
            $html .= "<div>$headerRight</div>";
        }
        $html .= '</div>';
    }

    // Content
    if ($content) {
        $padClass = $padding ? 'px-6 py-5' : '';
        $html .= "<div class=\"{$padClass}\">{$content}</div>";
    }

    // Footer
    if ($footer) {
        $html .= "<div class=\"px-6 py-4 border-t border-gray-100 bg-gray-50/50\">{$footer}</div>";
    }

    $html .= '</div>';
    return $html;
}


// ─────────────────────────────────────────────
// TABLE
// ─────────────────────────────────────────────

/**
 * Render a data table component
 * 
 * @param array  $headers Array of column header labels
 * @param array  $rows    Array of row arrays (each row is an array of cell HTML strings)
 * @param array  $options {
 *   @type string $id       Element ID
 *   @type string $class    Additional CSS classes
 *   @type bool   $striped  Enable striped rows (default: true)
 *   @type string $empty    Message when no rows
 * }
 * @return string HTML
 */
function component_table(array $headers, array $rows, array $options = []): string
{
    $id      = $options['id'] ?? '';
    $class   = $options['class'] ?? '';
    $striped = $options['striped'] ?? true;
    $empty   = $options['empty'] ?? 'No data available.';

    $idAttr = $id ? " id=\"$id\"" : '';

    $html = "<div{$idAttr} class=\"bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden {$class}\">";
    $html .= '<div class="overflow-x-auto">';
    $html .= '<table class="w-full text-sm">';

    // Headers
    $html .= '<thead><tr class="border-b border-gray-100">';
    foreach ($headers as $header) {
        $html .= '<th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">' . htmlspecialchars($header) . '</th>';
    }
    $html .= '</tr></thead>';

    // Body
    $html .= '<tbody class="divide-y divide-gray-50">';
    if (empty($rows)) {
        $colspan = count($headers);
        $html .= "<tr><td colspan=\"{$colspan}\" class=\"px-6 py-12 text-center text-gray-400\">";
        $html .= '<svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>';
        $html .= htmlspecialchars($empty) . '</td></tr>';
    } else {
        foreach ($rows as $i => $row) {
            $bg = ($striped && $i % 2 === 1) ? 'bg-gray-50/50' : '';
            $html .= "<tr class=\"hover:bg-cyan-50/30 transition-colors {$bg}\">";
            foreach ($row as $cell) {
                $html .= "<td class=\"px-6 py-4 text-gray-700\">{$cell}</td>";
            }
            $html .= '</tr>';
        }
    }
    $html .= '</tbody></table></div></div>';

    return $html;
}


// ─────────────────────────────────────────────
// INPUT
// ─────────────────────────────────────────────

/**
 * Render a form input component
 * 
 * @param string $name    Input name attribute
 * @param array  $options {
 *   @type string $label       Label text
 *   @type string $type        Input type (default: 'text')
 *   @type string $placeholder Placeholder text
 *   @type string $value       Default value
 *   @type string $error       Error message
 *   @type string $hint        Hint text below input
 *   @type string $id          Element ID (defaults to name)
 *   @type string $class       Additional CSS classes
 *   @type bool   $required    Required attribute
 *   @type bool   $disabled    Disabled state
 * }
 * @return string HTML
 */
function component_input(string $name, array $options = []): string
{
    $label       = $options['label'] ?? '';
    $type        = $options['type'] ?? 'text';
    $placeholder = $options['placeholder'] ?? '';
    $value       = $options['value'] ?? '';
    $error       = $options['error'] ?? '';
    $hint        = $options['hint'] ?? '';
    $id          = $options['id'] ?? $name;
    $class       = $options['class'] ?? '';
    $required    = $options['required'] ?? false;
    $disabled    = $options['disabled'] ?? false;

    $errorBorder = $error ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-500' : 'border-gray-200 focus:ring-cyan-500/20 focus:border-cyan-500';

    $html = '<div class="' . $class . '">';

    if ($label) {
        $html .= '<label for="' . $id . '" class="block text-sm font-medium text-gray-700 mb-1.5">';
        $html .= htmlspecialchars($label);
        if ($required) {
            $html .= ' <span class="text-red-400">*</span>';
        }
        $html .= '</label>';
    }

    if ($type === 'textarea') {
        $html .= '<textarea id="' . $id . '" name="' . $name . '"';
        $html .= ' placeholder="' . htmlspecialchars($placeholder) . '"';
        $html .= ' class="w-full px-4 py-2.5 bg-gray-50 border ' . $errorBorder . ' rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:bg-white transition-all resize-none"';
        $html .= ' rows="4"';
        if ($required) $html .= ' required';
        if ($disabled) $html .= ' disabled';
        $html .= '>' . htmlspecialchars($value) . '</textarea>';
    } else {
        $html .= '<input type="' . $type . '" id="' . $id . '" name="' . $name . '"';
        $html .= ' value="' . htmlspecialchars($value) . '"';
        $html .= ' placeholder="' . htmlspecialchars($placeholder) . '"';
        $html .= ' class="w-full px-4 py-2.5 bg-gray-50 border ' . $errorBorder . ' rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:bg-white transition-all"';
        if ($required) $html .= ' required';
        if ($disabled) $html .= ' disabled';
        $html .= '>';
    }

    if ($error) {
        $html .= '<p class="mt-1 text-xs text-red-500">' . htmlspecialchars($error) . '</p>';
    } elseif ($hint) {
        $html .= '<p class="mt-1 text-xs text-gray-400">' . htmlspecialchars($hint) . '</p>';
    }

    $html .= '</div>';
    return $html;
}


// ─────────────────────────────────────────────
// BADGE
// ─────────────────────────────────────────────

/**
 * Render a badge component
 * 
 * @param string $text    Badge text
 * @param string $variant 'default'|'primary'|'success'|'warning'|'error'|'info'
 * @param array  $options { @type string $class Additional CSS classes, @type string $icon SVG icon }
 * @return string HTML
 */
function component_badge(string $text, string $variant = 'default', array $options = []): string
{
    $class = $options['class'] ?? '';
    $icon  = $options['icon'] ?? '';

    $variants = [
        'default' => 'bg-gray-100 text-gray-600',
        'primary' => 'bg-cyan-50 text-cyan-700 border border-cyan-100',
        'success' => 'bg-green-50 text-green-700 border border-green-100',
        'warning' => 'bg-amber-50 text-amber-700 border border-amber-100',
        'error'   => 'bg-red-50 text-red-700 border border-red-100',
        'info'    => 'bg-blue-50 text-blue-700 border border-blue-100',
    ];

    $variantClass = $variants[$variant] ?? $variants['default'];

    return '<span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full ' . $variantClass . ' ' . $class . '">'
        . ($icon ? "$icon " : '')
        . htmlspecialchars($text) . '</span>';
}


// ─────────────────────────────────────────────
// ALERT
// ─────────────────────────────────────────────

/**
 * Render an alert component
 * 
 * @param string $message   Alert message
 * @param string $type      'info'|'success'|'warning'|'error'
 * @param array  $options   { @type string $title, @type bool $dismissible, @type string $id }
 * @return string HTML
 */
function component_alert(string $message, string $type = 'info', array $options = []): string
{
    $title       = $options['title'] ?? '';
    $dismissible = $options['dismissible'] ?? false;
    $id          = $options['id'] ?? 'alert-' . uniqid();

    $styles = [
        'info'    => ['bg' => 'bg-blue-50 border-blue-200 text-blue-800', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        'success' => ['bg' => 'bg-green-50 border-green-200 text-green-800', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        'warning' => ['bg' => 'bg-amber-50 border-amber-200 text-amber-800', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'],
        'error'   => ['bg' => 'bg-red-50 border-red-200 text-red-800', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ];

    $style = $styles[$type] ?? $styles['info'];

    $html = "<div id=\"{$id}\" class=\"flex items-start gap-3 px-4 py-3.5 rounded-xl border {$style['bg']}\">";
    $html .= '<svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . $style['icon'] . '</svg>';
    $html .= '<div class="flex-1 min-w-0">';
    if ($title) {
        $html .= '<p class="font-semibold text-sm">' . htmlspecialchars($title) . '</p>';
    }
    $html .= '<p class="text-sm' . ($title ? ' mt-0.5 opacity-90' : '') . '">' . htmlspecialchars($message) . '</p>';
    $html .= '</div>';

    if ($dismissible) {
        $html .= '<button onclick="document.getElementById(\'' . $id . '\').remove()" class="shrink-0 p-1 rounded-lg hover:bg-black/5 transition-colors">';
        $html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        $html .= '</button>';
    }

    $html .= '</div>';
    return $html;
}


// ─────────────────────────────────────────────
// SIDEBAR
// ─────────────────────────────────────────────

/**
 * Render a sidebar navigation component
 * 
 * @param array  $items  Array of items: [ ['label' => '...', 'href' => '...', 'icon' => '...SVG...'], ... ]
 * @param string $active Label of the active item
 * @param array  $options { @type string $title Sidebar heading, @type string $id }
 * @return string HTML
 */
function component_sidebar(array $items, string $active = '', array $options = []): string
{
    $title = $options['title'] ?? '';
    $id    = $options['id'] ?? 'sidebar';

    $html = "<aside id=\"{$id}\" class=\"w-64 bg-white border-r border-gray-200 min-h-[calc(100vh-4rem)] flex-shrink-0\">";
    $html .= '<div class="p-4">';

    if ($title) {
        $html .= '<h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-3">' . htmlspecialchars($title) . '</h2>';
    }

    $html .= '<nav class="space-y-1">';
    foreach ($items as $item) {
        $isActive = ($item['label'] === $active);
        $icon     = $item['icon'] ?? '';
        $href     = $item['href'] ?? '#';

        $activeClass = $isActive
            ? 'bg-cyan-50 text-cyan-700 border-l-2 border-cyan-600'
            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800 border-l-2 border-transparent';

        $html .= '<a href="' . htmlspecialchars($href) . '" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-r-lg transition-colors ' . $activeClass . '">';
        if ($icon) $html .= '<span class="w-5 h-5 shrink-0">' . $icon . '</span>';
        $html .= htmlspecialchars($item['label']);
        
        // Badge support
        if (isset($item['badge'])) {
            $html .= ' <span class="ml-auto text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">' . htmlspecialchars($item['badge']) . '</span>';
        }
        
        $html .= '</a>';
    }
    $html .= '</nav></div></aside>';

    return $html;
}


// ─────────────────────────────────────────────
// MODAL
// ─────────────────────────────────────────────

/**
 * Render a modal dialog component
 * 
 * @param string $id       Unique modal ID
 * @param array  $options  {
 *   @type string $title   Modal title
 *   @type string $content Modal body (HTML)
 *   @type string $footer  Modal footer (HTML, e.g. action buttons)
 *   @type string $size    'sm'|'md'|'lg'|'xl' (default: 'md')
 * }
 * @return string HTML
 */
function component_modal(string $id, array $options = []): string
{
    $title   = $options['title'] ?? '';
    $content = $options['content'] ?? '';
    $footer  = $options['footer'] ?? '';
    $size    = $options['size'] ?? 'md';

    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];

    $maxW = $sizes[$size] ?? $sizes['md'];

    $html = "<div id=\"{$id}\" class=\"fixed inset-0 z-[100] hidden\">";

    // Backdrop
    $html .= "<div class=\"fixed inset-0 bg-black/40 backdrop-blur-sm\" onclick=\"closeModal('{$id}')\"></div>";

    // Dialog
    $html .= "<div class=\"fixed inset-0 flex items-center justify-center p-4\">";
    $html .= "<div class=\"bg-white rounded-2xl shadow-xl border border-gray-200 w-full {$maxW} max-h-[85vh] flex flex-col transform scale-95 opacity-0 transition-all duration-200\" id=\"{$id}-panel\">";

    // Header
    if ($title) {
        $html .= '<div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">';
        $html .= '<h3 class="text-lg font-semibold text-gray-800">' . htmlspecialchars($title) . '</h3>';
        $html .= '<button onclick="closeModal(\'' . $id . '\')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">';
        $html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        $html .= '</button></div>';
    }

    // Content
    $html .= "<div class=\"px-6 py-5 overflow-y-auto flex-1\">{$content}</div>";

    // Footer
    if ($footer) {
        $html .= "<div class=\"px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end gap-3\">{$footer}</div>";
    }

    $html .= '</div></div></div>';

    return $html;
}


// ─────────────────────────────────────────────
// DROPDOWN
// ─────────────────────────────────────────────

/**
 * Render a dropdown menu component
 * 
 * @param string $triggerHtml  HTML for the trigger element
 * @param array  $items        Array of items: [ ['label' => '...', 'href' => '#', 'icon' => '...'], ... ]
 *                             Use ['divider' => true] for a separator
 * @param array  $options      { @type string $id, @type string $align 'left'|'right' }
 * @return string HTML
 */
function component_dropdown(string $triggerHtml, array $items, array $options = []): string
{
    $id    = $options['id'] ?? 'dropdown-' . uniqid();
    $align = $options['align'] ?? 'right';
    $menuId = $id . '-menu';

    $alignClass = $align === 'left' ? 'left-0' : 'right-0';

    $html = "<div class=\"relative inline-block\" id=\"{$id}\">";
    $html .= "<div onclick=\"toggleDropdown('{$menuId}')\" class=\"cursor-pointer\">{$triggerHtml}</div>";

    $html .= "<div id=\"{$menuId}\" class=\"hidden absolute {$alignClass} mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1.5 z-50\">";
    foreach ($items as $item) {
        if (isset($item['divider']) && $item['divider']) {
            $html .= '<div class="my-1.5 border-t border-gray-100"></div>';
            continue;
        }
        $icon  = $item['icon'] ?? '';
        $href  = $item['href'] ?? '#';
        $label = $item['label'] ?? '';
        $itemClass = $item['class'] ?? 'text-gray-700 hover:bg-gray-50';

        $html .= '<a href="' . htmlspecialchars($href) . '" class="flex items-center gap-2.5 px-4 py-2 text-sm transition-colors ' . $itemClass . '">';
        if ($icon) $html .= "<span class=\"w-4 h-4 shrink-0\">{$icon}</span>";
        $html .= htmlspecialchars($label) . '</a>';
    }
    $html .= '</div></div>';

    return $html;
}


// ─────────────────────────────────────────────
// AVATAR
// ─────────────────────────────────────────────

/**
 * Render an avatar component (initials-based)
 * 
 * @param string $name    Full name to extract initials from
 * @param string $size    'sm'|'md'|'lg'|'xl'
 * @param array  $options { @type string $class, @type string $color gradient from/to }
 * @return string HTML
 */
function component_avatar(string $name, string $size = 'md', array $options = []): string
{
    $class = $options['class'] ?? '';

    // Extract initials
    $parts = explode(' ', trim($name));
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr(end($parts), 0, 1));
    }

    $sizes = [
        'sm' => 'w-7 h-7 text-xs',
        'md' => 'w-9 h-9 text-sm',
        'lg' => 'w-12 h-12 text-base',
        'xl' => 'w-16 h-16 text-xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];

    return '<div class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-full flex items-center justify-center shadow-sm ' . $sizeClass . ' ' . $class . '">'
        . '<span class="text-white font-semibold">' . htmlspecialchars($initials) . '</span>'
        . '</div>';
}


// ─────────────────────────────────────────────
// STAT CARD
// ─────────────────────────────────────────────

/**
 * Render a stat/metric card
 * 
 * @param string $label  Stat label
 * @param string $value  Stat value
 * @param array  $options { @type string $icon SVG, @type string $trend '+12%', @type string $trendDir 'up'|'down' }
 * @return string HTML
 */
function component_stat(string $label, string $value, array $options = []): string
{
    $icon     = $options['icon'] ?? '';
    $trend    = $options['trend'] ?? '';
    $trendDir = $options['trendDir'] ?? 'up';

    $trendColor = $trendDir === 'up' ? 'text-green-600' : 'text-red-500';

    $html = '<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 hover:shadow-md hover:-translate-y-0.5 transition-all">';
    $html .= '<div class="flex items-center justify-between mb-3">';
    $html .= '<p class="text-sm font-medium text-gray-500">' . htmlspecialchars($label) . '</p>';
    if ($icon) {
        $html .= '<div class="w-10 h-10 bg-cyan-50 rounded-xl flex items-center justify-center text-cyan-600">' . $icon . '</div>';
    }
    $html .= '</div>';
    $html .= '<p class="text-2xl font-bold text-gray-800">' . htmlspecialchars($value) . '</p>';
    if ($trend) {
        $html .= '<p class="text-xs mt-1 ' . $trendColor . ' font-medium">' . htmlspecialchars($trend) . '</p>';
    }
    $html .= '</div>';

    return $html;
}


// ─────────────────────────────────────────────
// EMPTY STATE
// ─────────────────────────────────────────────

/**
 * Render an empty state placeholder
 * 
 * @param string $title   Title text
 * @param string $message Description text
 * @param string $action  Action button HTML (optional)
 * @return string HTML
 */
function component_empty_state(string $title, string $message, string $action = ''): string
{
    $html = '<div class="flex flex-col items-center justify-center py-16 px-4 text-center">';
    $html .= '<div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">';
    $html .= '<svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>';
    $html .= '</div>';
    $html .= '<h3 class="text-base font-semibold text-gray-800 mb-1">' . htmlspecialchars($title) . '</h3>';
    $html .= '<p class="text-sm text-gray-500 max-w-sm">' . htmlspecialchars($message) . '</p>';
    if ($action) {
        $html .= '<div class="mt-5">' . $action . '</div>';
    }
    $html .= '</div>';

    return $html;
}
