<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkittabs extends ArticleComponent {

    public $vars;
    public $origin_tab;

    public function template() {

        $content = $this->addParam('content',$this->options,false);
        $indicator_mode = $this->addParam('indicator_mode',$this->options,'bottom');
        $divider = $this->addParam('divider',$this->options,false);
        $active = $this->addParam('active',$this->options,false);
        $btn_padding = $this->addParam('btn_padding',$this->options,'10 10 10 10');
        $color_topbar = $this->addParam('color_topbar',$this->options,$this->factoryobj->color_topbar);
        $color_topbar_hilite = $this->addParam('color_topbar_hilite',$this->options,$this->factoryobj->color_topbar_hilite);
        $this->origin_tab = $this->addParam('origin_tab',$this->options,false);

        $params = $this->getTabParams( $content );

        $fontsize = $params['fontsize'];
        $width = $params['width'];

        $btn_params = array(
            'padding' => $btn_padding,
            'color' => $this->factoryobj->colors['top_bar_text_color'],
            'text-align' => 'center',
            'font-size' => $fontsize
        );

        foreach($content as $tab_key => $tab_title){

            $tab_num = str_replace('tab', '', $tab_key);

            $onclick = new StdClass();
            $onclick->action = 'open-tab';
            $onclick->action_config = $tab_num;
            $onclick->id = 'key-' . $tab_key;

            if ( $indicator_mode == 'fulltab' AND $this->tabIsActive( $active, $tab_num ) ) {
                $btn_params['background-color'] = $color_topbar_hilite;
            } else {
                $btn_params['background-color'] = $color_topbar;
            }

            $btn1 = $this->factoryobj->getText($tab_title, $btn_params);
            $btn2 = array();

            if ( $indicator_mode == 'top' OR $indicator_mode == 'bottom' ) {
                if ( $this->tabIsActive( $active, $tab_num ) ) {
                    $btn2 = $this->factoryobj->getText('',array('height' => '3','background-color' => $color_topbar_hilite,'width' => $width));
                } else {
                    $btn2 = $this->factoryobj->getText('',array('height' => '3','background-color' => $color_topbar,'width' => $width));
                }
            }

            if ( $indicator_mode == 'top' ) {
                $btn = array($btn2, $btn1);                
            } else if ( $indicator_mode == 'bottom' ) {
                $btn = array($btn1, $btn2);
            } else {
                $btn = array( $btn1 );
            }

            $col[] = $this->factoryobj->getColumn($btn,array('width' => $width,'onclick' => $onclick));
            unset($btn);

            if($divider){
                $col[] = $this->factoryobj->getVerticalSpacer(1,array('background-color' => $this->factoryobj->colors['top_bar_text_color']));
            }

        }

        if(isset($col)){
            $row = $this->factoryobj->getRow($col,array('bakcground-color' => $this->factoryobj->color_topbar));
            return $row;
        }
    }

    private function tabIsActive( $active, $tab_num ) {

        if($this->origin_tab AND $this->origin_tab == $tab_num){
            return true;
        } elseif ( !$this->origin_tab AND $active AND $active == $tab_num ) {
            return true;
        } else if ( !$this->origin_tab AND !$active AND $this->factoryobj->current_tab == $tab_num ) {
            return true;
        }

        return false;
    }

    private function getTabParams( $content ) {
        
        if(count($content) == 1){
            $width = $this->factoryobj->screen_width;
            $fontsize = '14';
        } elseif(count($content) == 2){
            $width = round($this->factoryobj->screen_width/2,0);
            $fontsize = '14';
        } elseif(count($content) == 3){
            $width = round($this->factoryobj->screen_width/3,0);
            $fontsize = '14';
        } elseif(count($content) == 4){
            $width = round($this->factoryobj->screen_width/4,0);
            $fontsize = '12';
        } elseif(count($content) == 5){
            $width = round($this->factoryobj->screen_width/5,0);
            $fontsize = '10';
        } else {
            $width = round($this->factoryobj->screen_width/6,0);
            $fontsize = '10';
        }

        return array(
            'width' => $width,
            'fontsize' => $fontsize,
        );
    }

}