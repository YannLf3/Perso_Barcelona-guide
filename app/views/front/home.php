<?php

declare(strict_types=1);

/**
 * Variables attendues : $lang, $tours, $csrf.
 */
?>
<div class="cursor-glow" aria-hidden="true"></div>
<?= partial('front/partials/navbar', ['lang' => $lang]) ?>
<main id="main">
    <?= partial('front/partials/intro') ?>
    <?= partial('front/partials/guide') ?>
    <?= partial('front/partials/tours', ['tours' => $tours, 'lang' => $lang]) ?>
    <?= partial('front/partials/contact', ['lang' => $lang]) ?>
</main>
<?= partial('front/partials/tour-modal') ?>
