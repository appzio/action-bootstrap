<?php

Yii::import('application.modules.aelogic.components.*');

class Article_View_Music extends ArticleComponent {

	public function template() {
		$obj = new StdClass;
        $obj->type = '';
        $obj->content = $this->content;


        return $obj;
	}

}