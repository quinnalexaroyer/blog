<?php
require_once 'installed_info.php';
require_once $InstalledPath . 'functions.php';
require_once $InstalledPath . 'connect.php';
require_once $InstalledPath . 'url_info.php';
global $ErrorMessage, $InstalledPath;

function pCommentAction($p) {
  global $ErrorMessage;
  global $debugFile;
  if(isP($p)) {
    if(!canUserComment($p)) {
      $ErrorMessage .= lang('ERROR_CANNOT_COMMENT') . "\n";
    } else if(!isLoggedIn() && !canGuestUseName($_POST['name'])) {
      $ErrorMessage .= lang('ERROR_NAME_REGISTERED') . "\n";
    } else {
      if(isset($_POST['replyTo'])) $replyTo = $_POST['replyTo'];
      else $replyTo = NULL;
      if(isLoggedIn()) $nameID = getNameIDForUser($_SESSION['userID']);
      else $nameID = getGuestNameIDOrCreate($_POST['name']);
      newComment($p, $_POST['comment'], $_POST['id'], $replyTo, $nameID);
    }
  }
}

if(isset($_POST['action'])) {
  if($_POST['action'] == 'postcomment') pCommentAction('post');
  else if($_POST['action'] == 'pagecomment') pCommentAction('page');
}
getSection('footer');
