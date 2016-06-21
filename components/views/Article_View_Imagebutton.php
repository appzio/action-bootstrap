<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Imagebutton extends ArticleComponent {

    public $required_params = array('image','id');
    public $image;

    public function template() {

        $image = $this->addParam('image',$this->options);
        $id = $this->addParam('id',$this->options);
        $action = $this->addParam('action',$this->options,'submit-form-content');
        $config = $this->addParam('config',$this->options,'');
        $variable = $this->addParam('variable',$this->options,false);
        $sync_open = $this->addParam('sync_open',$this->options,0);
        $sync_close = $this->addParam('sync_close',$this->options,0);
        $back_button = $this->addParam('back_button',$this->options,1);
        $fallbackimage = $this->addParam('fallbackimage',$this->options,'');
        $style = $this->addParam('style',$this->options,'');
        $open_popup = $this->addParam('open_popup',$this->options,'');
        $sync_upload = $this->addParam('sync_upload',$this->options,0);
        $crop = $this->addParam('crop',$this->options,false);
        $max_dimensions = $this->addParam('max_dimensions',$this->options,'1200');
        $context = $this->addParam('context',$this->options,false);

        $imgparams['width'] = $this->addParam('imgwidth',$this->options,false);
        $imgparams['height'] = $this->addParam('imgheight',$this->options,false);
        $imgparams['crop'] = $this->addParam('imgcrop',$this->options,false);

        $item = new StdClass;
        $item->id = $id;
        $item->image = $image;
        $item->state = 'active';
        $item->action = $action;
        $item->action_config = $config;
        $item->open_popup = $open_popup;
        $item->sync_open = $sync_open;
        $item->sync_close = $sync_close;

        if($context){
            $item->context = $context;
        }

        if($action == 'upload-image'){
            $item->max_dimensions = $max_dimensions;
        }

        $item->sync_upload = $sync_upload;
        $item->back_button = $back_button;
        $item->variable = $variable;
        $item->style_content = new StdClass();
        $item->style_content->crop = $crop;

        if($fallbackimage){
            $item->image_fallback = $fallbackimage;
        }

        $obj = new StdClass;
        $obj->type = 'menu';

        $obj->menu_content = new StdClass;
        $obj->menu_content->id = $id;
        $obj->menu_content->items[] = $item;

        if(is_object($style)){
            $obj->style_content = $style;
        } elseif($style) {
            $obj->style = $style;
        }

        return $obj;

	}

}