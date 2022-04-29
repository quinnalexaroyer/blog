<div class="rightColumn">
<h3>detcartu</h3>
<?php printCalendar(...getMonthForCalendar()); ?>

<h3><?php echo lang("CATEGORIES");?></h3>
<ul>
<?php
$categories = getCategoriesBySize('post');
foreach($categories as $i) {
  echo "  <li><a href=\"" . getCategoryURL('post', $i[0]) . "\">${i[1]}</a> (${i[2]})</li>\n";
}
?>
</ul>
<h3>samrla'a</h3>
<ul>
  <li><a href="https://mw.lojban.org/index.php?title=Lojban&setlang=en-US">Lojban</a></li>
  <li><a href="https://la-lojban.github.io/sutysisku/lojban/index.html#&bangu=en">la sutysisku</a></li>
  <li><a href="https://lojban.github.io/ilmentufa/camxes.html">camxes</a></li>
  <li><a href="https://jbovlaste.lojban.org/">jbovlaste</a></li>
  <li><a href="https://lojban.github.io/cll/">lo gerna be la lojban.</a></li>
</ul>
</div>

