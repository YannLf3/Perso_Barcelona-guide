<?php

declare(strict_types=1);

/**
 * Variables attendues : $lang.
 */
$blockClass = 'border-t border-line py-6';
$titleClass = 'mb-[0.8rem] text-[1.35rem] font-bold text-accent-700';
$textClass = 'text-muted [&+p]:mt-[0.35rem]';
?>
<header class="bg-site-band px-[max(1rem,calc((100%-72rem)/2))] py-[1.1rem] mobile:px-3">
    <a class="font-heading text-[0.95rem] font-extrabold uppercase tracking-[0.1em] text-white/90 mobile:text-[0.8rem]" href="/<?= e($lang) ?>/"><?= e(t('footer.copyright')) ?></a>
</header>
<main class="mx-auto w-[min(100%-2rem,48rem)] flex-1 py-[clamp(2rem,6vw,5rem)] mobile:w-[min(100%-1.5rem,48rem)]" id="main">
    <a class="mb-12 inline-block font-heading text-[0.85rem] font-bold text-accent-700 hover:text-accent-600 focus-visible:text-accent-600" href="/<?= e($lang) ?>/">&larr; <?= e(t('legal.back')) ?></a>
    <p class="mb-[0.65rem] font-heading text-[0.75rem] font-extrabold uppercase tracking-[0.16em] text-accent-700"><?= e(t('legal.eyebrow')) ?></p>
    <h1 class="mb-[clamp(2rem,5vw,3.5rem)] text-[clamp(2.5rem,7vw,4.5rem)] font-bold leading-none"><?= e(t('legal.title')) ?></h1>

    <section class="<?= e($blockClass) ?>" aria-labelledby="creation-heading">
        <h2 class="<?= e($titleClass) ?>" id="creation-heading"><?= e(t('legal.creation_title')) ?></h2>
        <p class="<?= e($textClass) ?>"><?= e(t('legal.creation_text')) ?></p>
        <p class="<?= e($textClass) ?>"><?= e(t('legal.creation_name')) ?></p>
        <p class="<?= e($textClass) ?>"><?= e(t('legal.creation_contact')) ?> <a class="text-accent-700 underline underline-offset-[0.2em]" href="mailto:yann.dev-web@ikmail.com">yann.dev-web@ikmail.com</a></p>
    </section>

    <section class="<?= e($blockClass) ?>" aria-labelledby="hosting-heading">
        <h2 class="<?= e($titleClass) ?>" id="hosting-heading"><?= e(t('legal.hosting_title')) ?></h2>
        <p class="<?= e($textClass) ?>"><?= e(t('legal.hosting_text')) ?></p>
    </section>

    <section class="<?= e($blockClass) ?>" aria-labelledby="client-heading">
        <h2 class="<?= e($titleClass) ?>" id="client-heading"><?= e(t('legal.client_title')) ?></h2>
        <p class="<?= e($textClass) ?>"><?= e(t('legal.client_text')) ?></p>
    </section>
</main>
