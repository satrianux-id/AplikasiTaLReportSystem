<?php

session_start();
session_unset();
session_destroy();
header('Location: /tal_app/public/login.php');
exit;