<?php
function getPathElements() {
  return array_values(array_filter(explode('/', $_GET['path']), function($x) {return strlen($x) > 0;}));
}

function setCategoryData($pathElements) {
  global $ID, $CategoryName, $PType;
  if(isP($pathElements[1])) {
    $PType = $pathElements[1];
    if(is_numeric($pathElements[2])) {
      $ID = intval($pathElements[2]);
      $CategoryName = getCategoryName($ID);
    } else {
      $CategoryName = $pathElements[2];
      $ID = getCategoryID($CategoryName);
    }
  }
}

function setUserPageData() {
}

function setNamePageData() {
}

function processPostURL($pathElements) {
  global $PathArray, $ContentType;
  if(count($pathElements) >= 3 && is_numeric($pathElements[1] && is_numeric($pathElements[2]))) {
    $PostArray = getPostsbyIDRange(intval($pathElements[1]), intval($pathElements[2]));
    $ContentType = 'multipost';
  } else if(count($pathElements) >= 2 && is_numeric($pathElements[1])) {
    setPData(getPost($pathElements[1]));
  } else {
    setPData(getPostByName($pathElements[1]));
  }
}

function processPageURL($pathElements) {
  global $PathArray;
  if(count($pathElements) >= 2 && is_numeric($pathElements[1])) {
    setPData(getPage($pathElements[1]));
  } else {
    setPData(getPageByName($pathElements[1]));
  }
}

function getOffset($pathElements) {
  $i = 0;
  while($i < count($pathElements) && $pathElements[$i] != langurl('LIMIT')) {
    $i++;
  }
  $offsetLimit = array();
  if($i < count($pathElements)) {
    if($i + 1 < count($pathElements) && is_numeric($pathElements[$i+1])) {
      array_push($offsetLimit, intval($pathElements[$i+1]));
      if($i + 2 < count($pathElements) && is_numeric($pathElements[$i+2])) {
        array_push($offsetLimit, intval($pathElements[$i+2]));
      }
    }
  }
  return $offsetLimit;
}

function setContentType($pathElements) {
  global $ContentType, $OffsetAndLimit, $ErrorMessage;
  $OffsetAndLimit = getOffset($pathElements);
  if(count($pathElements) == 0 || $pathElements[0] == langurl('LIMIT')) {
    $ContentType = 'index';
  } else if($pathElements[0] == langurl('ADMIN')) {
    $ContentType = 'admin';
  } else if($pathElements[0] == langurl('LOGIN')) {
    $ContentType = 'login';
  } else if($pathElements[0] == langurl('LOGOUT')) {
    $ContentType = 'logout';
  } else if($pathElements[0] == langurl('REGISTER')) {
    $ContentType = 'register';
  } else if($pathElements[0] == langurl('EDIT_PROFILE')) {
  } else if(is_numeric($pathElements[0])) {
    $ContentType = 'date';
  } else if(count($pathElements) == 1) {
    $nameResults = getPByName($pathElements[0]);
    $ContentType = $nameResults[0];
  } else if($pathElements[0] == langurl('POST')) {
    $ContentType = 'post';
    processPostURL($pathElements);
  } else if($pathElements[0] == langurl('PAGE')) {
    $ContentType = 'page';
    processPageURL($pathElements);
  } else if($pathElements[0] == langurl('CATEGORY')) {
    $ContentType = 'category';
  } else if($pathElements[0] == langurl('USER')) {
    $ContentType = 'user';
  } else if($pathElements[0] == langurl('NAME')) {
    $ContentType = 'name';
  }
}




