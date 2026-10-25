<?php

declare(strict_types=1);

/**
 * Variables attendues : $csrf, $strings (textes utilisés par le JavaScript du back-office).
 * Chaque écran est une section [data-screen] ; le menu bascule de l'une à l'autre via l'ancre de l'URL (#texts, #design...).
 */
$jsonFlags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR;
?>
<a href="#main" class="sr-only z-[100] rounded-lg bg-admin-action px-4 py-2 font-heading font-bold text-admin-on-action focus:not-sr-only focus:fixed focus:top-3 focus:left-3"><?= e(t('admin.skip')) ?></a>
<div class="lg:grid lg:min-h-screen lg:grid-cols-[17.5rem_minmax(0,1fr)]">
    <?= partial('admin/partials/sidebar', ['csrf' => $csrf]) ?>
    <main id="main" class="min-w-0 px-4 pt-6 pb-36 outline-none sm:px-6 lg:px-10 lg:pt-10" data-view="dashboard" tabindex="-1">
        <?= partial('admin/partials/home') ?>
        <?= partial('admin/partials/texts') ?>
        <?= partial('admin/partials/design') ?>
        <?= partial('admin/partials/tours') ?>
        <?= partial('admin/partials/images') ?>
        <?= partial('admin/partials/messages') ?>
    </main>
</div>
<div class="pointer-events-none fixed inset-x-0 bottom-24 z-[70] flex justify-center px-4 lg:left-[17.5rem]">
    <p class="pointer-events-auto max-w-xl rounded-xl px-5 py-3.5 font-semibold shadow-[0_12px_32px_rgba(20,10,0,0.25)] data-[tone=error]:bg-admin-danger data-[tone=error]:text-white data-[tone=success]:bg-admin-success data-[tone=success]:text-white" data-toast role="status" aria-live="polite" hidden></p>
</div>
<?= partial('admin/partials/dialogs') ?>
<script type="application/json" id="admin-strings"><?= json_encode($strings, $jsonFlags) ?></script>
