<?php

declare(strict_types=1);

?>
<template id="messages-template" data-empty="<?= e(t('admin.messages.empty')) ?>">
    <section class="grid gap-4 px-8 py-7">
        <h2 class="admin-title mb-2"><?= e(t('admin.messages.title')) ?></h2>
        <div data-message-list></div>
    </section>
</template>

<template id="message-template">
    <article class="grid gap-2 rounded-[1.25rem] border border-l-4 border-line border-l-brand bg-surface px-6 py-5 shadow-[0_1px_1px_rgba(36,22,11,0.06)] transition-[box-shadow,transform] duration-160 ease-[ease] hover:-translate-y-0.5 hover:shadow-surface">
        <h3 class="font-heading text-[1rem] font-bold text-ink" data-field="fullname"></h3>
        <div class="flex flex-wrap gap-x-6 gap-y-2">
            <p class="font-body text-[0.8rem] leading-[1.6] text-muted"><strong class="font-semibold text-ink"><?= e(t('admin.messages.email')) ?></strong> <span data-field="email"></span></p>
            <p class="font-body text-[0.8rem] leading-[1.6] text-muted"><strong class="font-semibold text-ink"><?= e(t('admin.messages.date')) ?></strong> <span data-field="created_at"></span></p>
        </div>
        <div class="border-t border-line-muted pt-2">
            <p class="font-body text-[0.875rem] leading-[1.6] text-muted" data-field="message"></p>
        </div>
    </article>
</template>
