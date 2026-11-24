<?php

declare(strict_types=1);

/**
 * Fenêtres ouvertes par les boutons de la grande photo : types de groupes, votre guide, ma conception d'une visite.
 * Chaque <template> est cloné par le JavaScript (V.openPanel). Aucune variable requise.
 */
$dialog = 'fixed inset-0 m-auto h-fit max-h-[calc(100vh-4rem)] w-[min(36rem,calc(100vw-2rem))] overflow-y-auto rounded-[20px] border-0 bg-ui-modal-bg p-8 shadow-[0_24px_80px_rgba(26,15,0,0.28)] backdrop:bg-[rgba(26,15,0,0.55)] backdrop:backdrop-blur-[4px] mobile:inset-[auto_0_0_0] mobile:m-0 mobile:max-h-[90vh] mobile:w-screen mobile:max-w-[100vw] mobile:animate-modal-slide-up mobile:rounded-[24px_24px_0_0] mobile:px-6 mobile:pt-6 mobile:pb-10';
$close = '<button type="button" class="absolute top-3 right-3 flex font-[family-name:Arial] size-10 cursor-pointer items-center justify-center rounded-full border-0 bg-black/5 text-[1.5rem] leading-none text-site-ink transition-[background-color,transform] duration-200 ease-[ease] hover:scale-105 hover:bg-black/10 focus-visible:bg-black/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-site-accent" data-modal-close aria-label="' . e(t('modal.close')) . '"><span aria-hidden="true">&times;</span></button>';
$titleClass = 'mb-5 pr-10 font-heading text-[clamp(1.35rem,3vw,1.75rem)] font-extrabold leading-[1.2] text-ui-modal-title';
$textClass = 'font-body text-[1.08rem] leading-[1.75] text-ui-modal-text';
$types = ['private', 'small', 'school'];
// Photos de la fenêtre « Votre guide » : la guide puis sa carte officielle, affichées seulement si le fichier existe dans assets/images
$guidePhotos = array_values(array_filter(
    [
        ['file' => 'guide-anne.webp', 'alt' => t('guide.photo_portrait_alt')],
        ['file' => 'guide-carte.webp', 'alt' => t('guide.photo_card_alt')],
    ],
    fn (array $photo): bool => ImageUploader::exists($photo['file'])
));
$guidePhotosHtml = implode('', array_map(
    function (array $photo): string {
        $dimensions = ImageUploader::dimensions($photo['file']);

        return '<img class="h-28 w-auto rounded-[10px] object-cover shadow-[0_4px_14px_rgba(26,15,0,0.15)] mobile:h-24" src="' . e(ImageUploader::publicUrl($photo['file'])) . '" alt="' . e($photo['alt']) . '"'
            . ($dimensions !== null ? ' width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '"' : '') . ' loading="lazy">';
    },
    $guidePhotos
));
$panels = [
    'groups' => '<h2 class="' . $titleClass . '" id="panel-groups-title">' . e(t('intro.groups_title')) . '</h2>'
        . '<dl class="grid gap-5">'
        . implode('', array_map(
            fn (string $type): string => '<div><dt class="font-heading text-[1.05rem] font-bold text-ui-modal-tagline">' . e(t('tours.filter_' . $type)) . '</dt><dd class="mt-1 ' . $textClass . '">' . e(t('tours.desc_' . $type)) . '</dd></div>',
            $types
        ))
        . '</dl>',
    'guide' => ($guidePhotosHtml !== ''
            ? '<div class="mb-5 flex flex-wrap items-start justify-between gap-4 pr-10"><h2 class="font-heading text-[clamp(1.35rem,3vw,1.75rem)] font-extrabold leading-[1.2] text-ui-modal-title" id="panel-guide-title">' . e(t('guide.eyebrow')) . '</h2><div class="flex gap-3">' . $guidePhotosHtml . '</div></div>'
            : '<h2 class="' . $titleClass . '" id="panel-guide-title">' . e(t('guide.eyebrow')) . '</h2>')
        . paragraphs(t('intro.text'), $textClass),
    'concept' => '<h2 class="' . $titleClass . '" id="panel-concept-title">' . e(t('guide.title_before')) . '<span class="text-ui-modal-tagline">' . e(t('guide.title_accent')) . '</span></h2>'
        . paragraphs(t('guide.desc'), $textClass),
];
?>
<?= implode("\n", array_map(
    fn (string $name, string $content): string => '<template id="panel-' . e($name) . '-template"><dialog class="' . e($dialog) . '" aria-labelledby="panel-' . e($name) . '-title">' . $close . $content . '</dialog></template>',
    array_keys($panels),
    $panels
)) ?>

