<?php

if (!defined('IN_PHPBB')) exit;

if (empty($lang) || !is_array($lang)) $lang = [];

$lang = array_merge($lang, [
    'UCP_SECUREPM_TITLE' => 'Secure PM',
    'UCP_SECUREPM_DESC'  => 'Manage your GPG keys for encrypted messages',
]);
