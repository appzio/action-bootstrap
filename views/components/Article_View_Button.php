<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Button extends ArticleComponent {

    public $required_params = array('id');
    public $image;

    public function template() {
        $id = $this->addParam('id',$this->options,38338338);
        $action = $this->addParam('action',$this->options,'submit-form-content');
        $config = $this->addParam('config',$this->options,'');
        $variable = $this->addParam('variable',$this->options,false);
        $sync_open = $this->addParam('sync_open',$this->options,0);
        $back_button = $this->addParam('back_button',$this->options,1);
        $fallbackimage = $this->addParam('fallbackimage',$this->options,'');
        $style = $this->addParam('style',$this->options,'');
        $open_popup = $this->addParam('open_popup',$this->options,'');
        $sync_upload = $this->addParam('sync_upload',$this->options,0);
        $crop = $this->addParam('crop',$this->options,false);
        $colors = $this->addParam('colors',$this->options,false);
        $max_dimensions = $this->addParam('max_dimensions',$this->options,'1200');

        $imgparams['width'] = $this->addParam('imgwidth',$this->options,false);
        $imgparams['height'] = $this->addParam('imgheight',$this->options,false);
        $imgparams['crop'] = $this->addParam('imgcrop',$this->options,false);

        $item = new StdClass;
        $item->id = $id;
        $item->state = 'active';
        $item->action = $action;

        if($action == 'upload-image'){
            $item->max_dimensions = $max_dimensions;
        }

        ( $config ? $item->action_config = $config : false );

        $item->text = $this->content;
        //$item->slug = 'whatever';
        //$item->fallback_slug = '';
        //$item->call_backend = 0;
        ( $open_popup ? $item->open_popup = $open_popup : false );
        ( $sync_open ? $item->sync_open = $sync_open : false );
        ( $sync_upload ? $item->sync_upload = $sync_upload : false );
        ( $back_button ? $item->back_button = $back_button : false );
        ( $variable ? $item->variable = $variable : false );
        ( $fallbackimage ? $item->image_fallback = $fallbackimage : false );

        $obj = new StdClass;
        $obj->type = 'menu';

        $obj->menu_content = new StdClass;
        $obj->menu_content->id = $id;
        //$obj->menu_content->title = 'test';
        //$obj->menu_content->slug = 'test';
        $obj->menu_content->items[] = $item;
        // $obj->style = $style ? $style : 'article-button';

        return $obj;
	}

}