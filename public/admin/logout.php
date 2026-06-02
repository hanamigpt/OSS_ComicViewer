<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

logout_admin();
redirect('/admin/login.php');
