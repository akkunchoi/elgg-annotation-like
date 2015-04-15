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


/**
 * Get a list of annotations for a given object/user/annotation type.
 *
 * @param int|array $entity_guid
 * @param string $entity_type
 * @param string $entity_subtype
 * @param string $name
 * @param mixed $value
 * @param int|array $owner_guid
 * @param int $limit
 * @param int $offset
 * @param string $order_by
 */
function annotation_like_get_annotations($params = array()){
  $defaults = array(
      'entity_guid' => 0,
      'entity_type' => '',
      'entity_subtype' => '',
      'name' => '',
      'value' => '',
      'owner_guid' => 0,
      'limit' => 10,
      'offset' => 0,
      'order_by' => 'asc',
      'timelower' => 0,
      'timeupper' => 0,
      'entity_owner_guid' => 0,
      'value_type' => '',
      'time_created' => '',
      'except' => 0,
      'special' => '',
  );
  $params = array_merge($defaults, $params);
  extract($params);
  
	global $CONFIG;

	$timelower = (int) $timelower;
	$timeupper = (int) $timeupper;

	if (is_array($entity_guid)) {
		if (sizeof($entity_guid) > 0) {
			foreach($entity_guid as $key => $val) {
				$entity_guid[$key] = (int) $val;
			}
		} else {
			$entity_guid = 0;
		}
	} else {
		$entity_guid = (int)$entity_guid;
	}
	$entity_type = sanitise_string($entity_type);
	$entity_subtype = get_subtype_id($entity_type, $entity_subtype);
	if ($name) {
		$name = get_metastring_id($name);

		if ($name === false) {
			$name = 0;
		}
	}
	if ($value != "") {
		$value = get_metastring_id($value);
    
    if ($value === false){
      $value = 0;
    }
	}

	if (is_array($owner_guid)) {
		if (sizeof($owner_guid) > 0) {
			foreach($owner_guid as $key => $val) {
				$owner_guid[$key] = (int) $val;
			}
		} else {
			$owner_guid = 0;
		}
	} else {
		$owner_guid = (int)$owner_guid;
	}

	if (is_array($entity_owner_guid)) {
		if (sizeof($entity_owner_guid) > 0) {
			foreach($entity_owner_guid as $key => $val) {
				$entity_owner_guid[$key] = (int) $val;
			}
		} else {
			$entity_owner_guid = 0;
		}
	} else {
		$entity_owner_guid = (int)$entity_owner_guid;
	}

	$limit = (int)$limit;
	$offset = (int)$offset;
	if($order_by == 'asc') {
		$order_by = "a.time_created asc";
	}

	if($order_by == 'desc') {
		$order_by = "a.time_created desc";
	}

	$where = array();

	if ($entity_guid != 0 && !is_array($entity_guid)) {
		$where[] = "a.entity_guid=$entity_guid";
	} else if (is_array($entity_guid)) {
		$where[] = "a.entity_guid in (". implode(",",$entity_guid) . ")";
	}
  
  $except = (int) $except;
  if ($except){
    $where[] = "a.id != $except";
  }

	if ($entity_type != "") {
		$where[] = "e.type='$entity_type'";
	}

	if ($entity_subtype != "") {
		$where[] = "e.subtype='$entity_subtype'";
	}

	if ($owner_guid != 0 && !is_array($owner_guid)) {
		$where[] = "a.owner_guid=$owner_guid";
	} else {
		if (is_array($owner_guid)) {
			$where[] = "a.owner_guid in (" . implode(",",$owner_guid) . ")";
		}
	}

	if ($entity_owner_guid != 0 && !is_array($entity_owner_guid)) {
		$where[] = "e.owner_guid=$entity_owner_guid";
	} else {
		if (is_array($entity_owner_guid)) {
			$where[] = "e.owner_guid in (" . implode(",",$entity_owner_guid) . ")";
		}
	}

	if ($name !== "") {
		$where[] = "a.name_id='$name'";
	}

	if ($value !== "") {
		$where[] = "a.value_id='$value'";
	}

	if ($timelower) {
		$where[] = "a.time_created >= {$timelower}";
	}

	if ($timeupper) {
		$where[] = "a.time_created <= {$timeupper}";
	}
  
	if ($value_type) {
		$where[] = "a.value_type = '{$value_type}'";
	}

  if ($time_created){
		$where[] = "a.time_created = {$time_created}";
  }

	$query = "SELECT a.*, n.string as name, v.string as value
		FROM {$CONFIG->dbprefix}annotations a
		JOIN {$CONFIG->dbprefix}entities e on a.entity_guid = e.guid
		JOIN {$CONFIG->dbprefix}metastrings v on a.value_id=v.id
		JOIN {$CONFIG->dbprefix}metastrings n on a.name_id = n.id where ";

	foreach ($where as $w) {
		$query .= " $w and ";
	}
	$query .= get_access_sql_suffix("a"); // Add access controls

  $query .= " order by $order_by limit $offset,$limit"; // Add order and limit

  return get_data($query, "row_to_elggannotation");
}

register_elgg_event_handler('init','system','annotation_like_init');

global $CONFIG;

register_action("annotation_like/like",false,$CONFIG->pluginspath . "annotation_like/actions/like.php");
register_action("annotation_like/cancel",false,$CONFIG->pluginspath . "annotation_like/actions/cancel_like.php");
