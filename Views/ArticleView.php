<?php

namespace Article\Views;

interface ArticleViewInterface {
    function tab1();
}

class ArticleView implements ArticleViewInterface {

    use ViewHelpers;
    use Components\Text;
    use Components\Image;
    use Components\Onclick;

    public $model;
    public $controller;
    public $data;
    public $colors;

    public $color_text;
    public $color_icon;
    public $color_background;
    public $color_button_text;
    public $color_dark_button_text;

    /*Array ( [text_color] => #FF000000 [icon_color] => #FF000000 [background_color] => #FFCFD8DC [button_text] => #FF000000
    [dark_button_text] => #FFFFFFFF [top_bar_text_color] => #FFFFFFFF [top_bar_icon_color] => #FFFFFFFF
    [button_more_info_color] => #FF000000 [button_more_info_icon] => #FFFFFFFF [button_more_info_icon_color] => #FFFFFFFF
    [button_more_info_text_color] => #FFFFFFFF [item_text_color] => #FFFFFFFF [top_bar_color] => #FFD32F2F
    [button_color] => #FF536DFE [item_color] => #FFFFCDD2 [button_icon_color] => #FFFFFFFF
     [button_text_color] => #FFFFFFFF [side_menu_color] => #FFFFFFFF [side_menu_text_color] => #FF000000 )*/

    public function __construct($obj){

        while($n = each($this)){
            $key = $n['key'];
            if(isset($obj->$key) AND !$this->$key){
                $this->$key = $obj->$key;
            }
        }

        $this->data = new \stdClass();

    }

    public function tab1(){
        $this->data = new \stdClass();
        $this->data->header = array();
        $this->data->scroll = array();
        $this->data->footer = array();
        $this->data->onload = array();
        $this->data->control = array();
        $this->data->divs = array();
        return $this->data;
    }



}