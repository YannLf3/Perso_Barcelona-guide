<?php

declare(strict_types=1);

?>
<template id="images-template" data-no-image="<?= e(t('admin.tours.no_image')) ?>">
    <section class="grid gap-8 px-8 py-7">
        <h2 class="admin-title"><?= e(t('admin.images.title')) ?></h2>

        <form data-form="image-upload">
            <label>
                <?= e(t('admin.images.upload_label')) ?>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
            </label>

            <button type="submit"><?= e(t('admin.images.upload_submit')) ?></button>
        </form>

        <div class="admin-grid p-6" data-gallery></div>
    </section>
</template>

<template id="image-item-template">
    <figure class="m-0 min-w-0 overflow-hidden rounded-2xl border border-line bg-surface shadow-[0_1px_1px_rgba(36,22,11,0.06)]">
        <img class="block aspect-[4/3] w-full object-cover" loading="lazy" alt="">
        <figcaption class="overflow-hidden p-[0.65rem] font-body text-[0.75rem] text-ellipsis whitespace-nowrap text-muted"></figcaption>
    </figure>
</template>
