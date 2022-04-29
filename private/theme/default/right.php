<div class="rightColumn">
<ul>
<?php
$categories = getCategoriesBySize('post');
foreach($categories as $i) {
  echo "  <li><a href=\"" . getCategoryURL('post', $i[0]) . "\">${i[1]}</a> (${i[2]})</li>\n";
}
?>
</ul>
</div>

