<?php
class AnnotationLike{
  /**
   * annotation key 
   */
  const KEY = 'annotation_like';
  /**
   *
   * @var ElggAnnotation
   */
  protected $annotation;
  /**
   *
   * @var boolean
   */
  public static $changed = false;
  /**
   * 
   * @param int $annotationId the target annotation id
   */
  public function __construct($annotationId){
    if (is_object($annotationId) && $annotationId instanceof ElggAnnotation){
      $annotation = $annotationId;
    }else{
      $annotation = get_annotation($annotationId);
      if (!$annotation || !$annotation instanceof ElggAnnotation){
        return;
      }
    }
    $entity = $annotation->getEntity();
    if (!$entity){
      return;
    }
    $this->annotation = $annotation;
  }
  /**
   *
   * @return ElggAnnotation 
   */
  public function getAnnotation(){
    return $this->annotation;
  }
  /**
   * Whether if the target annotation is valid.
   * 
   * @return boolean return true if the target annotation is valid
   */
  public function isValid(){
    return isset($this->annotation);
  }
  /**
   * 
   * 
   * @param type $userid a user who like the target annotation
   * @return boolean 
   */
  public function like($userid){
    if (is_object($userid)){
      $userid = $userid->guid;
    }
    
    $entity = $this->annotation->getEntity();
    
    if ($this->liked($userid)){
      return false;
    }
    
    self::$changed = true;
    $result = $entity->annotate(self::KEY, $this->annotation->id, $this->annotation->access_id, $userid, 'integer');
    
    if ($result){
      trigger_plugin_hook('annotation_like:changed', 'all', array('entity' => $entity, 'annotation' => $this->annotation, 'method' => 'like'), null);
    }
    
    return $result;
  }
  /**
   *
   * @param type $userid a user who cancel to like the target annotation
   * @return boolean 
   */
  public function cancel($userid){
    if (is_object($userid)){
      $userid = $userid->guid;
    }
    
    $entity = $this->annotation->getEntity();
    
    if (!$this->liked($userid)){
      return false;
    }
    
    $an = annotation_like_get_annotations(array(
        'entity_guid' => $entity->guid, 
        'name' => self::KEY, 
        'value' => $this->annotation->id, 
        'value_type' => 'integer', 
        'owner_guid' => $userid
    ));
    if ($an){
      self::$changed = true;
      foreach ($an as $a){
        $a->delete();
      }
      trigger_plugin_hook('annotation_like:changed', 'all', array('entity' => $entity, 'annotation' => $this->annotation, 'method' => 'cancel'), null);
      return true;
    }
    return false;
  }
  /**
   *
   * @param type $user 
   * @return boolean return true if a user already have liked the target comment.
   */
  public function liked($userid){
    if (is_object($userid)){
      $userid = $userid->guid;
    }
    $entity = $this->annotation->getEntity();
    $an = annotation_like_get_annotations(array(
        'entity_guid' => $entity->guid, 
        'name' => self::KEY, 
        'value' => $this->annotation->id, 
        'value_type' => 'integer', 
        'owner_guid' => $userid
    ));
    if ($an && count($an) > 0){
      return true;
    }else{
      return false;
    }
  }
  /**
   *
   * @return int 
   */
  public function count(){
    $annotation = $this->annotation;
    
    $an = annotation_like_get_annotations(array(
        'entity_guid' => $annotation->getEntity()->guid, 
        'name' => self::KEY, 
        'value' => $annotation->id, 
        'value_type' => 'integer',
        'limit' => 10000
    ));
    if ($an){
      return count($an);
    }else{
      return 0;
    }
  }

  /**
   * You should call this method before you delete a target annotation.
   * 
   * @return int
   */
  public function delete(){
    $annotation = $this->annotation;

    $an = annotation_like_get_annotations(array(
        'entity_guid' => $annotation->getEntity()->guid,
        'name' => self::KEY,
        'value' => $annotation->id,
        'value_type' => 'integer',
        'limit' => 10000
    ));
    $count = 0;
    foreach ($an as $a){
      if ($a->delete()){
        $count++;
      }
    }
    return $count;
  }
  /**
   * notification control
   * 
   * @see annotation_like_notification_intercept
   * @return boolean 
   */
  public static function changed(){
    return self::$changed;
  }
}
