<?php
global $OffsetAndLimit, $ContentType, $PostArray, $PType;

function setPostArrayForDate() {
  global $PostArray, $Year, $Month, $Day;
  $limit = getGlobalLimit(DEFAULT_POSTS_DISPLAYED);
  $offset = getGlobalOffset(0);
  if(isset($Year)) {
    if(isset($Month)) {
      if(isset($Day)) {
        $PostArray = getPostOnDate("$Year-$Month-$Day", $limit, $offset);
      } else {
        $theRange = getMonthRange($Year, $Month);
        $PostArray = getPostsBetweenDates($theRange[0], $theRange[1], $limit, $offset);
      }
    } else {
      $theRange = getYearRange($Year);
      $PostArray = getPostsBetweenDates($theRange[0], $theRange[1], $limit, $offset);
    }
  }
}

function setPostArrayForFeed() {
  global $PostArray;
  $limit = getGlobalLimit(DEFAULT_POSTS_DISPLAYED);
  $offset = getGlobalOffset(0);
  $PostArray = getRecentPosts($limit, $offset);
}

function setPostArray() {
  global $PostArray, $ContentType;
  if($ContentType == 'date') {
    setPostArrayForDate();
  } else {
    setPostArrayForFeed();
  }
}

function loadMultiPost() {
  setPostArray();
  getSection('multipost');
}

function loadSinglePost() {
  global $ID;
  setPData(getPost($ID));
  getSection('singlepost');
}

function loadPage() {
  global $ID;
  setPData(getPage($ID));
  getSection('page');
}

function loadUser() {
  getSection('user');
}

function loadName() {
  getSection('name');
}

function loadCategory($pathElements) {
  setCategoryData($pathElements);
  getSection('category');
}

function setDateData() {
  global $Year, $Month, $Day, $DayOfWeek;
  $pathElements = getPathElements();
  $Year = $pathElements[0];
  if(count($pathElements) >= 2 && is_numeric($pathElements[1])) {
    $Month = $pathElements[1];
    if(count($pathElements) >= 3 && is_numeric($pathElements[2])) {
      $Day = $pathElements[2];
      $DayOfWeek = dayOfWeek($Year, $Month, $Day);
    }
  }
}

function setDateSingleOrMulti() {
  global $PostArray, $ErrorMessage;
  if(count($PostArray) >= 2) {
    getSection('multipost');
  } else if(count($PostArray) == 1) {
    setPData($PostArray[0]);
    getSection('singlepost');
  } else {
    $ErrorMessage .= lang('NO_MORE_POSTS_ON_DATE') . "\n";
  }
}

function setDateByName() {
  global $Year, $Month, $Day, $DayOfWeek;
  $p = getPostByName($pathElements[3]);
  if(date('Ymd', $p[7]) == ($Year . str_pad($Month, 2, '0') . str_pad($Day, 2, '0'))) {
    setPData($p);
    $ContentType = 'post';
  } else {
    $ContentType = 'error';
    $ErrorMessage .= lang('NO_POST_W_NAME_ON_DATE');
  }
}

function loadDate($pathElements) {
  global $PostArray, $Year, $Month, $Day, $DayOfWeek, $ContentType, $ErrorMessage;
  setDateData();
  echo "| $Year - $Month - $Day |";
  setPostArray();
  print_r($pathElements); echo $ContentType;
  if(count($pathElements) > 0 && is_numeric($pathElements[count($pathElements)-1])) {
    setDateSingleOrMulti();
  } else {
    setDateByName();
  }
}

function loadIndex() {
  global $ContentType;
  $indexPage = getStringSettingByName('indexPage');
  if(isFeedPage($indexPage)) {
    $ContentType = 'feed';
    loadMultiPost();
  } else {
    setContentType(explode("/", $indexPage));
  }
}

$pathElements = getPathElements();
if(isset($_POST['submit'])) {
  if($ContentType == 'admin') {
    require_once 'admin.php';
  } else if($ContentType != 'login' && $ContentType != 'logout' && $ContentType != 'register') {
    require_once 'submit.php';
  }
}
if($ContentType == 'index') {
  loadIndex();
} else if($ContentType == 'post') {
  loadSinglePost();
} else if($ContentType == 'page') {
  loadPage();
} else if($ContentType == 'date') {
  loadDate($pathElements);
} else if($ContentType == 'multipost') {
  loadMultiPost();
} else if($ContentType == 'category') {
  loadCategory($pathElements);
} else if($ContentType == 'user') {
  loadUser();
} else if($ContentType == 'name') {
  loadName();
} else if($ContentType == 'error') {
  getSection('error');
} else if($ContentType == 'login') {
  require_once 'login.php';
} else if($ContentType == 'logout') {
  require_once 'logout.php';
} else if($ContentType == 'register') {
  require_once 'register.php';
}
?>
