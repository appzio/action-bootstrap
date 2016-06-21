<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Row extends ArticleComponent {	


    public function template() {
        $obj = new StdClass;
        $obj->type = 'row';
        $obj->row_content = $this->content;

        $params = array(
            'noanimate','id'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}