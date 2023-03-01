<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Service;

use Composer\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Finder\Finder;

class ComposerService extends BaseService
{
    protected Application $application;
    protected Finder $finder;

    public function __construct()
    {
        $this->application = new Application();
        $this->finder = new Finder();

        parent::__construct();
    }

    public function requireAllCoreExtensions(): int
    {
        $coreExtensions = $this->getCoreExtensions();
        foreach ($coreExtensions as $key => $extension) {
            $coreExtensions[$key] = $extension . ':@dev';
        }

        if (count($coreExtensions)) {
            $input = new ArrayInput(array('command' => 'require', 'packages' => $coreExtensions));
            $this->application->run($input);
        }

        return Command::SUCCESS;
    }

    public function removeAllCoreExtensions(): int
    {
        $coreExtensions = $this->getCoreExtensions();
        if (count($coreExtensions)) {
            $input = new ArrayInput(array('command' => 'remove', 'packages' => $coreExtensions));
            return $this->application->run($input);
        }

        return Command::SUCCESS;
    }

    public function getCoreExtensionsFolder(string $path = 'public/typo3/sysext'): Finder
    {
        return $this->finder->in($path)->depth(0)->directories();
    }

    public function getCoreExtensions(string $path = BaseService::CORE_DEV_FOLDER . '/typo3/sysext/'): array
    {
        $files = $this->finder->name('composer.json')->in($path)->depth(1)->files();

        $coreExtensions = [];
        foreach ($files as $file) {
            $json = json_decode($file->getContents(), true, 512, JSON_THROW_ON_ERROR);
            $coreExtensions[] = $json['name'];
        }

        return $coreExtensions;
    }
}
