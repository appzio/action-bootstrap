<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Infinitescroll extends ArticleComponent {

    public function template() {
        $obj = new StdClass;
        $obj->type = 'infinite-scroll';

        $obj->items = $this->content;

        $params = array(
        	'next_page_id'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}