<?php

Yii::import('application.modules.aelogic.components.*');

class Article_View_Progress extends ArticleComponent {

    /*
        $obj = new StdClass;
        $obj->type = 'progress';
        $obj->content = $points / 30;
        $obj->text_content = 'Cookify Chef, Level 1';
        $obj->progress_image = $this->getImageFileName('progress-fill.png');
        $obj->track_color = '#FFFFFF';
        $obj->style = 'progress_style1';
        $output[] = $obj;
    */


    public function template() {
    	$obj = new StdClass;
        $obj->type = 'progress';
        $obj->content = $this->content;

        $params = array(
            'text_content', 'progress_image', 'track_image','track_color','progress_color'
        );

        foreach ($params as $param) {
        	if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}