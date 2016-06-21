<?php

Yii::import('application.modules.aelogic.components.*');

class Article_View_Text extends ArticleComponent {

    public $required_params = array();
    public $content;

	public function template() {

		$obj = new StdClass;
        $obj->type = 'msg-plain';
        $obj->content = $this->content;

        return $obj;
	}

}