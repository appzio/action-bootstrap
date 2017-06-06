<?php

class ArticlePreprocessor {

    public $styles;
    public $gid;

    public function loadStyles(){

    }

    public function Process($data){

        if(isset($data->scroll)){
            $data->scroll = $this->doProcessing($data->scroll);
        }

        return $data;
    }

    public function saveStyles(){

        $existing_styles = (array)Apistyles::getAppStyles($this->gid);
        $new_styles = (array)$this->styles;
        $styles = $existing_styles+$new_styles;

        if(md5(serialize($existing_styles)) != md5(serialize($styles))){
            $mobile = Aemobile::model()->findByPk($this->gid);
            $styles = json_decode($mobile->styles,true) + $new_styles;
            $mobile->styles = json_encode($styles);
            $mobile->update();
        }

    }

    private function doProcessing($data){
        $output = array();

        foreach ($data as $node){
            if(isset($node->style_content)){
                $md5 = md5(serialize($node->style_content));
                if(isset($this->styles[$md5])){
                    unset($node->style_content);
                    $node->style = $this->styles[$md5];
                } else {
                    $node->style = $this->addNewStyle($md5,$node->style_content);
                    unset($node->style_content);
                }
            }

            $output[] = $node;
        }

        return $output;
    }


    private function addNewStyle($md5,$stylecontent){
        $this->styles[$md5] = $stylecontent;
        return $md5;
    }

}


