<?php
/**
 * Created by PhpStorm.
 * User: trailo
 * Date: 11/01/17
 * Time: 16:36
 */

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aechat.models.*');

class ArticleContactsection extends ArticleComponent
{

    public function template(){
        $output[] = $this->factoryobj->formkitTitle('{#contact#}');
        $output[] = $this->factoryobj->formkitField('real_name', '{#name#}', '{#your_real_name#}');
        $output[] = $this->factoryobj->formkitField('phone', '{#phone#}', '{#your_phone#}');
        $output[] = $this->factoryobj->formkitField('screen_name', '{#screen_name_sila#}', '{#your_screen_name#}');
        $output[] = $this->factoryobj->formkitField('email', '{#email#}', '{#your_email#}');
        $output[] = $this->factoryobj->formkitCheckbox('notify', '{#push_messages#}', false);
        return $output;
    }

    public function addTitle($title){
        $output[] = $this->factoryobj->getText(strtoupper($title),array('style' => 'form-field-section-title'));
        $output[] = $this->factoryobj->getText('',array('height' => '1','background-color' => '#b5b5b5','margin' => '0 0 10 0'));
        return $this->factoryobj->getColumn($output);
    }



}