<?php

test('Not debugging statements are left in our code.')
    ->expect(['dd', 'ddd', 'dump', 'ray', 'die', 'var_dump', 'print_r'])
    ->each->not->toBeUsed();
