<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Progress extends ArticleComponent {

    public $required_params = array('track_color');

    public function template() {
    	$obj = new StdClass;
        $obj->type = 'progress';
        $obj->content = $this->content;

        $params = array(
            'text_content', 'progress_image', 'track_image','track_color','progress_color','animate'
        );

        foreach ($params as $param) {
        	if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}