<?php

declare(strict_types=1);

/**
 * Écran « Couleurs et polices » : réglages à gauche, aperçu en direct du site à droite.
 * Les réglages sont construits par le JavaScript à partir de /api/admin/design ; l'aperçu reçoit les changements par postMessage.
 */
$segmentGroup = fn (string $label, string $action, array $options, string $current): string => '<div class="admin-segmented" role="group" aria-label="' . e($label) . '">'
    . implode('', array_map(
        fn (string $value, string $text): string => '<button type="button" class="admin-segment" aria-pressed="' . ($value === $current ? 'true' : 'false') . '" data-action="' . e($action) . '" data-value="' . e($value) . '">' . e($text) . '</button>',
        array_keys($options),
        $options
    ))
    . '</div>';
?>
<section class="mx-auto max-w-[96rem]" data-screen="design" aria-labelledby="design-title" hidden>
    <header class="mb-8 max-w-3xl">
        <h1 class="admin-h1" id="design-title"><?= e(t('admin.design.title')) ?></h1>
        <p class="admin-lead"><?= e(t('admin.design.intro')) ?></p>
    </header>
    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] xl:items-start">
        <div class="order-2 grid min-w-0 gap-6 xl:order-1">
            <section class="admin-card" aria-labelledby="contrast-title" data-contrast>
                <h2 class="admin-h2" id="contrast-title"><?= e(t('admin.design.contrast_title')) ?></h2>
                <p class="mt-2 text-admin-muted" data-contrast-summary><?= e(t('admin.feedback.loading')) ?></p>
                <details class="mt-3" data-contrast-details hidden>
                    <summary class="admin-link inline-block" data-contrast-count></summary>
                    <ul class="mt-3 grid list-disc gap-1.5 pl-5" data-contrast-list></ul>
                </details>
            </section>

            <section class="admin-card" aria-labelledby="presets-title">
                <h2 class="admin-h2" id="presets-title"><?= e(t('admin.design.presets')) ?></h2>
                <p class="mt-1 text-admin-muted"><?= e(t('admin.design.presets_help')) ?></p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2" data-presets></div>
            </section>

            <section class="admin-card" aria-labelledby="fonts-title">
                <h2 class="admin-h2" id="fonts-title"><?= e(t('admin.design.fonts')) ?></h2>
                <p class="mt-1 text-admin-muted"><?= e(t('admin.design.fonts_help')) ?></p>
                <fieldset class="mt-6">
                    <legend class="admin-h3"><?= e(t('admin.design.font_heading')) ?></legend>
                    <div class="mt-3 grid gap-2.5 sm:grid-cols-2" data-fonts="heading"></div>
                </fieldset>
                <fieldset class="mt-8">
                    <legend class="admin-h3"><?= e(t('admin.design.font_body')) ?></legend>
                    <div class="mt-3 grid gap-2.5 sm:grid-cols-2" data-fonts="body"></div>
                </fieldset>
            </section>

            <section class="admin-card grid gap-7" aria-labelledby="shapes-title">
                <h2 class="admin-h2" id="shapes-title"><?= e(t('admin.design.shapes')) ?></h2>
                <fieldset>
                    <legend class="admin-h3"><?= e(t('admin.design.text_size')) ?></legend>
                    <p class="mt-0.5 text-admin-muted"><?= e(t('admin.design.text_size_help')) ?></p>
                    <div class="mt-3 flex flex-wrap gap-2" data-choices="textSize"></div>
                </fieldset>
                <fieldset>
                    <legend class="admin-h3"><?= e(t('admin.design.button_shape')) ?></legend>
                    <div class="mt-3 flex flex-wrap gap-2" data-choices="button"></div>
                </fieldset>
                <fieldset>
                    <legend class="admin-h3"><?= e(t('admin.design.card_shape')) ?></legend>
                    <div class="mt-3 flex flex-wrap gap-2" data-choices="card"></div>
                </fieldset>
            </section>

            <section class="admin-card" aria-labelledby="colors-title">
                <h2 class="admin-h2" id="colors-title"><?= e(t('admin.design.colors')) ?></h2>
                <p class="mt-1 text-admin-muted"><?= e(t('admin.design.colors_help')) ?></p>
                <div class="mt-5 grid gap-3" data-color-groups></div>
            </section>

            <section class="admin-card" aria-labelledby="photos-title">
                <h2 class="admin-h2" id="photos-title"><?= e(t('admin.design.photos')) ?></h2>
                <p class="mt-1 text-admin-muted"><?= e(t('admin.design.photos_help')) ?></p>
                <ol class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3" data-photos></ol>
            </section>

            <section class="admin-card" aria-labelledby="history-title">
                <h2 class="admin-h2" id="history-title"><?= e(t('admin.design.history')) ?></h2>
                <p class="mt-1 text-admin-muted"><?= e(t('admin.design.history_help')) ?></p>
                <ol class="mt-5 grid gap-2" data-history></ol>
            </section>
        </div>

        <section class="order-1 min-w-0 xl:sticky xl:top-6 xl:order-2" aria-labelledby="preview-title" id="design-preview">
            <div class="admin-card overflow-hidden p-0">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-admin-line px-4 py-3">
                    <h2 class="admin-h3" id="preview-title"><?= e(t('admin.design.preview')) ?></h2>
                    <div class="flex flex-wrap gap-2">
                        <?= $segmentGroup(t('admin.design.preview_page'), 'preview-page', ['home' => t('admin.design.preview_home'), 'legal' => t('admin.design.preview_legal')], 'home') ?>

                        <?= $segmentGroup(t('admin.design.preview_device'), 'preview-device', ['desktop' => t('admin.design.preview_desktop'), 'mobile' => t('admin.design.preview_mobile')], 'desktop') ?>

                    </div>
                </div>
                <div class="relative h-[60vh] overflow-hidden bg-admin-surface-2 xl:h-[calc(100vh-10rem)]" data-preview-stage>
                    <iframe class="absolute top-0 left-0 origin-top-left border-0 bg-white" title="<?= e(t('admin.design.preview_frame')) ?>" data-preview-frame></iframe>
                </div>
            </div>
        </section>
    </div>
    <?= partial('admin/partials/savebar', ['name' => 'design', 'saveLabel' => t('admin.savebar.publish')]) ?>
</section>

<template id="preset-template">
    <button type="button" class="grid gap-3 rounded-xl border-2 border-admin-line bg-admin-surface p-4 text-left transition-colors duration-150 hover:border-admin-action" data-action="apply-preset">
        <span class="flex h-8 overflow-hidden rounded-lg border border-admin-line" data-preset-swatches></span>
        <span class="font-heading text-[1.05rem] font-bold" data-preset-label></span>
        <span class="text-[0.92rem] text-admin-muted" data-preset-description></span>
    </button>
</template>

<template id="font-template">
    <label class="flex cursor-pointer flex-col gap-1 rounded-xl border-2 border-admin-line bg-admin-surface p-4 transition-colors duration-150 hover:border-admin-action has-[:checked]:border-admin-action has-[:checked]:bg-admin-surface-2 has-[:focus-visible]:outline-3 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-admin-focus">
        <input class="sr-only" type="radio" data-action="choose-font">
        <span class="text-[1.45rem] leading-tight" data-font-sample></span>
        <span class="font-semibold" data-font-name></span>
        <span class="text-[0.9rem] text-admin-muted" data-font-description></span>
    </label>
</template>

<template id="choice-template">
    <label class="inline-flex min-h-11 cursor-pointer items-center rounded-xl border-2 border-admin-line bg-admin-surface px-4 py-2 font-semibold transition-colors duration-150 hover:border-admin-action has-[:checked]:border-admin-action has-[:checked]:bg-admin-surface-2 has-[:focus-visible]:outline-3 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-admin-focus">
        <input class="sr-only" type="radio" data-action="choose-option">
        <span data-choice-label></span>
    </label>
</template>

<template id="color-group-template">
    <details class="group/details rounded-xl border border-admin-line" data-color-group>
        <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-3.5 [&::-webkit-details-marker]:hidden">
            <span class="flex shrink-0 overflow-hidden rounded-md border border-admin-line" data-group-swatches></span>
            <span class="grid flex-1 gap-0.5">
                <span class="font-heading text-[1.05rem] font-bold" data-group-label></span>
                <span class="text-[0.9rem] text-admin-muted" data-group-help></span>
            </span>
            <span class="admin-badge-warning" data-group-warning hidden></span>
            <?= partial('admin/partials/icon', ['name' => 'chevron', 'class' => 'size-5 shrink-0 text-admin-muted transition-transform duration-150 group-open/details:rotate-180']) ?>
        </summary>
        <div class="border-t border-admin-line px-4" data-group-rows></div>
    </details>
</template>

<template id="color-row-template">
    <div class="grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-4 gap-y-1 border-b border-admin-line py-3.5 last:border-b-0" data-color-row>
        <input class="h-12 w-14 cursor-pointer rounded-lg border border-admin-line bg-admin-surface p-1 disabled:cursor-not-allowed disabled:opacity-40" type="color" data-action="color-input">
        <div class="grid min-w-0 gap-0.5">
            <label class="font-semibold" data-color-label></label>
            <p class="text-[0.9rem] text-admin-muted" data-color-state></p>
            <label class="mt-1 flex items-center gap-2 text-[0.95rem]" data-transparent-wrap hidden>
                <input class="size-5 accent-admin-action" type="checkbox" data-action="color-transparent">
                <span><?= e(t('admin.design.same_as_page')) ?></span>
            </label>
        </div>
        <button type="button" class="admin-link col-start-2 justify-self-start" data-action="color-reset" hidden><?= e(t('admin.design.reset_color')) ?></button>
    </div>
</template>

<template id="photo-slot-template">
    <li class="grid gap-2">
        <span class="block aspect-[3/4] overflow-hidden rounded-xl bg-admin-surface-2"><img class="h-full w-full object-cover" alt="" data-photo-image></span>
        <span class="flex items-center justify-between gap-2">
            <span class="font-semibold" data-photo-label></span>
            <button type="button" class="admin-link" data-action="photo-change"><?= e(t('admin.design.change_photo')) ?></button>
        </span>
    </li>
</template>

<template id="history-item-template">
    <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-admin-line px-4 py-3">
        <span class="grid">
            <span class="font-semibold" data-history-date></span>
            <span class="text-[0.92rem] text-admin-muted" data-history-label></span>
        </span>
        <span class="admin-badge" data-history-current hidden><?= e(t('admin.design.history_current')) ?></span>
        <button type="button" class="admin-btn admin-btn-secondary" data-action="history-restore" hidden><?= e(t('admin.design.history_restore')) ?></button>
    </li>
</template>
