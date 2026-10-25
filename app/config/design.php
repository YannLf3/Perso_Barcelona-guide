<?php

declare(strict_types=1);

/**
 * Catalogue de l'apparence modifiable depuis l'administration.
 * Les valeurs par défaut reproduisent exactement src/css/tokens.css (variables --ui-*).
 * "@cle" signifie "suit la couleur cle" ; "transparent" signifie "même fond que la page".
 * Les libellés affichés à la cliente vivent dans app/lang/admin.php (design.*).
 */
return [
    'groups' => [
        'general' => [
            'page-bg' => '#fdf6ec',
            'text' => '#1a0f00',
            'muted' => '#6b5740',
            'accent' => '#d4780a',
            'accent-hover' => '#b5620a',
            'on-accent' => '#ffffff',
            'band' => '#ffa07a',
        ],
        'nav' => [
            'nav-bg' => '@band',
            'nav-text' => '#ffffff',
            'nav-hover' => '@accent',
            'nav-menu-bg' => '@text',
        ],
        'intro' => [
            'intro-bg' => 'transparent',
            'intro-title' => '@text',
            'intro-accent' => '@accent',
            'intro-text' => '@text',
            'sticker-bg' => '@accent',
            'sticker-text' => '@on-accent',
            'sticker-border' => '@text',
        ],
        'guide' => [
            'guide-bg' => 'transparent',
            'guide-eyebrow' => '#8b6914',
            'guide-line' => '#d4a017',
            'guide-title' => '@text',
            'guide-accent' => '@accent',
            'guide-text' => '#5a4a35',
            'guide-btn-bg' => '@accent',
            'guide-btn-text' => '@on-accent',
        ],
        'tours' => [
            'tours-bg' => 'transparent',
            'tours-title' => '@text',
            'tours-line' => '@accent',
            'filter' => '@text',
            'filter-active-text' => '#ffffff',
            'card-bg' => '#ffffff',
            'card-border' => '#ede0cc',
            'card-title' => '@text',
            'card-text' => '@muted',
            'card-label' => '#9e7c4a',
            'card-value' => '@text',
            'card-btn-bg' => '@text',
            'card-btn-text' => '#ffffff',
            'card-btn-hover' => '@accent',
        ],
        'modal' => [
            'modal-bg' => '#ffffff',
            'modal-title' => '@text',
            'modal-text' => '#5a4a35',
        ],
        'contact' => [
            'contact-bg' => '@band',
            'contact-eyebrow' => '@accent',
            'contact-title' => '#ffffff',
            'contact-accent' => '@accent',
            'contact-text' => '#ffffff',
            'contact-field-text' => '#ffffff',
            'contact-btn-bg' => '@accent',
            'contact-btn-text' => '@on-accent',
        ],
        'footer' => [
            'footer-bg' => 'transparent',
            'footer-text' => '@text',
        ],
        'pages' => [
            'header-bg' => '@band',
            'header-text' => '#ffffff',
            'legal-bg' => '#fff5e6',
            'legal-title' => '@text',
            'legal-heading' => '#8f4500',
            'legal-text' => '#81542b',
            'legal-link' => '#8f4500',
        ],
    ],

    // Paires texte / fond contrôlées (opacité du texte telle qu'appliquée dans les vues, seuil WCAG AA)
    'contrast' => [
        ['fg' => 'text', 'bg' => 'page-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'muted', 'bg' => 'page-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'nav-text', 'bg' => 'nav-bg', 'alpha' => 0.7, 'min' => 4.5],
        ['fg' => 'nav-text', 'bg' => 'nav-menu-bg', 'alpha' => 0.7, 'min' => 4.5],
        ['fg' => 'intro-title', 'bg' => 'intro-bg', 'alpha' => 1, 'min' => 3],
        ['fg' => 'intro-accent', 'bg' => 'intro-bg', 'alpha' => 1, 'min' => 3],
        ['fg' => 'intro-text', 'bg' => 'intro-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'sticker-text', 'bg' => 'sticker-bg', 'alpha' => 1, 'min' => 3],
        ['fg' => 'guide-eyebrow', 'bg' => 'guide-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'guide-title', 'bg' => 'guide-bg', 'alpha' => 1, 'min' => 3],
        ['fg' => 'guide-accent', 'bg' => 'guide-bg', 'alpha' => 1, 'min' => 3],
        ['fg' => 'guide-text', 'bg' => 'guide-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'guide-btn-text', 'bg' => 'guide-btn-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'tours-title', 'bg' => 'tours-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'filter', 'bg' => 'tours-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'filter-active-text', 'bg' => 'filter', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'card-title', 'bg' => 'card-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'card-text', 'bg' => 'card-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'card-label', 'bg' => 'card-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'card-btn-text', 'bg' => 'card-btn-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'card-btn-text', 'bg' => 'card-btn-hover', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'modal-title', 'bg' => 'modal-bg', 'alpha' => 1, 'min' => 3],
        ['fg' => 'modal-text', 'bg' => 'modal-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'contact-eyebrow', 'bg' => 'contact-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'contact-title', 'bg' => 'contact-bg', 'alpha' => 1, 'min' => 3],
        ['fg' => 'contact-accent', 'bg' => 'contact-bg', 'alpha' => 1, 'min' => 3],
        ['fg' => 'contact-text', 'bg' => 'contact-bg', 'alpha' => 0.65, 'min' => 4.5],
        ['fg' => 'contact-btn-text', 'bg' => 'contact-btn-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'footer-text', 'bg' => 'footer-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'header-text', 'bg' => 'header-bg', 'alpha' => 0.9, 'min' => 4.5],
        ['fg' => 'legal-heading', 'bg' => 'legal-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'legal-text', 'bg' => 'legal-bg', 'alpha' => 1, 'min' => 4.5],
        ['fg' => 'legal-link', 'bg' => 'legal-bg', 'alpha' => 1, 'min' => 4.5],
    ],

    // Polices hébergées dans public/assets/fonts (catalogue fermé, aucune police externe)
    'fonts' => [
        'outfit' => ['family' => 'Outfit', 'fallback' => 'sans-serif', 'weight' => '600 800', 'files' => ['Outfit-latin.woff2', 'Outfit-latin-ext.woff2']],
        'inter' => ['family' => 'Inter', 'fallback' => 'sans-serif', 'weight' => '400 600', 'files' => ['Inter-latin.woff2', 'Inter-latin-ext.woff2']],
        'fraunces' => ['family' => 'Fraunces', 'fallback' => 'serif', 'weight' => '100 900', 'files' => ['fraunces-latin.woff2', 'fraunces-latin-ext.woff2']],
        'playfair-display' => ['family' => 'Playfair Display', 'fallback' => 'serif', 'weight' => '400 900', 'files' => ['playfair-display-latin.woff2', 'playfair-display-latin-ext.woff2']],
        'lora' => ['family' => 'Lora', 'fallback' => 'serif', 'weight' => '400 700', 'files' => ['lora-latin.woff2', 'lora-latin-ext.woff2']],
        'bricolage-grotesque' => ['family' => 'Bricolage Grotesque', 'fallback' => 'sans-serif', 'weight' => '200 800', 'files' => ['bricolage-grotesque-latin.woff2', 'bricolage-grotesque-latin-ext.woff2']],
        'nunito-sans' => ['family' => 'Nunito Sans', 'fallback' => 'sans-serif', 'weight' => '200 1000', 'files' => ['nunito-sans-latin.woff2', 'nunito-sans-latin-ext.woff2']],
        'source-sans-3' => ['family' => 'Source Sans 3', 'fallback' => 'sans-serif', 'weight' => '200 900', 'files' => ['source-sans-3-latin.woff2', 'source-sans-3-latin-ext.woff2']],
        'work-sans' => ['family' => 'Work Sans', 'fallback' => 'sans-serif', 'weight' => '100 900', 'files' => ['work-sans-latin.woff2', 'work-sans-latin-ext.woff2']],
        'atkinson-hyperlegible-next' => ['family' => 'Atkinson Hyperlegible Next', 'fallback' => 'sans-serif', 'weight' => '200 800', 'files' => ['atkinson-hyperlegible-next-latin.woff2', 'atkinson-hyperlegible-next-latin-ext.woff2']],
    ],

    'defaultFonts' => ['heading' => 'outfit', 'body' => 'inter'],

    // Taille globale du texte (pourcentage appliqué à la racine du document)
    'textSizes' => ['90', '100', '110', '120'],

    // Formes : "default" garde l'arrondi d'origine propre à chaque élément
    'radius' => [
        'button' => ['default' => null, 'square' => '0', 'soft' => '0.5rem', 'round' => '9999px'],
        'card' => ['default' => null, 'square' => '0', 'soft' => '8px', 'round' => '24px'],
    ],

    // Photos du carrousel de la section guide (5 emplacements, un texte alternatif par emplacement dans app/lang)
    'defaultPhotos' => ['asset23.webp', 'asset24.webp', 'asset25.webp', 'asset26.webp', 'asset27.webp'],

    // Palettes reprises de preview-couleurs.html (A, B, C), chaque clé absente garde la valeur d'origine
    'presets' => [
        'origin' => [],
        'orange' => [
            'page-bg' => '#fff8ee', 'text' => '#24160b', 'muted' => '#6b4a2b', 'accent' => '#b04a00', 'accent-hover' => '#8f3d00', 'on-accent' => '#24160b', 'band' => '#f58a1f',
            'nav-text' => '#24160b', 'nav-hover' => '#24160b', 'nav-menu-bg' => '#f58a1f',
            'sticker-bg' => '#f58a1f', 'sticker-border' => '#24160b',
            'guide-eyebrow' => '#b04a00', 'guide-line' => '#f58a1f', 'guide-text' => '#3d2814', 'guide-btn-bg' => '#f58a1f',
            'filter-active-text' => '#fff8ee', 'card-border' => '#ead9bd', 'card-label' => '#6b4a2b', 'card-btn-bg' => '#24160b', 'card-btn-text' => '#ffffff', 'card-btn-hover' => '#b04a00',
            'modal-text' => '#3d2814',
            'contact-eyebrow' => '#24160b', 'contact-title' => '#24160b', 'contact-accent' => '#24160b', 'contact-text' => '#24160b', 'contact-field-text' => '#24160b', 'contact-btn-bg' => '#24160b', 'contact-btn-text' => '#ffffff',
            'header-text' => '#24160b', 'legal-heading' => '#b04a00', 'legal-link' => '#b04a00',
        ],
        'espresso' => [
            'page-bg' => '#fff8ee', 'text' => '#24160b', 'muted' => '#6b4a2b', 'accent' => '#a64a00', 'accent-hover' => '#a64300', 'on-accent' => '#ffffff', 'band' => '#2f1708',
            'nav-text' => '#fff1de', 'nav-hover' => '#ff9a3d', 'nav-menu-bg' => '#2f1708',
            'sticker-bg' => '#c4510b', 'sticker-border' => '#24160b',
            'guide-eyebrow' => '#a64a00', 'guide-line' => '#c4510b', 'guide-text' => '#3d2814', 'guide-btn-bg' => '#c4510b',
            'filter-active-text' => '#fff8ee', 'card-border' => '#ead9bd', 'card-label' => '#6b4a2b', 'card-btn-bg' => '#24160b', 'card-btn-hover' => '#c4510b',
            'modal-text' => '#3d2814',
            'contact-eyebrow' => '#ff9a3d', 'contact-title' => '#fff1de', 'contact-accent' => '#ff9a3d', 'contact-text' => '#fff1de', 'contact-field-text' => '#fff1de', 'contact-btn-bg' => '#ff9a3d', 'contact-btn-text' => '#1a0d04',
            'header-text' => '#fff1de', 'legal-heading' => '#a64a00', 'legal-link' => '#a64a00',
        ],
        'apricot' => [
            'page-bg' => '#fffaf2', 'text' => '#24160b', 'muted' => '#6b4a2b', 'accent' => '#8f3d00', 'accent-hover' => '#a64300', 'on-accent' => '#ffffff', 'band' => '#ffcf99',
            'nav-text' => '#2b1608', 'nav-hover' => '#8f3d00', 'nav-menu-bg' => '#ffcf99',
            'sticker-bg' => '#c4510b', 'sticker-border' => '#24160b',
            'guide-eyebrow' => '#8f3d00', 'guide-line' => '#c4510b', 'guide-text' => '#3d2814', 'guide-btn-bg' => '#c4510b',
            'filter-active-text' => '#fffaf2', 'card-border' => '#ead9bd', 'card-label' => '#6b4a2b', 'card-btn-bg' => '#24160b', 'card-btn-hover' => '#c4510b',
            'modal-text' => '#3d2814',
            'contact-eyebrow' => '#8f3d00', 'contact-title' => '#2b1608', 'contact-accent' => '#8f3d00', 'contact-text' => '#2b1608', 'contact-field-text' => '#2b1608', 'contact-btn-bg' => '#2b1608', 'contact-btn-text' => '#ffffff',
            'header-text' => '#2b1608', 'legal-heading' => '#8f3d00', 'legal-link' => '#8f3d00',
        ],
    ],
];
