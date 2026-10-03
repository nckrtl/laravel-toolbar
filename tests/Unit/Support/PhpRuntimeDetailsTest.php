<?php

declare(strict_types=1);

use NckRtl\Toolbar\CollectorManager;
use NckRtl\Toolbar\Collectors\PhpCollector;

it('reports PHP settings, extensions and no FPM pool outside PHP-FPM', function () {
    $data = (new PhpCollector)->collectData(new CollectorManager)->toArray();

    expect($data['sapi'])->toBe(PHP_SAPI)
        ->and($data['settings'])->toHaveKeys(['post_max_size', 'upload_max_filesize', 'max_input_vars', 'opcache.enable'])
        ->and($data['settings']['post_max_size'])->toBe(ini_get('post_max_size'))
        ->and($data['extensions'])->toContain('Core')
        ->and($data['fpm'])->toBeNull();
});
