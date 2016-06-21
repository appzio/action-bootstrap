<?php

/* here is stuff that COULD be in the Aeaction model, but its hear mainly for security purposes */


class ArticleModel extends CActiveRecord {


    public function init(){

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

    public static function saveVariables($vars,$playid){
        
        foreach ($vars as $var_id => $var_value) {
            AeplayVariable::updateWithId($playid, $var_id, $var_value);
        }

        return true;

    }

}