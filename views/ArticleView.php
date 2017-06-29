<?php

namespace Article\Views;


class ArticleView {

    public $model;
    public $controller;
    public $data;

    public $exception_state;

    public function __construct($obj){
        //error_reporting(0);
        //set_exception_handler(array($this, 'exception_handler'));

        while($n = each($this)){
            $key = $n['key'];
            if(isset($obj->$key) AND !$this->$key){
                $this->$key = $obj->$key;
            }
        }
    }

/*    public function exception_handler($exception) {
        //print_r($exception->message);die();
        //$string = $exception['messge'] .$exception['line'] .$exception['file'];
        $string = $exception->message .$exception->line .$exception->file;
        $this->exception_state = $string;
        //ini_set( "display_errors", "off" );
        return $string;
    }*/


    /**
     * @param $content
     * @param array $params for example onclick
     * @param array $styles for example font-size
     * @return \stdClass
     */

    public function getText(string $content,array $params=array(),array $styles=array()){
        $obj = new \stdClass();
        $obj->type = 'text';
        $obj->content = $content;
        return $obj;
    }




}