<?php

require dirname(__DIR__) . '/../packages/tdk-composer-plugin/src/Service/BaseService.php';
use Ochorocho\TdkComposer\Service\BaseService;

$branch = getenv('TDK_BRANCH') ?: 'main';
$composerFile = 'https://raw.githubusercontent.com/TYPO3/typo3/' . $branch . '/composer.json';
try {
    echo BaseService::getPhpVersion($composerFile) . PHP_EOL;
} catch (JsonException $e) {
    echo '8.1' . PHP_EOL;
}