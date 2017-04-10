<?php
/**
 * Created by PhpStorm.
 * User: trailo
 * Date: 11/01/17
 * Time: 16:36
 */

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aechat.models.*');

class ArticleConfirmationdialog extends ArticleComponent
{

    public function template(){
        $text = $this->addParam('text',$this->options,false);
        $button_yes = $this->addParam('button_yes',$this->options,false);
        $menuid = $this->addParam('menuid',$this->options,false);

        $col[] = $this->factoryobj->getText($text,array('color' => '#ffffff','height' => '50','text-align' => 'center'));
        $row[] = $this->getButton('{#cancel#}','whtaver');
        $row[] = $this->factoryobj->getVerticalSpacer('30');
        $row[] = $this->getButton($button_yes,$menuid);
        $col[] = $this->factoryobj->getSpacer(50);
        $col[] = $this->factoryobj->getRow($row,array('height' => '50','text-align' => 'center'));

        $output = $this->factoryobj->getColumn($col,array('margin' => '50 50 50 50', 'padding' => '50 50 50 50',
            'opacity' => '0.8','background-color' => '#000000','border-radius' => 12,'floating' => 1));

        return $output;
    }

    public function getButton($text,$menuid){
        $onclick = new stdClass();
        $onclick->action = 'submit-form-content';
        $onclick->id = $menuid;

        return $this->factoryobj->getText($text, array(
            'background-color' => '#ffffff',
            'color' => '#000000',
            'height' => '25',
            'border-radius' => 7,
            'width' => '40%',
            'font-size' => '12',
            'text-align' => 'center',
            'onclick' => $onclick
        ));
    }

}