<?php

declare(strict_types=1);

?>
<template id="tour-modal-template" data-persons="<?= e(t('tours.persons')) ?>">
    <dialog class="fixed inset-0 m-auto h-[min(800px,calc(100vh-4rem))] max-h-[calc(100vh-4rem)] w-[min(920px,calc(100vw-2rem))] overflow-hidden rounded-[20px] border-0 bg-ui-modal-bg p-0 shadow-[0_24px_80px_rgba(26,15,0,0.28)] backdrop:bg-[rgba(26,15,0,0.55)] backdrop:backdrop-blur-[4px] mobile:inset-[auto_0_0_0] mobile:m-0 mobile:h-[90vh] mobile:max-h-[90vh] mobile:w-screen mobile:max-w-[100vw] mobile:animate-modal-slide-up mobile:rounded-[24px_24px_0_0]" aria-labelledby="tour-modal-title">
        <button type="button" class="absolute top-3 right-3 z-[2] flex font-[family-name:Arial] size-10 cursor-pointer items-center justify-center rounded-full border-0 bg-white/95 text-[1.5rem] leading-none text-site-ink transition-[background-color,transform] duration-200 ease-[ease] hover:scale-105 hover:bg-white focus-visible:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-site-accent" data-modal-close aria-label="<?= e(t('modal.close')) ?>">
            <span aria-hidden="true">&times;</span>
        </button>
        <div class="grid h-full min-h-0 grid-cols-2 mobile:flex mobile:h-full mobile:max-h-full mobile:flex-col">
            <figure class="m-0 max-h-[calc(100vh-2rem)] min-h-[280px] overflow-hidden bg-sand mobile:h-[30vh] mobile:max-h-60 mobile:min-h-[200px] mobile:shrink-0" data-modal-media>
                <img class="block h-full max-h-[calc(100vh-2rem)] min-h-[280px] w-full object-cover mobile:h-full mobile:max-h-60 mobile:min-h-[200px]" data-modal-image src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt="">
            </figure>
            <div class="m-0 flex max-h-[calc(100vh-2rem)] min-h-[280px] items-center justify-center overflow-hidden bg-[linear-gradient(135deg,#f0e6d3_0%,#e2d0b5_100%)] p-8 mobile:h-[30vh] mobile:max-h-60 mobile:min-h-[200px] mobile:shrink-0" data-modal-placeholder hidden>
                <p class="text-center font-body text-[0.9rem] text-ui-muted"><?= e(t('modal.tour_no_image')) ?></p>
            </div>
            <div class="flex min-h-0 min-w-0 flex-col gap-4 overflow-y-auto p-8 [scrollbar-color:#d4780a_#f0e6d3] [scrollbar-width:thin] mobile:flex-1 mobile:px-6 mobile:pt-6 mobile:pb-10">
                <h2 class="pr-10 font-heading text-[clamp(1.35rem,3vw,1.75rem)] font-extrabold leading-[1.2] text-ui-modal-title" id="tour-modal-title" data-modal-title></h2>
                <dl class="flex gap-6">
                    <div class="flex flex-col gap-[0.15rem]">
                        <dt class="font-heading text-[0.65rem] font-bold uppercase tracking-[0.1em] text-ui-card-label"><?= e(t('tours.duration')) ?></dt>
                        <dd class="font-heading text-[1rem] font-bold text-ui-card-value" data-modal-duration></dd>
                    </div>
                    <div class="flex flex-col gap-[0.15rem]">
                        <dt class="font-heading text-[0.65rem] font-bold uppercase tracking-[0.1em] text-ui-card-label"><?= e(t('tours.capacity')) ?></dt>
                        <dd class="font-heading text-[1rem] font-bold text-ui-card-value" data-modal-capacity></dd>
                    </div>
                </dl>
                <p class="flex-1 font-body text-[0.95rem] leading-[1.75] text-ui-modal-text" data-modal-summary></p>
                <a href="#contact-form" class="inline-block self-start rounded-[var(--ui-radius-btn,0.5rem)] bg-ui-card-btn-bg px-[1.4em] py-[0.7em] font-heading text-[0.85rem] font-bold tracking-[0.05em] text-ui-card-btn-text transition-colors duration-200 ease-[ease] hover:bg-ui-card-btn-hover focus-visible:bg-ui-card-btn-hover focus-visible:outline-none" data-modal-book><?= e(t('modal.tour_book')) ?></a>
            </div>
        </div>
    </dialog>
</template>
