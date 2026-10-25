<?php

declare(strict_types=1);

/**
 * Variables attendues : $lang, $page. Ferme le document ouvert par layouts/header.php.
 */
$footerClass = $page === 'legal'
    ? 'mx-auto flex w-[min(100%-2rem,72rem)] max-w-[72rem] items-center justify-between gap-4 border-t border-line px-4 pt-5 pb-8 text-[0.8rem] text-muted mobile:flex-col mobile:items-start mobile:px-3'
    : 'mx-auto flex items-center justify-between gap-4 px-4 pt-5 pb-8 text-[0.8rem] mobile:flex-col mobile:items-start mobile:px-3';
$linkClass = $page === 'legal'
    ? 'font-heading font-bold tracking-[0.04em] text-accent-700 underline decoration-[rgba(143,69,0,0.35)] underline-offset-[0.25em] transition-[color,text-decoration-color] duration-160 ease-[ease] hover:text-accent-600 hover:decoration-current focus-visible:text-accent-600 focus-visible:decoration-current'
    : 'font-body font-bold tracking-[0.04em] underline decoration-[rgba(143,69,0,0.35)] underline-offset-[0.25em] hover:decoration-current focus-visible:decoration-current';
?>
<?php if ($page === 'home' || $page === 'legal'): ?>
    <footer class="<?= e($footerClass) ?>">
        <p><?= e(t('footer.copyright')) ?></p>
        <a class="<?= e($linkClass) ?>" href="/<?= e($lang) ?>/legal"<?= $page === 'legal' ? ' aria-current="page"' : '' ?>><?= e(t('footer.legal_link')) ?></a>
    </footer>
<?php endif; ?>
<?php if ($page === 'home'): ?>
    <script src="<?= e(asset('/js/front/m.js')) ?>" defer></script>
    <script src="<?= e(asset('/js/front/v.js')) ?>" defer></script>
    <script src="<?= e(asset('/js/front/c.js')) ?>" defer></script>
<?php endif; ?>
<?php if ($page === 'admin'): ?>
    <script src="<?= e(asset('/js/admin/m.js')) ?>" defer></script>
    <script src="<?= e(asset('/js/admin/v.js')) ?>" defer></script>
    <script src="<?= e(asset('/js/admin/c.js')) ?>" defer></script>
<?php endif; ?>
</body>
</html>
