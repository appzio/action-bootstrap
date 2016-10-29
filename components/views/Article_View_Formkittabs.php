<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkittabs extends ArticleComponent {

    public $vars;

    public function template() {

        $content = $this->addParam('content',$this->options,false);
        $indicatorontop = $this->addParam('indicatorontop',$this->options,false);
        $divider = $this->addParam('divider',$this->options,false);

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

        $count = 1;

        foreach($content as $item){
            $onclick = new StdClass();
            $onclick->action = 'open-tab';
            $onclick->action_config = $count;
            $onclick->id = $count .'11';

            $btn1 = $this->factoryobj->getText($item,array('padding' => '10 10 10 10',
                'color' => $this->factoryobj->colors['top_bar_text_color'],'text-align' => 'center',
                'onclick' => $onclick,'font-size' => $fontsize
            ));

            if($this->factoryobj->current_tab == $count){
                $btn2 = $this->factoryobj->getText('',array('height' => '3','background-color' => $this->factoryobj->color_topbar_hilite,'width' => $width));
            } else {
                $btn2 = $this->factoryobj->getText('',array('height' => '3','background-color' => $this->factoryobj->color_topbar,'width' => $width));
            }

            if($indicatorontop){
                $btn = array($btn2,$btn1);
            } else {
                $btn = array($btn1,$btn2);

            }

            $col[] = $this->factoryobj->getColumn($btn,array('width' => $width));
            unset($btn);

            if($divider){
                $col[] = $this->factoryobj->getVerticalSpacer(1,array('background-color' => $this->factoryobj->colors['top_bar_text_color']));
            }

            $count++;

        }

        if(isset($col)){
            $row = $this->factoryobj->getRow($col,array('background-color' => $this->factoryobj->color_topbar));
            return $row;
        }
    }

}