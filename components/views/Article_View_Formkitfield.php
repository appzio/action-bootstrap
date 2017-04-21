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
        $popup_action_id = $this->addParam('popup_action_id',$this->options,false);
        $var_id = $this->factoryobj->getVariableId($variable);

        if ( !$var_id ) {
            $var_id = $variable;
        }

        if(!$this->value){
            $this->value = $this->factoryobj->getSubmittedVariableByName($variable);

            if(!$this->value){
                $this->value = $this->factoryobj->getSavedVariable($variable);
            }
        }

        if(!$this->value){
            $this->value = $this->content;
        }

        $col[] = $this->factoryobj->getText(strtoupper($title),array('style' => 'form-field-titletext'));

        if($error){
            $style = 'form-field-textfield';
            $style_separator = 'form-field-separator-error';
        } else {
            $style = 'form-field-textfield';
            $style_separator = 'form-field-separator';
        }

        $args = array(
            'variable' => $var_id,
            'hint' => $hint,
            'style' => $style
        );

        if ( $type ) {
            $args['input_type'] = $type;
        }
            
        if ( $popup_action_id ) {

            $var = $this->factoryobj->getSubmitVariable($var_id) ? $this->factoryobj->getSubmitVariable($var_id) : $this->factoryobj->getVariable($var_id);
            $label = ( $var ? $var : '{#select#} ' . strtolower($title) );

            $onclick = new StdClass();
            $onclick->action = 'open-action';
            $onclick->id = 'open-helper-popup';
            $onclick->sync_open = 1;
            $onclick->open_popup = true;
            $onclick->action_config = $popup_action_id;

            $col[] = $this->factoryobj->getText($label, array(
                'variable' => $var_id,
                'style' => $style,
                'onclick' => $onclick,
            ));

            $col[] = $this->factoryobj->getFieldtext($var, array(
                'variable' => $var_id,
                'width' => 1,
                'height' => 1,
                'opacity' => 0
            ));

        } else {
            $col[] = $this->factoryobj->getFieldtext($this->value, $args);
        }

        $col[] = $this->factoryobj->getText('',array('style' => $style_separator));

        if($error){
            $col[] = $this->factoryobj->getText($error,array('style' => 'formkit-error'));
        }

        return $this->factoryobj->getColumn($col,array('style' => 'form-field-row'));
	}

}