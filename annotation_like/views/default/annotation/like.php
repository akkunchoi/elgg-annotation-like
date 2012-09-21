<?php
$al = new AnnotationLike($vars['entity']->id);
if (!$al->isValid()){
  return '';
}
$targetId = $vars['entity']->id;
?>
<p class="annotation-like">
  <?php if ($al->liked(get_loggedin_userid())){ ?>
    <a class="liked"
       href="<?php echo elgg_add_action_tokens_to_url($vars['url'] . 'action/annotation_like/cancel?id=' . $targetId) ?>"
       data-href="<?php echo elgg_add_action_tokens_to_url($vars['url'] . 'action/annotation_like/like?id=' . $targetId) ?>"
       data-text="<?php echo elgg_echo('annotations:like') ?>">
        <?php echo elgg_echo('annotations:cancel_like') ?>
    </a>
  <?php }else{ ?>
    <a class="like"
       href="<?php echo elgg_add_action_tokens_to_url($vars['url'] . 'action/annotation_like/like?id=' . $targetId) ?>"
       data-href="<?php echo elgg_add_action_tokens_to_url($vars['url'] . 'action/annotation_like/cancel?id=' . $targetId) ?>"
       data-text="<?php echo elgg_echo('annotations:cancel_like') ?>">
        <?php echo elgg_echo('annotations:like') ?>
    </a>
  <?php } ?>
  <span class="counter-holder">
    (<span class="counter"><?php echo $al->count() ?></span>)
  </span>
</p>
