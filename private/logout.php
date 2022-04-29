<?php
require_once 'installed_info.php';
session_start();
include_once $InstalledPath . 'functions.php';
require_once $InstalledPath . 'lang/' . $Language . '.php';
unset($_SESSION['userID']);
unset($_SESSION['userName']);
session_destroy();
echo "<p>" . lang('HAVE_LOGGED_OUT') . "</p>\n";
echo "<p><a href=\"$HomeURL" . langurl("LOGIN") . "\">" . lang('CLICK_TO_LOGIN') . "</p>\n";
?>

