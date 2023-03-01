<?php

$config = new PhpCsFixer\Config();
$config->getFinder()->in([__DIR__ . '/packages/tdk-composer-plugin', __DIR__ . '/tests/Acceptance']);
return $config;
