<?php

Yii::import('application.modules.aelogic.components.*');

class Article_View_Timer extends ArticleComponent {

    public function template() {
    	$obj = new StdClass;
        $obj->type = 'timer';
        $obj->content = $this->content;

        $params = array(
            'mode'
        );

        foreach ($params as $param) {
        	if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}