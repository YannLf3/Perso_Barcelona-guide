<?php

declare(strict_types=1);

/**
 * Variables attendues : $lang.
 */
$fieldClass = 'flex flex-col gap-[0.4rem]';
$labelClass = 'font-heading text-[0.75rem] font-bold uppercase tracking-[0.08em] text-white/60';
$perks = [
    ['icon' => '⚡', 'text' => t('contact.perk_reply')],
    ['icon' => '🗺️', 'text' => t('contact.perk_custom')],
    ['icon' => '🌍', 'text' => t('contact.perk_languages')],
];
?>
<div id="contact-form">
    <section class="bg-site-band px-8 py-20 mobile:px-4 mobile:py-12" aria-labelledby="contact-heading">
        <div class="mx-auto grid max-w-[1100px] grid-cols-2 items-start gap-16 mobile:grid-cols-1 mobile:gap-10">
            <div>
                <p class="mb-4 font-heading text-[0.7rem] font-bold uppercase tracking-[0.2em] text-site-accent"><?= e(t('contact.eyebrow')) ?></p>
                <h2 class="mb-5 font-heading text-[clamp(1.75rem,3vw,2.5rem)] font-extrabold leading-[1.15] text-white" id="contact-heading"><?= e(t('contact.title_before')) ?><span class="text-site-accent"><?= e(t('contact.title_accent')) ?></span></h2>
                <p class="mb-8 font-body text-[0.9rem] leading-[1.7] text-white/65"><?= e(t('contact.subtitle')) ?></p>
                <ul class="flex flex-col gap-3" aria-label="<?= e(t('a11y.perks')) ?>">
                    <?= implode("\n                    ", array_map(
                        fn (array $perk): string => '<li class="flex items-center gap-3 font-body text-[0.875rem] text-white/70"><span class="shrink-0 text-[1.1rem]" aria-hidden="true">' . $perk['icon'] . '</span><span>' . e($perk['text']) . '</span></li>',
                        $perks
                    )) ?>

                </ul>
            </div>
            <div class="rounded-[20px] border border-white/10 bg-white/5 p-8 backdrop-blur-[8px]">
                <form class="flex flex-col gap-5" method="post" novalidate data-form="contact" data-error="<?= e(t('api.message_error')) ?>">
                    <input type="hidden" name="lang" value="<?= e($lang) ?>">
                    <div class="sr-only" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input id="contact-website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="<?= e($fieldClass) ?>">
                        <label class="<?= e($labelClass) ?>" for="contact-name"><?= e(t('contact.name_label')) ?></label>
                        <input class="field-contact" id="contact-name" type="text" name="fullname" placeholder="<?= e(t('contact.name_placeholder')) ?>" required autocomplete="name" maxlength="<?= Message::MAX_NAME ?>">
                    </div>
                    <div class="<?= e($fieldClass) ?>">
                        <label class="<?= e($labelClass) ?>" for="contact-email"><?= e(t('contact.email_label')) ?></label>
                        <input class="field-contact" id="contact-email" type="email" name="email" placeholder="<?= e(t('contact.email_placeholder')) ?>" required autocomplete="email" maxlength="<?= Message::MAX_EMAIL ?>">
                    </div>
                    <div class="<?= e($fieldClass) ?>">
                        <label class="<?= e($labelClass) ?>" for="contact-message"><?= e(t('contact.message_label')) ?></label>
                        <textarea class="field-contact min-h-[120px] resize-y" id="contact-message" name="message" placeholder="<?= e(t('contact.message_placeholder')) ?>" rows="5" required maxlength="<?= Message::MAX_MESSAGE ?>"></textarea>
                    </div>
                    <button type="submit" class="cursor-pointer self-start rounded-[10px] border-0 bg-site-accent px-[1.5em] py-[0.85em] font-heading text-[0.9rem] font-bold leading-[normal] tracking-[0.05em] text-white transition-[background-color,transform] duration-200 ease-[ease] hover:-translate-y-0.5 hover:bg-site-accent-hover focus-visible:-translate-y-0.5 focus-visible:bg-site-accent-hover focus-visible:outline-none"><?= e(t('contact.submit')) ?> &rarr;</button>
                    <div id="message-box" class="min-h-0 rounded-lg p-0 font-body text-[0.875rem] transition-[padding,min-height] duration-200 [&:not(:empty)]:px-4 [&:not(:empty)]:py-3 data-[state=error]:border data-[state=error]:border-[rgba(220,60,60,0.3)] data-[state=error]:bg-[rgba(220,60,60,0.15)] data-[state=error]:text-[#ff9090] data-[state=success]:border data-[state=success]:border-[rgba(40,180,80,0.3)] data-[state=success]:bg-[rgba(40,180,80,0.15)] data-[state=success]:text-[#6afa9e]" role="alert" aria-live="polite"></div>
                </form>
            </div>
        </div>
    </section>
</div>
