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
        $viewport = $this->addParam('viewport',$this->options,'current');
        $back_button = $this->addParam('back_button',$this->options,1);
        $fallbackimage = $this->addParam('fallbackimage',$this->options,'');
        $style = $this->addParam('style',$this->options,'');
        $open_popup = $this->addParam('open_popup',$this->options,'');
        $sync_upload = $this->addParam('sync_upload',$this->options,0);
        $crop = $this->addParam('crop',$this->options,false);
        $max_dimensions = $this->addParam('max_dimensions',$this->options,'1200');
        $context = $this->addParam('context',$this->options,false);
        $send_ids = $this->addParam('send_ids',$this->options,false);

        $imgparams['width'] = $this->addParam('imgwidth',$this->options,false);
        $imgparams['height'] = $this->addParam('imgheight',$this->options,false);
        $imgparams['crop'] = $this->addParam('imgcrop',$this->options,false);

        $item = new StdClass;
        $item->id = $id;

        if($image) { $item->image = $image; }
        //if($item->state) { $item->state = 'active'; }
        if($action) { $item->action = $action; }
        if($config) { $item->action_config = $config; }
        if($open_popup) { $item->open_popup = $open_popup; }
        if($sync_open) { $item->sync_open = $sync_open; }
        if($sync_close) { $item->sync_close = $sync_close; }
        if($viewport) { $item->viewport = $viewport; }

        if($context){
            $item->context = $context;
        }

        if($send_ids){
            $item->send_ids = $send_ids;
        }


        if($action == 'upload-image'){
            $item->max_dimensions = $max_dimensions;
        }

        if($sync_upload){ $item->sync_upload = $sync_upload; }
        if($back_button){$item->back_button = $back_button; }
        if($variable){ $item->variable = $variable;}


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