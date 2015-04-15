Elgg Annotation Like plugin
===========================

This plugin provides the function to 'like' annotation to annotation

support version:  Elgg 1.8

MIT License.

Installation
----------------

1. Copy annotation_like to mod directory
2. Enable annotation_like plugin
3. Render "annotation/like" through elgg_view in any target annotation.

Example Usage
----------------

Edit mod/groups/views/default/annotation/group_topic_post.php

And add follow the line: 

    <?php echo elgg_view('annotation/like', array('annotation' => $vars['annotation'])) ?>

