<?php
$al = new AnnotationLike(get_input('id'));

if (!$al->isValid()){
  register_error(elgg_echo('annotations:annotation:notfound'));
  forward(REFERER);
}

$userid = get_loggedin_userid();
if (!$userid){
  register_error(elgg_echo('annotations:login_required'));
  forward(REFERER);
}

if ($al->cancel($userid)){
  system_message(elgg_echo('annotations:cancel_like:success'));
}else{
  register_error(elgg_echo('annotations:error'));
}

forward($al->getAnnotation()->getEntity()->getURL());

