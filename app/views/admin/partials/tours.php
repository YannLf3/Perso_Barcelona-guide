<?php

declare(strict_types=1);

$langs = [
    ['code' => 'en', 'accent' => 'border-l-[#c85d00]'],
    ['code' => 'es', 'accent' => 'border-l-[#c98300]'],
    ['code' => 'fr', 'accent' => 'border-l-[#1867c0]'],
    ['code' => 'it', 'accent' => 'border-l-[#0f7b6c]'],
];
$groupOptions = implode('', array_map(
    fn (string $type): string => '<option value="' . e($type) . '"' . ($type === 'small' ? ' selected' : '') . '>' . e(t('admin.tours.group_' . $type)) . '</option>',
    Tour::GROUP_TYPES
));

/**
 * Bloc de traduction du formulaire d'ajout pour une langue.
 *
 * @param array{code: string, accent: string} $lang
 */
$langBlock = function (array $lang): string {
    $code = $lang['code'];
    $upper = strtoupper($code);
    $heading = t('admin.tours.flag_' . $code) . ' ' . t('admin.tours.lang_' . $code) . ($code === 'en' ? ' (' . t('admin.tours.en_required') . ')' : '');
    $required = $code === 'en' ? ' required' : '';

    return '<div class="admin-lang ' . $lang['accent'] . '">'
        . '<h4 class="admin-eyebrow">' . e($heading) . '</h4>'
        . '<label class="admin-label">' . e(t('admin.tours.title')) . ' (' . $upper . ')<input class="admin-control" name="title_' . $code . '"' . $required . '></label>'
        . '<label class="admin-label">' . e(t('admin.tours.tagline')) . ' (' . $upper . ')<input class="admin-control" name="tagline_' . $code . '"' . $required . '></label>'
        . '<label class="admin-label">' . e(t('admin.tours.summary')) . ' (' . $upper . ')<textarea class="admin-control resize-y" name="summary_' . $code . '" rows="3"' . $required . '></textarea></label>'
        . '</div>';
};
?>
<template id="tours-template">
    <section class="grid gap-8 px-8 py-7">
        <h2 class="admin-title"><?= e(t('admin.tours.add_title')) ?></h2>
        <form class="admin-card" data-form="tour-add">
            <div class="admin-section">
                <h3 class="admin-subtitle"><?= e(t('admin.tours.common_fields')) ?></h3>
                <label class="admin-label"><?= e(t('admin.tours.duration')) ?> <input class="admin-control" name="duration" required maxlength="60"></label>
                <label class="admin-label"><?= e(t('admin.tours.capacity')) ?> <input class="admin-control" type="number" step="1" min="1" name="capacity" required></label>
                <label class="admin-label"><?= e(t('admin.tours.group_type')) ?> <select class="admin-control" name="group_type" required><?= $groupOptions ?></select></label>
                <label class="admin-label"><?= e(t('admin.tours.image')) ?> <select class="admin-control" name="image_url" data-images></select></label>
            </div>
            <div class="admin-section">
                <h3 class="admin-subtitle"><?= e(t('admin.tours.translations')) ?></h3>
                <?= implode("\n                ", array_map($langBlock, $langs)) ?>

            </div>
            <button class="admin-button" type="submit"><?= e(t('admin.tours.add_submit')) ?></button>
        </form>
        <h2 class="admin-title"><?= e(t('admin.tours.edit_title')) ?></h2>
        <div class="admin-grid" data-tour-list></div>
    </section>
</template>

<template id="tour-row-template">
    <div class="grid gap-[0.875rem] rounded-[1.25rem] border border-line bg-surface px-6 py-5 shadow-[0_1px_1px_rgba(36,22,11,0.06)] transition-shadow duration-160 ease-[ease] hover:shadow-surface" data-tour-row>
        <h3 class="admin-subtitle"><?= e(t('admin.tours.tour_number')) ?><span data-field="id"></span></h3>
        <div class="admin-section">
            <h4 class="admin-eyebrow"><?= e(t('admin.tours.common_fields')) ?></h4>
            <label><?= e(t('admin.tours.duration')) ?> <input name="duration" required></label>
            <label><?= e(t('admin.tours.capacity')) ?>
                <input type="number" step="1" min="1" name="capacity" required></label>
            <label><?= e(t('admin.tours.active')) ?>
                <select name="is_active">
                    <option value="1"><?= e(t('admin.tours.yes')) ?></option>
                    <option value="0"><?= e(t('admin.tours.no')) ?></option>
                </select>
            </label>
            <label>
                <?= e(t('admin.tours.group_type')) ?>
                <select name="group_type"><?= $groupOptions ?></select>
            </label>
            <label>
                <?= e(t('admin.tours.image')) ?>
                <select name="image_url" data-images></select>
            </label>
        </div>
        <div class="grid gap-2">
            <h4 class="admin-eyebrow"><?= e(t('admin.tours.edit_translation')) ?></h4>
            <select class="admin-control max-w-72" data-action="edit-lang">
                <?= implode('', array_map(
                    fn (array $lang): string => '<option value="' . e($lang['code']) . '">' . e(t('admin.tours.flag_' . $lang['code']) . ' ' . t('admin.tours.lang_' . $lang['code'])) . '</option>',
                    $langs
                )) ?>

            </select>
        </div>
        <form class="admin-card" data-form="tour-edit">
            <input type="hidden" name="id">
            <input type="hidden" name="locale" value="en">
            <div class="admin-lang">
                <label class="admin-label"><?= e(t('admin.tours.title')) ?> <input class="admin-control" name="title" required></label>
                <label class="admin-label"><?= e(t('admin.tours.tagline')) ?> <input class="admin-control" name="tagline" required></label>
                <label class="admin-label"><?= e(t('admin.tours.summary')) ?> <textarea class="admin-control resize-y" name="summary" rows="3" required></textarea></label>
            </div>
            <button class="admin-button" type="submit" data-label="<?= e(t('admin.tours.update')) ?>"></button>
        </form>
    </div>
</template>
