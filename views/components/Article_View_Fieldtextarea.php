<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Fieldtextarea extends ArticleComponent {

    public $vars;

	public function template() {
        $obj = new StdClass;
        $obj->type = 'field-textview';
        $obj->content = $this->content;

        $params = array(
            'hint', 'height','variable','submit_menu_id','maxlength','activation','content'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        /* crashes iOS if its missing */
        if(!isset($obj->hint)){
            $obj->hint = '';
        }


        return $obj;
    }

}