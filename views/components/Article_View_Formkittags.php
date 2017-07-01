<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkittags extends ArticleComponent {

    public $vars;
    public $variable;
    public $value;
    public $title;
    public $varname;
    public $items;
    public $error;
    public $show_separator;
    public $field_offset;

    public function template() {

        $this->title = $this->addParam('title',$this->options,false);
        $this->varname = $this->addParam('variable',$this->options,false);
        $this->items = $this->addParam('items',$this->options,false);
        $this->error = $this->addParam('error',$this->options,false);
        $this->show_separator = $this->addParam('show_separator',$this->options,true);
        $this->field_offset = $this->addParam('field_offset',$this->options,7);
        $this->value = $this->addParam('value',$this->options,false);

        /* this will show as many tags as possible on one row */
        $clustered_mode = $this->addParam('clustered_mode',$this->options,true);
        $row_mode = $this->addParam('row_mode',$this->options,false);

        $savearray = array();

        foreach($this->factoryobj->submitvariables as $key=>$val){
            if(stristr($key,$this->varname)){
                $id = str_replace($this->varname.'_','',$key);
                $savearray[$id] = $val;
            }
        }

        if (is_array($this->value) AND !empty($this->value)) {
            // Do nothing
        } elseif(empty($savearray)) {
            $this->value = json_decode($this->factoryobj->getSavedVariable($this->varname),true);
        } else {
            $this->value = $savearray;
        }
        
        $this->variable = $this->factoryobj->getVariableId($this->varname) ? $this->factoryobj->getVariableId($this->varname) : $this->varname;

        if($clustered_mode){
            $output = $this->cluster();
        } else if ( $row_mode ) {
            $output = $this->displayInRow();
        } else {
            $output = $this->sidebyside();
        }

        if ( $this->show_separator ) {
            if($this->error){
                $output[] = $this->factoryobj->getText('',array('style' => 'form-field-separator-error'));
                $output[] = $this->factoryobj->getText($this->error,array('style' => 'formkit-error'));
            } else {
                $output[] = $this->factoryobj->getText('',array('style' => 'form-field-separator'));
            }
        }

        return $this->factoryobj->getColumn($output, array('style' => 'form-field-row'));
    }


    public function displayInRow() {
        $output[] = $this->factoryobj->getText(strtoupper($this->title), array('style' => 'form-field-textfield-onoff'));

        foreach ($this->items as $key=>$item){
            $output[] = $this->factoryobj->getRow(array(
                $this->getItemRowBig($key,$item, 'onrow')
            ));
        }

        return $output;
    }


    public function sidebyside(){
        $output[] = $this->factoryobj->getText(strtoupper($this->title), array('style' => 'form-field-textfield-onoff'));
        $counter = 1;

        foreach ($this->items as $key=>$item){
            if($counter == 2){
                $col[] = $this->getItemRowBig($key,$item);
                $row[] = $this->factoryobj->getRow($col);
                unset($col);
                $counter = 1;
            } else {
                $col[] = $this->getItemRowBig($key,$item);
                $counter++;
            }
        }

        if($counter != 1 AND isset($col)){
            $row[] = $this->factoryobj->getRow($col);
        }

        if(!empty($row)){
            $row[] = $this->factoryobj->getVerticalSpacer('');
            $output[] = $this->factoryobj->getColumn($row);
        }

        return $output;
    }


    public function getItemRowBig($key,$title, $class = 'big') {
        if(isset($this->value[$key]) AND $this->value[$key] == 1){
            $selectstate = array('style' => 'formkit-radiobutton-selected-' . $class,'active' => '1','variable_value' => 1,'allow_unselect' => 1,'animation' => 'fade');
            return $this->factoryobj->getText($title,array('variable'=> $this->variable.'_'.$key,'allow_unselect' => 1,'style' => 'formkit-radiobutton-unselected-' . $class,'selected_state' => $selectstate));
        } else {
            $selectstate = array('style' => 'formkit-radiobutton-selected-' . $class,'variable_value' => 1,'allow_unselect' => 1,'animation' => 'fade');
            return $this->factoryobj->getText($title,array('variable'=> $this->variable.'_'.$key,'allow_unselect' => 1,'style' => 'formkit-radiobutton-unselected-' . $class,'selected_state' => $selectstate));
        }
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

    public function cluster() {
        $output[] = $this->factoryobj->getText(strtoupper($this->title), array('style' => 'form-field-textfield-onoff'));

        /* here you find a most demented way to try to figure out how many items in a row we should have */
        $row = array();
        $counter=0;

        foreach ($this->items as $key=>$item){
            /* 27 is the width with paddings and margins */
            $counter = $counter + 35;
            $counter = $counter + (strlen($item) * $this->field_offset);

            if($counter > $this->factoryobj->screen_width){
                $row[] = $this->factoryobj->getVerticalSpacer('');
                $output[] = $this->factoryobj->getRow($row);
                unset($row);
                $row[] = $this->getItemRow($key,$item);
                $counter=0;
                $counter = $counter + 35;
                $counter = $counter + (strlen($item) * $this->field_offset);
            } else {
                $row[] = $this->getItemRow($key,$item);
            }
        }

        if(!empty($row)){
            $row[] = $this->factoryobj->getVerticalSpacer('');
            $output[] = $this->factoryobj->getRow($row);
        }

        return $output;
    }

}