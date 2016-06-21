<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Topbar extends ArticleComponent {

    public $required_params = array('colors');

    public function template() {

        $colors = $this->addParam('colors',$this->options,false);
        $left = $this->addParam('left',$this->options,false);
        $right = $this->addParam('right',$this->options,false);
        // $textcolor = $this->addParam('top_bar_text_color',$colors,'#000000');
        // $bgcolor = $this->addParam('top_bar_color',$colors,'#ffffff');
        $style = $this->addParam('style',$this->options,'');

        if($left){
            $leftparams['width'] = 40;
            $leftparams['margin'] = '0 0 0 7';
            $leftparams['action'] = $left['action'];
            $leftparams['id'] = $left['id'];

            if(isset($left['config'])){
                $leftparams['config'] = $left['config'];
            }

            $columns[] = $this->factoryobj->getImagebutton($left['image'],$left['id'],false,$leftparams);
        }

        $columns[] = $this->factoryobj->getText($this->content, array( 'style' => 'topbar-text' ));

        if($right){
            $rightparams['width'] = 40;
            $columns[] = $this->factoryobj->getImagebutton($right['image'],$right['id'],false,$rightparams);
        }

        $obj = new StdClass;
        $obj->type = 'row';
        $obj->row_content = $columns;
        $obj->style = $style ? $style : 'topbar-row';

        return $obj;
    }

}