<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Banner extends ArticleComponent {

    public function template() {

        $obj = new StdClass;
        $obj->type = 'ad';
        $obj->content = $this->content;

        $this->options['ad_size'] = $this->addParam('ad_size',$this->options,'banner');

        /*
         *
         * sizes:
         *
        (null)
        large
        rectangle
        */

        $params = array(
            'ad_size'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;


    }

}