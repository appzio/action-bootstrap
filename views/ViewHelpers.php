<?php

namespace Article\Views;

trait ViewHelpers {


	public function attachStyles(\stdClass $obj, array $styles) {

	    if(!$styles){
	        return $obj;
        }

	    $obj->style_content = new \stdClass();

	    foreach($styles as $name=>$style){
            $obj->style_content->$name = $style;
        }

        return $obj;
	}


    public function attachParameters(\stdClass $obj, array $parameters) {

	    if(!$parameters){
	        return $obj;
        }

        $obj->style_content = new \stdClass();

        foreach($parameters as $name=>$param){
            $obj->$name = $param;
        }

        return $obj;
    }


    public function configureDefaults($obj){

	    if(!isset($obj->style) AND !isset($obj->style_content)){
	        $obj->style = $obj->type .'-default';
        }

        return $obj;

    }


}