<?php


namespace Article\Models;

use CActiveRecord;
use Aevariable;
use AeplayVariable;

class ArticleModel extends CActiveRecord {

    use Variables;

    public $configobj;
    private $varcontent;

    public function __construct($obj){

        parent::__construct();
        /* this exist to make the referencing of
        passed objects & variables easier */

        while($n = each($this)){
            $key = $n['key'];
            if(isset($obj->$key) AND !$this->$key){
                $this->$key = $obj->$key;
            }
        }

    }

    public function tester(){
        echo('hello');
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


    public function getConfigParam($param,$default=false){

        if (isset($this->configobj->$param)) {
            return $this->configobj->$param;
        } elseif ($default) {
            return $default;
        }

        return false;
    }




}