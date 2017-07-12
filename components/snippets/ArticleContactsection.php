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

        $hide = $this->addParam('hide',$this->options,false);
        $output[] = $this->factoryobj->formkitTitle('{#contact#}');
        $screen_name_error = $this->validateScreenName();
        $emailerror = $this->validateEmail();

        if(!isset($hide['real_name']))
            $output[] = $this->factoryobj->formkitField('real_name', '{#name#}', '{#your_real_name#}');

        if(!isset($hide['phone']))
            $output[] = $this->factoryobj->formkitField('phone', '{#phone#}', '{#your_phone#}');

        if(!isset($hide['screen_name']))
            $output[] = $this->factoryobj->formkitField('screen_name', '{#screen_name_sila#}', '{#your_screen_name#}',false,$screen_name_error);

        if(!isset($hide['email']))
            $output[] = $this->factoryobj->formkitField('email', '{#email#}', '{#your_email#}',false,$emailerror);

        if(!isset($hide['notify']))
            $output[] = $this->factoryobj->formkitCheckbox('notify', '{#push_messages#}', array(
                'type' => 'toggle'
            ));

        return $this->factoryobj->getColumn($output);
    }

    public function addTitle($title){
        $output[] = $this->factoryobj->getText(strtoupper($title),array('style' => 'form-field-section-title'));
        $output[] = $this->factoryobj->getText('',array('height' => '1','background-color' => '#b5b5b5','margin' => '0 0 10 0'));
        return $this->factoryobj->getColumn($output);
    }

    public function validateScreenName(){
        $error = false;
        $varid = $this->factoryobj->getVariableId('screen_name');

        if($this->factoryobj->getSubmittedVariableByName('screen_name')) {
            $varid = $this->factoryobj->getVariableId('screen_name');
            $ob = AeplayVariable::model()->findByAttributes(array('variable_id' => $varid,'value' => $this->factoryobj->getSubmittedVariableByName('screen_name')));

            if(is_object($ob) AND isset($ob->id) AND $ob->play_id != $this->factoryobj->playid){
                $error = '{#this_screen_name_is_taken#}';
            }
        } elseif(isset($this->factoryobj->submitvariables[$varid])) {
            $error = '{#screen_name_is_mandatory#}';
        }

        if($error){
            unset($this->factoryobj->submitvariables['screen_name']);
        }

        return $error;
    }

    public function validateEmail(){
        $email = $this->factoryobj->getSubmittedVariableByName('email');
        if(!$email){ return false; }

        $email_ok = $this->factoryobj->validateEmail($email);

        if(!$email_ok){
            $error = '{#not_a_valid_email#}';
            unset($this->factoryobj->submitvariables['email']);
        } else {
            $error = false;
        }

        return $error;

    }



}