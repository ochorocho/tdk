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

    public function summary(): string
    {
        $coreFolder = $this->coreDevFolder;
        return <<<EOF

💡For more Details read the docs:
* Setting up Gerrit (ssh):
  https://docs.typo3.org/m/typo3/guide-contributionworkflow/master/en-us/Account/GerritAccount.html
* Git Setup:
  https://docs.typo3.org/m/typo3/guide-contributionworkflow/master/en-us/Setup/Git/Index.html
* Setup your IDE:
  https://docs.typo3.org/m/typo3/guide-contributionworkflow/master/en-us/Setup/SetupIde.html
* runTests.sh docs still apply, but don't forget to cd into '$coreFolder':
  https://docs.typo3.org/m/typo3/guide-contributionworkflow/master/en-us/Testing/Index.html

<fg=yellow;options=bold>To be able to push to Gerrit, you need to add your public key, see https://review.typo3.org/settings/#SSHKeys</>
EOF;
    }

    public function done(): string
    {
        return '<info>🎉 Happy days ... TYPO3 Composer CoreDev Setup done!</info>';
    }
}
