<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkitcheckbox extends ArticleComponent {

    public $vars;
    public $value;

    public function template() {

        $title = $this->addParam('title',$this->options,false);
        $variable = $this->addParam('variable',$this->options,false);
        $error = $this->addParam('error',$this->options,false);
        $onclick = $this->addParam('onclick',$this->options,false);
        $toggle_type = $this->addParam('type',$this->options,'default');
        $margin = $this->addParam('margin',$this->options,'0 15 9 0');
        $this->value = $this->addParam('value',$this->options,'');

        if ( empty($this->value) ) {
            $this->value = $this->factoryobj->getSubmittedVariableByName($variable);
            if ( empty($this->value) ) {
                $this->value = $this->factoryobj->getSavedVariable($variable);
            }
        }

        if ( empty($this->value) ) {
            $this->value = 'default';
        }
        
        $variable = $this->factoryobj->getVariableId($variable) ? $this->factoryobj->getVariableId($variable) : $variable;

        if($onclick){
            $row[] = $this->factoryobj->getText(strtoupper($title), array('style' => 'form-field-textfield-onoff-link', 'onclick' => $onclick));
        } else {
            $row[] = $this->factoryobj->getText(strtoupper($title), array('style' => 'form-field-textfield-onoff'));
        }

        $args = array(
            'type' => $toggle_type,
            'value' => $this->value,
            'variable' => $variable,
            'margin' => $margin,
            'floating' => '1',
            'float' => 'right'
        );

        $row[] = $this->factoryobj->getFieldonoff($this->value, $args);

        $columns[] = $this->factoryobj->getRow($row);

        if($error){
            $columns[] = $this->factoryobj->getText('',array('style' => 'form-field-separator-error'));
            $columns[] = $this->factoryobj->getText($error,array('style' => 'formkit-error'));
        } else {
            $columns[] = $this->factoryobj->getText('',array('style' => 'form-field-separator'));
        }

        return $this->factoryobj->getColumn($columns, array('style' => 'form-field-row'));
	}

}