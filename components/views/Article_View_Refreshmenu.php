<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Image extends ArticleComponent {	

    public $debug;
    public $vars;
    public $imagesobj;

    public function template() {

        $output[] = $this->factoryobj->getImagebutton('refresh.png','76767676767676',array('style' => 'refresh_menu'));
        $obj = new StdClass;
        $obj->type = 'msg-plain';
        $obj->style = $this->options['style'];
        $obj->content = $this->content;
        $output[] = $obj;

        return $output;
    }

}