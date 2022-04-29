<?php
echo "<div class=\"usermenu\">\n";
if(isLoggedIn()) {
  if(isOwner()) {
    echo "    <a href=\"$HomeURL" . langurl("ADMIN") . "\">" . lang('ADMIN') . "</a> | \n";
  }
  echo "    <a href=\"$HomeURL" . langurl("EDIT_PROFILE") . "\">" . lang('EDIT_PROFILE') . "</a> | \n";
  echo "    <a href=\"$HomeURL" . langurl("LOGOUT") . "\">" . lang('LOGOUT') . "</a> | \n";
  echo "    <strong>" . $_SESSION['userName'] . "</strong>\n";
} else {
  echo "    <a href=\"$HomeURL" . langurl("REGISTER") . "\">" . lang('REGISTER') . "</a> | \n";
  echo "    <a href=\"$HomeURL" . langurl("LOGIN") . "\">" .  lang('LOGIN') . "</a>\n";
}
echo "</div>\n";
?>
