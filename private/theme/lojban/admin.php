<!DOCTYPE html>
<html><head>
  <title><?php echo lang('ADMIN');?></title>
  <?php stylesheetTag(); ?>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<?php
global $InstalledPath;
$adminTitles = array('pendingusers', 'pendingpostcomments', 'pendingpagecomments', 'newpost',
                     'newpage', 'showpages', 'showposts', 'showusers', 'settings');
if(!isset($_SESSION['userID'])) {
  echo lang('NOT_LOGGED_IN');
} else if($_SESSION['userID'] != 0) {
  echo lang('NOT_ADMIN');
} else {
getSection('usermenu');
?>
<h2><?php echo lang("ADMIN_ACTIONS");?></h2>
<div class="adminMenu">
  <ul>
<?php
  $adminTerm = langurl('ADMIN');
  foreach($adminTitles as $actionType) {
    echo "    <li><a href=\"${HomeURL}${adminTerm}/$actionType\">" . lang(strtoupper($actionType))
       . "</a></li>\n";
  }
?>
  </ul>
</div>
<div class="adminMainSection">
<?php
  require_once $InstalledPath . 'admin.php';
}
?>
</div>
</body>
</html>

