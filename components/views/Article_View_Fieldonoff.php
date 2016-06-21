<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Fieldonoff extends ArticleComponent {

    public $vars;

    public function template() {
		$obj = new StdClass;
        $obj->type = 'field-checkbox';
        $obj->content = $this->content;

        if(isset($this->options['value'])){
            $obj->value = $this->options['value'];
        }

        return $obj;
	}

}