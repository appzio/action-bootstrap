<?php


namespace Article\Controllers;

interface BootstrapControllerInterface {
    function tab1();
}

class BootstrapController implements BootstrapControllerInterface {


    /* this is here just to fix a phpstorm auto complete bug with namespaces */
    /* @var \Bootstrap\Models\BootstrapModel */
    public $phpstorm_bugfix;

    /* @var \Article\Views\ArticleView */
    public $view;

    /* @var \Bootstrap\Models\BootstrapModel */
    public $model;

    public function __construct($obj){

        /* this exist to make the referencing of
        passed objects & variables easier */

        while($n = each($this)){
            $key = $n['key'];
            if(isset($obj->$key) AND !$this->$key){
                $this->$key = $obj->$key;
            }
        }

    }

    public function tab1(){

    }


    public function tester(){
        echo('hello');
    }


}