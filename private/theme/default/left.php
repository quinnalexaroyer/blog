<div class="leftColumn">
<ul>
<?php
$pageTitles = getPageTitlesAlphabeticalWithID();
for($i=0; $i<count($pageTitles); $i++) {
  $pageURL = getPageURL($pageTitles[$i][0]);
  echo "  <li><a href=\"$pageURL\">{$pageTitles[$i][1]}</a></li>\n";
}
?>
</ul>
</div>

