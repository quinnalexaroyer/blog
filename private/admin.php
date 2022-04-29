<?php
global $HomeURL, $adminURL, $fieldForm;
$adminURL = $HomeURL . langurl("ADMIN");

$fieldForm = <<<EOD
    <div class="editFieldForm %s">
      <button class="removeFieldButton" type="button">-</button>
      <textarea class="editFieldName" name="fieldName[]" rows="4" cols="20">%s</textarea>
      &#x27F6;
      <textarea class="editFieldValue" name="fieldValue[]" rows="4" cols="50">%s</textarea>
      <br/>
    </div>
EOD;

function radioClearButton() { ?>
  <script>
    $('.clearButton').click(function(e) {
      var id = e.target.id.substring(5);
      $('#approve' + id).prop('checked', false);
      $('#decline' + id).prop('checked', false);
    }
  </script><?php
}

function pendingUsersTable() {
  global $adminURL, $OffsetAndLimit;
?>
  <form method="post" action="<?php echo $adminURL . "/pendingusers"?>">
  <table class="pendingUsersTable borderCellsTable">
    <tr><th>Clear</th><th>Approve</th><th>Decline</th><th>User Name</th><th>Registered</th><th>Email</th></tr><?php
  foreach(getPendingUsers(...$OffsetAndLimit) as $user) {?>
    <tr>
      <td><button id="clear<?php echo $user[0]; ?>" class="clearButton" type="button">Clear</button></td><?php
              $elementID = "approve" . $user[0]; ?>
      <td><label for="<?php echo elementID; ?>">Approve</label><input id="<?php echo $elementID;
              ?>" type="radio" name="pending[<?php echo $user[0]; ?>]" value="1"/></td><?php $elementID = "decline" . $user[0]; ?>
      <td><label for="<?php echo elementID; ?>">Decline</label><input id="<?php echo $elementID;
              ?>" type="radio" name="pending[<?php echo $user[0]; ?>]" value="0"/></td>
      <td><?php echo $user[5]; ?></td>
      <td><?php echo $user[3]; ?></td>
      <td><?php echo $user[2]; ?></td>
    </tr><?php
  } ?>
  </table>
  <input type="submit" name="submit" value="<?php echo lang("SUBMIT");?>"/>
  </form>
<?php
}

function pendingCommentsTable($p) {
  global $adminURL, $OffsetAndLimit;
?>
  <form method="post" action="<?php echo "$adminURL/pending${p}comments";?>">
  <table class="pendingPostCommentsTable borderTableCells">
    <tr><th>Clear</th><th>Approve</th><th>Decline</th><th>User</th><th><?php
        echo ucfirst($p); ?></th><th>Reply To</th><th>Comment</th><th>Date/Time</th></tr><?php
  foreach(getPendingComments($p, ...$OffsetAndLimit) as $comment) { ?>
    <tr>
      <td><button id="clear<?php echo $comment[0];
          ?>" class="clearButton" type="button">Clear</button></td><?php
          $elementID = "approve" . $comment[0]; ?>
      <td><label for="<?php echo $elementID; ?>">Approve</label><input id="<?php
          echo $elementID; ?>" type="radio" name="pending[<?php echo $comment[0];
          ?>]" value="1"/></td><?php $elementID = "decline" . $comment[0]; ?>
      <td><label for="<?php echo $elementID; ?>">Decline</label><input id="<?php
          echo $elementID; ?>" type="radio" name="pending[<?php echo $comment[0]; ?>]" value="0"/></td>
      <td><a href="<?php echo getNameURLByID($comment[3]); ?>"><?php echo getName($comment[3]); ?></a></td>
      <td><a href="<?php echo getPURL($p, $comment[1]); ?>"><?php echo getP($p, $comment[1])[2]; ?></a></td>
      <td><?php echo substr($comment[2], 0, 140); ?></td>
      <td><?php echo $comment[3]; ?></td>
      <td><?php echo $comment[4]; ?></td>
    </tr><?php
  } ?>
  </table>
  <input type="submit" name="submit" value="<?php echo lang("SUBMIT");?>"/>
  </form>
<?php
}

function manageUsersTable() {
  global $OffsetAndLimit, $adminURL;
?>
<form method="post" action="<?php echo "$adminURL/showusers";?>">
  <table class="manageUsersTable borderTableCells">
    <tr>
      <th><?php echo lang("BAN_HEAD");?></th>
      <th><?php echo lang("MODERATE_HEAD");?></th>
      <th><?php echo lang("USERNAME");?></th>
      <th><?php echo lang("REGISTERED");?></th>
      <th><?php echo lang("EMAIL");?></th>
    </tr>
<?php
  foreach(getUsers(...$OffsetAndLimit) as $user) {
    $flag = $user[5]; ?>
    <tr>
<?php
    if(isBanned($flag)) {$cellClass = "banCell"; $flagText = "Banned"; $boxText = "Unban";}
    else {$cellClass = "unbanCell"; $flagText = "Not Banned"; $boxText = "Ban";}
    $elementID = $boxText . $user[0]; ?>
      <td class="<?php echo $cellClass; ?>">
        <span class="userFlagText"><?php echo $flagText; ?></span><br/>
        <label for="<?php echo $elementID; ?>"><?php echo $boxText; ?></label>
        <input id="<?php echo $elementID; ?>" type="checkbox" name="<?php echo lcfirst($boxText)
                . "[" . $user[0] . "]"; ?>" value="1"/>
      </td><?php
    if(isModerated($flag)) {$cellClass = "moderatedCell"; $flagText = "Moderated"; $boxText = "Unmoderate";}
    else {$cellClass = "unmoderatedCell"; $flagText = "Not Moderated"; $boxText = "Moderate";}
    $elementID = $boxText . $user[0]; ?>
      <td class="<?php echo $cellClass; ?>">
        <span class="userFlagText"><?php echo $flagText; ?></span><br/>
        <label for="<?php echo $elementID; ?>"><?php echo $boxText; ?></label>
        <input id="<?php echo $elementID; ?>" type="checkbox" name="<?php echo lcfirst($boxText)
                . "[" . $user[0] . "]"; ?>" value="0"/>
      </td>
      <td><a href="<?php echo getNameURLByID($user[0]); ?>"><?php echo getUserName($user[0]); ?></a></td>
      <td><?php echo $user[3]; ?></td>
      <td><?php echo $user[2]; ?></td>
    </tr><?php
  } ?>
  </table>
  <input type="submit" name="submit" value="<?php echo lang("SUBMIT");?>"/>
</form>
<?php
}

function managePTable($p) {
  global $adminURL;
?>
  <form method="post" action="<?php echo "$adminURL/show${p}s";?>">
  <table class="manage<?php echo ucfirst($p); ?>s borderTableCells">
    <tr><th><?php echo lang("EDIT");
             ?></th><th><?php echo lang("TITLE");
             ?></th><th><?php echo lang("CREATED_ON");
             ?></th><th><?php echo lang("DELETE");
             ?></th></tr>
  <?php
  $offset = getGlobalOffset(0);
  $limit = getGlobalLimit(ADMIN_TABLE_LIMIT);
  foreach(getPs($p, $limit, $offset) as $info) { ?>
      <tr>
        <td><a href="<?php echo "$adminURL/edit$p/${info[0]}";?>"><?php
               echo lang("EDIT");?></a></td>
        <td><?php echo $info[2]; ?></td>
        <td><?php echo $info[5]; ?></td>
        <td>
          <label for="deleteP<?php echo $info[0]; ?>"><?php echo lang("DELETE"); ?></label>
          <input id="deleteP<?php echo $info[0]; ?>" type="checkbox" name="deleteP[<?php echo $info[0]; ?>]" value="1"/>
        </td>
      </tr><?php
  } ?>
  </table>
  <input type="submit" name="submit" value="<?php echo lang("SUBMIT");?>"/>
  </form>
<?php
}

function detectEditScript() { ?>
    $(document).ready(function() {
      $('#contentEditedInput').val(0);
      $('#titleEditedInput').val(0);
      $('#nameEditedInput').val(0);
      $('#categoriesEditedInput').val(0);
      $('#metaDescriptionEditedInput').val(0);
      $('#metaKeywordsEditedInput').val(0);
      $('#dateEditedInput').val(0);
      $('#pathEditedInput').val(0);
      $('#editTitle').on('input', function() {$('#titleEditedInput').val('1')});
      $('#editName').on('input', function() {$('#nameEditedInput').val('1')});
      $('#editContent').on('input', function() {$('#contentEditedInput').val('1')});
      $('#editCategories').on('input', function() {$('#categoriesEditedInput').val('1')});
      $('#editDescription').on('input', function() {$('#metaDescriptionEditedInput').val('1')});
      $('#editKeywords').on('input', function() {$('#metaKeywordsEditedInput').val('1')}); 
      $('#editDate').on('input', function() {$('#dateEditedInput').val('1')});
      $('#editPath').on('input', function() {$('#pathEditedInput').val('1')});
    });
<?php 
}

function makeFieldForm($fieldClass='', $fieldName='', $fieldValue='') {
  global $fieldForm;
  echo sprintf($fieldForm, $fieldClass, $fieldName, $fieldValue);
}

function fieldScript() {
  global $fieldForm;
?>
    var formField = '<?php
    echo escapeReturns(sprintf($fieldForm, "newField", '', ''));
  ?>';
    $(document).ready(function() {
      $(".editFieldsSection").on("click", ".removeFieldButton", function(e) {
        e.target.parentElement.remove();
      });
      $(".newFieldForm").click(function(e) {
        $(".editFieldsSection").append(formField);
      });
    });
<?php
}

function editPForm($p, $action, $id=NULL) {
  global $HomeURL;
  if(isP($p)) {
    if(!is_null($id)) {
      $info = getP($p, $id);
      $content = htmlentities($info[4]);
      $categories = implode(";", array_map(function($x) {return trim($x[1]);},
                    getCategoriesForP($p, $id)));
      $title = htmlentities($info[2]);
      $name = htmlentities($info[3]);
      if($p == 'post') {
        $dated = $info[7];
      } else if($p == 'page') {
        $path = $info[7];
      }
      $metaDescription = htmlentities($info[9]);
      $metaKeywords = htmlentities($info[8]);
    } else {
      $content = ""; $title = ""; $name = ""; $categories = ""; $metaDescription = "";
      $metaKeywords = ""; $dated = date(SQLDATEFORMAT); $path = "";
    }
    $adminURL = $HomeURL . langurl('ADMIN');
    echo "<form method=\"post\" action=\"$adminURL/$action\">\n";
    if(!is_null($id)) {
      echo "  <input type=\"hidden\" name=\"id\" value=\"$id\"/>\n";
    }
    echo "  <label for=\"editTitle\">" . LANG("TITLE") . "</label>\n";
    echo "  <input id=\"editTitle\" class=\"longInput\" type=\"text\" name=\"title\" size=\"60\" value=\"$title\"/><br/>\n";
    echo "  <label for=\"editName\">" . LANG("NAME") . "</label>\n";
    echo "  <input id=\"editName\" class=\"longInput\" type=\"text\" name=\"name\" size=\"60\" value=\"$name\"/><br/>\n";
    if($p == 'post') {
      echo "  <label for=\"editDate\">" . LANG("DATED") . "</label>\n";
      echo "  <input id=\"editDate\" class=\"longInput\" type=\"datetime\" name=\"dated\" size=\"20\" value=\"$dated\"/><br/>\n";
      echo "  <input id=\"dateEditedInput\" type=\"hidden\" name=\"dateEdited\" value=\"1\"/>\n";
    } else if($p == 'page') {
      echo "  <label for=\"editPath\">" . LANG("PATH") . "</label>\n";
      echo "  <input id=\"editPath\" class=\"longInput\" type=\"text\" name=\"path\" size=\"60\" value=\"$path\"/><br/>\n";
      echo "  <input id=\"pathEditedInput\" type=\"hidden\" name=\"pathEdited\" value=\"1\"/>\n";
    }
    echo '  <textarea id="editContent" class="editPTextarea" name="content" rows="30" cols="80">'; echo "\n";
    echo $content;?>
    </textarea><br/>
    <label for="editCategories">Categories</label>
    <input id="editCategories" class="categoriesInput" type="text" name="categories" size="60"<?php
        if(isset($categories)) echo " value=\"$categories\"";?>/><br/>
    <label for="editDescription">Meta Description</label>
    <textarea id="editDescription" name="metaDescription" rows="4" cols="60"><?php echo $metaDescription; ?></textarea><br/>
    <label for="editKeywords">Meta Keywords</label>
    <textarea id="editKeywords" name="metaKeywords" rows="4" cols="60"><?php echo $metaKeywords;?></textarea><br/>
    <h4>Fields</h4>
    <div class="editFieldsSection"><?php
    foreach(getFieldsOfP($p, $id) as $i) {
      makeFieldForm("originalField", $i[0], $i[1]);
    }
    makeFieldForm("newField");
    ?>
    </div>
    <button class="newFieldForm" type="button"><?php echo lang('ADD_FIELD');?></button>
    <input type="submit" name="submit" value="Submit"/>
    <input id="contentEditedInput" type="hidden" name="contentEdited" value="1"/>
    <input id="titleEditedInput" type="hidden" name="titleEdited" value="1"/>
    <input id="nameEditedInput" type="hidden" name="nameEdited" value="1"/>
    <input id="categoriesEditedInput" type="hidden" name="categoriesEdited" value="1"/>
    <input id="metaDescriptionEditedInput" type="hidden" name="metaDescriptionEdited" value="1"/>
    <input id="metaKeywordsEditedInput" type="hidden" name="metaKeywordsEdited" value="1"/>
    </form>
    <script>
  <?php
    detectEditScript();
    fieldScript();
    echo "</script>\n";
  }
}

function settingsForm() {
  global $adminURL;
?>
<form method="post" action="<?php echo "$adminURL/settings";?>">
  <h4><?php echo lang("GENERAL_SETTINGS");?></h4>
  <?php
    $flagValue = getIntSetting(0);
    makeSettingBox(FLAGS::commentsOnPosts,      "ALLOW_POST_COMMENTS",   $flagValue);
    makeSettingBox(FLAGS::commentsOnPages,      "ALLOW_PAGE_COMMENTS",   $flagValue);
    makeSettingBox(FLAGS::guestsCanComment,     "UNREGISTER_COMMENTS",   $flagValue);
    makeSettingBox(FLAGS::changeName,           "CHANGE_NAME",           $flagValue);
    makeSettingBox(FLAGS::registerUnname,       "REGISTER_GUEST_NAMES",  $flagValue);
    makeSettingBox(FLAGS::useRegisteredName,    "GUEST_USE_REGISTERED",  $flagValue);
    makeSettingBox(FLAGS::approveRegistration,  "APPROVE_REGISTRATION",  $flagValue);
    makeSettingBox(FLAGS::autoModerateNewUsers, "MODERATE_NEW_USERS",    $flagValue);
    makeSettingBox(FLAGS::allowRegistration,    "ALLOW_REGISTRATION",    $flagValue);
    makeSettingBox(FLAGS::moderateAllComments,  "MODERATE_ALL_COMMENTS", $flagValue);
    makeSettingBox(FLAGS::moderateGuestComments,"MODERATE_GUEST_COMMENTS",$flagValue);
    makeSettingBox(FLAGS::editComments,         "ALLOW_EDIT_COMMENTS",   $flagValue);
    makeSettingBox(FLAGS::useRecaptcha,         "USE_RECAPTCHA",         $flagValue);
?>
  <h4><?php echo lang("URL_SETTINGS");?></h4>
<?php
    $flagValue = getIntSetting(8);
    makeSettingRadio(0, array("POST",
                        langurl("POST") . "/" . lang("ID"),
                        langurl("POST") . "/" . lang("NAME"),
                        lang("NAME"),
                        langurl("POST") . "/" . lang("DATE")),
                     $flagValue);
    makeSettingRadio(2, array("PAGE",
                        langurl("PAGE") . "/" . lang("ID"),
                        langurl("PAGE") . "/" . lang("NAME"),
                        lang("NAME")),
                     $flagValue);
    makeSettingRadio(4, array("CATEGORY",
                        langurl("CATEGORY") . "/" . lang("ID"),
                        langurl("CATEGORY") . "/" . lang("NAME")),
                     $flagValue);
    makeSettingRadio(5, array("USER",
                        langurl("USER") . "/" . lang("ID"),
                        langurl("USER") . "/" . lang("NAME")),
                     $flagValue);
    makeSettingRadio(6, array("NAME",
                        langurl("NAME") . "/" . lang("ID"),
                        langurl("NAME") . "/" . lang("NAME")),
                     $flagValue);
  ?>
  <input type="submit" name="submit" value="submit"/>
</form>
<?php
}

function approveOrDecline($postVar, $approveFunction, $declineFunction) {
  if(isOwner()) {
    foreach($_POST[$postVar] as $id => $value) {
      if($value == 1) {
        $approveFunction($id);
      } else if($value == 0) {
        $declineFunction($id);
      }
    }
  }
}

function wasEdited($value, $wasEdited) {
  if($wasEdited == '1') {
    return $value;
  } else {
    return NULL;
  }
}

function processP($p) {
  if(isP($p)) {
    foreach($_POST['deleteP'] as $id => $value) {
      echo "AAAAAAAAAA $p $id | ";
      deleteP($p, $id);
    }
  }
}

function changeSettings() {
  $flag0 = 0;
  foreach($_POST['checkboxSetting'] as $key => $value) {
    $flag0 += 1 << $key;
  }
  setSetting(0, $flag0, 'int');
  $flag8 = 0;
  $flag8 += intval($_POST['urlPOST'])-1;
  $flag8 += 4*(intval($_POST['urlPAGE'])-1);
  $flag8 += 16*(intval($_POST['urlCATEGORY'])-1);
  $flag8 += 32*(intval($_POST['urlUSER'])-1);
  $flag8 += 64*(intval($_POST['urlNAME'])-1);
  setSetting(8, $flag8, 'int');
}

$pathElements = getPathElements();
if(count($pathElements) >= 3 && is_numeric($pathElements[2]) && $pathElements[1] != langurl('LIMIT')
         && $pathElements[2] != langurl('LIMIT')) {
  $id = intval($pathElements[2]);
}
if(isset($_POST['submit']) && isOwner()) {
  if($pathElements[1] == 'pendingusers') {
    approveOrDecline('pending', 'approvePendingUser', 'declinePendingUser');
  } else if($pathElements[1] == 'pendingpostcomments') {
    approveOrDecline('pending', 'approvePostComment', 'declinePostComment');
  } else if($pathElements[1] == 'pendingpagecomments') {
    approveOrDecline('pending', 'approvePageComment', 'declinePageComment');
  } else if($pathElements[1] == 'editpost' || $pathElements[1] == 'editpage') {
    $args = array(
            substr($pathElements[1], 4, 4),
            $_POST['id'],
            wasEdited($_POST['content'], $_POST['contentEdited']),
            wasEdited($_POST['title'], $_POST['titleEdited']),
            wasEdited($_POST['name'], $_POST['nameEdited']),
            wasEdited($_POST['categories'], $_POST['categoriesEdited']),
            wasEdited($_POST['metaDescription'], $_POST['metaDescriptionEdited']),
            wasEdited($_POST['metaKeywords'], $_POST['metaKeywordsEdited']),
            $_POST['fieldName'],
            $_POST['fieldValue']
    );
    echo "DATE EDITED"; echo $_POST['dateEdited']; echo "DATED"; echo $_POST['dated'];
    if(isset($_POST['dateEdited']) && $_POST['dateEdited'] == 1) {
      array_push($args, NULL); array_push($args, $_POST['dated']);
    } else if(isset($_POST['pathEdited']) && $_POST['pathEdited'] == 1) {
      array_push($args, $_POST['path']);
    }
    editP(...$args);
  } else if($pathElements[1] == 'newpost' || $pathElements[1] == 'newpage') {
    $args = array(substr($pathElements[1], -4), $_POST['content'], $_POST['title'], $_POST['name'],
            $_POST['categories'], $_SESSION['userID'], $_POST['metaDescription'],
            $_POST['metaKeywords'], $_POST['fieldName'], $_POST['fieldValue']);
    if($pathElements[1] == 'newpost') {
      array_push($args, NULL); array_push($args, $_POST['dated']);
      newP(...$args);
    } else if($pathElements[1] == 'newpage') {
      array_push($args, $_POST['path']);
      newP(...$args);
    }
  } else if($pathElements[1] == 'showposts' || $pathElements[1] == 'showpages') {
    processP(substr($pathElements[1], -5, 4));
  } else if($pathElements[1] == 'settings') {
    changeSettings();
  } else if($pathElements[1] == 'showusers') {
    foreach($_POST['moderate'] as $id => $value) {
      setUserFlagByName('moderated', $id);
    }
    foreach($_POST['unmoderate'] as $id => $value) {
      unsetUserFlagByName('moderated', $id);
    }
    foreach($_POST['ban'] as $id => $value) {
      setUserFlagByName('banned', $id);
    }
    foreach($_POST['unban'] as $id => $value) {
      unsetUserFlagByName('banned', $id);
    }
  }
}
if(count($pathElements) == 1) {
  echo "<div class=\"adminSelectOption\">";
  echo lang("SELECT_MENU_OPTION");
  echo "</div>";
} else if($pathElements[1] == 'pendingusers') {
  pendingUsersTable();
} else if($pathElements[1] == 'pendingpostcomments' || $pathElements[1] == 'pendingpagecomments') {
  pendingCommentsTable(substr($pathElements[1], 7, 4));
} else if(($pathElements[1] == 'editpost' || $pathElements[1] == 'editpage')
          && !isset($_POST['submit']) ** count($pathElements) >= 3) {
  editPForm(substr($pathElements[1], -4), $pathElements[1], $pathElements[2]);
} else if($pathElements[1] == 'newpost' || $pathElements[1] == 'newpage') {
  editPForm(substr($pathElements[1], -4), $pathElements[1]);
} else if($pathElements[1] == 'showposts' || $pathElements[1] == 'showpages'
          || (isset($_POST['submit']) && ($pathElements[1] == 'editpost' || $pathElements[1] == 'editpage')) ) {
  managePTable(substr($pathElements[1], 4, 4));
} else if($pathElements[1] == 'showusers') {
  manageUsersTable();
} else if($pathElements[1] == 'settings') {
  settingsForm();
}
echo "</div>\n";
?>

