<?php

// phpcs:disable

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

echo 'PHPUnit is booting from bootstrap...';
echo PHP_EOL;

\DG\BypassFinals::enable();

// phpcs:enable
