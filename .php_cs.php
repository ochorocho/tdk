<?php

$config = new PhpCsFixer\Config();
$config->getFinder()->in([__DIR__ . '/Scripts', __DIR__ . '/tests/Acceptance']);
return $config;
