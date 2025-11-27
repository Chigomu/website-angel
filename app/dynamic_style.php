<?php
require_once __DIR__ . '/settings_loader.php';

// 1. Definisikan Preset Font (Nama Font & Link Google)
$font_presets = [
    'default' => [
        'name' => 'Default (DM Serif + Outfit)',
        'heading' => "'DM Serif Display', serif",
        'body' => "'Outfit', sans-serif",
        'url' => "https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Outfit:wght@300;400;500;600&display=swap"
    ],
    'elegant' => [
        'name' => 'Elegant (Playfair + Lato)',
        'heading' => "'Playfair Display', serif",
        'body' => "'Lato', sans-serif",
        'url' => "https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:wght@400;700&display=swap"
    ],
    'modern' => [
        'name' => 'Modern (Poppins + Open Sans)',
        'heading' => "'Poppins', sans-serif",
        'body' => "'Open Sans', sans-serif",
        'url' => "https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&family=Poppins:wght@400;600;700&display=swap"
    ],
    'classic' => [
        'name' => 'Classic (Merriweather + Roboto)',
        'heading' => "'Merriweather', serif",
        'body' => "'Roboto', sans-serif",
        'url' => "https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&family=Roboto:wght@300;400;500&display=swap"
    ]
];

// 2. Ambil settingan saat ini
$current_preset = set('style_font_preset', 'default');
$selected_font = $font_presets[$current_preset] ?? $font_presets['default'];

// 3. Render Link Google Font
echo '<link rel="stylesheet" href="' . $selected_font['url'] . '">';

// 4. Render CSS Override (:root)
?>
<style>
    :root {
        /* === DYNAMIC TYPOGRAPHY === */
        --font-heading: <?= $selected_font['heading'] ?>;
        --font-body: <?= $selected_font['body'] ?>;
        
        /* === DYNAMIC SIZE (Base Font Size) === */
        font-size: <?= set('style_base_size', '16') ?>px;

        /* === DYNAMIC COLORS === */
        --bg-cream: <?= set('color_bg_cream', '#FDFBF7') ?>;
        --bg-card: <?= set('color_card', '#FFFFFF') ?>;
        --text-dark: <?= set('color_text_dark', '#2C1810') ?>;
        --accent: <?= set('color_accent', '#D97757') ?>;
        
        /* Hitung warna turunan (opsional, misal accent gelap) */
        /* Kita biarkan CSS bawaan menangani hal lain */
    }
</style>