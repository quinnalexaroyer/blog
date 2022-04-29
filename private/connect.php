<?php
global $BlogDB, $Language, $conn, $DatetimeFormat, $DateFormat, $TimeFormat,
       $servername, $username, $password;
require_once 'connection_info.php';
$BaseURL = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')+1);
$HomeURL = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')+1);
$ErrorMessage = '';
$conn = new mysqli($servername, $username, $password, $BlogDB);
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}
require_once 'lang/' . $Language . '.php';
if(isset($_GET['path'])) {
  $Theme = getTheme($_GET['path']);
} else {
  $Theme = getTheme('');
}
$DatetimeFormat = getStringSetting(1);
$DateFormat = getStringSetting(2);
$TimeFormat = getStringSetting(3);
if(isEmptyResult($DatetimeFormat)) {
  $DatetimeFormat = SQLDATEFORMAT;
}
if(isEmptyResult($DateFormat)) {
  $DateFormat = substr(SQLDATEFORMAT, 0, strpos(SQLDATEFORMAT, ' '));
}
if(isEmptyResult($TimeFormat)) {
  $TimeFormat = substr(SQLDATEFORMAT, strpos(SQLDATEFORMAT, ' ')+1);
}
session_start();
?>
