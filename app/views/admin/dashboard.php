<?php

declare(strict_types=1);

/**
 * Variables attendues : $csrf, $logMessages.
 */
?>
<?= partial('admin/partials/topbar') ?>
<main class="mx-auto grid max-w-[80rem] gap-6 px-6 pt-8 pb-16 mobile:gap-4 mobile:px-3 mobile:pt-4 mobile:pb-10" data-view="dashboard">
    <section class="admin-panel" id="log"><?= partial('admin/partials/log', ['logMessages' => $logMessages]) ?></section>
    <section class="admin-panel [animation-delay:0.05s]" id="tourForm"></section>
    <section class="admin-panel [animation-delay:0.15s]" id="imageGallery"></section>
    <section class="admin-panel" id="messageList"></section>
</main>
<div class="mx-auto flex max-w-[80rem] justify-end px-6 pb-12 mobile:px-3">
    <form method="post" action="/admin/logout">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button class="inline-flex cursor-pointer items-center rounded-full border border-line bg-surface px-[0.85rem] py-[0.4rem] font-heading text-[0.72rem] font-bold tracking-[0.08em] text-muted transition-[background,border-color] duration-160 ease-[ease] hover:border-brand" type="submit"><?= e(t('admin.logout')) ?></button>
    </form>
</div>
<?= partial('admin/partials/tours') ?>
<?= partial('admin/partials/images') ?>
<?= partial('admin/partials/messages') ?>
