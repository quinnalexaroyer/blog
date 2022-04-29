<?php
global $PostArray, $ContentType;
for($i=0; $i<count($PostArray); $i++) {
  if($i != 0) {
    getSection('postDivider');
  }
  setPData($PostArray[$i]);
  echo "<div class=\"postWrapper\">\n";
  getSection('post');
  echo sprintf("<p><a href=\"%s#comments\">%s %s</a></p>\n", 
       getPostURL($PostArray[$i][0]), countCommentsForPost($PostArray[$i][0]),
       lang('COMMENT_S'));
  echo "</div>\n";
}
printResultsTabForActive();
?>
