<?php

declare(strict_types=1);

/**
 * Variables attendues : $tours (array<Tour>), $lang.
 */
$types = ['all', 'private', 'small', 'school'];
?>
<section class="mx-auto max-w-[1200px] px-8 py-16 section-bleed [--bleed:var(--ui-tours-bg)]" aria-labelledby="tours-title">
    <h2 class="mb-8 inline-block border-b-[3px] border-ui-tours-line pb-3 font-heading text-[0.75rem] font-extrabold uppercase tracking-[0.2em] text-ui-tours-title" id="tours-title"><?= e(t('tours.title')) ?></h2>
    <div class="mb-6 flex flex-wrap gap-2" role="group" aria-label="<?= e(t('tours.filter_label')) ?>">
        <?= implode("\n        ", array_map(
            fn (string $type): string => '<button type="button" class="min-h-11 cursor-pointer rounded-[var(--ui-radius-btn,9999px)] border-[1.5px] border-ui-filter bg-transparent px-[1.1em] py-[0.5em] font-heading text-[0.75rem] font-bold tracking-[0.06em] text-ui-filter transition-colors duration-200 hover:bg-ui-filter/10 aria-pressed:bg-ui-filter aria-pressed:text-ui-filter-active-text focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ui-accent" aria-pressed="' . ($type === 'all' ? 'true' : 'false') . '" data-action="filter-tours" data-filter="' . e($type) . '">' . e(t('tours.filter_' . $type)) . '</button>',
            $types
        )) ?>

    </div>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] items-stretch gap-6" id="tour-list">
        <p class="rounded-[16px] border border-dashed border-ui-card-border bg-ui-card-bg p-8 text-center font-body text-[0.9rem] text-ui-card-text" data-tour-empty hidden><?= e(t('tours.empty_filter')) ?></p>
        <?php if ($tours === []): ?>
        <p class="rounded-[16px] border border-dashed border-ui-card-border bg-ui-card-bg p-8 text-center font-body text-[0.9rem] text-ui-card-text"><?= e(t('tours.empty')) ?></p>
        <?php else: ?>
        <?= implode("\n        ", array_map(fn (Tour $tour): string => partial('front/partials/tour-card', ['tour' => $tour, 'lang' => $lang]), $tours)) ?>

        <?php endif; ?>
    </div>
</section>
