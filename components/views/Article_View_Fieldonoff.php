<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Fieldonoff extends ArticleComponent {

    public $vars;

    public function template() {

        $type = ( isset($this->options['type']) ? $this->options['type'] : '' );

		$obj = new StdClass;
        $obj->type = ( $type == 'toggle' ? 'toggle' : 'field-checkbox' );
        $obj->content = $this->content;

	    $params = array(
		    'value', 'listbranches_on_change'
	    );

	    foreach ($params as $param) {
		    if ( isset($this->options[$param]) ) {
			    $obj->$param  = $this->options[$param];
		    }
	    }

        return $obj;
	}

}