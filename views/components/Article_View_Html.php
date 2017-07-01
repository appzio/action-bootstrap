<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Html extends ArticleComponent {

	public function template() {

		$obj = new StdClass;
        $obj->type = 'msg-html';
        $obj->content = $this->content;


        return $obj;
	}

}