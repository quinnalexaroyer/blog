<?php global $CommentIndent, $CommenterName, $CommenterUserID, $CommentTime, $CommentEditTime,
             $CommenterNameID, $Comment, $CommentID, $PType, $ID;
?>
<div class="comment commentIndent<?php echo $CommentIndent; ?>">
  <h5><?php echo nameTag($CommenterName, $CommenterUserID); ?> - <?php echo $CommentTime; ?></h5>
  <?php echo commentParagraphs($Comment); ?>
  <?php if(isset($CommentEditTime) && $CommentEditTime != $CommentTime) { ?>
    <p class="commentEditTime">Comment lasted edited <?php echo $CommentEditTime; ?></p>
  <?php }
        if(canUserComment($PType, $ID)) { ?>
    <button id="replyToComment<?php echo $CommentID; ?>" class="replyToComment" type="button"><?php 
            echo lang('REPLY');?></button>
  <?php } ?>
  <hr/>
</div>

