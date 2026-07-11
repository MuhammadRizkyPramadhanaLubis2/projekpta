<?php
declare(strict_types=1);

session_destroy();
session_start();
flash('Anda telah berhasil keluar.');
redirect('login');
