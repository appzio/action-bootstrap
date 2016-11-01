<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkitfield extends ArticleComponent {

    public $vars;
    public $value;

    public function template() {

        $title = $this->addParam('title',$this->options,false);
        $variable = $this->addParam('variable',$this->options,false);
        $error = $this->addParam('error',$this->options,false);
        $hint = $this->addParam('hint',$this->options,false);
        $type = $this->addParam('type',$this->options,false);
        $param = $this->factoryobj->getVariableId($variable);

        if(!$this->value){
            $this->value = $this->factoryobj->getSubmittedVariableByName($variable);

            if(!$this->value){
                $this->value = $this->factoryobj->getSavedVariable($variable);
            }
        }

        $col[] = $this->factoryobj->getText(strtoupper($title),array('style' => 'form-field-titletext'));

        if($error){
            $style = 'form-field-textfield';
            $style_separator = 'form-field-separator-error';
        } else {
            $style = 'form-field-textfield';
            $style_separator = 'form-field-separator';
        }

        if($type){
            $col[] = $this->factoryobj->getFieldtext($this->value,array('variable' => $param,'hint' => $hint,'style' => $style,'input_type' => $type));
        } else {
            $col[] = $this->factoryobj->getFieldtext($this->value,array('variable' => $param,'hint' => $hint,'style' => $style));
        }


        $col[] = $this->factoryobj->getText('',array('style' => $style_separator));

        if($error){
            $col[] = $this->factoryobj->getText($error,array('style' => 'formkit-error'));
        }

        return $this->factoryobj->getColumn($col,array('style' => 'form-field-row'));


	}

}