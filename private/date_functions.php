<?php
const SV = Array(0, 0, 3, 3, 6, 1, 4, 6, 2, 5, 0, 3, 5, 1);
const CENTURY = Array(-1, 4, 2, 0);

function isLeapYear($year) {
  return (($year%4) == 0 && ($year%100) != 0) || ($year%400) == 0;
}

function leapYearAdjust($year, $month) {
  if(isLeapYear($year) && ($month == 1 || $month == 2)) {
    return -1;
  } else {
    return 0;
  }
}

function daysInMonth($month, $year=1) {
  if($month == 2) {
    if(isLeapYear($year)) {
      return 29;
    } else {
      return 28;
    }
  } else if(($month%2 == 1 && $month <= 7) || ($month%2 == 0 && $month >= 8)) {
    return 31;
  } else {
    return 30;
  }
}

function dayOfWeek($year, $month, $day) {
  return ( ($year%100) + floor(($year%100)/4) + $day + SV[$month] + CENTURY[floor($year/100)%4] + leapYearAdjust($year, $month) ) % 7;
}

function getMonthNames($m) {
  return LangCalendar::MONTHS[$m-1];
}

function getDayOfWeekNames($d) {
  return LangCalendar::DAYS_OF_WEEK[$d];
}

function getDateRange($year, $month=0, $day=0) {
  if($month == 0) {
    return getYearRange($year);
  } else if($day == 0) {
    return getMonthRange($year, $month);
  } else {
    return getDayRange($year, $month, $day);
  }
}

function formatDate($format, $timestamp=NULL) {
  if(is_null($timestamp)) {
    $timestamp = intval(date(SQLDATEFORMAT));
  }
  $month = getMonthNames(intval(date('m', $timestamp)));
  $dayOfWeek = getDayOfWeekNames(intval(date('w', $timestamp)));
  preg_replace('~M(?!\\\\)~', $month[0], $format);
  preg_replace('~F(?!\\\\)~', $month[2], $format);
  preg_replace('~D(?!\\\\)~', $dayOfWeek[1], $format);
  preg_replace('~l(?!\\\\)~', $dayOfWeek[2], $format);
  return date($format, $timestamp);
}

function dateBoxYMD($timestamp=NULL) {
  if(is_null($timestamp)) {
    $timestamp = date(SQLDATEFORMAT);
  }
  $weekday = dayOfWeek(intval(substr($timestamp, 0, 4)),
                       intval(substr($timestamp, 5, 2)),
                       intval(substr($timestamp, 8, 2)));
  return "<span class=\"dateBox\"><time datetime=\"" . $timestamp . "\"><span class=\"year\">"
       . substr($timestamp, 0, 4) . "</span><span class=\"month\">"
       . strtoupper(LangCalendar::MONTHS[intval(substr($timestamp, 5, 2))-1][0])
       . "</span><span class=\"day\">" . intval(substr($timestamp, 8, 2)) . "</span><span class=\"weekday\">"
       . strtoupper(LangCalendar::DAYS_OF_WEEK[$weekday][1]) . "</span></time></span>\n";
}

function dateBoxMDY($timestamp=NULL) {
  if(is_null($timestamp)) {
    $timestamp = intval(date('U'));
  }
  return "<span class=\"dateBox\"><time datetime=\"" . date("Y-m-d") . "\"><span class=\"weekday\">"
       . strtoupper(LangCalendar::DAYS_OF_WEEK[intval(date("w"))][1]) . "</span><span class=\"month\">"
       . strtoupper(LangCalendar::MONTHS[intval(date("n", $timestamp))-1][0])
       . "</span><span class=\"day\">" . date("j", $timestamp) . "</span><span class=\"year\">"
       . date("Y", $timestamp) . "</span></time></span>\n";
}

function dateBoxDMY($timestamp=NULL) {
  if(is_null($timestamp)) {
    $timestamp = intval(date('U'));
  }
  return "<span class=\"dateBox\"><time datetime=\"" . date("Y-m-d") . "\"><span class=\"weekday\">"
       . strtoupper(LangCalendar::DAYS_OF_WEEK[intval(date("w"))][1]) . "</span><span class=\"day\">"
       . date("j", $timestamp) . "</span><span class=\"month\">"
       . strtoupper(LangCalendar::MONTHS[intval(date("n", $timestamp))-1][0])
       . "</span><span class=\"year\">" . date("Y", $timestamp) . "</span></time></span>\n";
}

function printCalendarDay($year, $month, $day, $lastDay, $dayFlag) {
  global $HomeURL;
  if(1 <= $day && $day <= $lastDay) {
    if(($dayFlag & (1 << ($day-1))) > 0) {
      ?><td><a href="<?php echo stripExtraSlashes("$HomeURL/$year/$month/$day");?>"><?php echo $day;?></a></td><?php
    } else {
      ?><td><?php echo $day;?></td><?php
    }
  } else {
  ?><td>&nbsp;</td><?php
  }
}

function printCalendar($year, $month) {
  $startDay = dayOfWeek($year, $month, 1);
  $lastDay = daysInMonth($month, $year);
  $dayFlag = getDayFlag($year, $month);
?>
  <table class="calendar">
    <tr class="monthRow"><td colspan="7"><?php echo LangCalendar::MONTHS[$month-1][2]; echo " "; echo $year;?></td></tr>
    <tr class="weekdayRow"><?php
  for($i=0; $i<7; $i++) {
  ?><td><?php echo LangCalendar::DAYS_OF_WEEK[$i][0];?></td><?php
  }
  echo "</tr>\n";
  for($i=0; $i<5; $i++) {?>
    <tr class="dayRow"><?php
    for($j=0; $j<7; $j++) {
      printCalendarDay($year, $month, 7*$i+$j+1-$startDay, $lastDay, $dayFlag);
    }?>
      </tr>
<?php
  }
  if(36-$startDay == $lastDay) {?>
    <tr class="dayRow"><?php echo printCalendarDay($year, $month, $lastDay, $lastDay, $dayFlag);
  ?><td colspan="6">&nbsp;</td></tr><?php
  } else if($lastDay == 31 && $startDay == 6) {?>
    <tr class="dayRow"><?php 
      echo printCalendarDay($year, $month, 30, 31, $dayFlag);
      echo printCalendarDay($year, $month, 31, 31, $dayFlag);
  ?><td colspan="5">&nbsp;</td></tr>
<?php
  }
  ?>  </table><?php
}

function getMonthForCalendar() {
  global $Year, $Month;
  if(isset($Year) && isset($Month)) {
    return array($Year, $Month);
  } else {
    return array(intval(date("Y")), intval(date("m")));
  }
}
?>

