<div class="middleColumn">
<?php
  include $InstalledPath . 'load_content.php'; ?>
<?php
global $ErrorMessage;
if(isset($ErrorMessage) && $ErrorMessage != "") {
?><div class="errorMessages">
<?php echo lang("FOLLOWING_ERRORS"); echo "<br/>\n<ul>\n  <li>";
  echo str_replace("\n", "</li>\n  <li>", trim($ErrorMessage));
  echo "</li>\n</ul>\n</div>\n";
}
?>
</div>
