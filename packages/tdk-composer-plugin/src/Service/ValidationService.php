<?php

declare(strict_types=1);

namespace Ochorocho\TdkComposer\Service;

use Composer\Composer;
use Composer\Downloader\TransportException;
use Composer\IO\IOInterface;
use Composer\Util\HttpDownloader;

class ValidationService
{
    protected IOInterface $io;
    protected Composer $composer;

    public function __construct(IOInterface $io, Composer $composer)
    {
        $this->io = $io;
        $this->composer = $composer;
    }

    public function projectName(): \Closure
    {
        return function ($value) {
            if (!preg_match('/^[a-zA-Z0-9_-]*$/', trim($value))) {
                throw new \UnexpectedValueException('Invalid ddev project name "' . $value . '"');
            }

            return trim($value);
        };
    }

    public function filePath(): \Closure
    {
        return function ($value) {
            if (!is_file($value)) {
                throw new \UnexpectedValueException('Invalid file path "' . $value . '"');
            }

            return $value;
        };
    }

    public function user(): \Closure
    {
        return function ($username) {
            try {
                $request = new HttpDownloader($this->io, $this->composer->getConfig());
                $json = $request->get('https://review.typo3.org/accounts/' . urlencode($username ?? '') . '/?pp=0');

                // Gerrit does not return valid JSON using their JSON API
                // therefore we need to chop off the first line
                // Sounds weird? See why https://gerrit-review.googlesource.com/Documentation/rest-api.html#output
                $validJson = str_replace(')]}\'', '', $json->getBody());

                $userData = json_decode($validJson, true, 512, JSON_THROW_ON_ERROR);
            } catch (TransportException $exception) {
                throw new \UnexpectedValueException('Username "' . $username . '" not found in TYPO3 Gerrit.');
            }

            return $userData;
        };
    }
}
