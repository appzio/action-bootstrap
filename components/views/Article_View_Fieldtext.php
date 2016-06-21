<?php

Yii::import('application.modules.aelogic.components.*');

class Article_View_Fieldtext extends ArticleComponent {

    public $vars;

	public function template() {
		$obj = new StdClass;
        $obj->type = 'field-text';
        $obj->content = ( !empty($this->content) ? $this->content : '' );

        $params = array(
            'hint', 'height','submit_menu_id','maxlength'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }


        return $obj;
	}

}