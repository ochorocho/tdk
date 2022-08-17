<?php

declare(strict_types=1);

namespace Ochorocho\Tdk\Scripts;

abstract class BaseScript
{
    protected static string $coreDevFolder = 'typo3-core';

    protected static function getArguments($array): array
    {
        $items = [];
        foreach ($array as $argument) {
            preg_match('/^--(.*)/', $argument, $parsed);

            $key = explode('=', $parsed[1] ?? '');
            $items[$key[0]] = $key[1] ?? true;
        }

        return $items;
    }
}
