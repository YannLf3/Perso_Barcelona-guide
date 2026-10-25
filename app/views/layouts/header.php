<?php

declare(strict_types=1);

/**
 * Variables attendues : $lang, $page ('home', 'legal', 'notfound', 'admin'), $meta, $stylesheet.
 * Optionnelles : $meta['description'], $meta['canonical'], $meta['alternates'], $meta['jsonLd'], $meta['robots'], $csrf, $preview.
 * Les pages publiques chargent /theme.css (apparence publiée depuis l'administration) ; l'administration ne la charge jamais.
 */
$isAdmin = $page === 'admin';
$isPreview = !empty($preview);
$fontPreloads = $isAdmin ? ['/assets/fonts/Inter-latin.woff2', '/assets/fonts/Outfit-latin.woff2'] : Design::fontPreloads();
$themeVersion = $isAdmin ? 0 : Design::currentId();
$bodyClasses = [
    'home' => 'font-body leading-[1.6] text-ui-text',
    'legal' => 'flex min-h-screen min-w-[320px] flex-col bg-ui-legal-bg bg-[linear-gradient(135deg,rgba(255,255,255,0.72),transparent_55%)] font-body leading-[1.6] text-ui-text',
    'notfound' => 'flex min-h-screen flex-col bg-ui-page-bg font-body leading-[1.6] text-ui-text',
    'admin' => 'admin-body',
];
$ogTitle = $meta['title'];
$ogDescription = $meta['description'] ?? '';
$ogImage = Seo::absolute('/og-image.jpg');
?>
<!doctype html>
<html lang="<?= e($lang) ?>"<?= $isPreview ? ' data-preview="true"' : '' ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($meta['title']) ?></title>
<?php if ($ogDescription !== ''): ?>
    <meta name="description" content="<?= e($ogDescription) ?>">
<?php endif; ?>
<?php if (!empty($meta['robots'])): ?>
    <meta name="robots" content="<?= e($meta['robots']) ?>">
<?php endif; ?>
<?php if (!empty($meta['canonical'])): ?>
    <link rel="canonical" href="<?= e($meta['canonical']) ?>">
    <?= implode("\n    ", array_map(
        fn (array $alternate): string => '<link rel="alternate" hreflang="' . e($alternate['hreflang']) . '" href="' . e($alternate['href']) . '">',
        $meta['alternates'] ?? []
    )) ?>

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e(Seo::SITE_NAME) ?>">
    <meta property="og:title" content="<?= e($ogTitle) ?>">
    <meta property="og:description" content="<?= e($ogDescription) ?>">
    <meta property="og:url" content="<?= e($meta['canonical']) ?>">
    <meta property="og:locale" content="<?= e(Lang::ogLocale($lang)) ?>">
    <?= implode("\n    ", array_map(
        fn (string $code): string => '<meta property="og:locale:alternate" content="' . e(Lang::ogLocale($code)) . '">',
        array_values(array_filter(Lang::SUPPORTED, fn (string $code): bool => $code !== $lang))
    )) ?>

    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= e(t('meta.og_image_alt')) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($ogTitle) ?>">
    <meta name="twitter:description" content="<?= e($ogDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
<?php endif; ?>
<?php if (!empty($csrf)): ?>
    <meta name="csrf-token" content="<?= e($csrf) ?>">
<?php endif; ?>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/assets/icons/favicon-32.png" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
    <?= implode("\n    ", array_map(
        fn (string $font): string => '<link rel="preload" href="' . e($font) . '" as="font" type="font/woff2" crossorigin>',
        $fontPreloads
    )) ?>

    <link rel="stylesheet" href="<?= e(asset($stylesheet)) ?>">
<?php if ($themeVersion > 0): ?>
    <link rel="stylesheet" href="/theme.css?v=<?= e((string) $themeVersion) ?>">
<?php endif; ?>
<?php if ($isAdmin): ?>
    <script nonce="<?= e(Response::nonce()) ?>">
        try {
            var theme = localStorage.getItem('admin-theme');
            document.documentElement.dataset.theme = theme === 'dark' ? 'dark' : 'light';
        } catch (error) {
            document.documentElement.dataset.theme = 'light';
        }
    </script>
<?php endif; ?>
<?= implode("\n", array_map(
    fn (string $json): string => '    <script type="application/ld+json">' . $json . '</script>',
    $meta['jsonLd'] ?? []
)) ?>

</head>
<body class="<?= e($bodyClasses[$page]) ?>">
<?php if (!$isAdmin): ?>
    <a href="#main" class="sr-only z-200 rounded-lg bg-site-ink px-4 py-2 font-heading text-[0.85rem] font-bold text-white focus:not-sr-only focus:fixed focus:top-2 focus:left-2"><?= e(t('a11y.skip')) ?></a>
<?php endif; ?>
