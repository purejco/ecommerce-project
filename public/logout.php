<?php

require_once 'app/services/SessionService.php';

startSecureSession();
logoutUserSession();

header('Location: signin.php');
exit;