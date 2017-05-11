<?php

Yii::import('application.modules.aelogic.article.components.*');

class ArticleSuggestedTerms {

    public $suggestions = array();

    public function getSuggestions( $key, $loosematch = false, $limit = 999 ) {
        $words = $this->suggestions;

        if ( empty($words) ) {
            return false;
        }

        $output = array();
        $key = strtolower($key);

        foreach($words AS $word){
            $original_word = $word;
            $word = strtolower($word);
            $len = strlen($key);
            $pointerlength = $len+1;
            $pointer = mb_substr($word,0,$pointerlength);

            if ($loosematch) {
                if(strstr($word,$key)){
                    if(!isset($output[$pointer]) OR count($output[$pointer]) < $limit){
                        $output[] = $original_word;
                        //$output[$pointer][] = $word;
                    }
                }
            } else {
                $wordpart = mb_substr($word,0,$len);
                if($wordpart == $key){
                    if(!isset($output[$pointer]) OR count($output[$pointer]) < $limit) {
                        //$output[$pointer][] = $word;
                        $output[] = $original_word;
                    }
                }
            }
        }

        return $output;
    }

}