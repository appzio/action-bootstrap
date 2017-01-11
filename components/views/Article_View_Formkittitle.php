<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkittitle extends ArticleComponent {

    public function template() {
        $title = $this->addParam('title',$this->options,false);
        $output[] = $this->factoryobj->getText(strtoupper($title),array('style' => 'form-field-section-title'));
        $output[] = $this->factoryobj->getText('',array('height' => '1','background-color' => '#b5b5b5','margin' => '0 0 10 0'));
        return $this->factoryobj->getColumn($output);
	}

}