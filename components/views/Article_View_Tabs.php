<?php

Yii::import('application.modules.aelogic.components.*');

class Article_View_Tabs extends ArticleComponent {

	public function template() {
		$obj = new StdClass;
        $obj->type = 'menu';
        $obj->content = $this->content;


        return $obj;
	}

}