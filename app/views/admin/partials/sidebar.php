<?php

declare(strict_types=1);

/**
 * Variables attendues : $csrf.
 * Menu principal : barre latérale sur ordinateur, panneau ouvert par le bouton « Menu » sur téléphone.
 */
$screens = ['home', 'texts', 'design', 'tours', 'images', 'messages'];
$icon = fn (string $name): string => trim(partial('admin/partials/icon', ['name' => $name]));
$bottomLink = 'flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left font-semibold text-admin-sidebar-muted transition-colors duration-150 hover:bg-admin-sidebar-active hover:text-admin-sidebar-text';
?>
<header class="sticky top-0 z-40 flex h-14 items-center justify-between bg-admin-sidebar px-4 text-admin-sidebar-text lg:hidden">
    <span class="font-heading text-[0.95rem] font-extrabold tracking-[0.04em]"><?= e(t('admin.brand')) ?></span>
    <button type="button" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-admin-sidebar-text/30 px-4 font-heading font-bold" aria-expanded="false" aria-controls="admin-menu" data-action="toggle-menu">
        <?= $icon('menu') ?>
        <?= e(t('admin.menu_open')) ?>
    </button>
</header>
<aside id="admin-menu" class="z-30 flex flex-col bg-admin-sidebar text-admin-sidebar-text max-lg:fixed max-lg:inset-x-0 max-lg:top-14 max-lg:bottom-0 max-lg:hidden max-lg:overflow-y-auto max-lg:data-[open=true]:flex lg:sticky lg:top-0 lg:h-screen" data-open="false">
    <div class="hidden px-6 pt-8 pb-6 lg:block">
        <p class="font-heading text-[1.15rem] font-extrabold leading-tight"><?= e(t('admin.brand')) ?></p>
        <p class="mt-1 text-[0.9rem] text-admin-sidebar-muted"><?= e(t('admin.brand_sub')) ?></p>
    </div>
    <nav class="px-3 max-lg:pt-4" aria-label="<?= e(t('admin.menu_label')) ?>">
        <ul class="grid gap-1">
            <?= implode("\n            ", array_map(
                fn (string $screen): string => '<li><a href="#' . e($screen) . '" class="flex min-h-12 items-center gap-3 rounded-xl px-3 py-2.5 font-heading text-[1.02rem] font-bold text-admin-sidebar-muted transition-colors duration-150 hover:bg-admin-sidebar-active hover:text-admin-sidebar-text aria-[current=page]:bg-admin-sidebar-active aria-[current=page]:text-admin-sidebar-text aria-[current=page]:shadow-[inset_4px_0_0_var(--admin-accent)]" data-screen-link="' . e($screen) . '">'
                    . $icon($screen) . '<span>' . e(t('admin.nav.' . $screen)) . '</span>'
                    . ($screen === 'messages' ? '<span class="ml-auto rounded-full bg-admin-accent px-2 py-0.5 text-[0.8rem] text-admin-sidebar" data-message-badge hidden></span>' : '')
                    . '</a></li>',
                $screens
            )) ?>

        </ul>
    </nav>
    <div class="mt-auto grid gap-1 border-t border-admin-sidebar-text/15 px-3 py-4">
        <a class="<?= e($bottomLink) ?>" href="/" target="_blank" rel="noopener">
            <?= $icon('external') ?>
            <span><?= e(t('admin.view_site')) ?> <span class="sr-only"><?= e(t('admin.view_site_hint')) ?></span></span>
        </a>
        <button type="button" class="<?= e($bottomLink) ?>" aria-pressed="false" data-action="toggle-theme" data-label-dark="<?= e(t('admin.theme.dark')) ?>" data-label-light="<?= e(t('admin.theme.light')) ?>">
            <?= $icon('moon') ?>
            <span data-theme-label><?= e(t('admin.theme.dark')) ?></span>
        </button>
        <form method="post" action="/admin/logout">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <button class="<?= e($bottomLink) ?>" type="submit">
                <?= $icon('logout') ?>
                <span><?= e(t('admin.logout')) ?></span>
            </button>
        </form>
    </div>
</aside>
