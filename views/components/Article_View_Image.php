<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Image extends ArticleComponent {	

    public $debug;
    public $vars;
    public $imagesobj;

    public function template() {
        $obj = new StdClass;
        $obj->type = ( $this->debug == 1 ? 'msg-plain' : 'image' );
        $obj->content = $this->content;

        $params = array(
            'onclick', 'variable', 'crop', 'image_fallback', 'selected_state', 'lazy','tap_to_open','tap_image'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}