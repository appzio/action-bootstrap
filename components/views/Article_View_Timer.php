<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Timer extends ArticleComponent {

    public function template() {
    	$obj = new StdClass;
        $obj->type = 'timer';
        $obj->content = $this->content;

        $params = array(
            'timer_id', 'submit_menu_id', 'mode'
        );

        foreach ($params as $param) {
        	if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}