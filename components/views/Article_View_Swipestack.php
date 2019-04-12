<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Swipestack extends ArticleComponent {

    public function template() {
        $obj = new StdClass;
        $obj->type = 'swipestack';
        $obj->swipe_content = $this->content;

        $params = array(
            'swipe_content', 'overlay_left', 'overlay_right', 'rightswipeid', 'leftswipeid', 'backswipeid', 'swipe_back_content'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}