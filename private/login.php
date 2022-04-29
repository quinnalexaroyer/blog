<?php
global $ContentType, $HomeURL;
function printLoginForm() {
  global $HomeURL;
  getSection("header");
?>
<form method="post" action="<?php echo $HomeURL; echo langurl('LOGIN'); ?>">
<label for="loginNameInput">Login</label>
<input type="text" id="loginNameInput" name="loginName"/>
<label for="passwordInput">Password</label>
<input type="password" id="passwordInput" name="password"/>
<input type="submit" name="submit" value="submit"/>
</form>
<?php 
  getSection("footer");
}

if(session_status() === PHP_SESSION_NONE || !isset($_SESSION['userID'])) {
  if(isset($_POST['submit'])) {
    $userID = verifyPasswordForUser(trim($_POST['loginName']), $_POST['password']);
    if(gettype($userID) == 'string') {
      echo '<p>' . $userID . '</p>';
      printLoginForm();
    } else {
      session_start();
      $_SESSION['userID'] = $userID;
      $_SESSION['userName'] = $_POST['loginName'];
      $ContentType = 'index';
      getSection("header");
      getSection("body");
      getSection("footer");
    }
  } else {
    printLoginForm();
  }
}
?>
