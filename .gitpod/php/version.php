<?php

require dirname(__DIR__) . '/../packages/tdk-composer-plugin/src/Service/BaseService.php';
use Ochorocho\TdkComposer\Service\BaseService;

$branch = getenv('TDK_BRANCH') ?: 'main';
$composerFile = 'https://raw.githubusercontent.com/TYPO3/typo3/' . $branch . '/composer.json';
echo BaseService::getPhpVersion($composerFile) . PHP_EOL;