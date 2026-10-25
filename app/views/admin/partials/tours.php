<?php

declare(strict_types=1);

/**
 * Écran « Visites » : liste des visites. L'ajout et la modification se font dans la fenêtre partials/dialogs.php.
 */
?>
<section class="mx-auto max-w-6xl" data-screen="tours" aria-labelledby="tours-title" hidden>
    <header class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div class="max-w-3xl">
            <h1 class="admin-h1" id="tours-title"><?= e(t('admin.tours.title')) ?></h1>
            <p class="admin-lead"><?= e(t('admin.tours.intro')) ?></p>
        </div>
        <button type="button" class="admin-btn admin-btn-primary" data-action="tour-new">
            <?= partial('admin/partials/icon', ['name' => 'plus']) ?>
            <?= e(t('admin.tours.add')) ?>
        </button>
    </header>
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" data-tour-list>
        <p class="text-admin-muted"><?= e(t('admin.feedback.loading')) ?></p>
    </div>
</section>

<template id="tour-card-template">
    <article class="admin-card flex flex-col overflow-hidden p-0">
        <div class="relative grid aspect-video place-items-center overflow-hidden bg-admin-surface-2 text-admin-muted">
            <img class="absolute inset-0 h-full w-full object-cover" alt="" loading="lazy" data-tour-image>
            <span data-tour-noimage hidden><?= e(t('admin.tours.no_photo')) ?></span>
        </div>
        <div class="grid flex-1 content-start gap-2 p-5">
            <span class="justify-self-start" data-tour-status></span>
            <h2 class="font-heading text-[1.2rem] font-bold leading-snug" data-tour-title></h2>
            <p class="text-admin-muted" data-tour-meta></p>
        </div>
        <div class="px-5 pb-5">
            <button type="button" class="admin-btn admin-btn-secondary w-full" data-action="tour-edit"><?= e(t('admin.tours.edit')) ?></button>
        </div>
    </article>
</template>
