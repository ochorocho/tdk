<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Service;

use Symfony\Component\Finder\Finder;

class HookService extends BaseService
{
    public function delete(array $files): void
    {
        $filePaths = array_map(function ($value) {
            return BaseService::CORE_DEV_FOLDER . '/.git/hooks/' . $value;
        }, $files);

        $this->filesystem->remove($filePaths);
    }

    public function create(string $fileName): void
    {
        $finder = new Finder();
        $hookTarget = BaseService::CORE_DEV_FOLDER . '/.git/hooks/' . $fileName;
        $files = $finder->name($fileName)->in(BaseService::CORE_DEV_FOLDER . '/Build/git-hooks/')->files();
        foreach ($files as $file) {
            $this->filesystem->copy($file->getPath() . '/' . $file->getFilename(), $hookTarget);
        }

        if (!is_executable($hookTarget)) {
            $this->filesystem->chmod($hookTarget, 0755);
        }
    }

    public function exists($hook): bool
    {
        $hookTarget = BaseService::CORE_DEV_FOLDER . '/.git/hooks/' . $hook;
        return $this->filesystem->exists($hookTarget);
    }
}
