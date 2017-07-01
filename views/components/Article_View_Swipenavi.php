<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Swipenavi extends ArticleComponent {

    public $required_params = array();
    public $content;

	public function template() {

        $totalcount = $this->addParam('totalcount',$this->options,0);
        $color = $this->addParam('navicolor',$this->options,'white');
        $currentitem = $this->addParam('currentitem',$this->options,1);

        $count = 1;

        if($color == 'white'){
            $color_normal = "#80ffffff";
            $color_selected = "#ffffff";
        } else {
            $color_normal = "#4D000000";
            $color_selected = "#000000";
        }

        while($count <= $totalcount){

            if($count == 1){
                $margin = '0 0 0 0';
            } else {
                $margin = '0 0 0 3';
            }

            if($count == $currentitem){
                $row[] = $this->factoryobj->getText('•',array('color' => $color_selected,'font-size' => '27','width' => '14','text-align' => 'center','margin' => $margin));
            } else {
                $row[] = $this->factoryobj->getText('•',array('color' => $color_normal,'font-size' => '27','width' => '14','text-align' => 'center','margin' => $margin));
            }

            $count++;
        }

        $obj = $this->factoryobj->getRow($row,array('width' => '100%', 'text-align' => 'center'));

        return $obj;
	}

}