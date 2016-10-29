<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkitcheckbox extends ArticleComponent {

    public $vars;

    public function template() {

        $title = $this->addParam('title',$this->options,false);
        $varname = $this->addParam('variable',$this->options,false);

        $row[] = $this->factoryobj->getText(strtoupper($title), array('style' => 'form-field-textfield-onoff'));
        $row[] = $this->factoryobj->getFieldonoff($this->factoryobj->getSavedVariable($varname),array(
                    'value' => $this->factoryobj->getSavedVariable($varname),
                    'variable' => $this->factoryobj->getVariableId($varname),
                    'margin' => '0 15 9 0',
                    'floating' => '1',
                    'float' => 'right'
                )
            );

        $columns[] = $this->factoryobj->getRow($row);
        $columns[] = $this->factoryobj->getText('',array('style' => 'form-field-separator'));
        return $this->factoryobj->getColumn($columns, array('style' => 'form-field-row'));
	}

}