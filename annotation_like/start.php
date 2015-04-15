<?php
require_once dirname(__FILE__) . '/lib/annotation_like.php';

// Enable ajax request
define('ANNOTATION_LIKE_XHR', 1);

function annotation_like_init(){
  if (ANNOTATION_LIKE_XHR){
    elgg_extend_view("js/initialise_elgg", "annotation/javascript");
    register_plugin_hook('forward', 'system', 'annotation_like_xhr_forwarder');
  }
  
  if (is_plugin_enabled('groups') && is_plugin_enabled('notifications')){
    // Group forum notify any annotation on create.
    // If you set annotation_like on group forum and enable notification plugin,
    // you should control notification.
    register_plugin_hook('object:notifications','object','annotation_like_notification_intercept');
    
  }
  // TODO like notification
  // register_elgg_event_handler('create', 'annotation', 'annotation_like_create_handler');
  register_plugin_hook('unit_test', 'system', 'annotation_like_unittest');
}

function annotation_like_xhr_forwarder($hook, $entity_type, $returnvalue, $params){
  if (isset($params['current_url'])){
    if (!preg_match('/annotation_like/', $params['current_url'])){
      return $returnvalue;
    }
  }
    
  $xhr = false;
  if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'){
    $xhr = true;
  }
  
  if ($xhr === false){
    return $returnvalue;
  }
  
  if (count_messages('errors') > 0){
    echo 0;
  }else{
    echo 1;
  }
  // clear messages
  $_SESSION['msg'] = array();
  return '';
}

function annotation_like_notification_intercept($hook, $entity_type, $returnvalue, $params) {
  if (AnnotationLike::changed()){
    // true means to cancel notification
    return true;
  }
  return null;
}

function annotation_like_unittest($hook, $type, $value, $params) {
  error_reporting(E_ALL ^ E_NOTICE);
  global $CONFIG;
//  $value = array();
  $value[] = $CONFIG->pluginspath . 'annotation_like/tests/annotation_like.php';
  return $value;
}

register_elgg_event_handler('init','system','annotation_like_init');

global $CONFIG;

register_action("annotation_like/like",false,$CONFIG->pluginspath . "annotation_like/actions/like.php");
register_action("annotation_like/cancel",false,$CONFIG->pluginspath . "annotation_like/actions/cancel_like.php");
