<?php

declare(strict_types=1);

namespace Ochorocho\Tdk\Scripts;

use Composer\Downloader\TransportException;
use Composer\Script\Event;
use Composer\Util\HttpDownloader;

abstract class BaseScript
{
    protected static string $coreDevFolder = 'typo3-core';

    /**
     * @return \Closure
     */
    protected static function validateDdevProjectName(): \Closure
    {
        return function ($value) {
            if (!preg_match('/^[a-zA-Z0-9_-]*$/', trim($value))) {
                throw new \UnexpectedValueException('Invalid ddev project name "' . $value . '"');
            }

            return trim($value);
        };
    }

    /**
     * @return \Closure
     */
    protected static function validateFilePath(): \Closure
    {
        return function ($value) {
            if (!is_file($value)) {
                throw new \UnexpectedValueException('Invalid file path "' . $value . '"');
            }

            return $value;
        };
    }

    /**
     * @param Event $event
     * @return \Closure
     */
    protected static function validateUsername(Event $event): \Closure
    {
        return function ($value) use ($event) {
            try {
                $userData = self::getGerritUserData($event, $value);
            } catch (TransportException $exception) {
                throw new \UnexpectedValueException('Username "' . $value . '" not found in TYPO3 Gerrit.');
            }

            return $userData;
        };
    }

    /**
     * @throws \JsonException
     */
    private static function getGerritUserData(Event $event, string $username): array
    {
        $request = new HttpDownloader($event->getIO(), $event->getComposer()->getConfig());
        $json = $request->get('https://review.typo3.org/accounts/' . urlencode($username) . '/?pp=0');

        // Gerrit does not return valid JSON using their JSON API
        // therefore we need to chop off the first line
        // Sounds weird? See why https://gerrit-review.googlesource.com/Documentation/rest-api.html#output
        $validJson = str_replace(')]}\'', '', $json->getBody());

        return json_decode($validJson, true, 512, JSON_THROW_ON_ERROR);
    }
}