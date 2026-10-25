<?php

declare(strict_types=1);

/**
 * Aucune variable requise.
 */
$buttonClass = 'inline-flex cursor-pointer items-center gap-[0.4rem] rounded-full border border-white/18 bg-white/8 px-[0.85rem] py-[0.4rem] font-heading text-[0.72rem] font-bold tracking-[0.08em] text-white/75 transition-[background,border-color] duration-160 ease-[ease] hover:border-brand hover:bg-white/14';
?>
<header class="sticky top-0 z-100 flex h-14 items-center justify-between bg-contrast px-8 mobile:px-4">
    <span class="font-heading text-[0.82rem] font-extrabold uppercase tracking-[0.12em] text-white/75"><?= e(t('admin.brand')) ?> <span class="text-brand"><?= e(t('admin.brand_accent')) ?></span></span>
        <button class="<?= e($buttonClass) ?>" id="themeToggle" type="button" aria-label="<?= e(t('admin.theme.toggle_label')) ?>" aria-pressed="false" data-action="toggle-theme" data-label-dark="<?= e(t('admin.theme.dark')) ?>" data-label-light="<?= e(t('admin.theme.light')) ?>" data-icon-dark="<?= e(t('admin.theme.icon_dark')) ?>" data-icon-light="<?= e(t('admin.theme.icon_light')) ?>">
            <span class="text-[0.95rem]" aria-hidden="true" data-theme-icon>🌙</span>
            <span data-theme-label><?= e(t('admin.theme.dark')) ?></span>
        </button>
</header>
