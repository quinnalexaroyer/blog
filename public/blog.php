<?php
session_start();
require_once 'installed_info.php';
require_once $InstalledPath . 'functions.php';
require_once $InstalledPath . 'connect.php';
require_once $InstalledPath . 'url_info.php';
setContentType(getPathElements());
if($ContentType == 'login') require_once $InstalledPath . 'login.php';
else if($ContentType == 'logout') require_once $InstalledPath . 'logout.php';
else if($ContentType == 'admin') getSection('admin');
else {
  getSection('header');
  getSection('body');
  getSection('footer');
}
echo "\n";
?>

