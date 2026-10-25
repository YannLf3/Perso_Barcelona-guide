<?php

declare(strict_types=1);

/**
 * Fenêtres (<dialog> natifs) : édition d'une visite et choix d'une photo.
 */
$groupTypes = Tour::GROUP_TYPES;
$langs = ['en', 'fr', 'es', 'it'];
$closeButton = '<button type="button" class="grid size-11 shrink-0 place-items-center rounded-xl text-admin-muted hover:bg-admin-surface-2 hover:text-admin-text" data-action="dialog-close" aria-label="' . e(t('admin.tours.close')) . '">'
    . trim(partial('admin/partials/icon', ['name' => 'close', 'class' => 'size-6'])) . '</button>';

/**
 * Champs de texte d'une visite pour une langue.
 */
$langPanel = function (string $code): string {
    $required = $code === 'en' ? ' required' : '';
    $field = fn (string $label, string $control): string => '<label class="grid gap-1.5"><span class="admin-label">' . e($label) . '</span>' . $control . '</label>';

    return '<div class="grid gap-5" data-lang-panel="' . e($code) . '"' . ($code === 'en' ? '' : ' hidden') . '>'
        . $field(t('admin.tours.field_title'), '<input class="admin-input" name="title_' . e($code) . '" maxlength="120"' . $required . '>')
        . $field(t('admin.tours.field_tagline'), '<textarea class="admin-input resize-y" rows="2" name="tagline_' . e($code) . '" maxlength="5000"' . $required . '></textarea>')
        . $field(t('admin.tours.field_summary'), '<textarea class="admin-input resize-y" rows="6" name="summary_' . e($code) . '" maxlength="10000"' . $required . '></textarea>')
        . '</div>';
};
?>
<dialog class="m-auto max-h-[calc(100dvh-1.5rem)] w-[min(54rem,calc(100vw-1.5rem))] overflow-hidden rounded-2xl border-0 bg-admin-surface p-0 text-admin-text shadow-[0_24px_80px_rgba(20,10,0,0.35)] backdrop:bg-black/55" id="tour-editor" aria-labelledby="tour-editor-title" data-title-new="<?= e(t('admin.tours.editor_new')) ?>" data-title-edit="<?= e(t('admin.tours.editor_edit')) ?>">
    <form class="flex max-h-[calc(100dvh-1.5rem)] flex-col" data-form="tour" novalidate>
        <header class="flex items-center justify-between gap-4 border-b border-admin-line px-6 py-4">
            <h2 class="admin-h2" id="tour-editor-title" data-editor-title></h2>
            <?= $closeButton ?>
        </header>
        <div class="grid gap-9 overflow-y-auto px-6 py-6">
            <p class="rounded-xl bg-admin-danger-bg px-4 py-3 font-semibold text-admin-danger" role="alert" data-editor-error hidden></p>
            <input type="hidden" name="id">
            <fieldset class="grid gap-5">
                <legend class="admin-h3 mb-4"><?= e(t('admin.tours.section_info')) ?></legend>
                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="grid gap-1.5">
                        <span class="admin-label"><?= e(t('admin.tours.duration')) ?></span>
                        <input class="admin-input" name="duration" maxlength="60" required aria-describedby="tour-duration-help">
                        <span class="admin-help" id="tour-duration-help"><?= e(t('admin.tours.duration_help')) ?></span>
                    </label>
                    <label class="grid content-start gap-1.5">
                        <span class="admin-label"><?= e(t('admin.tours.capacity')) ?></span>
                        <input class="admin-input" type="number" name="capacity" min="1" step="1" inputmode="numeric" required>
                    </label>
                </div>
                <fieldset>
                    <legend class="admin-label mb-2"><?= e(t('admin.tours.group_type')) ?></legend>
                    <div class="flex flex-wrap gap-2">
                        <?= implode('', array_map(
                            fn (string $type): string => '<label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-xl border-2 border-admin-line px-4 py-2 font-semibold has-[:checked]:border-admin-action has-[:checked]:bg-admin-surface-2 has-[:focus-visible]:outline-3 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-admin-focus">'
                                . '<input class="size-4 accent-admin-action" type="radio" name="group_type" value="' . e($type) . '"' . ($type === 'small' ? ' checked' : '') . '>' . e(t('admin.tours.group_' . $type)) . '</label>',
                            $groupTypes
                        )) ?>

                    </div>
                </fieldset>
                <label class="flex cursor-pointer items-center gap-3 rounded-xl bg-admin-surface-2 px-4 py-3.5 font-semibold">
                    <input class="size-5 accent-admin-action" type="checkbox" name="is_active" checked>
                    <?= e(t('admin.tours.active')) ?>
                </label>
            </fieldset>
            <fieldset>
                <legend class="admin-h3 mb-4"><?= e(t('admin.tours.section_photo')) ?></legend>
                <div class="flex flex-wrap items-center gap-4">
                    <span class="grid h-24 w-36 place-items-center overflow-hidden rounded-xl bg-admin-surface-2 text-center text-[0.85rem] text-admin-muted">
                        <img class="h-full w-full object-cover" alt="" data-editor-image hidden>
                        <span data-editor-noimage><?= e(t('admin.tours.no_photo')) ?></span>
                    </span>
                    <button type="button" class="admin-btn admin-btn-secondary" data-action="tour-pick-image"><?= e(t('admin.tours.choose_photo')) ?></button>
                    <input type="hidden" name="image_url">
                </div>
            </fieldset>
            <fieldset>
                <legend class="admin-h3"><?= e(t('admin.tours.section_texts')) ?></legend>
                <p class="mt-1 mb-4 text-admin-muted"><?= e(t('admin.tours.texts_help')) ?></p>
                <div class="admin-segmented mb-5" role="group" aria-label="<?= e(t('admin.texts.language')) ?>">
                    <?= implode('', array_map(
                        fn (string $code): string => '<button type="button" class="admin-segment" aria-pressed="' . ($code === 'en' ? 'true' : 'false') . '" data-action="tour-lang" data-lang="' . e($code) . '">' . e(t('admin.lang.' . $code)) . ($code === 'en' ? ' <span class="font-normal">(' . e(t('admin.tours.required')) . ')</span>' : '') . '</button>',
                        $langs
                    )) ?>

                </div>
                <?= implode("\n                ", array_map($langPanel, $langs)) ?>

            </fieldset>
        </div>
        <footer class="flex flex-wrap justify-end gap-3 border-t border-admin-line px-6 py-4">
            <button type="button" class="admin-btn admin-btn-secondary" data-action="dialog-close"><?= e(t('admin.tours.cancel')) ?></button>
            <button type="submit" class="admin-btn admin-btn-primary" data-label="<?= e(t('admin.tours.save')) ?>"><?= e(t('admin.tours.save')) ?></button>
        </footer>
    </form>
</dialog>

<dialog class="m-auto max-h-[calc(100dvh-1.5rem)] w-[min(60rem,calc(100vw-1.5rem))] overflow-hidden rounded-2xl border-0 bg-admin-surface p-0 text-admin-text shadow-[0_24px_80px_rgba(20,10,0,0.35)] backdrop:bg-black/55" id="image-picker" aria-labelledby="image-picker-title">
    <div class="flex max-h-[calc(100dvh-1.5rem)] flex-col">
        <header class="flex items-center justify-between gap-4 border-b border-admin-line px-6 py-4">
            <h2 class="admin-h2" id="image-picker-title"><?= e(t('admin.images.picker_title')) ?></h2>
            <?= $closeButton ?>
        </header>
        <div class="overflow-y-auto px-6 py-5">
            <p class="mb-4 text-admin-muted"><?= e(t('admin.images.picker_help')) ?></p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4" data-picker-grid></div>
        </div>
    </div>
</dialog>

<template id="picker-item-template">
    <button type="button" class="overflow-hidden rounded-xl border-4 border-transparent bg-admin-surface-2 transition-colors duration-150 hover:border-admin-line aria-pressed:border-admin-action" aria-pressed="false" data-action="pick-image">
        <img class="block aspect-[4/3] w-full object-cover" alt="" loading="lazy">
    </button>
</template>
