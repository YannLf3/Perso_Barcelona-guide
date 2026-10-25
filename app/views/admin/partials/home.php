<?php

declare(strict_types=1);

/**
 * Écran d'accueil : raccourcis vers les tâches courantes, derniers messages et état des visites.
 */
$screens = ['texts', 'design', 'tours', 'images', 'messages'];
?>
<section class="mx-auto max-w-5xl" data-screen="home" aria-labelledby="home-title">
    <header class="mb-8">
        <h1 class="admin-h1" id="home-title"><?= e(t('admin.home.title')) ?></h1>
        <p class="admin-lead"><?= e(t('admin.home.intro')) ?></p>
    </header>
    <ul class="grid gap-4 sm:grid-cols-2">
        <?= implode("\n        ", array_map(
            fn (string $screen): string => '<li><a href="#' . e($screen) . '" class="admin-card flex h-full items-start gap-4 transition-[border-color,transform] duration-150 hover:-translate-y-0.5 hover:border-admin-action">'
                . '<span class="grid size-11 shrink-0 place-items-center rounded-xl bg-admin-surface-2 text-admin-action">' . trim(partial('admin/partials/icon', ['name' => $screen, 'class' => 'size-6'])) . '</span>'
                . '<span class="grid gap-1"><span class="font-heading text-[1.15rem] font-bold text-admin-text">' . e(t('admin.home.action_' . $screen)) . '</span>'
                . '<span class="text-admin-muted">' . e(t('admin.home.action_' . $screen . '_desc')) . '</span></span></a></li>',
            $screens
        )) ?>

    </ul>
    <div class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
        <section class="admin-card" aria-labelledby="home-messages-title">
            <h2 class="admin-h2" id="home-messages-title"><?= e(t('admin.home.latest_messages')) ?></h2>
            <ul class="mt-4 grid gap-3" data-home-messages>
                <li class="text-admin-muted"><?= e(t('admin.feedback.loading')) ?></li>
            </ul>
            <a class="admin-link mt-4 inline-block" href="#messages"><?= e(t('admin.home.all_messages')) ?></a>
        </section>
        <div class="grid content-start gap-6">
            <section class="admin-card" aria-labelledby="home-tours-title">
                <h2 class="admin-h2" id="home-tours-title"><?= e(t('admin.home.tours_status')) ?></h2>
                <p class="mt-3 text-admin-muted" data-home-tours><?= e(t('admin.feedback.loading')) ?></p>
            </section>
            <aside class="rounded-2xl bg-admin-surface-2 p-6" aria-labelledby="home-help-title">
                <h2 class="font-heading text-[1.1rem] font-bold" id="home-help-title"><?= e(t('admin.home.reassurance_title')) ?></h2>
                <p class="mt-2 text-admin-muted"><?= e(t('admin.home.reassurance')) ?></p>
            </aside>
        </div>
    </div>
</section>

<template id="home-message-template">
    <li class="rounded-xl border border-admin-line px-4 py-3">
        <p class="font-semibold" data-field="fullname"></p>
        <p class="text-[0.92rem] text-admin-muted" data-field="date"></p>
        <p class="mt-1 line-clamp-2" data-field="message"></p>
    </li>
</template>
