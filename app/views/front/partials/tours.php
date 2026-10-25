<?php

declare(strict_types=1);

/**
 * Variables attendues : $tours (array<Tour>), $lang.
 */
$types = ['all', 'private', 'small', 'school'];
?>
<section class="mx-auto max-w-[1200px] px-8 py-16" aria-labelledby="tours-title">
    <h2 class="mb-8 inline-block border-b-[3px] border-site-accent pb-3 font-heading text-[0.75rem] font-extrabold uppercase tracking-[0.2em] text-site-ink" id="tours-title"><?= e(t('tours.title')) ?></h2>
    <div class="mb-6 flex items-center gap-[0.9rem]">
        <label class="font-body text-[0.75rem] font-extrabold uppercase tracking-[0.12em]" for="tour-group-filter"><?= e(t('tours.filter_label')) ?></label>
        <select class="min-h-11 min-w-52 cursor-pointer rounded-none border-0 bg-transparent py-[0.65rem] pr-10 pl-[0.9rem] font-body text-[0.85rem] font-bold leading-[normal] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-site-accent" id="tour-group-filter" data-action="filter-tours">
            <?= implode('', array_map(
                fn (string $type): string => '<option value="' . e($type) . '">' . e(t('tours.filter_' . $type)) . '</option>',
                $types
            )) ?>

        </select>
    </div>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] items-stretch gap-6" id="tour-list">
        <p class="rounded-[16px] border border-dashed border-site-border bg-site-surface p-8 text-center font-body text-[0.9rem] text-umber" data-tour-empty hidden><?= e(t('tours.empty_filter')) ?></p>
        <?php if ($tours === []): ?>
        <p class="rounded-[16px] border border-dashed border-site-border bg-site-surface p-8 text-center font-body text-[0.9rem] text-umber"><?= e(t('tours.empty')) ?></p>
        <?php else: ?>
        <?= implode("\n        ", array_map(fn (Tour $tour): string => partial('front/partials/tour-card', ['tour' => $tour, 'lang' => $lang]), $tours)) ?>

        <?php endif; ?>
    </div>
</section>
