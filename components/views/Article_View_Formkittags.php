<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkittags extends ArticleComponent {


    public $vars;
    public $variable;
    public $value;

    public function template() {

        $title = $this->addParam('title',$this->options,false);
        $varname = $this->addParam('variable',$this->options,false);
        $this->value = $this->addParam('value',$this->options,false);
        $items = $this->addParam('items',$this->options,false);
        $error = $this->addParam('error',$this->options,false);

        $savearray = array();

        foreach($this->factoryobj->submitvariables as $key=>$val){
            if(stristr($key,$varname)){
                $id = str_replace($varname.'_','',$key);
                $savearray[$id] = $val;
            }
        }

        if(is_array($this->value) AND !empty($this->value)){

        }elseif(empty($savearray)){
            $this->value = json_decode($this->factoryobj->getSavedVariable($varname),true);
        } else {
            $this->value = $savearray;
        }
        
        $this->variable = $this->factoryobj->getVariableId($varname) ? $this->factoryobj->getVariableId($varname) : $varname;
        $output[] = $this->factoryobj->getText(strtoupper($title), array('style' => 'form-field-textfield-onoff'));

        /* here you find a most demented way to try to figure out how many items in a row we should have */
        $row = array();
        $counter=0;

        foreach ($items as $key=>$item){
            /* 27 is the width with paddings and margins */
            $counter = $counter + 27;
            $counter = $counter + (strlen($item)*6.5);

            if($counter > $this->factoryobj->screen_width){
                $row[] = $this->factoryobj->getVerticalSpacer('');
                $output[] = $this->factoryobj->getRow($row);
                unset($row);
                $row[] = $this->getItemRow($key,$item);
                $counter=0;
                $counter = $counter + 27;
                $counter = $counter + (strlen($item)*6.5);
            } else {
                $row[] = $this->getItemRow($key,$item);
            }
        }

        if(!empty($row)){
            $row[] = $this->factoryobj->getVerticalSpacer('');
            $output[] = $this->factoryobj->getRow($row);
        }

        if($error){
            $output[] = $this->factoryobj->getText('',array('style' => 'form-field-separator-error'));
            $output[] = $this->factoryobj->getText($error,array('style' => 'formkit-error'));
        } else {
            $output[] = $this->factoryobj->getText('',array('style' => 'form-field-separator'));
        }



        return $this->factoryobj->getColumn($output, array('style' => 'form-field-row'));
    }


    public function getItemRow($key,$title) {
        if(isset($this->value[$key]) AND $this->value[$key] == 1){
            $selectstate = array('style' => 'formkit-radiobutton-selected','active' => '1','variable_value' => 1,'allow_unselect' => 1,'animation' => 'fade');
            return $this->factoryobj->getText($title,array('variable'=> $this->variable.'_'.$key,'allow_unselect' => 1,'style' => 'formkit-radiobutton-unselected','selected_state' => $selectstate));
        } else {
            $selectstate = array('style' => 'formkit-radiobutton-selected','variable_value' => 1,'allow_unselect' => 1,'animation' => 'fade');
            return $this->factoryobj->getText($title,array('variable'=> $this->variable.'_'.$key,'allow_unselect' => 1,'style' => 'formkit-radiobutton-unselected','selected_state' => $selectstate));
        }
    }
}