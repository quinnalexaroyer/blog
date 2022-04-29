<?php
class HOLIDAYS {
  const _101 = "lo cnino detna'a";
  const _115 = "lo jbedetri be la marten.lutyr.kin.";
  const _202 = "lo marmota";
  const _212 = "lo jbedetri be la eibryxam.linkyn.";
  const _214 = "lo se pramrvalentine";
  const _215 = "lo lanci be lo kadnygu'e";
  const _224 = "lo lanci be lo mexygu'e";
  const _222 = "lo jbedetri be la djordj.uacintyn.";
  const _229 = "la plipe nanca";
  const _314 = "li pai";
  const _315 = "la aidz. pe la fipma'i";
  const _317 = "la seint.patrek.";
  const _318 = "lo jbedetri be mi";
  const _331 = "lo jbedetri be la sizyr.cavez.";
  const _401 = "lo se bebna pe la lanma'i";
  const _405 = "lo pamoi penmi be lo kesfange be'o pe la star.trek.";
  const _422 = "lo terdi";
  const _501 = "lo terdi jibri gunka";
  const _504 = "la star.warz";
  const _530 = "lonu lo morji lo morsi sonci";
  const _614 = "lo lanci be lo mergu'e";
  const _618 = "loka jgira beloka .atmi";
  const _701 = "la kadnygu'e";
  const _704 = "loka lo mergu'e cu zifyje'a";
  const _911 = "lo naijgi be lo mergu'e";
  const _916 = "lo lanci be lo mexygu'e";
  const _930 = "lo narju creka pe lo kadnygu'e"; // lonu ge sanji lo jetnu gi xruti pedypa'i tcini pe lo kadnygu'e
  const _1031 = "lo seltepsla";
  const _1101 = "lo cespre";
  const _1102 = "lo morsi";
  const _1111 = "lo jibmu'osonci";
  const _1215 = "la flalu be lo krali bei lo mergu'e";
  const _1223 = "la festevys.";
  const _1224 = "lo prula'i bela krirmsa";
  const _1225 = "la krirmsa";
  const _1226 = "la baksin.";
  const _1231 = "lo prula'i belo cnino detna'a";
}


function lojbanDigits($n) {
  $digits = array('no', 'pa', 're', 'ci', 'vo', 'mu', 'xa', 'ze', 'bi', 'so');
  if($n == 0) return "no";
  $s = "";
  while($n > 0) {
    $s = $digits[$n%10] . $s;
    $n = intdiv($n,10);
  }
  return $s;
}

function jboLongDate($timestamp=NULL) {
  if(is_null($timestamp)) {
    $timestamp = date(SQL_DATE);
  }
  return "<span class=\"postDateLong\"><time datetime=\"" . $timestamp . "\">li "
       . lojbanDigits(intval(substr($timestamp, 0, 4))) . " ce'o lo "
       . LangCalendar::MONTHS[intval(substr($timestamp, 5, 2))-1][2] . " ce'o li "
       . lojbanDigits(intval(substr($timestamp, 8, 2))) . " poi "
       . LangCalendar::DAYS_OF_WEEK[intval(date("w"))][2] . "</time></span>\n";
}
?>
