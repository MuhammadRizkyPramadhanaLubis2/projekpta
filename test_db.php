<?php
require 'app/database.php';
$pdo = db();
$res = $pdo->query('SELECT user_id, count(*) FROM target_kinerja GROUP BY user_id')->fetchAll();
print_r($res);
