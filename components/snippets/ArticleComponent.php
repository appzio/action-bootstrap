<?php

/**
 * This should hold complete implementation of all article view
 * components we currently support
 * Implements factory design pattern
 *
 **/

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aelogic.article.components.views.*');

class ArticleComponent {

    public $content;
    public $options;

    /** @var ArticleController */
    public $factoryobj;

    public $playid;
    public $gid;
    public $vars;

    public $action_id;

    public $background_color;
    public $hilite_color;
    public $text_color;

    public function __construct($obj) {
        while($n = each($this)){
            $key = $n['key'];

            if(isset($obj->$key) AND !$this->$key){
                $this->$key = $obj->$key;
            }
        }
    }

    /**
     * Merge user defined arguments into defaults array.
     *
     * @param array $args   Value to merge with $defaults
     * @param array         $defaults Optional. Array that serves as the defaults. Default empty.
     * @return array        Merged user defined values with defaults.
     */
    public function parseDefaultArgs( $args, $defaults = '' ) {

        if ( is_object( $args ) ) {
            $r = get_object_vars( $args );
        } elseif ( is_array( $args ) ) {
            $r =& $args;
        }

        if ( is_array( $defaults ) ) {
            return array_merge( $defaults, $r );
        }

        return $r;
    }


    public function addStyles($obj,$options){
        if ( isset($options['style']) ) {
            /* global style name */
            $obj->style = $options['style'];

            // any inline styles will override the style content entirely
            unset($this->options['style_content']);

        } elseif(isset($this->options['style_content'])){
            /* inline styles go here as an array */
            $obj->style_content = $options['style_content'];
        }

        if(isset($options['onclick'])){
            $obj->onclick = $options['onclick'];
        }

        /* this makes it possible to include inline styles in the main config array */

        $styleattributes = array_flip($this->styleAttributes());

        $comparison = false;

        if ( is_array($options) AND !empty($options) ) {
            $comparison = array_intersect_key($options,$styleattributes);
        }


        if($comparison){
            if(!isset($obj->style_content)){
                $obj->style_content = new StdClass();
            }

            foreach($comparison as $key => $value){
                $obj->style_content->$key = $value;
            }
        }

        if(isset($obj->style) AND !$obj->style){
            unset($obj->style);
        }

        if(isset($obj->style)){
            unset($obj->style_content);
        }

        return $obj;
    }

    /* currently supported inline styles */
    public static function styleAttributes(){
        return array(
            'margin',
            'padding',
            'orientation',
            'background',
            'alignment',
            'radius',
            'opacity',
            'orientation',
            'height',
            'width',
            'align',
            'crop',
            'text-style',
            'font-size',
            'text-color',
            'border-color',
            'border-width',
            'font-android',
            'font-ios',
            'background-color',
            'background-image',
            'background-size',
            'color',
            'shadow-color',
            'shadow-offset',
            'shadow-radius',
            'vertical-align',
            'border-radius',
            'text-align',
            'lazy',
            'floating',
            'float',
            'max-height'
        );
    }

    public function getTemplate(){

        if ( !empty($this->options) AND !empty($this->defaults) ) {
            $this->options = $this->parseDefaultArgs( $this->options, $this->defaults );
        }

        if( isset($this->required_params) AND !empty($this->required_params) ){
            foreach($this->required_params AS $param){
                if(!isset($this->options[$param])){
                    $out[] = $this->factoryobj->getText('Required parameter {' .$param .'} missing',array('color' => '#C91E19','font-size' => 13,'text-align' => 'center'));
                }
            }

            if(isset($out)){
                return $this->factoryobj->getColumn($out);
            }

        }

        if(isset($this->options['strlen'])){
            if(strlen($this->content) > $this->options['strlen']){
                $this->content = substr($this->content,0,$this->options['strlen']) .'...';
            }
        }

        $obj = $this->template();

        if ( isset($this->options['variable']) ) {
            $variable = $this->options['variable'];

            if(!is_numeric($variable) AND isset($this->vars[$variable])){
                $obj->variable = $this->vars[$variable];
            } else {
                $obj->variable = $variable;
            }
        }

        if ( isset($this->options['visibility']) ) {
            $obj->visibility = $this->options['visibility'];
        }

        if ( isset($this->options['swipe_id']) ) {
            $obj->swipe_id = $this->options['swipe_id'];
        }

        if ( isset($this->options['visibility_delay']) ) {
            $obj->visibility_delay = $this->options['visibility_delay'];
        }

        if ( isset($this->options['transition']) ) {
            $obj->transition = $this->options['transition'];
        }

        if ( isset($this->options['animation']) ) {
            $obj->animation = $this->options['animation'];
        }

        if ( isset($this->options['time_to_live']) ) {
            $obj->time_to_live = $this->options['time_to_live'];
        }

        if ( isset($this->options['send_ids']) ) {
            $obj->time_to_live = $this->options['send_ids'];
        }

        return $this->addStyles($obj,$this->options);

    }
    
    public static function addParam($name,$params,$default=false){

        if(is_object($params) AND isset($params->$name))
            return $params->$name;
        if(is_array($params) AND isset($params[$name])){
            return $params[$name];
        } else {
            return $default;
        }
    }

    public function setMyColors(){
        $colors = Controller::getColors(false,false,$this->action_id);
        $background_color = Helper::normalizeColor($colors->top_bar_color);
        $this->background_color = substr($background_color,3);
        $colorhelp = new Color($this->background_color);
        $this->hilite_color = $colorhelp->darken();
        $this->text_color = Helper::normalizeColor($colors->top_bar_text_color);
    }


}