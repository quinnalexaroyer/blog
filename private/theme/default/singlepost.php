<?php global $ID;?>
<div class="postWrapper">
<?php include 'post.php'; ?>
  <div id="comments">
<?php
if(canUserComment('post', $ID)) {
  echo commentForm('post', $ID);
  echo "<script>\n";
  replyToCommentScript('post', $ID);
}
echo "</script>\n";
printComments(getThreadedComments('post', $ID, NULL, 'ASC'), 0);
?>
  </div>
</div>

