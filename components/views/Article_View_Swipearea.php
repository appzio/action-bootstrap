<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Swipearea extends ArticleComponent {

    public function template() {
        $obj = new StdClass;
        $obj->type = 'swipe';
        $obj->swipe_content = $this->content;

        $params = array(
        	'swipe_content', 'text_content', 'progress_image', 'track_image','animate','remember_position','position',
            'item_width','dynamic','id','items','animation','container_id','item_scale','transition','world_ending'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}