<?php
global $ID, $CategoryName, $OffsetAndLimit, $PType;
$entries = getPFromCategory($PType, $ID, ...$OffsetAndLimit);

function listCategoryEntries($p, $entryList) {
  global $ID, $CategoryName, $OffsetAndLimit, $PType;
  if(isP($p)) {
?>
    <h2><?php echo $CategoryName;?></h2>
    <ul class="categoryList">
<?php
    foreach($entryList as $entry) {?>
      <li><a href="<?php echo getPURL($p, $entry[0]);?>"><?php echo $entry[1]; ?></a></li>
<?php
    }
  }
}
?> 
<?php
if(count($entries) == 0) {
  echo lang('NONE_FOUND_IN_CATEGORY');
} else {
?>
  <div class="categoryList">
<?php
    listCategoryEntries($PType, $entries);
?>
  </div>
<?php
  printResultsTabForCategory();
} ?>

