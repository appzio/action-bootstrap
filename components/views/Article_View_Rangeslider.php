<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Rangeslider extends ArticleComponent {

    public $required_params = array(
        'variable', 'min_value', 'max_value', 'value', 'step',
    );

    public function template() {
        $obj = new StdClass;
        $obj->type = 'slider';

        $params = array(
            'variable', 'min_value', 'max_value', 'value', 'step', 'left_track_color', 'right_track_color', 'thumb_color', 'thumb_image', 'track_height'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}