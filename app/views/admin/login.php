<?php

declare(strict_types=1);

/**
 * Variables attendues : $csrf, $logMessages (message d'erreur éventuel, chaîne vide sinon).
 */
$errors = array_values(array_filter($logMessages, fn (string $message): bool => $message !== ''));
?>
<main class="flex min-h-screen items-center justify-center px-4 py-10" data-view="login">
    <div class="w-full max-w-md">
        <p class="mb-6 text-center font-heading text-[1.05rem] font-extrabold tracking-[0.04em] text-admin-muted"><?= e(t('admin.brand')) ?></p>
        <section class="admin-card p-8" aria-labelledby="login-title">
            <h1 class="admin-h1" id="login-title"><?= e(t('admin.login.title')) ?></h1>
            <p class="admin-lead mb-6"><?= e(t('admin.login.intro')) ?></p>
<?php if ($errors !== []): ?>
            <p class="mb-5 rounded-xl bg-admin-danger-bg px-4 py-3 font-semibold text-admin-danger" role="alert"><?= e($errors[0]) ?></p>
<?php endif; ?>
            <form class="grid gap-5" method="post" action="/admin/login">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="username" value="<?= e(Env::get('ADMIN_USER', 'admin')) ?>" autocomplete="username">
                <label class="grid gap-1.5">
                    <span class="admin-label"><?= e(t('admin.login.password')) ?></span>
                    <input class="admin-input" type="password" name="password" required autocomplete="current-password" data-password>
                </label>
                <label class="flex cursor-pointer items-center gap-2.5">
                    <input class="size-5 accent-admin-action" type="checkbox" data-action="toggle-password">
                    <span><?= e(t('admin.login.show_password')) ?></span>
                </label>
                <button class="admin-btn admin-btn-primary w-full" type="submit"><?= e(t('admin.login.submit')) ?></button>
            </form>
        </section>
        <p class="mt-6 text-center"><a class="admin-link" href="/"><?= e(t('admin.login.back')) ?></a></p>
    </div>
</main>
