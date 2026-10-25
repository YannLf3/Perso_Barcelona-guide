<?php

declare(strict_types=1);

/**
 * Écran « Photos » : envoi par bouton ou glisser-déposer, puis galerie.
 */
?>
<section class="mx-auto max-w-6xl" data-screen="images" aria-labelledby="images-title" hidden>
    <header class="mb-8 max-w-3xl">
        <h1 class="admin-h1" id="images-title"><?= e(t('admin.images.title')) ?></h1>
        <p class="admin-lead"><?= e(t('admin.images.intro')) ?></p>
    </header>
    <label class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-admin-line bg-admin-surface px-6 py-10 text-center transition-colors duration-150 hover:border-admin-action focus-within:outline-3 focus-within:outline-offset-2 focus-within:outline-admin-focus data-[dragging=true]:border-admin-action data-[dragging=true]:bg-admin-surface-2" data-dropzone>
        <?= partial('admin/partials/icon', ['name' => 'upload', 'class' => 'size-9 text-admin-action']) ?>
        <span class="admin-btn admin-btn-primary"><?= e(t('admin.images.upload')) ?></span>
        <span class="text-admin-muted"><?= e(t('admin.images.upload_hint')) ?></span>
        <input class="sr-only" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-action="image-upload">
    </label>
    <p class="mt-3 min-h-6 font-semibold text-admin-muted" role="status" data-upload-status data-uploading="<?= e(t('admin.images.uploading')) ?>"></p>
    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5" data-gallery>
        <p class="text-admin-muted"><?= e(t('admin.feedback.loading')) ?></p>
    </div>
</section>

<template id="gallery-item-template">
    <figure class="m-0 overflow-hidden rounded-xl border border-admin-line bg-admin-surface">
        <img class="block aspect-[4/3] w-full object-cover" loading="lazy" alt="">
    </figure>
</template>
