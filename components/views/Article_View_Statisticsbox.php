<?php

Yii::import('application.modules.aelogic.article.components.*');

/* this shows statistics in pretty format */

class Article_View_Statisticsbox extends ArticleComponent {


    public $mode;

    public function template() {

        $mode = self::addParam('type',$this->options);

        if(method_exists($this,$mode)){
            return $this->$mode();
        }
    }


    public function headerNumber(){

        $row[] = $this->factoryobj->getText($this->content,array(
            'text-align' => 'center','padding' => '0 20 0 20',
            'background-color' => $this->factoryobj->color_topbar,
            'color' => $this->factoryobj->colors['top_bar_text_color'],
            'font-size' => '78','font-ios' => 'Roboto-Light'
             ));

        $title = $this->addParam('title',$this->options,false);

        if($title){
            $row[] = $this->factoryobj->getText($title,array(
                'background-color' => $this->factoryobj->color_topbar,
                'color' => $this->factoryobj->colors['top_bar_text_color'],
                'font-size' => '14',
                'padding' => '0 4 10 4','text-align' => 'center','font-ios' => 'Roboto-Light'
            ));
        }

        return $this->factoryobj->getColumn($row);



    }


    public function rowNumber(){

        $inverse = $this->addParam('invert_colors',$this->options,false);

        if($inverse){
            $bg = $this->factoryobj->color_topbar;
            $color = $this->factoryobj->colors['top_bar_text_color'];
        } else {
            $color = $this->factoryobj->colors['text_color'];
            $bg = '';
        }

        $row[] = $this->factoryobj->getText($this->content,array(
            'text-align' => 'right','padding' => '0 20 0 0',
            'width' => '50%',
            'background-color' => $bg,
            'color' => $color,
            'font-size' => '34','font-ios' => 'Roboto-Light'
        ));

        $row[] = $this->factoryobj->getText($this->addParam('title',$this->options,'Title missing'),array(
            'color' => $color,
            'background-color' => $bg,
            'font-size' => '12',
            'padding' => '27 4 10 4','text-align' => 'left','font-ios' => 'Roboto-Light'
        ));

        return $this->factoryobj->getRow($row);

    }








}
