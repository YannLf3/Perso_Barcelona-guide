<?php

declare(strict_types=1);

/**
 * Variables attendues : $tour (Tour), $lang.
 */
$src = ImageUploader::publicUrl($tour->getImageUrl());
$dimensions = ImageUploader::dimensions($tour->getImageUrl());
$capacity = $tour->getCapacity() !== '' ? $tour->getCapacity() . ' ' . t('tours.persons') : '-';
?>
<article class="card-surface group/card cursor-pointer" tabindex="0" aria-label="<?= e(t('tours.view_details') . ': ' . $tour->getTitle()) ?>" data-card="tour" data-tour-id="<?= e((string) $tour->getId()) ?>" data-group-type="<?= e($tour->getGroupType()) ?>">
    <?php if ($src !== ''): ?>
    <div class="aspect-video w-full shrink-0 overflow-hidden bg-sand">
        <img src="<?= e($src) ?>" alt="<?= e($tour->getTitle()) ?>"<?= $dimensions !== null ? ' width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '"' : '' ?> class="block h-full w-full object-cover transition-transform duration-[400ms] ease-[ease] group-hover/card:scale-[1.04]" loading="lazy">
    </div>
    <?php else: ?>
    <div class="relative aspect-video w-full shrink-0 overflow-hidden bg-[linear-gradient(135deg,#f0e6d3_0%,#e2d0b5_100%)] after:absolute after:inset-0 after:flex after:items-center after:justify-center after:text-[2rem] after:opacity-30 after:content-['📍']" aria-hidden="true"></div>
    <?php endif; ?>
    <div class="flex flex-1 flex-col gap-2 px-5 pt-5 pb-3">
        <h3 class="font-heading text-[1rem] font-bold leading-[1.3] text-ui-card-title"><?= e($tour->getTitle()) ?></h3>
        <p class="line-clamp-3 font-body text-[0.85rem] leading-[1.6] text-ui-card-text"><?= e($tour->getTagline()) ?></p>
    </div>
    <div class="flex flex-col gap-3 border-t border-ui-card-border px-5 pt-3 pb-5">
        <div class="flex justify-between gap-2">
            <span class="flex flex-col gap-[0.1rem]"><span class="font-heading text-[0.65rem] font-bold uppercase tracking-[0.1em] text-ui-card-label"><?= e(t('tours.duration')) ?></span> <span class="font-heading text-[0.9rem] font-bold text-ui-card-value"><?= e($tour->getDuration()) ?></span></span>
            <span class="flex flex-col gap-[0.1rem]"><span class="font-heading text-[0.65rem] font-bold uppercase tracking-[0.1em] text-ui-card-label"><?= e(t('tours.capacity')) ?></span> <span class="font-heading text-[0.9rem] font-bold text-ui-card-value"><?= e($capacity) ?></span></span>
        </div>
        <a href="#contact-form" class="relative z-[1] block rounded-[var(--ui-radius-btn,0.5rem)] bg-ui-card-btn-bg px-[1em] py-[0.6em] text-center font-heading text-[0.8rem] font-bold tracking-[0.05em] text-ui-card-btn-text transition-colors duration-200 ease-[ease] hover:bg-ui-card-btn-hover focus-visible:bg-ui-card-btn-hover focus-visible:outline-none" data-action="book-tour"><?= e(t('tours.book')) ?></a>
    </div>
</article>
