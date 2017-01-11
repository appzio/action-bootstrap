<?php

Yii::import('application.modules.aelogic.article.components.*');

/* by default the information is saved into a variable as a json object */

class ArticleSelectorlist extends ArticleComponent {

    public $mode;
    public $data;
    public $variable;
    public $hint;
    public $title;

    public $required_params = array( 'mode', 'variable' );
    public $list_data;
    public $tab;
    public $tab_back;
    public $saved_data;
    public $dont_save_variable;


    public function template(){

        $this->mode = $this->addParam('mode',$this->options);
        $this->data = $this->addParam('data',$this->options);
        $this->list_data = $this->addParam('list_data',$this->options);
        $this->variable = $this->addParam('variable',$this->options);
        $this->hint = $this->addParam('hint',$this->options,false);
        $this->title = $this->addParam('title',$this->options,false);
        $this->tab = $this->addParam('tab',$this->options,2);
        $this->tab_back = $this->addParam('tab_back',$this->options,1);
        $this->dont_save_variable = $this->addParam('dont_save_variable',$this->options,false);

        if($this->mode == 'field'){
            return $this->getField();
        } else {
            return $this->getListing();
        }
    }

    public function getField(){
        $col[] = $this->factoryobj->getText(strtoupper($this->title),array('style' => 'form-field-titletext'));

        $onclick3 = new stdClass();
        $onclick3->action = 'open-tab';
        $onclick3->action_config = $this->tab;
        $onclick3->back_button = 1;

        $list = json_decode($this->factoryobj->getSavedVariable($this->variable),true);
        $countries = '';

        if(!empty($list)){
            foreach($list as $item){
                $item = str_replace(' ', '_',strtolower($item));
                $countries .= '{#' .$item .'#}, ';
            }

            $countries = substr($countries,0,-2);
        }

        $countries = !empty($countries) ? $countries : '{#all#}';

        $row[] = $this->factoryobj->getText($countries,array('variable' => '','hint' => $this->hint,'style' => 'form-field-non-editable-textfield'));
        $row[] = $this->factoryobj->getImage('beak-icon.png',array('height' => '22','margin' => '0 20 0 0','opacity' => '0.6',
            'floating' => '1',
            'float' => 'right'
        ));

        $col[] = $this->factoryobj->getRow($row,array('onclick' => $onclick3));
        $col[] = $this->factoryobj->getText('',array('style' => 'form-field-separator'));

        return $this->factoryobj->getColumn($col,array('style' => 'form-field-row'));

    }


    public function saveData($data){

        if($this->dont_save_variable){
            $this->saved_data = $data;
        } else {
            $this->factoryobj->saveVariable($this->variable,$data);
        }


    }
    public function getListing(){

        if($this->factoryobj->menuid == 'list-saver'){
            $names = array();
            foreach($this->factoryobj->submitvariables AS $key=>$value){

                if($value == 1){
                    if(stristr($key,'listitem_')) {
                        $name = str_replace('listitem_','',$key);
                        $names[] = $name;
                    }
                }
            }

            if(!empty($names)){
                $this->saveData(json_encode($names));
            }
        } elseif($this->factoryobj->menuid == 'choose_all'){
            $this->saveData('all');
            return true;
        }

        $this->factoryobj->copyAssetWithoutProcessing('checkbox-icon-checked-wide.png');
        $this->factoryobj->copyAssetWithoutProcessing('checkbox-icon-unchecked-wide.png');

        $output[] = $this->factoryobj->getText(strtoupper('{#search_filtering#}'),array('style' => 'form-field-section-title'));

        $output = new stdClass();
        if($this->hint){
            $output->scroll[] = $this->factoryobj->getText($this->hint, array( 'style' => 'register-text-step-2'));
        }
        $output->scroll[] = $this->factoryobj->getSpacer(9);
        $output->scroll[] = $this->factoryobj->getText('',array('style' => 'form-field-separator'));

        $count=0;

        if($this->factoryobj->menuid == 'searchbox' AND isset($this->factoryobj->submitvariables['searchterm'])){
            $searchterm = $this->factoryobj->submitvariables['searchterm'];

            foreach($this->list_data as $key=>$value){
                if(stristr($key,$searchterm)){
                    $output->scroll[] = $this->getItemRow($key,$value);
                }
            }
        } else {
            foreach($this->list_data as $key=>$value){
                $output->scroll[] = $this->getItemRow($key,$value);
                $count++;
            }
        }

        $onclick1 = new stdClass();
        $onclick1->action = 'submit-form-content';
        $onclick1->id = 'choose_all';

        $onclick2 = new stdClass();
        $onclick2->action = 'submit-form-content';
        $onclick2->id = 'list-saver';

        $onclick3 = new stdClass();
        $onclick3->action = 'open-tab';
        $onclick3->action_config = $this->tab_back;

        unset($row);
        $row[] = $this->factoryobj->getTextbutton('{#cancel#}',array('id' => 'cancel','action' => 'open-tab', 'config' => $this->tab_back,'width' => '33%'));
        $row[] = $this->factoryobj->getVerticalSpacer('1%');
        $row[] = $this->factoryobj->getTextbutton('{#all#}',array('id' => 'cancel','onclick' => array($onclick1,$onclick3), 'width' => '32%'));
        $row[] = $this->factoryobj->getVerticalSpacer('1%');
        $row[] = $this->factoryobj->getTextbutton('{#save#}',array('id' => 'cancel','onclick' => array($onclick2,$onclick3),'width' => '33%'));

        $output->footer[] = $this->factoryobj->getRow($row);
        return $output;
    }

    public function getItemRow($key,$value)
    {

        if(is_array($this->data) AND !empty($this->data)){
            $data = array_flip($this->data);
        } else {
            $data = array();
        }

        if (isset($data[$key])){
            $selectstate = array('style' => 'selectorlist_selector_checkbox_selected','variable_value' => 1,'active' => 1, 'allow_unselect' => 1,'animation' => 'fade');
        } else {
            $selectstate = array('style' => 'selectorlist_selector_checkbox_selected','variable_value' => 1,'allow_unselect' => 1,'animation' => 'fade');
        }

        //$col[] = $this->factoryobj->getText($value,array('width' => '190'));
        $col[] = $this->factoryobj->getText($value,array('style'=>'selectorlist_selector_checkbox_unselected','variable' => 'listitem_'.$key,'selected_state' => $selectstate));

        return $this->factoryobj->getRow($col,array('margin' => '0 15 2 15','padding' => '5 5 5 5','background-color' => '#ffffff',
            'vertical-align' => 'middle'));
    }



}