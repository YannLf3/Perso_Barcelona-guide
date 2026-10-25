<?php

declare(strict_types=1);

$files = ['asset23.webp', 'asset24.webp', 'asset25.webp', 'asset26.webp', 'asset27.webp'];
$photoAlts = Lang::list('guide.photos');

/**
 * Construit un jeu de diapositives du carrousel. Le second jeu est une copie décorative pour la boucle infinie.
 */
$renderSet = function (bool $isCopy) use ($files, $photoAlts): string {
    return implode('', array_map(function (string $file, int $index) use ($isCopy, $photoAlts): string {
        $dimensions = ImageUploader::dimensions($file);
        $isFirst = $index === 0 && !$isCopy;
        $alt = $isCopy ? '' : (string) ($photoAlts[$index] ?? '');
        $attributes = $isFirst ? 'fetchpriority="high"' : 'loading="lazy"';

        return '<div class="group/slide relative aspect-[3/4] w-[80cqw] flex-[0_0_80cqw] overflow-hidden rounded-[16px] bg-sand-slide data-[broken=true]:bg-[linear-gradient(135deg,#e8dcc8_0%,#d4c5a9_100%)] mobile:aspect-[4/3] mobile:w-[85cqw] mobile:flex-[0_0_85cqw] motion-reduce:snap-start" data-slide'
            . ($isCopy ? '' : ' role="group" aria-label="' . e($alt) . '"') . '>'
            . '<img src="' . e(ImageUploader::publicUrl($file)) . '" alt="' . e($alt) . '"'
            . ($dimensions !== null ? ' width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '"' : '')
            . ' class="block h-full w-full object-cover group-data-[broken=true]/slide:hidden" ' . $attributes . '>'
            . '<div class="absolute right-0 bottom-0 left-0 flex flex-col gap-1 bg-[linear-gradient(to_top,rgba(0,0,0,0.75)_0%,transparent_100%)] px-4 pt-6 pb-5">'
            . '<span class="font-heading text-[0.75rem] font-extrabold tracking-[0.05em] text-white/70">' . sprintf('%02d', $index + 1) . '</span>'
            . '</div></div>';
    }, $files, array_keys($files)));
};
?>
<section class="mx-auto grid max-w-[1200px] grid-cols-2 items-center gap-12 px-8 py-16 mobile:grid-cols-1 mobile:gap-8 mobile:px-4 mobile:py-8" aria-labelledby="guide-title">
    <div>
        <p class="mb-4 inline-block border-b-2 border-gold pb-2 font-heading text-[0.75rem] font-bold uppercase tracking-[0.15em] text-gold-dark"><?= e(t('guide.eyebrow')) ?></p>
        <h2 class="mb-5 font-heading text-[clamp(2rem,4vw,3rem)] font-extrabold leading-[1.1] text-site-ink mobile:text-[1.75rem]" id="guide-title"><?= e(t('guide.title_before')) ?><span class="text-site-accent"><?= e(t('guide.title_accent')) ?></span></h2>
        <p class="mb-6 max-w-[38ch] font-body text-[1rem] leading-[1.7] text-bark"><?= e(t('guide.desc')) ?></p>
        <ul class="mb-8 flex flex-wrap gap-2">
            <?= implode('', array_map(
                fn (string $tag): string => '<li class="whitespace-nowrap rounded-full border-[1.5px] border-site-ink px-[0.9em] py-[0.35em] font-heading text-[0.7rem] font-bold tracking-[0.1em] text-site-ink">' . e($tag) . '</li>',
                Lang::list('guide.tags')
            )) ?>

        </ul>
        <a href="#tour-list" class="inline-block rounded-full bg-site-accent px-[1.75em] py-[0.75em] font-heading text-[0.875rem] font-bold tracking-[0.05em] text-white transition-[background-color,transform] duration-200 ease-[ease] hover:-translate-y-0.5 hover:bg-site-accent-hover focus-visible:-translate-y-0.5 focus-visible:bg-site-accent-hover focus-visible:outline-none"><?= e(t('guide.cta')) ?> &rarr;</a>
    </div>
    <div class="flex flex-col gap-3">
        <p class="font-heading text-[0.7rem] font-bold uppercase tracking-[0.15em] text-gold-dark"><?= e(t('guide.carousel_label')) ?></p>
        <section class="group/carousel @container/guide-carousel overflow-hidden rounded-[16px] outline-offset-4 focus-visible:outline-2 focus-visible:outline-site-accent motion-reduce:snap-x motion-reduce:snap-mandatory motion-reduce:overflow-x-auto motion-reduce:[scrollbar-width:none]" aria-label="<?= e(t('guide.carousel_label')) ?>" tabindex="0">
            <div class="flex w-max animate-carousel will-change-transform group-hover/carousel:[animation-play-state:paused] group-focus-within/carousel:[animation-play-state:paused] motion-reduce:transform-none motion-reduce:animate-none">
                <div class="flex shrink-0 gap-4"><?= $renderSet(false) ?></div>
                <div class="flex shrink-0 gap-4 motion-reduce:hidden" aria-hidden="true"><?= $renderSet(true) ?></div>
            </div>
        </section>
        <p class="text-center font-body text-[0.75rem] text-gold-dark"><?= e(t('guide.carousel_hint')) ?></p>
    </div>
</section>
