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
		$onclick = $this->addParam('onclick',$this->options,false);
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

		if ( $popup_action_id AND !$onclick ) {

			$var = $this->factoryobj->getSubmitVariable($var_id) ? $this->factoryobj->getSubmitVariable($var_id) : $this->factoryobj->getVariable($var_id);
			$label = ( $var ? $var : '{#select#} ' . strtolower($title) );

			$popup = new StdClass();
			$popup->action = 'open-action';
			$popup->id = 'open-helper-popup';
			$popup->sync_open = 1;
			$popup->open_popup = true;
			$popup->action_config = $popup_action_id;

			$col[] = $this->factoryobj->getText($label, array(
				'variable' => $var_id,
				'style' => $style,
				'onclick' => $popup,
			));

			$col[] = $this->factoryobj->getFieldtext($var, array(
				'variable' => $var_id,
				'width' => 1,
				'height' => 1,
				'opacity' => 0
			));

		} else if ( $onclick ) {
			unset( $args['input_type'] );

			$value = $this->value;

			if ( stristr($value, '{') ) {
				$value = @json_decode( $this->value );
				$value = $value->name;
			}

			$col[] = $this->factoryobj->getText($value, $args);
			$col[] = $this->factoryobj->getSpacer('9');
		} else {
			$col[] = $this->factoryobj->getFieldtext($this->value, $args);
		}

		$col[] = $this->factoryobj->getText('',array('style' => $style_separator));

		if($error){
			$col[] = $this->factoryobj->getText($error,array('style' => 'formkit-error'));
		}

		$col_args = array(
			'style' => 'form-field-row'
		);

		if ( $onclick ) {
			$col_args['onclick'] = $onclick;
		}

		return $this->factoryobj->getColumn($col);
	}

}