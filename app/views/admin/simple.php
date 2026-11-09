<?php

declare(strict_types=1);

/**
 * Espace simplifié du compte « editor » : même structure que l'ancien back-office (en-tête, puis une carte par bloc).
 * Variables attendues : $csrf, $strings (textes utilisés par le JavaScript).
 * Le JavaScript (public/js/admin/simple/) remplit les sections à partir de l'API, à partir des <template> ci-dessous.
 */
$jsonFlags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR;
$langs = ['en' => '🇬🇧', 'es' => '🇪🇸', 'fr' => '🇫🇷', 'it' => '🇮🇹'];
$groupTypes = ['private' => 'group_private', 'small' => 'group_small', 'school' => 'group_school'];

/** Les trois champs d'une langue (titre, accroche, résumé) : sert au formulaire d'ajout. */
$langBlock = fn (string $code, bool $required): string => '<div class="lang-block lang-' . e($code) . '">'
    . '<h4>' . $langs[$code] . ' ' . e(t('admin.lang.' . $code)) . ($required ? ' ' . e(t('admin.simple.lang_required')) : '') . '</h4>'
    . '<label>' . e(t('admin.simple.title_field')) . ' (' . strtoupper($code) . ') <input name="title_' . e($code) . '"' . ($required ? ' required' : '') . ' maxlength="120"></label>'
    . '<label>' . e(t('admin.simple.tagline_field')) . ' (' . strtoupper($code) . ') <input name="tagline_' . e($code) . '"' . ($required ? ' required' : '') . ' maxlength="5000"></label>'
    . '<label>' . e(t('admin.simple.summary_field')) . ' (' . strtoupper($code) . ') <textarea name="summary_' . e($code) . '" rows="3"' . ($required ? ' required' : '') . '></textarea></label>'
    . '</div>';
?>
<a href="#main" class="sr-only"><?= e(t('admin.skip')) ?></a>
<header class="admin-header">
    <span class="admin-header__brand"><?= e(t('admin.simple.brand')) ?> <span><?= e(t('admin.simple.brand_accent')) ?></span></span>
    <div class="admin-header__actions">
        <button class="theme-toggle" type="button" data-action="toggle-theme" aria-label="<?= e(t('admin.simple.theme_label')) ?>" aria-pressed="false">
            <span class="theme-toggle__icon" aria-hidden="true">🌙</span>
            <span class="theme-toggle__label"><?= e(t('admin.simple.theme_to_dark')) ?></span>
        </button>
        <form method="post" action="/admin/logout">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <button class="theme-toggle" type="submit"><?= e(t('admin.simple.logout')) ?></button>
        </form>
    </div>
</header>
<main id="main">
    <section id="log" data-log></section>

    <section id="tourForm">
        <section class="admin-block">
            <h2><?= e(t('admin.simple.tours_add_title')) ?></h2>
            <form class="tour-add-form" data-form="add-tour">
                <div class="form-section">
                    <h3><?= e(t('admin.simple.common_fields')) ?></h3>
                    <label><?= e(t('admin.simple.duration')) ?> <input name="duration" required maxlength="60"></label>
                    <label><?= e(t('admin.simple.capacity')) ?> <input type="number" step="1" min="1" name="capacity" required></label>
                    <label>
                        <?= e(t('admin.simple.group_type')) ?>
                        <select name="group_type" required>
                            <?= implode('', array_map(
                                fn (string $value, string $label): string => '<option value="' . e($value) . '"' . ($value === 'small' ? ' selected' : '') . '>' . e(t('admin.simple.' . $label)) . '</option>',
                                array_keys($groupTypes),
                                $groupTypes
                            )) ?>

                        </select>
                    </label>
                    <label>
                        <?= e(t('admin.simple.tour_image')) ?>
                        <select name="image_url" data-image-select></select>
                    </label>
                </div>
                <div class="form-section">
                    <h3><?= e(t('admin.simple.translations')) ?></h3>
                    <?= implode('', array_map(fn (string $code): string => $langBlock($code, $code === 'en'), array_keys($langs))) ?>

                </div>
                <button type="submit"><?= e(t('admin.simple.add_tour')) ?></button>
            </form>

            <h2><?= e(t('admin.simple.tours_edit_title')) ?></h2>
            <div data-tour-list></div>
        </section>
    </section>

    <section id="imageGallery">
        <section class="admin-block">
            <h2><?= e(t('admin.simple.gallery_title')) ?></h2>
            <form class="image-upload-form" data-form="upload-image">
                <label>
                    <?= e(t('admin.simple.upload_label')) ?>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
                </label>
                <button type="submit"><?= e(t('admin.simple.upload_button')) ?></button>
            </form>
            <div class="image-gallery" data-image-gallery></div>
        </section>
    </section>

    <section id="messageList">
        <section class="message-list-grid">
            <h2><?= e(t('admin.simple.messages_title')) ?></h2>
            <div data-message-list></div>
        </section>
    </section>

    <section id="siteTexts">
        <section class="admin-block">
            <h2><?= e(t('admin.simple.texts_title')) ?></h2>
            <p class="texts-intro"><?= e(t('admin.simple.texts_intro')) ?></p>
            <div class="lang-selector">
                <h4><?= e(t('admin.simple.texts_language')) ?></h4>
                <select data-action="text-lang" aria-label="<?= e(t('admin.simple.texts_language')) ?>">
                    <?= implode('', array_map(
                        fn (string $code, string $flag): string => '<option value="' . e($code) . '"' . ($code === 'fr' ? ' selected' : '') . '>' . $flag . ' ' . e(t('admin.lang.' . $code)) . '</option>',
                        array_keys($langs),
                        $langs
                    )) ?>

                </select>
            </div>
            <div class="texts-groups" data-text-groups></div>
            <button class="texts-save" type="button" data-action="save-texts"><?= e(t('admin.simple.texts_save')) ?></button>
        </section>
    </section>
</main>

<template id="tour-row-template">
    <div class="tour-edit-container" data-tour>
        <h3 data-tour-title></h3>
        <div class="form-section">
            <h4><?= e(t('admin.simple.common_fields')) ?></h4>
            <label><?= e(t('admin.simple.duration')) ?> <input name="duration" required maxlength="60"></label>
            <label><?= e(t('admin.simple.capacity')) ?> <input type="number" step="1" min="1" name="capacity" required></label>
            <label>
                <?= e(t('admin.simple.active')) ?>
                <select name="is_active">
                    <option value="1"><?= e(t('admin.simple.yes')) ?></option>
                    <option value="0"><?= e(t('admin.simple.no')) ?></option>
                </select>
            </label>
            <label>
                <?= e(t('admin.simple.group_type')) ?>
                <select name="group_type">
                    <?= implode('', array_map(
                        fn (string $value, string $label): string => '<option value="' . e($value) . '">' . e(t('admin.simple.' . $label)) . '</option>',
                        array_keys($groupTypes),
                        $groupTypes
                    )) ?>

                </select>
            </label>
            <label>
                <?= e(t('admin.simple.tour_image')) ?>
                <select name="image_url" data-image-select></select>
            </label>
        </div>
        <div class="lang-selector">
            <h4><?= e(t('admin.simple.edit_translation')) ?></h4>
            <select class="edit-lang-select" data-action="tour-lang">
                <?= implode('', array_map(
                    fn (string $code, string $flag): string => '<option value="' . e($code) . '">' . $flag . ' ' . e(t('admin.lang.' . $code)) . '</option>',
                    array_keys($langs),
                    $langs
                )) ?>

            </select>
        </div>
        <form class="tour-edit-form" data-form="update-tour">
            <div class="lang-edit-block">
                <label><?= e(t('admin.simple.title_field')) ?> <input name="title" required maxlength="120"></label>
                <label><?= e(t('admin.simple.tagline_field')) ?> <input name="tagline" required maxlength="5000"></label>
                <label><?= e(t('admin.simple.summary_field')) ?> <textarea name="summary" rows="3" required></textarea></label>
            </div>
            <button type="submit" data-update-label></button>
        </form>
    </div>
</template>

<template id="message-row-template">
    <article class="message-card">
        <h3 data-message-name></h3>
        <div class="message-card__meta">
            <p><strong><?= e(t('admin.simple.message_email')) ?></strong> <span data-message-email></span></p>
            <p><strong><?= e(t('admin.simple.message_date')) ?></strong> <span data-message-date></span></p>
        </div>
        <div class="message-card__body" data-message-body></div>
    </article>
</template>

<template id="gallery-item-template">
    <figure class="image-gallery__item">
        <img loading="lazy" alt="">
        <figcaption></figcaption>
    </figure>
</template>

<template id="text-group-template">
    <div class="tour-edit-container" data-text-group>
        <h3 data-group-label></h3>
        <h4 data-group-help></h4>
        <div class="texts-fields" data-group-fields></div>
    </div>
</template>

<template id="text-field-template">
    <div class="lang-edit-block" data-text-field>
        <label>
            <span data-field-label></span>
            <textarea data-field-input rows="2"></textarea>
        </label>
        <p class="texts-origin" data-field-origin></p>
    </div>
</template>
<script type="application/json" id="admin-strings"><?= json_encode($strings, $jsonFlags) ?></script>
