<?php

declare(strict_types=1);

/**
 * Écran « Textes du site » : tous les textes du dictionnaire, regroupés par zone, langue par langue.
 * Les groupes et les champs sont construits par le JavaScript à partir de /api/admin/texts.
 */
?>
<section class="mx-auto max-w-4xl" data-screen="texts" aria-labelledby="texts-title" hidden>
    <header class="mb-6">
        <h1 class="admin-h1" id="texts-title"><?= e(t('admin.texts.title')) ?></h1>
        <p class="admin-lead"><?= e(t('admin.texts.intro')) ?></p>
    </header>
    <div class="sticky top-14 z-20 -mx-4 mb-6 border-b border-admin-line bg-admin-bg px-4 py-4 sm:-mx-6 sm:px-6 lg:top-0 lg:-mx-10 lg:px-10">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <p class="admin-label mb-1.5" id="texts-lang-label"><?= e(t('admin.texts.language')) ?></p>
                <div class="admin-segmented" role="group" aria-labelledby="texts-lang-label">
                    <?= implode('', array_map(
                        fn (string $code): string => '<button type="button" class="admin-segment" aria-pressed="' . ($code === 'fr' ? 'true' : 'false') . '" data-action="text-lang" data-lang="' . e($code) . '">' . e(t('admin.lang.' . $code)) . '</button>',
                        ['fr', 'en', 'es', 'it']
                    )) ?>

                </div>
            </div>
            <label class="grid min-w-[14rem] flex-1 gap-1.5">
                <span class="admin-label"><?= e(t('admin.texts.search')) ?></span>
                <input class="admin-input" type="search" placeholder="<?= e(t('admin.texts.search_placeholder')) ?>" data-action="text-search">
            </label>
        </div>
    </div>
    <div class="grid gap-4" data-text-groups>
        <p class="text-admin-muted"><?= e(t('admin.feedback.loading')) ?></p>
    </div>
    <div class="mt-10 grid gap-4" data-text-advanced hidden>
        <div>
            <h2 class="admin-h2"><?= e(t('admin.texts.advanced')) ?></h2>
            <p class="mt-1 text-admin-muted"><?= e(t('admin.texts.advanced_help')) ?></p>
        </div>
        <div class="grid gap-4" data-text-advanced-groups></div>
    </div>
    <p class="admin-card text-admin-muted" data-text-noresult hidden><?= e(t('admin.texts.no_result')) ?></p>
    <?= partial('admin/partials/savebar', ['name' => 'texts', 'saveLabel' => t('admin.savebar.save')]) ?>
</section>

<template id="text-group-template">
    <details class="admin-card group/details" data-text-group>
        <summary class="flex cursor-pointer list-none items-center gap-4 [&::-webkit-details-marker]:hidden">
            <span class="grid flex-1 gap-0.5">
                <span class="font-heading text-[1.2rem] font-bold" data-group-label></span>
                <span class="text-[0.95rem] text-admin-muted" data-group-help></span>
            </span>
            <span class="admin-badge" data-group-count hidden></span>
            <?= partial('admin/partials/icon', ['name' => 'chevron', 'class' => 'size-6 shrink-0 text-admin-muted transition-transform duration-150 group-open/details:rotate-180']) ?>
        </summary>
        <div class="mt-6 grid gap-7 border-t border-admin-line pt-6" data-group-fields></div>
    </details>
</template>

<template id="text-field-template">
    <div class="grid gap-2" data-text-field>
        <div class="flex flex-wrap items-center gap-2">
            <label class="admin-label" data-field-label></label>
            <span class="admin-badge" data-field-modified hidden><?= e(t('admin.texts.modified')) ?></span>
        </div>
        <textarea class="admin-input resize-y" rows="1" data-field-input></textarea>
        <p class="rounded-lg bg-admin-warning-bg px-3 py-2 text-[0.92rem] text-admin-warning" data-field-warning hidden></p>
        <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-1 text-[0.92rem] text-admin-muted">
            <p class="min-w-0 flex-1" data-field-origin-wrap hidden><span class="font-semibold"><?= e(t('admin.texts.origin')) ?></span> <span data-field-origin></span></p>
            <button type="button" class="admin-link" data-action="text-reset" hidden><?= e(t('admin.texts.reset')) ?></button>
        </div>
    </div>
</template>
