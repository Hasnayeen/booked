<?php

declare(strict_types=1);

arch()->preset()->php();

arch('strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('no debug functions')
    ->expect('App')
    ->not->toUse([
        'die',
        'dd',
        'dump',
        'ray',
        'debug',
        'debug_backtrace',
    ]);
