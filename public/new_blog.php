<?php
require_once 'installed_info.php';
require_once $InstalledPath . 'functions.php';
?>
<!DOCTYPE html>
<html>
<head>
<title>New Blog</title>
</head>
<body>
<?php
require_once $InstalledPath . 'connection_info.php';
$conn = new mysqli($servername, $username, $password, $BlogDB);
if(!isset($Language)) {
  $Language = 'en';
}
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}
require_once $InstalledPath . '/lang/' . $Language . '.php';
if(!isset($_POST['theme'])) $_POST['theme'] = 'default';

function trimHttp($url) {
  $url = trim($url);
  $slash = "";
  if($url[-1] != '/') $slash = '/';
  if(substr($url, 0, 7) == 'http://') return substr($url, 7) . $slash;
  else if(substr($url, 0, 8) == 'https://') return substr($url, 8) . $slash;
  else return $url . $slash;
}

function validateBlogURL($url) {
  if(!filter_var('http://' . $url, FILTER_VALIDATE_URL)) return FALSE;
  $slash = strpos($url, '/');
  if($slash !== FALSE && strpos(substr($url, 0, $slash), '.') !== FALSE) {
    return strpos(substr($url, $slash), '.') === FALSE;
  } else {
    return strpos($url, '.') !== FALSE;
  }
}

function isDataValid() {
  global $conn, $ErrorMessage;
  if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $ErrorMessage .= lang('EMAIL_INVALID')."\n";
  if($_POST['password'] != $_POST['confirmPassword']) $ErrorMessage .= lang('PASSWORDS_NOT_MATCHED')."\n";
  if(strlen($_POST['password']) == 0) $ErrorMessage .= lang('PASSWORD_BLANK')."\n";
  if(strlen(trim($_POST['databaseName'])) == 0) $ErrorMessage .= lang('DB_BLANK')."\n";
  if(strlen(trim($_POST['ownerName'])) == 0) $ErrorMessage .= lang('OWNER_BLANK')."\n";
  if(!is_null(getOneSQLResult('SELECT schema_name FROM information_schema.schemata WHERE schema_name=?',
           's', 1, trim($_POST['databaseName']))[0])) { 
    if(trim($_POST['databaseName']) != $MetaDB) {
      $ErrorMessage .= lang('DB_EXISTS')."\n";
    } else {
      foreach(array('themes', 'users', 'names', 'posts', 'pages', 'pending_users', 'page_comments',
                    'post_comments', 'pending_page_comments', 'pending_post_comments', 'calendar',
                    'categories', 'category_post_entries', 'category_page_entries', 'plugins',
                    'plugin_int_values', 'plugin_float_values', 'plugin_string_values',
                    'settings_int', 'settings_float', 'settings_string') as $i) {
        if($conn->query('DESCRIBE ' . $i)) {
          $ErrorMessage .= lang('DB_EXISTS')."\n";
          break;
        }
      }
    }
  }
  if(!validateBlogURL(trimHttp($_POST['urlInput']))) $ErrorMessage .= lang('URL_ERROR')."\n";
  return $ErrorMessage;
}

function optionFlag0() {
  global $FLAGS;
  $sum = 0;
  foreach($FLAGS as $name => $value) {
    if($value[0] == 0) {
      if(isset($_POST[$name])) {
        $sum += 2**$value[1];
      }
    }
  }
  return $sum;
}

if(isset($_POST['submit'])) {
  $ErrorMessage = isDataValid();
  if(strlen($ErrorMessage == 0)) {
    $conn->query('CREATE TABLE themes (url VARCHAR(2048) NOT NULL, theme VARCHAR(256) NOT NULL)');
    $conn->query('CREATE TABLE users (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, password TEXT NOT NULL, email VARCHAR(1024), time_registered DATETIME, nanoseconds_registered INT, flag INT)');
    $conn->query('CREATE TABLE names (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, name VARCHAR(256) NOT NULL, user_id INT, FOREIGN KEY(user_id) REFERENCES users(id))');
    $conn->query('CREATE TABLE pending_users (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, password TEXT NOT NULL, email VARCHAR(1024), time_registered DATETIME, nanoseconds_registered INT, flag INT, name VARCHAR(256), UNIQUE(name))');
    $conn->query('CREATE TABLE pages (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT, title VARCHAR(1024), name VARCHAR(1024) NOT NULL, content LONGTEXT, created DATETIME, edited DATETIME, path VARCHAR(2048), meta_keywords TEXT, meta_description TEXT, FOREIGN KEY(user_id) REFERENCES users(id), UNIQUE(name))');
    $conn->query('CREATE TABLE posts (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id INT, title VARCHAR(1024), name VARCHAR(1024) NOT NULL, content LONGTEXT, created DATETIME, edited DATETIME, dated DATETIME, meta_keywords TEXT, meta_description TEXT, FOREIGN KEY(user_id) REFERENCES users(id), UNIQUE(name))');
    $conn->query('CREATE TABLE post_comments (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, post_id INT NOT NULL, reply_id INT, name_id INT NOT NULL, content LONGTEXT, created DATETIME, edited DATETIME, FOREIGN KEY(post_id) REFERENCES posts(id), FOREIGN KEY(reply_id) REFERENCES post_comments(id))');
    $conn->query('CREATE TABLE page_comments (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, page_id INT NOT NULL, reply_id INT, name_id INT NOT NULL, content LONGTEXT, created DATETIME, edited DATETIME, FOREIGN KEY(page_id) REFERENCES pages(id), FOREIGN KEY(reply_id) REFERENCES page_comments(id))');
    $conn->query('CREATE TABLE pending_post_comments (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, post_id INT NOT NULL, reply_id INT, name_id INT NOT NULL, content LONGTEXT, created DATETIME, edited DATETIME, FOREIGN KEY(post_id) REFERENCES posts(id), FOREIGN KEY(reply_id) REFERENCES post_comments(id))');
    $conn->query('CREATE TABLE pending_page_comments (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, page_id INT NOT NULL, reply_id INT, name_id INT NOT NULL, content LONGTEXT, created DATETIME, edited DATETIME, FOREIGN KEY(page_id) REFERENCES pages(id), FOREIGN KEY(reply_id) REFERENCES page_comments(id))');
    $conn->query('CREATE TABLE field_names (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, name VARCHAR(2048) NOT NULL, UNIQUE(name))');
    $conn->query('CREATE TABLE post_fields (field_id INT NOT NULL, post_id INT NOT NULL, the_value TEXT NOT NULL, FOREIGN KEY(field_id) REFERENCES field_names(id), FOREIGN KEY(post_id) REFERENCES posts(id))');
    $conn->query('CREATE TABLE page_fields (field_id INT NOT NULL, page_id INT NOT NULL, the_value TEXT NOT NULL, FOREIGN KEY(field_id) REFERENCES field_names(id), FOREIGN KEY(page_id) REFERENCES pages(id))');
    $conn->query('CREATE TABLE calendar (id INT NOT NULL PRIMARY KEY, short_name VARCHAR(8), medium_name VARCHAR(16), long_name VARCHAR(64))');
    $conn->query('CREATE TABLE categories (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, name VARCHAR(256) NOT NULL, UNIQUE(name))');
    $conn->query('CREATE TABLE category_post_entries (post_id INT NOT NULL, category_id INT NOT NULL, FOREIGN KEY(post_id) REFERENCES posts(id), FOREIGN KEY(category_id) REFERENCES categories(id))');
    $conn->query('CREATE TABLE category_page_entries (page_id INT NOT NULL, category_id INT NOT NULL, FOREIGN KEY(page_id) REFERENCES pages(id), FOREIGN KEY(category_id) REFERENCES categories(id))');
    $conn->query('CREATE TABLE plugins (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, name VARCHAR(256) NOT NULL, folder VARCHAR(2048) NOT NULL)');
    $conn->query('CREATE TABLE plugin_int_values (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, plugin_id INT, parameter INT, item INT, the_value INT, FOREIGN KEY(plugin_id) REFERENCES plugins(id))');
    $conn->query('CREATE TABLE plugin_float_values (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, plugin_id INT, parameter INT, item INT, the_value FLOAT, FOREIGN KEY(plugin_id) REFERENCES plugins(id))');
    $conn->query('CREATE TABLE plugin_string_values (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, plugin_id INT, parameter INT, item INT, the_value VARCHAR(8192), FOREIGN KEY(plugin_id) REFERENCES plugins(id))');
    $conn->query('CREATE TABLE settings_int (parameter INT NOT NULL, the_value INT)');
    $conn->query('CREATE TABLE settings_float (parameter INT NOT NULL, the_value FLOAT)');
    $conn->query('CREATE TABLE settings_string (parameter INT NOT NULL, the_value VARCHAR(2048))');
    $conn->query('CREATE TABLE user_data (user_id NOT NULL, parameter INT NOT NULL, the_value VARCHAR(2048), FOREIGN KEY(user_id) REFERENCES users(id))');
    $conn->query("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");
    insertSQL('INSERT INTO settings_string (parameter, the_value) VALUES (0,?)', 's', trim($_POST['blogTitle']));
    insertSQL('INSERT INTO settings_int (parameter, the_value) VALUES (0,?)', 'i', optionFlag0());
    insertSQL('INSERT INTO themes (url, theme) VALUES ("",?)', 's', trim($_POST['theme']));
    $nanodateResults = nanodate();
    $timeRegistered = $nanodateResults[0];
    $nanoseconds = $nanodateResults[1];
    insertSQL('INSERT INTO users (id,password,email,time_registered,nanoseconds_registered,flag) VALUES (0,?,?,?,?,0)',
              'sssi', password_hash($_POST['password'] . '-' . $timeRegistered . $nanoseconds, PASSWORD_DEFAULT),
              $_POST['email'], $timeRegistered, $nanoseconds);
    insertSQL('INSERT INTO names (name,user_id) VALUES (?,0)', 's', $_POST['ownerName']);
    session_start();
    $_SESSION['userID'] = 0;
    $_SESSION['userName'] = $_POST['ownerName'];
    require_once 'blog.php';
  }
}
if(isset($ErrorMessage) && strlen($ErrorMessage) != 0) {
  echo '<div class="errorMessage">';
  echo '<p>' . lang('FOLLOWING_ERRORS') . '</p>';
  echo "<ul class=\"errorList\">\n<li>";
  echo implode("</li>\n  <li>", explode("\n", $ErrorMessages));
  echo "</li>\n</ul>\n</div>\n";
} else if(isset($ErrorMessage) || !isset($_POST['submit'])) {
  foreach(array('ownerName', 'email', 'blogTitle', 'urlInput') as $i) {
    if(!isset($_POST[$i])) $_POST[$i] = "";
  }
?>
<div>
<form action="" method="post">
<table>
<tr><th colspan="2"><?php echo lang('ENTER_NAME_PASS');?></th></tr>
<tr><td><label for="ownerNameInput"><?php echo lang('OWNER_NAME');?></label></td><td><input type="text" id="ownerNameInput" name="ownerName" value="<?php echo $_POST['ownerName'];?>"/></td></tr>
<tr><td><label for="emailInput"><?php echo lang('EMAIL');?></label></td><td><input type="email" id="emailInput" name="email" value="<?php echo $_POST['ownerName'];?>"/></td></tr>
<tr><td><label for="passwordInput"><?php echo lang('PASSWORD');?></label></td><td><input type="password" id="passwordInput" name="password"/></td></tr>
<tr><td><label for="confirmPasswordInput"><?php echo lang('CONFIRM_PASS');?></label></td><td><input type="password" id="confirmPasswordInput" name="confirmPassword"/></td></tr>
</table>
<hr/>
<table>
<tr><th colspan="2"><?php echo lang('BLOG_INFO');?></th></tr>
<tr><td><label for="blogTitleInput"><?php echo lang('BLOG_TITLE');?></label></td><td><input type="text" id="blogTitleInput" name="blogTitle" value="<?php echo $_POST['blogTitle'];?>"/></td></tr>
<tr><td><label for="urlInput"><?php echo lang('BLOG_URL');?></label></td><td><input type="text" id="urlInput" name="urlInput" value="<?php echo $_POST['urlInput'];?>"/></td></tr>
<tr><td><label for="themeInput"><?php echo lang('THEME');?></label></td><td><input type="text" id="themeInput" name="theme" value="<?php echo $_POST['theme'];?>"/></td></tr>
</table>
<h3><?php echo lang('SET_OPTIONS');?></h3>
<?php $flagValue = 6463;
    makeSettingBox(FLAGS::commentsOnPosts,      "ALLOW_POST_COMMENTS",   $flagValue);
    makeSettingBox(FLAGS::commentsOnPages,      "ALLOW_PAGE_COMMENTS",   $flagValue);
    makeSettingBox(FLAGS::guestsCanComment,     "UNREGISTER_COMMENTS",   $flagValue);
    makeSettingBox(FLAGS::changeName,           "CHANGE_NAME",           $flagValue);
    makeSettingBox(FLAGS::registerUnname,       "REGISTER_GUEST_NAMES",  $flagValue);
    makeSettingBox(FLAGS::useRegisteredName,    "GUEST_USE_REGISTERED",  $flagValue);
    makeSettingBox(FLAGS::approveRegistration,  "APPROVE_REGISTRATION",  $flagValue);
    makeSettingBox(FLAGS::autoModerateNewUsers, "MODERATE_NEW_USERS",    $flagValue);
    makeSettingBox(FLAGS::allowRegistration,    "ALLOW_REGISTRATION",    $flagValue);
    makeSettingBox(FLAGS::moderateAllComments,  "MODERATE_ALL_COMMENTS", $flagValue);
    makeSettingBox(FLAGS::moderateGuestComments,"MODERATE_GUEST_COMMENTS",$flagValue);
    makeSettingBox(FLAGS::editComments,         "ALLOW_EDIT_COMMENTS",   $flagValue);
    makeSettingBox(FLAGS::useRecaptcha,         "USE_RECAPTCHA",         $flagValue);
?>
<input type="submit" value="<?php echo lang('SUBMIT');?>" name="submit"/> 
</form>
<?php } ?>
</div>
</body>
</html>
