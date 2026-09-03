<?php
/**
 * Inline SVG icons (stroke, currentColor) — no emoji.
 */
function icon(string $name, string $class = 'icon'): string
{
    $icons = [
        'ball' => '<circle cx="12" cy="12" r="9"/><path d="M12 3a12 12 0 0 1 0 18M12 3a12 12 0 0 0 0 18M3 12h18M5.5 6.5l13 11M5.5 17.5l13-11"/>',
        'trophy' => '<path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4z"/><path d="M7 6H4a2 2 0 0 0 2 4h1M17 6h3a2 2 0 0 1-2 4h-1"/>',
        'home' => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/>',
        'chart' => '<path d="M4 19V5M4 19h16"/><path d="M8 16V10M12 16V7M16 16v-5"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 19a6.5 6.5 0 0 1 13 0"/><circle cx="17" cy="9" r="2.5"/><path d="M16 19a5 5 0 0 1 5.5-4.7"/>',
        'user' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 19a7 7 0 0 1 14 0"/>',
        'shield' => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3z"/>',
        'tree' => '<path d="M12 21V11"/><path d="M12 11 7 6h10L12 11z"/><path d="M12 15 5.5 9H18.5L12 15z"/>',
        'news' => '<path d="M4 5h12v14H4z"/><path d="M16 8h4v11a2 2 0 0 1-2 2H6"/><path d="M7 9h6M7 12h6M7 15h4"/>',
        'file' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9l-5-6z"/><path d="M14 3v6h6"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v2.2M12 19.8V22M4.9 4.9l1.6 1.6M17.5 17.5l1.6 1.6M2 12h2.2M19.8 12H22M4.9 19.1l1.6-1.6M17.5 6.5l1.6-1.6"/>',
        'archive' => '<path d="M3 7h18v3H3z"/><path d="M5 10v9h14v-9"/><path d="M10 14h4"/>',
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="M16.5 16.5 21 21"/>',
        'star' => '<path d="M12 3.5 14.5 9l6 .5-4.6 4 1.4 5.8L12 16.5 6.7 19.3 8.1 13.5 3.5 9.5 9.5 9z"/>',
        'play' => '<path d="M8 5.5v13l11-6.5L8 5.5z"/>',
        'arrow-left' => '<path d="M15 5 8 12l7 7M8 12h12"/>',
        'external' => '<path d="M14 4h6v6"/><path d="M10 14 20 4"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'logout' => '<path d="M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4"/><path d="M15 8l4 4-4 4M8 12h11"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'copy' => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M4 16V6a2 2 0 0 1 2-2h10"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'live' => '<circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="8"/>',
        'goal' => '<circle cx="12" cy="12" r="8"/><path d="M12 4v16M4 12h16"/>',
        'refresh' => '<path d="M20 6v5h-5"/><path d="M4 18v-5h5"/><path d="M19 11A7 7 0 0 0 7.5 6.5L4 10M5 13a7 7 0 0 0 11.5 4.5L20 14"/>',
    ];

    $paths = $icons[$name] ?? $icons['ball'];
    $classAttr = e($class);
    return '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}
