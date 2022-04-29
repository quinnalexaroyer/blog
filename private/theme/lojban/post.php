<?php global $ID, $AuthorID, $Title, $URLName, $Content, $TimeCreated, $TimeEdited, $Path,
             $Dated, $ContentType, $DatetimeFormat, $URLName;
if(!is_null($Dated)) $useDate = $Dated;
else $useDate = $TimeCreated;
?>
<div id="post-<?php echo $URLName;?>" class="post">
  <h2 class="postTitle"><a href="<?php echo getPostURL($ID); ?>"><?php echo $Title; ?></a></h2>
  <?php echo jboLongDate($useDate);?>
  <?php echo DateBoxYMD($useDate);?>
  <div class="postContent"><?php echo $Content; ?></div>
  <div class="categories">
    <ul class="categoryList">
<?php
foreach(getCategoriesForPost($ID) as $i) {
  echo "      <li><a href=\"" . getCategoryURL('post', $i[0]) . "\">${i[1]}</a></li>\n";
}
?>
    </ul>
  </div>
  <p class="editedInfo"><?php echo lang('CREATED_ON') . " "
         . formatDate($DatetimeFormat, strtotime($TimeCreated)) . '. ';
  if(!is_null($TimeEdited) && $TimeEdited != $TimeCreated) {
    echo lang('EDITED_ON') . " " . formatDate($DatetimeFormat, strtotime($TimeEdited)) . '.';
  }?></p>
</div>

