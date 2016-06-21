<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Video extends ArticleComponent {

	public function template() {
		$obj = new StdClass;
        $obj->type = 'video';
        $obj->content = $this->content;

        $params = array(
            'repeat', 'autostart', 'showplayer'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
	}

}