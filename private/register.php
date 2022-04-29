<?php
global $ErrorMessage, $HomeURL;
require_once 'installed_info.php';
require_once $InstalledPath . 'functions.php';
require_once $InstalledPath . 'connect.php';
require_once $InstalledPath . 'url_info.php';

function printRegistrationForm($name="", $email="") {
  global $HomeURL;
?>
<div class="registerForm">
  <form method="post" action="<?php echo $HomeURL . langurl("REGISTER");?>">
    <table>
      <tr><td><label for="nameInput"><?php echo lang("NAME");?></label></td>
        <td><input id="nameInput" type="text" name="name" size="50" value="<?php echo $name;?>"/></td></tr>
      <tr><td><label for="emailInput"><?php echo lang("EMAIL");?></label></td>
        <td><input id="emailInput" type="email" name="email" size="50" value="<?php echo $email;?>"/></td></tr>
      <tr><td><label for="passwordInput"><?php echo lang("PASSWORD");?></label></td>
        <td><input id="passwordInput" type="password" name="password" size="50"/></td></tr>
      <tr><td><label for="confirmPasswordInput"><?php echo lang("CONFIRM_PASS");?></label></td>
        <td><input id="confirmPasswordInput" type="password" name="confirmPassword" size="50"/></td></tr>
    </table>
    <input class="g-recaptcha" type="submit" name="submit" value="<?php echo lang("SUBMIT");?>"/>
  </form>
</div>
<?php
}

if(isset($_POST['submit'])) {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $isValid = verifyRegistration($name, $_POST['password'], $_POST['confirmPassword'], $email);
  if($isValid && getFlagOption('allowRegistration')) {
    $userID = newUser($name, $_POST['password'], $email);
    if(getFlagOption('approveRegistration' || !is_int($userID) || $userID == 0)) {
      echo lang("REGISTER_PENDING");
    } else {
      echo lang("REGISTER_SUCCESS");
      echo "<br/><a href=\"$HomeURL\">";
      echo lang("MAIN_PAGE");
      echo "</a>\n";
      session_start();
      $_SESSION['userID'] = $userID;
      $_SESSION['userName'] = $name;
    }
  }
} else {
  $isValid = FALSE;
  $name = '';
  $email = '';
}
if(!$isValid) {
  printRegistrationForm($name, $email);
} else {
  $ContentType = 'index';
}
?>

