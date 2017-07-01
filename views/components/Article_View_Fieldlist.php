<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Fieldlist extends ArticleComponent {

    public $vars;

    public function template() {
		$obj = new StdClass;
        $obj->type = 'field-list';
        $obj->content = $this->content;

        if(isset($this->options['variable'])){
            $obj->value = $this->options['variable'];
        }

        if(isset($this->options['value'])){
            $obj->value = $this->options['value'];
        }

        return $obj;
	}

}