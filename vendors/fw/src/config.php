<?php

$config = \FW\Core\Config::getInstance();

$config->set('context', '/');
$config->set('app-id', md5($_SERVER['SERVER_NAME']));
$config->set('template', 'template');
$config->set('page-404', 'not-found');
$config->set('log-file', __DIR__ . '/logs/' . date('Y-m-d') . '-log-{level}.log');
$config->set('secret-key', md5('system-secret-key'));
$config->set('valid-time-token', 60 * 60 * 24 * 7);
$config->set('system-folders', [__DIR__ . '/security', __DIR__ . '/view']);
