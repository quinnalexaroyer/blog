<?php global $ID, $AuthorID, $Title, $URLName, $Content, $TimeCreated, $TimeEdited, $Path,
             $Dated, $ContentType, $DatetimeFormat, $URLName;
echo "  <div class=\"page\">\n";
echo "    <h2 class=\"pageTitle\">$Title</h2>\n";
echo "    <div class=\"pageContent\">\n$Content\n    </div>\n";
echo "    <div class=\"categories\">\n    <ul>\n";
foreach(getCategoriesForPage($ID) as $i) {
  echo "      <li><a href=\"" . getCategoryURL('page', $i[0]) . "\">${i[1]}</a></li>\n";
}
echo "    </ul>\n    </div>\n";
echo "    <p class=\"editedInfo\">" . lang('CREATED_ON') . " <datetime>" . formatDate($DatetimeFormat, strtotime($TimeCreated))
   . "</datetime>. " . lang('EDITED_ON') . " <datetime>" . formatDate($DatetimeFormat, strtotime($TimeEdited)) . "</datetime>.</p>\n";
echo "  </div>\n";
?>

