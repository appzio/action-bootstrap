<?php

Yii::import('application.modules.aelogic.components.*');

class Article_View_Textbutton extends ArticleComponent {

    public $required_params = array('id');
    public $image;

    public function template() {
        $id = $this->addParam('id',$this->options,2833838);
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
        $colors = $this->addParam('colors',$this->options,false);
        $max_dimensions = $this->addParam('max_dimensions',$this->options,'1200');
        $submit_menu_id = $this->addParam('submit_menu_id',$this->options,false);
        $smalltext = $this->addParam('small_text',$this->options,false);
        $icon = $this->addParam('icon',$this->options,false);

        $par['onclick'] = new StdClass();
        $par['onclick']->id = $id;
        $par['onclick']->state = 'active';
        $par['onclick']->action = $action;

        ( $config ? $par['onclick']->action_config = $config : false );
        ( $open_popup ? $par['onclick']->open_popup = $open_popup : false );
        ( $sync_open ? $par['onclick']->sync_open = $sync_open : false );
        ( $sync_close ? $par['onclick']->sync_close = $sync_close : false );
        ( $sync_upload ? $par['onclick']->sync_upload = $sync_upload : false );
        ( $back_button ? $par['onclick']->back_button = $back_button : false );
        ( $variable ? $par['onclick']->variable = $variable : false );
        ( $submit_menu_id ? $par['onclick']->submit_menu_id = $submit_menu_id : false );

        $par['onclick']->text = $this->content;

        if($action == 'upload-image'){
            $par['onclick']->max_dimensions = $max_dimensions;
        }

        $item = new StdClass;
        $item->id = $id;

        $bgcolor = $this->addParam('button_color',$colors,'#000000');
        $color = $this->addParam('button_text_color',$colors,'#ffffff');

        if(strstr($bgcolor,'rgba')){
            $bgcolor = Helper::normalizeColor($bgcolor);
        }

        if(strstr($color,'rgba')){
            $color = Helper::normalizeColor($color);
        }

        if ( $style ) {
            $par['style'] = $style;
        } else {
            $sty['background-color'] = $bgcolor;
            $sty['color'] = $color;
            $sty['text-align'] = 'center';
            $sty['vertical-align'] = 'middle';
            $sty['font-ios'] = 'Roboto-Regular';
            $sty['font-android'] = 'Roboto';
            $sty['width'] = '100%';

            if($smalltext){
                $sty['font-size'] = 14;
            } else {
                $sty['font-size'] = 18;
            }

            $sty['height'] = '50';
            $par['style_content'] = (object)$sty;
        }

        if($icon){
            $row[] = $this->factoryobj->getImage($icon,array('height' => '30','vertical-align' => 'middle','margin' => '0 4 0 0'));
            $par2 = $par;
            unset($par2['style_content']->width);
            unset($par2['onclick']);
            unset($par2['text-align']);
            $row[] = $this->factoryobj->getText($this->content,$par2);
            return $this->factoryobj->getRow($row,$par);
        } else {
            return $this->factoryobj->getText($this->content,$par);
        }

	}

}