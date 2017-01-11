<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkitslider extends ArticleComponent {

    public $vars;

    public function template() {

        $title = $this->addParam('title',$this->options,false);
        $variable = $this->addParam('variable',$this->options,false);
        $default = $this->addParam('default',$this->options,false);
        $minvalue = $this->addParam('minvalue',$this->options,false);
        $maxvalue = $this->addParam('maxvalue',$this->options,false);
        $step = $this->addParam('step',$this->options,false);
        $error = $this->addParam('error',$this->options,false);
        
        $val = $this->factoryobj->getSavedVariable($variable) ? $this->factoryobj->getSavedVariable($variable) : $default;
        $variableid = $this->factoryobj->getVariableId($variable);

        if(!$variableid){
            $variableid = $variable;
        }

        if(strlen($maxvalue) > 4){
            $width = '70%';
        } else {
            $width = '80%';
        }

        $params  = array(
            'variable' => $variableid,
            'min_value' => $minvalue,
            'max_value' => $maxvalue,
            'value' => $val,
            'step' => $step,
            'left_track_color' => '#a4c97f',
            'right_track_color' => '#000000',
            'width' => $width,
            'margin' => '0 10 0 15',
            'track_height' => '1',
            'vertical-align' => 'middle'
        );

        $params['value'] = $val;

        $col[] = $this->factoryobj->getText(strtoupper($title),array('style' => 'form-field-titletext'));

        $row[] = $this->factoryobj->getRangeslider('none',$params);
        $row[] = $this->factoryobj->getText($val, array( 'style' => 'profile-field-label','variable' => $variableid ));

        $col[] = $this->factoryobj->getRow($row,array('margin' => '15 0 15 0','height' => '30','vertical-align' => 'middle'));
        $col[] = $this->factoryobj->getText('',array('style' => 'form-field-separator'));

        if($error){
            $output[] = $this->factoryobj->getText('',array('style' => 'form-field-separator-error'));
            $output[] = $this->factoryobj->getText($error,array('style' => 'formkit-error'));
        } else {
            $output[] = $this->factoryobj->getText('',array('style' => 'form-field-separator'));
        }

        return $this->factoryobj->getColumn($col,array('style' => 'form-field-row'));
	}

}