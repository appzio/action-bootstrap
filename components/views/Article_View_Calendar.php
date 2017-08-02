<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Calendar extends ArticleComponent {

    public function template() {

        $obj = new StdClass;
        $obj->type = 'calendar';
        $obj->date = $this->content;

        $params = array(
            'selection_style'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;

    }

}