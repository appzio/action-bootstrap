<?php

/* Old model */


class ArticleModel extends CActiveRecord {

    public $configobj;
    public $varcontent;

    /* @var ArticleController */
    public $factory;

    public function factoryInit($data){
        $this->factory = $data;
    }

    public function tableName(){
        return 'ae_game_branch_action';
    }

    public function primaryKey()
    {
        return 'id';
    }

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function relations()
    {
        return array(
            'type' => array(self::BELONGS_TO, 'ae_game_branch_action_type', 'type_id'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'type_id' => '{%task_type%}',
            'name' => '{%task_name%}',
        );
    }

    public static function getVariables($gid){
        $vars = Aevariable::model()->findAllByAttributes(array('game_id' => $gid));

        foreach ($vars as $var) {
            $name = $var->name;
            $varnames[$name] = $var->id;
        }

        if(isset($varnames)){
            return $varnames;
        } else {
            return false;
        }
    }

    public static function getVariableContent($playid){
        $vars = AeplayVariable::model()->with('variable')->findAllByAttributes(array('play_id' => $playid));

        foreach($vars as $var){
            $name = $var->variable->name;
            $varcontent[$name] = $var->value;
        }

        if(isset($varcontent)){
            return $varcontent;
        } else {
            return false;
        }
    }

    public static function saveVariables($vars,$playid,$exclude=false){

        if(is_array($vars)){
            foreach ($vars as $var_id => $var_value) {
                if(!isset($exclude[$var_id])){
                    if(is_numeric($var_id)){
                        AeplayVariable::updateWithId($playid, $var_id, $var_value);
                    } else {
                        /* deals mainly tags or any other format where
                        the variable value is a list of values */
                        if(stristr($var_id,'_')){
                            $id = substr($var_id,0,strpos($var_id,'_'));
                            $fieldname=substr($var_id,strpos($var_id,'_')+1);
                            $arraysave[$id][$fieldname] = $var_value;
                        }
                    }
                }
            }
        }

        if(isset($arraysave)){
            foreach ($arraysave as $key=>$savebit){
                AeplayVariable::updateWithId($playid, $key, $savebit);
            }
        }

        return true;

    }

    public function getConfigParam($param,$default=false){

        if (isset($this->configobj->$param)) {
            return $this->configobj->$param;
        } elseif ($default) {
            return $default;
        }

        return false;
    }

    public function getSavedVariable($varname,$default=false){

        if (isset($this->varcontent[$varname])) {
            return $this->varcontent[$varname];
        } elseif ($default) {
            return $default;
        }

        return false;
    }



}