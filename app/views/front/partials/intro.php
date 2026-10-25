<?php

declare(strict_types=1);

$sectionClass = 'mt-6 max-w-[60ch]';
$headingClass = 'mb-[0.4rem] font-body text-[1rem] font-extrabold uppercase tracking-[0.08em]';
$textClass = 'max-w-[55ch] font-body text-[1rem] leading-[1.7]';
?>
<section class="mx-auto max-w-[1200px] px-8 pt-20 pb-12" id="intro" aria-labelledby="intro-title">
    <h1 class="mb-4 font-heading text-[clamp(2.5rem,6vw,4.5rem)] font-extrabold leading-[1.05] text-site-ink" id="intro-title"><?= e(t('intro.title_before')) ?> <span class="text-site-accent"><?= e(t('intro.title_accent')) ?></span></h1>
    <div class="<?= e($sectionClass) ?>">
        <h2 class="<?= e($headingClass) ?>"><?= e(t('intro.presentation_title')) ?></h2>
        <p class="<?= e($textClass) ?>"><?= e(t('intro.presentation_text')) ?></p>
    </div>
    <div class="<?= e($sectionClass) ?>">
        <h2 class="<?= e($headingClass) ?>"><?= e(t('intro.mission_title')) ?></h2>
        <p class="<?= e($textClass) ?>"><?= e(t('intro.mission_text')) ?></p>
    </div>
</section>
