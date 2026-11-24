<?php

declare(strict_types=1);

/**
 * Photo d'accueil encadrée (elle n'occupe plus tout l'écran) : titre au centre, un bouton à cheval sur chaque coin.
 * Haut gauche : votre guide. Haut droite : ma conception d'une visite. Bas gauche : visites. Bas droite : types de groupes.
 * Aucune variable requise (les textes passent par t(), qui intègre les modifications faites dans l'administration).
 */
$photo = Design::heroPhoto();
$dimensions = ImageUploader::dimensions($photo);
$corner = 'absolute z-[2] inline-flex min-h-11 cursor-pointer items-center rounded-[var(--ui-radius-btn,9999px)] border-0 bg-ui-hero-btn-bg px-[1.3em] py-[0.7em] text-center font-heading text-[0.8rem] font-bold uppercase leading-[1.2] tracking-[0.12em] text-ui-hero-btn-text shadow-[0_6px_20px_rgba(0,0,0,0.18)] transition-[background-color,transform] duration-200 ease-[ease] hover:-translate-y-0.5 hover:bg-ui-hero-btn-hover focus-visible:-translate-y-0.5 focus-visible:bg-ui-hero-btn-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-site-accent mobile:max-w-[46%] mobile:px-[0.9em] mobile:text-[0.68rem] mobile:tracking-[0.06em]';
$corners = [
    ['class' => '-top-5 left-4 md:left-8', 'attributes' => 'data-action="open-panel" data-panel="guide" aria-haspopup="dialog"', 'label' => t('guide.eyebrow')],
    ['class' => '-top-5 right-4 md:right-8', 'attributes' => 'data-action="open-panel" data-panel="concept" aria-haspopup="dialog"', 'label' => t('guide.title_before') . t('guide.title_accent')],
    ['class' => '-bottom-5 left-4 md:left-8', 'attributes' => 'data-action="show-tours"', 'label' => t('intro.btn_tours')],
    ['class' => '-bottom-5 right-4 md:right-8', 'attributes' => 'data-action="open-panel" data-panel="groups" aria-haspopup="dialog"', 'label' => t('intro.btn_groups')],
];
?>
<section class="px-4 py-14 md:px-8 md:py-16" id="intro" aria-labelledby="intro-title">
    <div class="relative mx-auto w-full max-w-[1100px]">
        <div class="relative isolate flex aspect-[999/419] items-center justify-center overflow-hidden rounded-[20px] bg-sand mobile:aspect-[4/3]">
            <img class="absolute inset-0 -z-20 size-full object-cover mobile:object-[22%_50%]" src="<?= e(ImageUploader::publicUrl($photo)) ?>" alt="<?= e(t('intro.photo_alt')) ?>"<?= $dimensions !== null ? ' width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '"' : '' ?> fetchpriority="high" data-hero-photo>
            <div class="absolute inset-0 -z-10 bg-ui-hero-overlay/25" aria-hidden="true"></div>
            <h1 class="mx-auto max-w-[900px] px-6 text-center font-heading text-[clamp(1.6rem,4.5vw,3.5rem)] font-extrabold leading-[1.05] text-ui-intro-title [text-shadow:0_2px_24px_rgba(0,0,0,0.35)] after:mx-auto after:mt-3 after:block after:w-fit after:-rotate-3 after:rounded-[10px] after:bg-ui-sticker-bg after:px-[0.6em] after:py-[0.15em] after:font-heading after:text-[clamp(0.9rem,2vw,1.3rem)] after:font-extrabold after:tracking-[0.04em] after:text-ui-sticker-text after:content-[attr(data-sticker)] after:[text-shadow:none]" id="intro-title" data-sticker="<?= e(t('intro.sticker')) ?>"><span class="text-ui-intro-accent"><?= e(t('intro.title_accent')) ?></span> <?= e(t('intro.title_before')) ?></h1>
        </div>
        <?= implode("\n        ", array_map(
            fn (array $item): string => '<button type="button" class="' . e($corner . ' ' . $item['class']) . '" ' . $item['attributes'] . '>' . e($item['label']) . '</button>',
            $corners
        )) ?>

    </div>
</section>
