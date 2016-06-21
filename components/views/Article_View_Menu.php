<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Menu extends ArticleComponent {

    public $menus;

	public function template() {
		$obj = new StdClass;
        $obj->type = 'menu';

        if(isset($this->options['sync_upload'])){
            $obj->sync_upload = '1';
        }

        if(isset($this->options['allow_delete'])){
            $obj->allow_delete = '1';
        }

        if(isset($this->options['sync_open'])){
            $obj->sync_open = '1';
        }

        if(isset($this->options['sync_close'])){
            $obj->sync_close = '1';
        }

        if(is_numeric($this->content)){
            $obj->content = $this->content;
        } elseif($this->content) {
            if(isset($this->menus[$this->content])){
                $obj->content = $this->menus[$this->content];
            }
        }

        return $obj;
	}

}