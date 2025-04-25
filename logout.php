<?php
require_once 'bienvenido/auth.php';

session_unset();
session_destroy();

header("Location: login.php");
exit();
?>