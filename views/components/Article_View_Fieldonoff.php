<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Fieldonoff extends ArticleComponent {

    public $vars;

    public function template() {

        $type = ( isset($this->options['type']) ? $this->options['type'] : '' );

		$obj = new StdClass;
        $obj->type = ( $type == 'toggle' ? 'toggle' : 'field-checkbox' );
        $obj->content = $this->content;

        if(isset($this->options['value'])){
            $obj->value = $this->options['value'];
        }

        return $obj;
	}

}