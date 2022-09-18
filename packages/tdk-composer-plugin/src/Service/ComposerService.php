<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Service;

use Composer\Util\ProcessExecutor;
use Symfony\Component\Finder\Finder;

class ComposerService extends BaseService
{
    public function addRepository(): string
    {
        $process = new ProcessExecutor();
        $command = 'composer config repositories.typo3-core-packages path "typo3-core/typo3/sysext/*"';
        $process->execute($command, $output);

        return $output;
    }
}
