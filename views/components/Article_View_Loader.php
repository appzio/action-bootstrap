<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Loader extends ArticleComponent {

    public $debug;
    public $vars;
    public $imagesobj;

    public function template() {
        $obj = new StdClass;
        $obj->type = 'loader';

        return $obj;
    }

}