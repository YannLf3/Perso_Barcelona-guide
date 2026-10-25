<?php

declare(strict_types=1);

/**
 * Variables attendues : $logMessages (array<string>).
 */
?>
<section class="bg-contrast px-6 py-[0.9rem] font-body text-[0.82rem] leading-[1.6] text-white/85 border-b-[3px] border-brand" data-log>
    <?= implode("\n    ", array_map(
        fn (string $message): string => '<p class="my-1 first:mt-0 last:mb-0">' . e($message) . '</p>',
        $logMessages
    )) ?>

</section>
