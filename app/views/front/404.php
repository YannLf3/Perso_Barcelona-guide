<?php

declare(strict_types=1);

/**
 * Variables attendues : $lang.
 */
?>
<header class="bg-site-band px-[max(1rem,calc((100%-72rem)/2))] py-[1.1rem] mobile:px-3">
    <a class="font-heading text-[0.95rem] font-extrabold uppercase tracking-[0.1em] text-white/90 mobile:text-[0.8rem]" href="/<?= e($lang) ?>/"><?= e(t('footer.copyright')) ?></a>
</header>
<main class="mx-auto w-[min(100%-2rem,48rem)] flex-1 py-[clamp(2rem,6vw,5rem)]" id="main">
    <p class="mb-[0.65rem] font-heading text-[0.75rem] font-extrabold uppercase tracking-[0.16em] text-site-accent">404</p>
    <h1 class="mb-6 font-heading text-[clamp(2.5rem,7vw,4.5rem)] font-extrabold leading-[1.05] text-site-ink"><?= e(t('notfound.title')) ?></h1>
    <p class="mb-8 max-w-[55ch] font-body text-[1rem] leading-[1.7] text-umber"><?= e(t('notfound.text')) ?></p>
    <a class="inline-block rounded-full bg-site-accent px-[1.75em] py-[0.75em] font-heading text-[0.875rem] font-bold tracking-[0.05em] text-white transition-[background-color,transform] duration-200 ease-[ease] hover:-translate-y-0.5 hover:bg-site-accent-hover focus-visible:-translate-y-0.5 focus-visible:bg-site-accent-hover focus-visible:outline-none" href="/<?= e($lang) ?>/">&larr; <?= e(t('notfound.back')) ?></a>
</main>
