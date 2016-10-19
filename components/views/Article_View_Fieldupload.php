<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Fieldupload extends ArticleComponent {

    public $required_params = array('type');
    public $vars;

    public function template() {
        $obj = new StdClass;
        $obj->type = ( $this->options['type'] == 'image' ? 'field-upload-image' : 'field-upload-video' );

        $image = $this->addParam('image',$this->options,false);

        if($image){
            $obj->image = $image;
        }

        $obj->content = $this->content;

        return $obj;
    }

}