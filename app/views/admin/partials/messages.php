<?php

declare(strict_types=1);

/**
 * Écran « Messages » : demandes reçues par le formulaire de contact (texte brut, inséré par textContent).
 */
?>
<section class="mx-auto max-w-4xl" data-screen="messages" aria-labelledby="messages-title" hidden>
    <header class="mb-8">
        <h1 class="admin-h1" id="messages-title"><?= e(t('admin.messages.title')) ?></h1>
        <p class="admin-lead"><?= e(t('admin.messages.intro')) ?></p>
    </header>
    <div class="grid gap-4" data-message-list>
        <p class="text-admin-muted"><?= e(t('admin.feedback.loading')) ?></p>
    </div>
</section>

<template id="message-template">
    <article class="admin-card grid gap-3">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 class="font-heading text-[1.2rem] font-bold break-words" data-field="fullname"></h2>
                <p class="text-[0.95rem] text-admin-muted" data-field="date"></p>
                <p class="text-[0.95rem] break-all text-admin-muted" data-field="email"></p>
            </div>
            <a class="admin-btn admin-btn-secondary" data-field="reply"><?= e(t('admin.messages.reply')) ?></a>
        </div>
        <p class="border-t border-admin-line pt-3 leading-relaxed break-words whitespace-pre-line" data-field="message"></p>
    </article>
</template>
