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
   * @param int $annotationId target annotation id
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
   * @param type $userid a user like the target annotation
   * @return boolean 
   */
  public function like($userid){
    $entity = $this->annotation->getEntity();
    self::$changed = true;
    return $entity->annotate(self::KEY, $this->annotation->id, $this->annotation->access_id, $userid, 'integer');
  }
  /**
   *
   * @param type $userid a user cancel to like the target annotation
   * @return boolean 
   */
  public function cancel($userid){
    if (is_object($userid)){
      $userid = $userid->guid;
    }
    $entity = $this->annotation->getEntity();
    $an = get_annotations2(array(
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
      return true;
    }
    return false;
  }
  /**
   *
   * @param type $user 
   * @return boolean return true if a user already have liked
   */
  public function liked($userid){
    if (is_object($userid)){
      $userid = $userid->guid;
    }
    $entity = $this->annotation->getEntity();
    $an = get_annotations2(array(
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
   * @param type $aid an annotation id
   * @return int 
   */
  public function count(){
    $annotation = $this->annotation;
    $an = get_annotations2(array(
        'entity_guid' => $annotation->getEntity()->guid, 
        'name' => self::KEY, 
        'value' => $annotation->id, 
        'value_type' => 'integer'
    ));
    if ($an){
      return count($an);
    }else{
      return 0;
    }
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