<?php

const NUM_PAGE_COLUMNS = 10;
const NUM_POST_COLUMNS = 10;
const NUM_CATEGORY_COLUMNS = 6;
const NUM_COMMENT_COLUMNS = 7;
const NUM_USER_COLUMNS = 6;
const NUM_PENDING_USER_COLUMNS = 6;
const MAX_COMMENT_INDENT = 9;
const SQLDATEFORMAT = 'Y-m-d H:i:s';
const DEFAULT_POSTS_DISPLAYED = 10;
const DEFAULT_CATEGORIES_DISPLAYED = 100;
const ADMIN_TABLE_LIMIT = 50;

class FLAGS {
  const guestsCanComment     = array(0,0);
  const approveRegistration  = array(0,1);
  const autoModerateNewUsers = array(0,2);
  const commentsOnPosts      = array(0,3);
  const commentsOnPages      = array(0,4);
  const changeName           = array(0,5);
  const registerUnname       = array(0,6);
  const useRegisteredName    = array(0,7);
  const allowRegistration    = array(0,8);
  const moderateAllComments  = array(0,9);
  const editComments         = array(0,10);
  const moderateGuestComments= array(0,11);
  const useRecaptcha         = array(0,12);
  const postURLFormat0       = array(8,0);
  const postURLFormat1       = array(8,1);
  const pageURLFormat0       = array(8,2);
  const pageURLFormat1       = array(8,3);
  const categoryURLFormat    = array(8,4);
  const userURLFormat        = array(8,5);
  const nameURLFormat        = array(8,6);
}

class INT_SETTINGS {
  const flag0                = 0;
  const postsPerPage         = 1;
  const minPasswordLength    = 2;
  const minPasswordDigits    = 3;
  const minPasswordLetters   = 4;
  const minPassowrdCapitals  = 5;
  const minPasswordLowers    = 6;
  const minPasswordSymbols   = 7;
  const urlFlag              = 8;
}

class FLOAT_SETTINGS {
}

class STRING_SETTINGS {
  const blogTitle            = 0;
  const dateTimeFormat       = 1;
  const dateFormat           = 2;
  const timeFormat           = 3;
  const indexPage            = 4;
}

class USER_FLAGS {
  const banned                = 0;
  const moderated             = 1;
  const moderateUsers         = 2;
  const moderateOwnedComments = 3; // user can moderate comments on their own post or own pages
  const moderatePageComments  = 4;
  const moderatePostComments  = 5;
  const makePosts             = 6;
  const makePages             = 7;
  const editOwnPosts          = 8;
  const editOwnPages          = 9;
  const editPosts             = 10;
  const editPages             = 11;
}

include_once 'date_functions.php';
include_once 'recaptcha.php';

function boolstr($x) {
  if($x) return "TRUE"; else return "FALSE";
}

function lang($x) {
  return constant("Lang::$x");
}

function langurl($x) {
  return constant("LangURL::$x");
}

function langMonth($n, $size) { // $n is month number, 1-12; $size is 0, 1, or 2
  return constant("LangCalendar::MONTHS")[intdiv($n-1, 3) + $size];
}

function langDay($n, $size) { // $n is day number, 0-6; $size is 0, 1, or 2
  return constant("LangCalendar::DAYS_OF_WEEK")[intdiv($n, 3) + $size];
}

function commit() {
  global $conn;
  $conn->commit();
}

function zeroIndexOrNull($a) {
  if(is_array($a) && count($a) > 0) {
    return $a[0];
  } else {
    return NULL;
  }
}

function isEmptyResult($r) {
  return is_null($r) || (is_array($r) && (count($r) == 0 || (count($r) == 1 && is_null($r[0]))));
}

function cloneArray(array $a) {
  $r = array();
  foreach($a as $key => $value) {
    $r[$key] = $value;
  }
  return $r;
}

function trimArray(array $a) {
  return array_map(function($x) {return trim($x);}, $a);
}

function escapeReturns($s) {
  return str_replace("\"", "\\\"", str_replace("\n", "\\\n", $s));
}

function nanodate() {
  $nanotime = explode('.', system('date +%s.%N'));
  return array(date(SQLDATEFORMAT, intval($nanotime[0])), $nanotime[1]);
}

function getSQLSelect($sql, $types, $nColumns, ...$parameters) {
  global $conn, $BlogDB;
  $result = array();
  for($i=0; $i<$nColumns; $i++) {
    array_push($result, NULL);
  }
  $statement = $conn->prepare($sql);
  if($statement !== FALSE && $statement !== NULL) {
    $statement->bind_param($types, ...$parameters);
    $statement->execute();
    $statement->bind_result(...$result);
  }
  print_r($conn->error);
  if($statement === FALSE || $statement === NULL) {
    return array($statement, NULL);
  } else {
    return array($statement, $result);
  }
}

function getSQLResults($sql, $types, $nColumns, ...$parameters) {
  $sr = getSQLSelect($sql, $types, $nColumns, ...$parameters);
  $rows = array();
  if($sr[0] !== FALSE && $sr[0] !== NULL) {
    while($sr[0]->fetch()) {
      array_push($rows, cloneArray($sr[1]));
    }
  }
  return $rows; 
}

function getOneSQLResult($sql, $types, $nColumns, ...$parameters) {
  $result = getSQLSelect($sql, $types, $nColumns, ...$parameters);
  if($result[0] !== FALSE && !is_null($result[0])) {
    if($result[0]->fetch()) {
      return $result[1];
    }
  }
  return NULL;
}

function getOneSQLValue($sql, $types, ...$parameters) {
  return zeroIndexOrNull(getOneSQLResult($sql, $types, 1, ...$parameters));
}

function getSQLQuery($sql) {
  global $conn;
  $result = $conn->query($sql);
  $rows = array();
  if($result !== FALSE) {
    while($row = $result->fetch_array()) {
      array_push($rows, cloneArray($row));
    }
  }
  return $rows;
}

function generalSQL($sql, $types, ...$parameters) {
  global $conn;
  $statement = $conn->prepare($sql);
  if($statement !== FALSE) {
    $statement->bind_param($types, ...$parameters);
    $statement->execute();
    commit();
  } else echo $conn->error;
}

function insertSQL($sql, $types, ...$parameters) {
  global $conn;
  $statement = $conn->prepare($sql);
  if($statement !== FALSE) {
    $statement->bind_param($types, ...$parameters);
    $statement->execute();
    $id = $conn->insert_id;
    commit();
    return $id;
  } else echo 'prepare sql error: ' . $conn->error;
}

function injSQL($sql, ...$parameters) {
  global $conn, $fout;
  $newParameters = array();
  foreach($parameters as $i) {
    $i2 = str_replace('"', "\\\"", str_replace("\\", "\\\\", $i));
    array_push($newParameters, $i2);
  }
  $newSQL = "";
  $stringIndex = 0;
  $parameterIndex = 0;
  while($stringIndex !== FALSE && $parameterIndex < count($newParameters)) {
    $newIndex = strpos($sql, '?', $stringIndex);
    if($newIndex !== FALSE && $newIndex !== NULL) {
      $newSQL .= substr($sql, $stringIndex, $newIndex) . $newParameters[$parameterIndex];
    } else {
      $newSQL .= substr($sql, $stringIndex);
    }
    $stringIndex = $newIndex+1;
    $parameterIndex++;
  }
  if($stringIndex !== FALSE && $stringIndex !== NULL) {
    $newSQL .= substr($sql, $stringIndex);
  }
  $conn->query($newSQL);
  commit();
}

function isBanned($flag) {
  return ($flag & 1) > 0;
}

function isModerated($flag) {
  return ($flag & 2) > 0;
}

function isFeedPage($indexPage) {
  return is_null($indexPage) || $indexPage == "" || $indexPage == langurl("INDEX")
         || $indexPage == langurl("FEED");
}

function getIntSetting($number) {
  return getOneSQLValue('SELECT the_value FROM settings_int WHERE parameter=?',
                        'i', $number);
}

function getFloatSetting($number) {
  return getOneSQLValue('SELECT the_value FROM settings_float WHERE parameter=?',
                        'i', $number);
}

function getStringSetting($number) {
  return getOneSQLValue('SELECT the_value FROM settings_string WHERE parameter=?',
                         'i', $number);
}

function getIntSettingByName($name) {
  return getIntSetting(constant("INT_SETTINGS::" . $name));
}

function getFloatSettingByName($name) {
  return getFloatSetting(constant("FLOAT_SETTINGS::" . $name));
}

function getStringSettingByName($name) {
  return getStringSetting(constant("STRING_SETTINGS::" . $name));
}

function getFlag($option, $bit) {
  $optionValue = getIntSetting($option);
  if(!isEmptyResult($optionValue)) {
    return ($optionValue & (1 << $bit)) >> $bit;
  } else {
    return NULL;
  }
}

function getFlagOption($name) {
  $flag = constant("FLAGS::" . $name);
  return getFlag($flag[0], $flag[1]);
}

function getUserFlagValue($userID) {
  return getOneSQLValue("SELECT flag FROM users WHERE id=?", 'i', $userID);
}

function getUserFlag($bit, $userID=NULL) {
  if(is_null($userID) && isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];
  }
  if(!is_null($userID)) {
    $flag = getUserFlagValue($userID);
    return ((1 << $bit) & $flag) > 0;
  } else {
    return NULL;
  }
}

function getUserFlagByName($name, $userID=NULL) {
  return getUserFlag(constant("USER_FLAGS::" . $name), $userID);
}

function setSetting($number, $value, $theType) {
  if($theType == 'int' || $theType == 'string' || $theType == 'float') {
    if($theType == 'float') $typeLetter = 'd'; else $typeLetter = $theType[0];
    $result = getOneSQLValue("SELECT the_value FROM settings_$theType WHERE parameter=?",
                              'i', $number);
    if(!isEmptyResult($result)) {
      generalSQL('UPDATE settings_' . $theType . ' SET the_value=? WHERE parameter=?',
                 $typeLetter . 'i', $value, $number);
    } else {
      insertSQL('INSERT INTO settings_' . $theType . ' (parameter, the_value) VALUES (?,?)', 
                'i' . $typeLetter, $number, $value);
    }
    commit();
  }
}

function setIntSetting($number, $value) {
  setSetting($number, $value, 'int');
}

function setFloatSetting($number, $value) {
  setSetting($number, $value, 'float');
}

function setStringSetting($number, $value) {
  setSetting($number, $value, 'string');
}

function setUserFlag($bit, $userID) {
  $flag = getUserFlagValue($userID);
  generalSQL('UPDATE users SET flag=? WHERE id=?', 'ii', $flag | (1 << $bit), $userID);
}

function unsetUserFlag($bit, $userID) {
  $flag = getUserFlagValue($userID);
  generalSQL('UPDATE users SET flag=? WHERE id=?', 'ii', $flag & ~(1 << $bit), $userID);
}

function setUserFlagByName($name, $userID) {
  setUserFlag(constant('USER_FLAGS::' . $name), $userID);
}

function unsetUserFlagByName($name, $userID) {
  unsetUserFlag(constant('USER_FLAGS::' . $name), $userID);
}

function getHTMLTitle() {
  return getStringSetting(0);
}

function blogTitle() {
  return getStringSetting(0);
}

function styleSheetTag() {
  global $Theme, $HomeURL; ?>
  <link rel="stylesheet" type="text/css" href="<?php echo $HomeURL;?>css.php?theme=<?php echo $Theme; ?>"/><?php
}

function isP($p) {
  return $p == 'post' || $p == 'page';
}

function pContentType() {
  global $ContentType;
  if($ContentType == 'post' || $ContentType == 'date' || $ContentType == 'index'
          || $ContentType == 'multipost'
          || (isset($_GET['action']) && substr($_GET['action'], -4) == 'post')) {
    return 'post';
  } else if($ContentType == 'page'
          || (isset($_GET['action']) && substr($_GET['action'], -4) == 'page')) {
    return 'page';
  } else {
    return 'none';
  }
}

function metaDescriptionTag() {
  global $ID, $ContentType;
  $p = pContentType();
  if(isP($p)) {
    $description = getOneSQLValue("SELECT meta_description FROM ${p}s WHERE id=?", 'i', $ID);
  ?>
<meta name="description" content="<?php echo htmlentities($description);?>"/>
  <?php
  }
}

function metaKeywordsTag() {
  global $ID;
  $p = pContentType();
  if(isP($p)) {
    $keywords = getOneSQLValue("SELECT meta_keywords FROM ${p}s WHERE id=?", 'i', $ID);
  ?>
<meta name="keywords" content="<?php echo htmlentities($keywords);?>"/>
  <?php
  }
}

function metaAuthorTag() {
}

function metaViewportTag() {
}

function isLoggedIn() {
  return isset($_SESSION['userID']);
}

function isOwner() {
  return isLoggedIn() && $_SESSION['userID'] == 0;
}

function isNameRegistered($name) {
  $a = getOneSQLValue("SELECT id FROM names WHERE name=? AND user_id IS NOT NULL",
                       's', $name);
  return !isEmptyResult($a);
}

function isNameUsed($name) {
  return !isEmptyResult(getOneSQLValue("SELECT id FROM names WHERE name=?", 's', $name));
}

function isNamePending($name) {
  return !isEmptyResult(getOneSQLValue("SELECT id FROM pending_users WHERE name=?", 's', $name));
}

function isEmailUsed($email) {
  return !isEmptyResult(getOneSQLValue("SELECT id FROM users WHERE email=?", 's', $email));
}

function canUserPost() {
  return isOwner();
}

function canUserComment($p, $pID=NULL) {
  return (isP($p) && getFlagOption("commentsOnP" . substr($p,1) . "s"))
         && (isOwner()
            || (isLoggedIn() && !getUserFlagByName('banned'))
            || ((!isLoggedIn() && getFlagOption('guestsCanComment'))));
}

function canGuestUseName($name) {
  return getFlagOption('useRegisteredName') || !isNameRegistered($name);
}

function createName($name) {
  return insertSQL("INSERT INTO names (name) VALUES (?)", 's', $name);
}

function createNameForUser($name, $userID) {
  return insertSQL("INSERT INTO names (name,user_id) VALUES (?,?)", 'si', $name, $userID);
}

function setPData($p) {
  global $ID, $AuthorID, $Title, $URLName, $Content, $TimeCreated, $TimeEdited, $Path, $Dated,
         $ContentType, $PType;
  $ID = $p[0];
  $AuthorID = $p[1];
  $Title = $p[2];
  $URLName = $p[3];
  $Content = $p[4];
  $TimeCreated = $p[5];
  $TimeEdited = $p[6];
  if($ContentType == 'page') {
    $Path = $p[7];
    $PType = 'page';
  } else if($ContentType == 'post' || $ContentType == 'feed') {
    $Dated = $p[7];
    $PType = 'post';
    $Year = intval(substr($p[7], 0, 4));
    $Month = intval(substr($p[7], 5, 2));
    $Day = intval(substr($p[7], 8, 2));
    $DayOfWeek = dayOfWeek($Year, $Month, $Day);
  }
}

function getTheme($getPath) {
  $path = explode('/', $getPath);
  $level = count($path);
  $result = '';
  while(strlen($result) == 0 && $level >= 0) {
    $result = getOneSQLValue('SELECT theme FROM themes WHERE url=?', 's',
                             implode('/', array_slice($path, 0, $level)));
    $level--;
  }
  if(isEmptyResult($result)) {
    return 'default';
  } else {
    return $result;
  }
}

function getGlobalLimit($alternate) {
  global $OffsetAndLimit;
  if(count($OffsetAndLimit) >= 1 && !is_null($OffsetAndLimit[0])) {
    return $OffsetAndLimit[0];
  } else {
    return $alternate;
  }
}

function getGlobalOffset($alternate) {
  global $OffsetAndLimit;
  if(count($OffsetAndLimit) >= 2 && !is_null($OffsetAndLimit[1])) {
    return $OffsetAndLimit[1];
  } else {
    return $alternate;
  }
}

function getSection($name) {
  global $InstalledPath, $Theme, $Language, $BaseURL, $HomeURL;
  include $InstalledPath . 'theme/' . $Theme . '/' . $name . '.php';
}

function getPlugin($name) {
  global $InstalledPath;
  include $InstalledPath . 'plugin/' . $name . '.php';
}

function getPost($id) {
  return getOneSQLResult('SELECT * FROM posts WHERE id=?', 'i', NUM_POST_COLUMNS, $id);
}

function getPage($id) {
  return getOneSQLResult('SELECT * FROM pages WHERE id=?', 'i', NUM_PAGE_COLUMNS, $id);
}

function getP($p, $id) {
  if($p == "post") return getPost($id);
  else if($p == "page") return getPage($id);
}

function getYearRange($year) {
  return array(strtotime("$year-01-01 00:00:00"), strtotime("$year-12-31 23:59:59"));
}

function getMonthRange($year, $month) {
  if($month == 12) {
    return array(strtotime("$year-12-01 00:00:00"), strtotime(($year+1) . "-01-01 00:00:00")-1);
  } else {
    return array(strtotime("$year-$month-01 00:00:00"), strtotime("$year-" . ($month+1) . "-01 00:00:00")-1);
  }
}

function getDayRange($year, $month, $day) {
  return array(strtotime("$year-$month-$day 00:00:00"), strtotime("$year-$month-$day 23:59:59"));
}

function getRecentPosts($limit=DEFAULT_POSTS_DISPLAYED, $offset=0) {
  return getSQLResults('SELECT * FROM posts WHERE dated < ? OR dated IS NULL ORDER BY '
                     . 'COALESCE(dated,created) DESC, id DESC LIMIT ?,?',
                       'sii', NUM_POST_COLUMNS, date(SQLDATEFORMAT), $offset, $limit);
}

function getPostsBetweenDates($startDate, $endDate, $limit=DEFAULT_POSTS_DISPLAYED, $offset=NULL) {
  $sql = 'SELECT * FROM pages WHERE dated BETWEEN CAST(? AS DATE) AND CAST(? AS DATE)';
  if(!is_null($limit)) {
    if($is_null($offset)) {
      return getSQLResults($sql . ' LIMIT ?,?', 'iiii', NUM_POST_COLUMNS, $startDate, $endDate,
                           $offset, $limit);
    } else {
      return getSQLResults($sql . ' LIMIT ?', 'iii', NUM_POST_COLUMNS, $startDate, $endDate, $limit);
    }
  } else {
    return getSQLResults($sql, 'ii', NUM_POST_COLUMNS, $startDate, $endDate);
  }
}

function getPostOnDate($date, $limit=DEFAULT_POSTS_DISPLAYED, $offset=0) {
  $startTime = strtotime($date . " 00:00:00");
  $endTime = strtotime($date . " 23:59:59");
  return getSQLResults('SELECT * FROM posts WHERE dated BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?) LIMIT ?,?',
                       'iiii', NUM_POST_COLUMNS, $startTime, $endTime, $offset, $limit);
}

function getNextPostAfterDate($date) {
  return getSQLResults('SELECT id,dated FROM posts WHERE dated>FROM_UNIXTIME(?) ORDER BY dated ASC LIMIT 1', 'i', 2, $date);
}

function getPreviousPostBeforeDate($date) {
  return getSQLResults('SELECT id,dated FROM posts WHERE dated<FROM_UNIXTIME(?) ORDER BY dated DESC LIMIT 1', 'i', 2, $date);
}

function getDayFlag($year, $month) {
  $results = getSQLResults('SELECT dated FROM posts WHERE dated BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?)',
             'ii', 1, ...getMonthRange($year, $month));
  $dayFlag = 0;
  foreach($results as $i) {
    $dayFlag |= (1 << (intval(substr($i[0], 8, 2))-1));
  }
  return $dayFlag;
}

function getCategoriesForPost($id) {
  return getSQLResults('SELECT c.id,c.name FROM category_post_entries e INNER JOIN categories c ON e.category_id=c.id WHERE e.post_id=?',
                      'i', 2, $id);
}

function getCategoriesForPage($id) {
  return getSQLResults('SELECT c.id,c.name FROM category_page_entries e INNER JOIN categories c ON e.category_id=c.id WHERE e.page_id=?',
                      'i', 2, $id);
}

function getCategoriesForP($p, $id) {
  if($p == 'post') return getCategoriesForPost($id);
  else if($p == 'page') return getCategoriesForPage($id);
}

function getPFromCategory($p, $categoryID, $limit=DEFAULT_CATEGORIES_DISPLAYED, $offset=0) {
  if(isP($p)) {
    return getSQLResults("SELECT p.id,p.title FROM ${p}s p INNER JOIN category_${p}_entries c "
                       . "ON c.${p}_id=p.id WHERE c.category_id=? ORDER BY p.title ASC LIMIT ?,?",
                         'iii', 2, $categoryID, $offset, $limit);
  }
}

function getPagesFromCategory($categoryID, $limit=DEFAULT_CATEGORIES_DISPLAYED, $offset=0) {
  return getPFromCategory('page', $categoryID, $offset, $limit);
}

function getPostsFromCategory($categoryID, $limit=DEFAULT_CATEGORIES_DISPLAYED, $offset=0) {
  return getPFromCategory('post', $categoryID, $offset, $limit);
}

function getCategoryName($id) {
  return getOneSQLValue('SELECT name FROM categories WHERE id=?', 'i', $id);
}

function getCategoryID($name) {
  return getOneSQLValue('SELECT id FROM categories WHERE name=?', 's', $name);
}

function getUserName($id) {
  return getOneSQLValue('SELECT name FROM names where user_id=?', 'i', $id);
}

function getName($id) {
  return getOneSQLValue('SELECT name FROM names WHERE id=?', 'i', $id);
}

function getUserNameID($name) {
  return getOneSQLValue("SELECT id FROM names WHERE name=? AND user_id IS NOT NULL", 's', $name);
}

function getGuestNameID($name) {
  return getOneSQLValue("SELECT id FROM names WHERE name=? AND user_id IS NULL", 's', $name);
}

function getGuestNameIDOrCreate($name) {
  $nameID = getGuestNameID($name);
  if(isEmptyResult($nameID)) {
    $nameID = createName($name);
  }
  return $nameID;
}

function getNameAndUser($id) {
  return getOneSQLResult('SELECT name,user_id FROM names WHERE id=?', 'i', 2, $id);
}

function getNameIDForUser($userID) {
  return getOneSQLValue("SELECT id FROM names WHERE user_id=?", 'i', $userID);
}

function getUserID($name) {
  return getOneSQLValue('SELECT u.id FROM users u INNER JOIN names n ON n.user_id=u.id WHERE n.name=?',
                         's', $name);
}

function getNameURLByID($id) {
  global $BaseURL;
  $r = getNameAndUser($id);
  if(is_null($r[1])) {
    return $BaseURL . langurl('NAME') . '/' . $r[0];
  } else {
    return $BaseURL . langurl('USER') . '/' . $r[0];
  }
}

function getPostByName($name) {
  return getOneSQLResult('SELECT * FROM posts WHERE name=?', 's',  NUM_POST_COLUMNS, $name);
}

function getPageByName($name) {
  return getOneSQLResult('SELECT * FROM pages WHERE name=?', 's', NUM_PAGE_COLUMNS, $name);
}

function getCommentsForPost($id) {
  return getSQLResults('SELECT * FROM post_comments WHERE post_id=?', 'i', NUM_COMMENT_COLUMNS, $id);
}

function getCommentsForPage($id) {
  return getSQLResults('SELECT * FROM page_comments WHERE page_id=?', 'i', NUM_COMMENT_COLUMNS, $id);
}

function getPostComment($id) {
  return getOneSQLResult('SELECT * FROM post_comments WHERE id=?', 'i', NUM_COMMENT_COLUMNS, $id);
}

function getPageComment($id) {
  return getOneSQLResult('SELECT * FROM page_comments WHERE id=?', 'i', NUM_COMMENT_COLUMNS, $id);
}

function getComment($p, $id) {
  if($p == "post") return getPostComment($id);
  else if($p == "page") return getPageComment($id);
}

function countCommentsForPost($id) {
  return getOneSQLValue('SELECT COUNT(*) FROM post_comments WHERE post_id=?', 'i', $id);
}

function countCommentsForPage($id) {
  return getOneSQLValue('SELECT COUNT(*) FROM page_comments WHERE page_id=?', 'i', $id);
}

function countEntriesInCategory($p, $id) {
  if(isP($p)) {
    return getOneSQLValue("SELECT COUNT(*) FROM category_${p}_entries WHERE category_id=?", 'i', $id);
  }
}

function countPostsOnDate($date) {
  return getOneSQLValue("SELECT COUNT(*) FROM posts WHERE dated=CAST(? AS DATE)",
                        "i", $date);
}

function countPostsBetweenDates($startDate, $endDate) {
  return getOneSQLValue("SELECT COUNT(*) FROM posts WHERE dated BETWEEN "
                      . "CAST(? AS DATE) AND CAST(? AS DATE)", "i", $startDate, $endDate);
}

function countPostsInFeed() {
  $theCount = getSQLQuery("SELECT COUNT(*) FROM posts WHERE dated <= NOW() OR dated IS NULL");
  if(count($theCount) == 0 || count($theCount[0]) == 0 || is_null($theCount[0])
         || is_null($theCount[0][0])) {
    return 0;
  } else return $theCount[0][0];
}

function getPageTitlesAlphabetical() {
  return getSQLQuery('SELECT title FROM pages ORDER BY title ASC');
}

function getPageTitlesAlphabeticalWithID() {
  return getSQLQuery('SELECT id,title FROM pages ORDER BY title ASC');
}

function getPostTitlesAlphabetical() {
  return getSQLQuery('SELECT title FROM posts ORDER BY title ASC');
}

function getPostTitlesByDate() {
  return getSQLQuery('SELECT title,dated FROM posts ORDER BY dated DESC');
}

function getLimitedPostTitlesByDate($offset, $limit) {
  return getSQLResults('SELECT title,dated FROM posts ORDER BY dated DESC LIMIT ?,?', 'ii', 2,
                       $offset, $limit);
}

function getCategoriesAlphabetical() {
  return getSQLQuery('SELECT name FROM categories ORDER BY name ASC');
}

function getCategoriesAndSize($p, $option) {
  if(isP($p)) {
    $sql = "SELECT c.id,c.name,(SELECT COUNT(*) FROM category_${p}_entries p WHERE "
         . "p.category_id=c.id) FROM categories c";
    if($option == 0) $sql .= " ORDER BY 3 DESC, 2 ASC";
    else if($option == 1) $sql .= " ORDER BY 2 ASC";
    return getSQLQuery($sql);
  }
}

function getCategoriesBySize($p) {
  return getCategoriesAndSize($p, 0);
}

function getCategoriesWithSize($p) {
  return getCategoriesAndSize($p, 1);
}

function checkParametersForThreadedComments($p, $order) {
  if($p != 'post' && $p != 'page') {
    return FALSE;
  } else if($order != 'ASC' && $order != 'DESC') {
    return FALSE;
  } else {
    return TRUE;
  }
}

function getRepliesToComment($p, $id, $replyID, $order) {
  if(!isP($p) || !checkParametersForThreadedComments($p, $order)) {
    return FALSE;
  }
  $sql1 = "SELECT c.content,c.id,c.created,c.edited,c.name_id,n.name,n.user_id FROM "
        . "${p}_comments c INNER JOIN names n ON c.name_id=n.id WHERE ${p}_id=? AND reply_id";
  $sql2 = ' ORDER BY created ' . $order;
  if(is_null($replyID)) {
    return getSQLResults($sql1 . ' IS NULL' . $sql2, 'i', 7, $id);
  } else {
    return getSQLResults($sql1 . '=?' . $sql2, 'ii', 7, $id, $replyID);
  }
}

function getThreadedComments($p, $id, $replyID, $order) {
  if(isP($p)) {
    $threads = array();
    $results = getRepliesToComment($p, $id, $replyID, $order);
    for($i=0; $i<count($results); $i++) {
      array_push($results[$i], getThreadedComments($p, $id, $results[$i][1], $order));
      array_push($threads, $results[$i]);
    }
    return $threads;
  }
}

function getPostsBeforeID($id, $limit) {
  return getSQLResults('SELECT * FROM posts WHERE dated <= (SELECT dated FROM posts WHERE id=?) ORDER BY dated LIMIT ?',
                       'ii', NUM_POST_COLUMNS, $id, $limit);
}

function getPagesBeforeID($id, $limit) {
  return getSQLResults('SELECT * FROM pages WHERE dated <= (SELECT dated FROM pages WHERE id=?) ORDER BY dated LIMIT ?',
                       'ii', NUM_PAGE_COLUMNS, $id, $limit);
}

function getLatestPosts($limit) {
  return getSQLResults('SELECT * FROM posts ORDER BY dated DESC LIMIT ?', 'i', NUM_POST_COLUMNS, $limit);
}

function getPostsbyIDRange($startID, $limit) {
  return getSQLResults('SELECT * FROM posts ORDER BY id ASC LIMIT ?,?', 'ii', NUM_POST_COLUMNS,
                       $startID, $limit);
}

function getPByName($name) {
  $p = getPageByName($name);
  if(mysqli_num_rows($p) > 0) {
    return array('page', $p);
  } else {
    $p = getPostByName($name);
    if(mysqli_num_rows($p) > 0) {
      return array('post', $p);
    }
  }
  return array(NULL, NULL);
}

function getPendingComments($p, $limit=ADMIN_TABLE_LIMIT, $offset=0) {
  if(isP($p)) {
    return getSQLResults("SELECT * FROM pending_${p}_comments ORDER BY id ASC LIMIT ?,?", 'ii',
                         NUM_COMMENT_COLUMNS, $offset, $limit);
  }
}

function getPendingUsers($limit=ADMIN_TABLE_LIMIT, $offset=0) {
  return getSQLResults('SELECT * FROM pending_users ORDER BY id ASC LIMIT ?,?', 'ii', NUM_USER_COLUMNS,
                       $offset, $limit);
}

function getUsers($limit=ADMIN_TABLE_LIMIT, $offset=0) {
  return getSQLResults('SELECT * FROM users ORDER BY id ASC LIMIT ?,?', 'ii', NUM_USER_COLUMNS, $offset, $limit);
}

function getPs($p, $limit=NULL, $offset=NULL) {
  if(isP($p)) {
    if($p == 'page') {
      $sql = 'SELECT id,user_id,title,name,created,edited,path FROM pages ORDER BY id ASC';
      $nColumns = 7;
    } else if($p == 'post') {
      $sql ='SELECT id,user_id,title,name,created,edited,dated FROM posts ORDER BY id ASC';
      $nColumns = 7;
    }
    if(!is_null($offset) && !is_null($limit)) {
      return getSQLResults($sql . " LIMIT ?,?", 'ii', $nColumns, $offset, $limit);
    } else if(!is_null($limit)) {
      return getSQLResults($sql . " LIMIT ?", 'i', $nColumns, $limit);
    } else {
      return getSQLQuery($sql);
    }
  }
}

function getPosts($limit=NULL, $offset=NULL) {
  getPs('post', $limit, $offset);
}

function getPages($limit=NULL, $offset=NULL) {
  getPs('page', $limit, $offset);
}

function getFieldsOfP($p, $id) {
  if(isP($p)) {
    return getSQLResults("SELECT n.name,f.the_value FROM ${p}_fields f INNER JOIN field_names n "
                       . "ON f.field_id=n.id WHERE ${p}_id=?", 'i', 2, $id);
  }
}

function getFieldsOfPost($id) {
  return getFieldsOfP('post', $id);
}

function getFieldsOfPage($id) {
  return getFieldsOfP('page', $id);
}

function getFieldOfP($p, $id, $fieldName) {
  if(isP($p)) {
    return getOneSQLValue("SELECT f.the_value FROM ${p}_fields f INNER JOIN field_names n "
                         . "ON f.field_id=n.id WHERE n.name=? AND f.${p}_id=?", 'si',
                           $fieldName, $id);
  }
}

function getFieldOfPost($id, $fieldName) {
  return getFieldOfP('post', $id, $fieldName);
}

function getFieldOfPage($id, $fieldName) {
  return getFieldOfP('page', $id, $fieldID);
}

function getField($fieldName) {
  global $ID;
  return getFieldOfP(pContentType(), $ID, $fieldName);
}

function getFields() {
  global $ID;
  return getFieldsOfP(pContentType(), $ID);
}

function getFieldNameID($name) {
  return getOneSQLValue("SELECT id FROM field_names WHERE name=?", 's', $name);
}

function areCommentsPending() {
  global $conn;
  return ($conn->query('SELECT id FROM pending_comments LIMIT 1')->num_rows) > 0;
}

function areUsersPending() {
  global $conn;
  return ($conn->query('SELECT id FROM pending_users LIMIT 1')->num_rows) > 0;
}

function isEmptyString($s) {
  return strlen($s) == 0;
}

function commentParagraphs($content) {
  return '<p>' . implode('</p>\n<p>', array_filter(explode('\n', $content),
          function($x) {return strlen($x) > 0;})) . '</p>';
}

function setIndexPage($url) {
  $slashPos = strpos($url, '/');
  if($slashPos !== FALSE) {
    $theType = substr($url, 0, $slashPos);
    $theValue = substr($url, $slashPos+1);
    if(in_array($theType, array_map(function($x) {return langurl($x);}, array('POST', 'PAGE',
                        'FEED', 'INDEX', 'CATEGORY', 'NAME', 'USER')))) {
      setStringSetting(STRING_SETTING::indexPage, $theValue);
    }
  } else if(in_array($url, array('', 'feed', 'index'))) {
    setStringSetting(STRING_SETTING::indexPage, "");
  }
}

function verifyRegistration($name, $password, $confirmPassword, $email) {
  global $ErrorMessage;
  $isValid = TRUE;
  if(strlen($name) == 0) {
    $ErrorMessage .= lang("USERNAME_BLANK") . "\n";
    $isValid = FALSE;
  }
  else if(isNameRegistered($name) || isNamePending($name)
          || (!getFlag(...FLAGS::registerUnname) && isNameUsed($name))) {
    $ErrorMessage .= lang("NAME_USED")."\n";
    $isValid = FALSE;
  }
  if(strlen($password) == 0) {
    $ErrorMessage .= lang('PASSWORD_BLANK')."\n";
    $isValid = FALSE;
  }
  if(!validatePassword($password)) {
    $isValid = FALSE;
  }
  if($password != $confirmPassword) {
    $ErrorMessage .= lang('PASSWORDS_NOT_MATCHED')."\n";
    $isValid = FALSE;
  }
  if(strlen($email) == 0) {
    $ErrorMessage .= lang('EMAIL_BLANK');
    $isValid = FALSE;
  } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $ErrorMessage .= lang('EMAIL_INVALID')."\n";
    $isValid = FALSE;
  } else if(isEmailUsed($email)) {
    $ErrorMessage .= lang('EMAIL_USED')."\n";
    $isValid = FALSE;
  }
  return $isValid;
}

function newUser($name, $password, $email) {
  // verifyRegistration should be called before calling newUser
  if(getFlagOption('approveRegistration')) $databaseString = "pending_"; else $databaseString = "";
  $nameResult = getOneSQLResult('SELECT id,user_id FROM names WHERE name=?', 's', 2, $name);
  $nanoResults = nanodate();
  $nanoseconds = $nanoResults[1];
  $timeRegistered = $nanoResults[0];
  $theHash = password_hash($password . '-' . $timeRegistered . $nanoseconds, PASSWORD_DEFAULT);
  $theFlag = 2*getFlagOption('autoModerateNewUsers');
  $userID = insertSQL("INSERT INTO ${databaseString}users (password, email, time_registered, "
          . "nanoseconds_registered, flag) VALUES (?,?,?,?,?)",
            'sssii', $theHash, $email, $timeRegistered, $nanoseconds, $theFlag);
  if($databaseString == "") {
    insertSQL('INSERT INTO names (name, user_id) VALUES (?,?)', 'si', $name, $userID);
    return $userID;
  } else {
    generalSQL('UPDATE pending_users SET name=? WHERE id=?', 'si', $name, $userID);
    return NULL;
  }
}

function newCategory($name) {
  if(trim($name) != "") {
    return insertSQL("INSERT INTO categories (name) VALUES (?)", 's', trim($name));
  }
}

function addCategoryByID($p, $categoryID, $pID) {
  if(isP($p)) {
    return insertSQL("INSERT INTO category_${p}_entries (${p}_id, category_id) VALUES (?,?)",
                     'ii', $pID, $categoryID);
  }
}

function addCategoryByName($p, $categoryName, $pID) {
  $categoryID = getOneSQLValue("SELECT id FROM categories WHERE name=?",
                's', $categoryName);
  if(is_null($categoryID)) {
    $categoryID = newCategory($categoryName);
  }
  return addCategoryByID($p, $categoryID, $pID);
}

function removeCategory($p, $categoryID, $pID) {
  if(isP($p)) {
    generalSQL("DELETE FROM category_${p}_entries WHERE ${p}_id=? AND category_id=?",
               'ii', $pID, $categoryID);
  }
}

function addFieldName($name) {
  if($name != '') {
    return insertSQL("INSERT INTO field_names (name) VALUES (?)", 's', $name);
  }
}

function setFieldForP($p, $id, $name, $value) {
  if(isP($p) && trim($name) != '') {
    $name = trim($name); $value = trim($value);
    $nameID = getFieldNameID($name);
    if(is_null($nameID)) {
      $nameID = addFieldName($name);
    }
    if(isEmptyResult(getFieldOfP($p, $id, $name))) {
      insertSQL("INSERT INTO ${p}_fields (field_id, ${p}_id, the_value) VALUES (?,?,?)",
                'iis', $nameID, $id, $value);
    } else {
      generalSQL("UPDATE ${p}_fields SET the_value=? WHERE ${p}_id=? AND field_id=?",
                 "sii", $value, $id, $nameID);
    }
  }
}

function removeFieldFromP($p, $id, $name) {
  if(isP($p)) {
    generalSQL("DELETE f FROM ${p}_fields f INNER JOIN field_names n ON f.field_id=n.id "
             . "WHERE n.name=? AND ${p}_id=?", 'si', $name, $id);
  }
}

function newP($p, $content, $title, $name, $categories, $user, $metaDescription, $metaKeywords,
              $fieldNames, $fieldValues, $dated=NULL, $path=NULL) {
  if(isP($p) && canUserPost()) {
    if(strpos($name, '.') !== FALSE) {
      $name = str_replace('.', '-', $name);
    }
    $entryID = insertSQL("INSERT INTO ${p}s (user_id, title, name, content, "
                       . "meta_description, meta_keywords, created, edited) VALUES "
                       . "(?,?,?,?,?,?,?,?)",
                         'isssssss', $user, $title, $name, $content, $metaDescription,
                         $metaKeywords, date(SQLDATEFORMAT), date(SQLDATEFORMAT));
    if($p == 'post') generalSQL("UPDATE posts SET dated=? WHERE id=?", "si", $dated, $entryID);
    else if($p == 'page') generalSQL("UPDATE pages SET path=? WHERE id=?", "si", $path, $entryID);
    foreach(array_map(function($x) {return trim($x);}, explode(';', $categories)) as $category) {
      addCategoryByName($p, $category, $entryID);
    }
    for($i=0; $i<count($fieldNames) && $i<count($fieldValues); $i++) {
      setFieldForP($p, $entryID, $fieldNames[$i], $fieldValues[$i]);
    }
    commit();
    return $entryID;
  }
}

function newPost($content, $title, $name, $categories, $user, $metaDescription, $metaKeywords,
                 $fieldNames, $fieldValues) {
  return newP('post', $content, $title, $name, $categories, $user, $metaDescription,
              $metaKeywords, $fieldNames, $fieldValues);
}

function newPage($content, $title, $name, $categories, $user, $metaDescription, $metaKeywords,
                 $fieldNames, $fieldValues) {
  return newP('page', $content, $title, $name, $categories, $user, $metaDescription,
              $metaKeywords, $fieldNames, $fieldValues);
}

function newComment($p, $content, $entry, $replyTo, $nameID) {
  $theTime = date(SQLDATEFORMAT);
  $extra = "";
  if(getFlagOption('moderateAllComments')
          || (isLoggedIn() && (getUserFlagByName('moderated')))
          || (!isLoggedIn() && (getFlagOption('moderateGuestComments')))) {
    $extra = "pending_";
  }
  $commentID = insertSQL("INSERT INTO ${extra}${p}_comments (${p}_id, reply_id, name_id, created, "
             . "edited, content) VALUES (?,?,?,?,?,?)", 'iiisss', $entry, $replyTo, $nameID,
               $theTime, $theTime, $content);
  if($extra == "") return $commentID;
  else return NULL;
}

function newCommentOnPost($content, $postID, $replyTo, $nameID) {
  return newComment('post', $content, $postID, $replyTo, $nameID);
}

function newCommentOnPage($content, $pageID, $replyTo, $nameID) {
  return newComment('page', $content, $pageID, $replyTo, $nameID);
}

function addUpdateToSQLString($key, $counter) {
  $s = "";
  if($counter > 0)
    $s .= ", ";
  return $s . $key . "=?";
}

function editCategories($p, $id, $categories) { // $categories is an array of category names
  if(isP($p)) {
    if($p == 'post') $currentCategories = getCategoriesForPost($id);
    if($p == 'page') $currentCategories = getCategoriesForPage($id);
    $currentCategoryNames = array_map(function($x) {return $x[1];}, $currentCategories);
    foreach($categories as $i) {
      if(!in_array($i, $currentCategoryNames)) {
        addCategoryByName($p, $i, $id);
      }
    }
    foreach($currentCategoryNames as $i) {
      if(!in_array($i, $categories)) {
        removeCategory($p, getCategoryID($i), $id);
      }
    }
  }
}

function editFields($p, $id, $names, $values) {
  if(isP($p)) {
    $currentFields = getFieldsOfP($p, $id);
    foreach($currentFields as $i) {
      if(!in_array($i[0], $names)) {
        removeFieldFromP($p, $id, $i[0]);
      }
    }
    $currentFieldNames = array_map(function($x) {return $x[0];}, $currentFields);
    for($i=0; $i<min(count($names), count($values)); $i++) {
      $fieldIndex = array_search($names[$i], $currentFieldNames);
      if($fieldIndex === FALSE || $currentFieldValues[$fieldIndex] != $values[$i]) {
        setFieldForP($p, $id, $names[$i], $values[$i]);
      }
    }
  }
}

function editP($p, $id, $content, $title, $name, $categories, $metaDescription, $metaKeywords,
               $fieldNames, $fieldValues, $path=NULL, $dated=NULL) {
  if(isP($p)) {
    $sql = "UPDATE " . $p . "s SET ";
    $values = array();
    $parameters = array('content' => $content, 'title' => $title, 'name' => $name,
                  'meta_description' => $metaDescription, 'meta_keywords' => $metaKeywords);
    foreach($parameters as $column => $variable) {
      if(!is_null($variable)) {
        $sql .= addUpdateToSQLString($column, count($values));
        array_push($values, $variable);
      }
    }
    if(count($values) > 0) {
      $sql .= ", edited=? WHERE id=?";
      array_push($values, date(SQLDATEFORMAT));
      array_push($values, $id);
      generalSQL($sql, str_repeat('s', count($values)-1) . 'i', ...$values);
    }
    if($p == 'post' && !is_null($dated)) {
      generalSQL("UPDATE posts SET dated=? WHERE id=?", 'si', $dated, $id);
    } else if($p == 'page' && !is_null($path)) {
      generalSQL("UPDATE pages SET path=? WHERE id=?", 'si', $path, $id);
    }
    if(!is_null($fieldNames) && !is_null($fieldValues)) {
      $fieldNames = trimArray($fieldNames);
      $fieldValues = trimArray($fieldValues);
      editFields($p, $id, $fieldNames, $fieldValues);
    }
    if(!is_null($categories)) {
      editCategories($p, $id, trimArray(explode(";", $categories)));
    }
  }
}

function deleteP($p, $id) {
  if(isP($p)) {
    generalSQL("DELETE FROM ${p}_comments WHERE ${p}_id=?", 'i', $id);
    generalSQL("DELETE FROM pending_${p}_comments WHERE ${p}_id=?", 'i', $id);
    generalSQL("DELETE FROM category_${p}_entries WHERE ${p}_id=?", 'i', $id);
    generalSQL("DELETE FROM ${p}s WHERE id=?", 'i', $id);
  }
}

function approveComment($p, $id) {
  if($p == 'post' || $p == 'page') {
    $comment = getOneSQLResult('SELECT * FROM pending_' . $p . '_comments WHERE id=?', 'i',
                               NUM_COMMENT_COLUMNS, $id);
    $commentID = insertSQL('INSERT INTO ' . $p . '_comments (' . $p . '_id, reply_id, name_id, content, '
                   . 'created, edited) VALUES (?,?,?,?,?,?)', 'iiisss', $comment[1], $comment[2],
                   $comment[3], $comment[4], $comment[5], $comment[6]);
    generalSQL('DELETE FROM pending_' . $p . '_comments WHERE id=?', 'i', $id);
    return $commentID;
  }
}

function declineComment($p, $id) {
  if($p == 'post' || $p == 'page') {
    generalSQL('DELETE FROM pending_' . $p . '_comments WHERE id=?', 'i', $id);
  }
}

function approvePostComment($id) {
  return approveComment('post', $id);
}

function approvePageComment($id) {
  return approveComment('page', $id);
}

function declinePostComment($id) {
  return declineComment('post', $id);
}

function declinePageComment($id) {
  return declineComment('page', $id);
}

function removePendingUser($id) {
  generalSQL("DELETE FROM pending_users WHERE id=?", 'i', $id);
}

function approvePendingUser($id) {
  $user = getOneSQLResult("SELECT * FROM pending_users WHERE id=?", 'i', NUM_USER_COLUMNS+1, $id);
  if(!isEmptyResult($user)) {
    $newID = insertSQL("INSERT INTO users (id, password, email, time_registered, "
                     . "nanoseconds_registered, flag) VALUES (?,?,?,?,?,?)", 'isssii',
                     ...array_merge(array_slice($user, 0, 5), $user[6]));
    if($newID !== FALSE && !is_null($newID)) {
      insertSQL("INSERT INTO names (name,user_id) VALUES (?,?)", 'si', $user[5], $newID);
      removePendingUser($id);
      return $newID;
    }
  }
  return NULL;
}

function declinePendingUser($id) {
  removePendingUser($id);
}

$commentForm1 = <<<'EOD'
<div class="commentForm">
  <form method="POST" action="%s">
    <input type="hidden" name="action" value="%scomment"/>
EOD;
$commentForm2 = <<<'EOD'
    <label for="commentName%s">%s</label>
    <input id="commentName%s type="text" name="name" size="30"/><br/>
EOD;
$commentForm3 = <<<'EOD'
    <input type="hidden" name="replyTo" value="%s"/>
EOD;
$commentForm4a = <<<'EOD'
    <label for="comment%s">%s</label><br/>
    <textarea id="comment%s" name="comment" rows="5" cols="60"></textarea><br/>
    <input type="hidden" name="id" value="%s"/>
    <input class="g-recaptcha"
EOD;
$commentForm4b = <<<'EOD'
 type="submit" name="submit" value="%s"/>
  </form>
</div>
EOD;

function commentForm($p, $id, $replyID=NULL) {
  global $HomeURL, $commentForm1, $commentForm2, $commentForm3, $commentForm4a, $commentForm4b;
  $commentForm4 = $commentForm4a . buttonRecaptcha() . $commentForm4b;
  $s = "";
  if(isP($p) && canUserComment($p, $id)) {
    $s .= sprintf($commentForm1, $HomeURL . $_GET['path'], $p);
    if(!isLoggedIn() && getFlagOption('guestsCanComment')) {
      $s .= sprintf($commentForm2, $id, lang('NAME'), $id);
    }
    if(!is_null($replyID)) {
      $s .= sprintf($commentForm3, $replyID);
    }
    $s .= sprintf($commentForm4, $id, lang('COMMENT'), $id, $id, lang('SUBMIT'));
  }
  return $s;
}

function replyToCommentScript($p, $id) {
  echo "  var commentForm = \"";
  echo escapeReturns(commentForm($p, $id, "@"));
  echo "\";";
?>
  $(document).ready(function() {
    $(".comment").one("click", ".replyToComment", function(e) {
      var id = $(e.target).attr("id").substring(14);
      $(e.target).parent().append(commentForm.replace("@", id));
    });
  });
<?php
}

function getPostURL($id) {
  global $BaseURL;
  $option = 2*getFlag(8,1) + getFlag(8,0);
  if($option == 0) {
    return $BaseURL . langurl('POST') . '/' . $id;
  } else if($option == 1) {
    $result = getPost($id);
    if(!isEmptyResult($result)) {
      return $BaseURL . langurl('POST') . '/' . $result[3];
    }
  } else if($option == 2) {
    $result = getPost($id);
    if(!isEmptyResult($result)) {
      return $HomeURL . $result[3];
    }
  } else if($option == 3) {
    $result = getPost($id);
    if(!isEmptyResult($result)) {
      return $BaseURL . substr($result[7], 0, 4) . '/' . substr($result[7], 5, 2) . '/'
             . substr($result[7], 8, 2) . '#' . $result[3];
    }
  }
  return $BaseURL;
}

function getPageURL($id) {
  global $HomeURL;
  $option = 2*getFlag(8,3) + getFlag(8,2);
  if($option == 0) {
    return $HomeURL . langurl('PAGE') . '/' . $id;
  } else if($option == 1) {
    $result = getPage($id);
    if(!isEmptyResult($result)) {
      return $HomeURL . langurl('PAGE') . '/' . $result[3];
    }
  } else if($option == 2) {
    $result = getPage($id);
    if(!isEmptyResult($result)) {
      return $HomeURL . '/' . $result[3];
    }
  }
  return $HomeURL;
}

function getPURL($p, $id) {
  if($p == "post") return getPostURL($id);
  else if($p == "page") return getPageURL($id);
}

function getCategoryURL($p, $id) {
  global $HomeURL;
  if(($p)) {
    if(getFlag(8,3)) {
      return $HomeeURL . langurl('CATEGORY') . '/' . langurl(strtoupper($p)) . '/' . $id;
    } else {
      $result = getCategoryName($id);
      if(!isEmptyResult($result)) {
        return $HomeURL . langurl('CATEGORY') . '/' . langurl(strtoupper($p)) . '/' . $result;
      }
    }
  }
  return $HomeURL;
}

function getUserURL($id) {
  global $BaseURL;
  if(getFlag(8,4)) {
    return $BaseURL . langurl('USER') . '/' . $id;
  } else {
    $result = getUserName($id);
    if($result->num_rows > 0) {
      return $BaseURL . langurl('USER') . '/' . $result[0];
    }
  }
  return $BaseURL;
}

function getNameURL($id) {
  global $BaseURL;
  if(getFlag(8,5)) {
    return $BaseURL . langurl('NAME') . '/' . $id;
  } else {
    $result = getName($id);
    if($result->num_rows > 0) {
      return $BaseURL . langurl('NAME') . '/' . $result[0];
    }
  }
  return $BaseURL;
}

function getFeedURL() {
  global $HomeURL;
  $indexPage = getStringSettingByName('indexPage');
  if(isFeedPage($indexPage)) {
    return $HomeURL;
  } else {
    return $HomeURL . langurl('feed');
  }
}

function getURLForResults($url, $limit, $offset) {
  return "${url}/limit/$limit/$offset";
}

function printResultsTab($url, $limit, $offset, $total) {
  if($total > $limit) {
    echo "<div class=\"resultTabs\">";
    $currentTab = intdiv($offset, $limit);
    if($currentTab > 0) {
      echo "<a href=\"" . getURLForResults($url, $limit, max(0, $offset-$limit))
         . "\">&#x1f838; " . lang('PREV_PAGE') . "</a>";
    }
    echo " | ";
    if($limit + $offset < $total) {
      echo "<a href=\"" . getURLForResults($url, $limit, min($total, $offset+$limit))
         . "\">" . lang('NEXT_PAGE') . " &#x1f83a;</a>";
    }
    echo "<br/>";
    for($i=0; $i<=intdiv($total, $limit); $i++) {
      if($i != 0) echo " | ";
      if($offset != $i*$limit) {
        echo "<a href=\"" . getURLForResults($url, $limit, $i*$limit) . "\">" . (1+$i) . "</a>";
      } else {
        echo "<strong>" . (1+$i) . "</strong>";
      }
    }
    echo "</div>";
  }
}

function printResultsTabForCategory() {
  global $ID, $OffsetAndLimit, $PType;
  printResultsTab(getCategoryURL($PType, $ID), getGlobalLimit(DEFAULT_CATEGORIES_DISPLAYED),
                  getGlobalOffset(0), countEntriesInCategory($PType, $ID));
}

function printResultsTabForDateRange($year, $month=0, $day=0) {
  global $ID, $OffsetAndLimit, $HomeURL;
  $url = $HomeURL . langurl('POST') . '/' . $year;
  $dateRange = getDateRange($year, $month, $day);
  $theCount = countPostsBetweenDates(...$dateRange);
  if($month != 0) {
    $url .= '/' . $month;
    if($day != 0) {
      $url .= '/' . $day;
    }
  }
  printResultsTab($url, $OffsetAndLimit[0], $OffsetAndLimit[1], $theCount);
}

function printResultsTabForFeed() {
  global $OffsetAndLimit, $HomeURL;
  $url = $HomeURL;
  if(!isFeedPage(getStringSettingByName('indexPage'))) {
    $url .= langurl('FEED') . '/';
  }
  printResultsTab($url, getGlobalLimit(DEFAULT_POSTS_DISPLAYED), getGlobalOffset(0),
                  countPostsInFeed());
}

function printResultsTabForActive() {
  global $ContentType, $Year, $Month, $Day;
  if($ContentType == 'feed') {
    printResultsTabForFeed();
  } else if($ContentType == 'date') {
    if(!isset($Month)) printResultsTabForDateRange($Year);
    else if(!isset($Day)) printResultsTabForDateRange($Year, $Month);
    else printResultsTabForDateRange($Year, $Month, $Day);
  } else if($ContentType == 'category') {
    printResultsTabForCategory();
  }
}

function userType($userID) {
  if(is_null($userID)) {
    return "guest";
  } else if($userID == 0) {
    return "owner";
  } else {
    return "registered";
  }
}

function nameTag($name, $id) {
  global $HomeURL;
  if(is_null($id)) {
    $directory = langurl('NAME');
  } else {
    $directory = langurl('USER');
  }
  $userType = userType($id);
  return "<span class=\"nameTag user_$userType\"><span class=\"userType\">" 
       . lang("USER_" . strtoupper($userType)) . "</span> - "
       . "<a href=\"$HomeURL/" . langurl('NAME') . "/$name\">$name</a></span>";
}

function printComments($commentThreads, $indentLevel) {
  global $CommenterName, $CommentTime, $CommentEditTime, $Comment, $CommenterUserID,
         $CommentIndent, $CommentID, $ID, $CommenterNameID;
  for($i=0; $i<count($commentThreads); $i++) {
    $CommentID = $commentThreads[$i][1];
    $CommenterName = $commentThreads[$i][5];
    $CommentTime = $commentThreads[$i][2];
    $CommentEditTime = $commentThreads[$i][3];
    $Comment = $commentThreads[$i][0];
    $CommenterUserID = $commentThreads[$i][6];
    $CommenterNameID = $commentThreads[$i][4];
    $CommentIndent = min($indentLevel, MAX_COMMENT_INDENT);
    getSection('comment');
    if(!is_null($commentThreads[$i][7])) {
      printComments($commentThreads[$i][7], $indentLevel+1);
    }
  }
}

function getSaltedPassword($password, $timeRegistered, $nanoseconds) {
  return $password . '-' . $timeRegistered . $nanoseconds;
}

function getPasswordHashForUser($name) {
  $result = getUserID($name);
  if($result->num_rows == 0) {
    return NULL;
  }
  return getOneSQLResult('SELECT password,time_registered,nanoseconds FROM users WHERE id=?', 'i', 3, $result[0]);
}

function verifyPassword($password, $hash, $timeRegistered, $nanoseconds) {
  return password_verify(getSaltedPassword($password, $timeRegistered, $nanoseconds), $hash);
}

function getUserCredentialsByName($name) {
  return getOneSQLResult('SELECT u.id,u.password,u.time_registered,u.nanoseconds_registered FROM users u '
                       . 'INNER JOIN names n ON n.user_id=u.id WHERE n.name=?', 's', 4, $name);
}

function verifyPasswordForUser($name, $password) {
  $credentials = getUserCredentialsByName($name);
  if(count($credentials) <= 1) return lang("USERNAME_NOT_FOUND");
  else if(verifyPassword($password, $credentials[1], $credentials[2], $credentials[3])) return $credentials[0];
  else return lang("PASSWORD_INCORRECT");
}

function getIntSettingOrZero($option) {
  $value = getIntSetting($option);
  if(!is_null($value)) return $value; else return 0;
}

function getMinPasswordLength() {
  return getIntSettingOrZero(2);
}

function getMinPasswordDigits() {
  return getIntSettingOrZero(3);
}

function getMinPasswordLetters() {
  return getIntSettingOrZero(4);
}

function getMinPasswordCapitalLetters() {
  return getIntSettingOrZero(5);
}

function getMinPasswordLowerCaseLetters() {
  return getIntSettingOrZero(6);
}

function getMinPasswordSymbols() {
  return getIntSettingOrZero(7);
}

function countCharacters($s) {
  $count = array(0, 0, 0, 0, 0); // 0:letters, 1:capital letters, 2:lower case letters, 3:digits, 4:symbols
  for($i=0; $i<strlen($s); $i++) {
    if(ctype_alpha($s[$i])) {
      $count[0]++;
      if(ctype_upper($s[$i])) {
        $count[1]++;
      } else if(ctype_lower($s[$i])) {
        $count[2]++;
      }
    } else if(ctype_digit($s[$i])) {
      $count[3]++;
    } else {
      $count[4]++;
    }
  }
  return $count;
}

function validatePassword($password) {
  global $ErrorMessage;
  $count = countCharacters($password);
  $isValid = TRUE;
  if(getMinPasswordLength() > strlen($password)) {
    $ErrorMessage .= lang("PASSWORD_TOO_SHORT")."\n";
    $isValid = FALSE;
  }
  if(getMinPasswordLetters() > $count[0]) {
     $ErrorMessage .= lang("PASSWORD_MORE_LETTERS")."\n";
    $isValid = FALSE;
  }
  if(getMinPasswordCapitalLetters() > $count[1]) {
    $ErrorMessage .= lang("PASSWORD_MORE_UPPER")."\n";
    $isValid = FALSE;
  }
  if(getMinPasswordLowerCaseLetters() > $count[2]) {
    $ErrorMessage .= lang("PASSWORD_MORE_LOWER")."\n";
    $isValid = FALSE;
  }
  if(getMinPasswordDigits() > $count[3]) {
    $ErrorMessage .= lang("PASSWORD_MORE_NUMBERS")."\n";
    $isValid = FALSE;
  }
  if(getMinPasswordSymbols() > $count[4]) {
    $ErrorMessage .= lang("PASSWORD_MORE_SYMBOLS")."\n";
    $isValid = FALSE;
  }
  return $isValid;
}

function getDatalistForEntries() {
  $postTitles = getPostTitlesAlphabetical();
  $pageTitles = getPageTitlesAlphabetical();
  $s = '<dataList id="searchTitlesDatalist">' . "\n";
  foreach($postTitles as $i) {
    $s .= "  <option value=\"$i[0]\">\n";
  }
  foreach($pageTitles as $i) {
    $s .= "  <option value=\"$i[0]\">\n";
  }
  return "</datalist>\n";
}

function makeSettingBox($flagBit, $text, $flagValue) {
?>
  <input id="<?php echo $text;?>_checkbox" type="checkbox" name="checkboxSetting[<?php
         echo $flagBit[1];?>]"<?php if(((1 << $flagBit[1]) & $flagValue) > 0) echo " checked=\"checked\"";
         ?>/>
  <label for="<?php echo $text;?>_checkbox"><?php echo lang($text);?></label><br/>
<?php
}

function makeRadioChecked($flagBit, $size, $flagValue, $option) {
  if($size <= 2) {
    if((((1 << $flagBit) & $flagValue) >> $flagBit) == $option) return " checked=\"checked\"";
    else return "";
  } else if($size == 3 || $size == 4) {
    $settingNumber = (((1 << ($flagBit+1)) & $flagValue) + (1 << $flagBit & $flagValue)) >> $flagBit;
    if($settingNumber == $option) return " checked=\"checked\"";
    else return "";
  }
}

function makeSettingRadio($flagBit, $labelArray, $flagValue) {
  echo "  <fieldset>\n    <legend>" . lang($labelArray[0]) . "</legend>\n";
  for($i=1; $i<count($labelArray); $i++) {
    $inputID = $labelArray[0] . $i;
    echo "    <input id=\"url$inputID\" type=\"radio\" name=\"url${labelArray[0]}\" value=\"$i\""
         . makeRadioChecked($flagBit, count($labelArray)-1, $flagValue, $i-1) . "/>\n";
    echo "    <label for=\"url$inputID\">" . $labelArray[$i] . "</label>\n";
  }
  echo "  </fieldset><br/>\n";
}

function shouldApplyRecaptcha() {
  return getFlagOption('useRecaptcha') && !isLoggedIn();
}

function stripExtraSlashes($s0) {
  if(strlen($s0) == 0) return "";
  $s = $s0[0];
  for($i=1; $i<strlen($s0); $i++) {
    if($s0[$i-1] != '/' || $s0[$i] != '/') {
      $s .= $s0[$i];
    }
  }
  return $s;
}

?>


