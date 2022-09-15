<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Service;

use Symfony\Component\Filesystem\Filesystem;

abstract class BaseService
{
    protected Filesystem $filesystem;
    protected string $coreDevFolder = 'typo3-core';

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }
}
