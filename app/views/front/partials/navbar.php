<?php

declare(strict_types=1);

/**
 * Variables attendues : $lang.
 */
$linkClass = 'cursor-pointer border-0 bg-transparent p-0 font-heading text-[0.85rem] font-bold uppercase leading-[normal] tracking-[0.12em] text-ui-nav-text/70 transition-colors duration-200 hover:text-ui-nav-hover';
$items = [
    ['action' => 'show-intro', 'label' => t('nav.intro')],
    ['action' => 'show-tours', 'label' => t('nav.tours')],
    ['action' => 'show-contact', 'label' => t('nav.contact')],
];
?>
<header>
    <nav class="group/nav sticky top-0 z-100 flex h-14 items-center justify-between bg-ui-nav-bg px-8" aria-label="<?= e(t('a11y.main_nav')) ?>" data-open="false">
        <button type="button" class="cursor-pointer border-0 bg-transparent p-0 font-heading text-[1rem] font-extrabold leading-[1.6] tracking-[0.1em] text-ui-nav-text/70" data-action="show-intro"><?= e(t('nav.brand')) ?></button>
        <ul class="flex items-center gap-8 mobile:absolute mobile:top-14 mobile:right-0 mobile:left-0 mobile:hidden mobile:flex-col mobile:gap-5 mobile:bg-ui-nav-menu-bg mobile:px-8 mobile:py-4 mobile:group-data-[open=true]/nav:flex" id="nav-links">
            <?= implode("\n            ", array_map(
                fn (array $item): string => '<li><button type="button" class="' . $linkClass . '" data-action="' . e($item['action']) . '">' . e($item['label']) . '</button></li>',
                $items
            )) ?>

            <li><a class="inline-block <?= e($linkClass) ?>" href="/admin" aria-label="<?= e(t('nav.admin_label')) ?>"><?= e(t('nav.admin')) ?></a></li>
            <li>
                <select class="cursor-pointer rounded-[6px] border border-ui-nav-text/15 bg-ui-nav-text/8 px-[0.6em] py-[0.3em] font-heading text-[0.85rem] font-bold uppercase leading-[normal] tracking-[0.1em] text-ui-nav-text/70" aria-label="<?= e(t('a11y.language')) ?>" data-action="change-lang">
                    <?= implode('', array_map(
                        fn (string $code): string => '<option value="' . e($code) . '"' . ($code === $lang ? ' selected' : '') . '>' . e(strtoupper($code)) . '</option>',
                        Lang::SUPPORTED
                    )) ?>

                </select>
            </li>
        </ul>
        <button type="button" class="hidden cursor-pointer border-0 bg-transparent font-[family-name:Arial] text-[1.5rem] leading-[normal] text-ui-nav-text mobile:block" aria-expanded="false" aria-controls="nav-links" aria-label="<?= e(t('a11y.menu')) ?>" data-action="toggle-nav">&#9776;</button>
    </nav>
</header>
