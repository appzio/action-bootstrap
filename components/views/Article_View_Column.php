<?php

Yii::import('application.modules.aelogic.components.*');

class Article_View_Column extends ArticleComponent {


    public function template() {
        $obj = new StdClass;
        $obj->type = 'column';
        $obj->column_content = $this->content;

        $params = array(
            'rightswipeid','leftswipeid','noanimate','onclick','id'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}