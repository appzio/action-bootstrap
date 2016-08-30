<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Fieldtext extends ArticleComponent {

    public $vars;

	public function template() {
		$obj = new StdClass;
        $obj->type = 'field-text';
        $obj->content = ( !empty($this->content) ? $this->content : '' );

        $params = array(
            'hint', 'height','submit_menu_id','maxlength',
            'suggestions','suggestions_style_row','suggestions_text_style','submit_on_select','submit_on_entry','submit_on_outfocus','id',
            'loading_content', 'input_type',
            'activation'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
	}

}