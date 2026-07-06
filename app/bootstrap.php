<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/site_content.php';

init_database();
