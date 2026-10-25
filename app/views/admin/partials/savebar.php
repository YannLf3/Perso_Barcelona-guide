<?php

declare(strict_types=1);

/**
 * Variables attendues : $name ("texts" ou "design"), $saveLabel.
 * Barre fixée en bas de l'écran, visible seulement quand des changements attendent d'être enregistrés.
 */
?>
<div class="fixed inset-x-0 bottom-0 z-40 border-t border-admin-line bg-admin-surface px-4 py-3 shadow-[0_-10px_30px_rgba(36,22,11,0.1)] sm:px-6 lg:left-[17.5rem] lg:px-10" data-savebar="<?= e($name) ?>" hidden>
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3">
        <p class="font-semibold" data-savebar-text></p>
        <div class="flex flex-wrap gap-2">
<?php if ($name === 'design'): ?>
            <button type="button" class="admin-btn admin-btn-ghost xl:hidden" data-action="preview-scroll"><?= e(t('admin.design.preview')) ?></button>
<?php endif; ?>
            <button type="button" class="admin-btn admin-btn-secondary" data-action="<?= e($name) ?>-cancel"><?= e(t('admin.savebar.cancel')) ?></button>
            <button type="button" class="admin-btn admin-btn-primary" data-action="<?= e($name) ?>-save" data-label="<?= e($saveLabel) ?>"><?= e($saveLabel) ?></button>
        </div>
    </div>
</div>
