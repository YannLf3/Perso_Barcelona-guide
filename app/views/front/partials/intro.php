<?php

declare(strict_types=1);

/**
 * Aucune variable requise (les textes passent par t(), qui intègre les modifications faites dans l'administration).
 */
?>
<section class="mx-auto max-w-[1200px] px-8 pt-20 pb-12 section-bleed [--bleed:var(--ui-intro-bg)]" id="intro" aria-labelledby="intro-title">
    <h1 class="mb-6 font-heading text-[clamp(2.5rem,6vw,4.5rem)] font-extrabold leading-[1.05] text-ui-intro-title after:mt-4 after:block after:w-fit after:-rotate-3 after:rounded-[10px] after:border-2 after:border-ui-sticker-border after:bg-ui-sticker-bg after:px-[0.6em] after:py-[0.15em] after:font-heading after:text-[clamp(1.1rem,2.5vw,1.6rem)] after:font-extrabold after:tracking-[0.04em] after:text-ui-sticker-text after:content-[attr(data-sticker)]" id="intro-title" data-sticker="<?= e(t('intro.sticker')) ?>"><span class="text-ui-intro-accent"><?= e(t('intro.title_accent')) ?></span> <?= e(t('intro.title_before')) ?></h1>
    <p class="max-w-[55ch] font-body text-[1rem] leading-[1.7] text-ui-intro-text"><?= e(t('intro.text')) ?></p>
</section>
