<?php

declare(strict_types=1);

/**
 * Variables attendues : $csrf, $logMessages.
 */
?>
<?= partial('admin/partials/topbar') ?>
<main class="mx-auto grid max-w-[80rem] gap-6 px-6 pt-8 pb-16 mobile:gap-4 mobile:px-3 mobile:pt-4 mobile:pb-10" data-view="login">
    <section class="admin-panel" id="log"><?= partial('admin/partials/log', ['logMessages' => $logMessages]) ?></section>
    <section class="admin-panel flex items-center justify-center px-6 py-12 [animation-delay:0.05s]" id="loginForm">
        <form class="flex w-full max-w-[26rem] flex-col gap-5" method="post" action="/admin/login">
            <h2 class="font-heading text-[clamp(1.4rem,3vw,1.9rem)] font-extrabold leading-[1.2] text-ink"><?= e(t('admin.login.title')) ?> <span class="text-brand"><?= e(t('admin.login.title_accent')) ?></span></h2>
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="username" value="<?= e(Env::get('ADMIN_USER', 'admin')) ?>" autocomplete="username">
            <label class="flex flex-col gap-[0.4rem] font-heading text-[0.72rem] font-bold uppercase tracking-[0.1em] text-muted">
                <?= e(t('admin.login.password')) ?>
                <input class="box-border w-full rounded-2xl border-[1.5px] border-line bg-surface-muted px-4 py-3 font-body text-[0.9rem] normal-case tracking-normal text-ink outline-none transition-[border-color] duration-160 ease-[ease] focus:border-brand focus:bg-white" type="password" name="password" placeholder="<?= e(t('admin.login.placeholder')) ?>" required autocomplete="current-password">
            </label>
            <button class="cursor-pointer self-start rounded-2xl border-0 bg-accent-600 px-[1.75em] py-[0.75em] font-heading text-[0.875rem] font-bold tracking-[0.05em] text-white transition-[background,transform] duration-160 ease-[ease] hover:-translate-y-0.5 hover:bg-brand-hover focus-visible:-translate-y-0.5 focus-visible:bg-brand-hover focus-visible:outline-none" type="submit"><?= e(t('admin.login.submit')) ?></button>
        </form>
    </section>
</main>
