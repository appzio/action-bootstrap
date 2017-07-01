<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Fieldpassword extends ArticleComponent {

    public $vars;

	public function template() {
		$obj = new StdClass;
        $obj->type = 'field-password';
        $obj->content = $this->content;

        $params = array(
            'hint', 'height','submit_menu_id','submit_menu_id','maxlength'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
	}

}