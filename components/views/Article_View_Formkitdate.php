<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Formkitdate extends ArticleComponent {


    public $vars;
    public $variable;
    public $value;
    public $years_display;

    public function template() {

        $title = $this->addParam('title',$this->options,false);
        $value = $this->addParam('value',$this->options,false);
        $variable = $this->addParam('variable',$this->options,false);
        $error = $this->addParam('error',$this->options,false);
        $this->years_display = $this->addParam('years',$this->options,'next-three');

        /* title */
        $output[] = $this->factoryobj->getSpacer(5);
        $output[] = $this->factoryobj->getText(strtoupper($title), array( 'style' => 'form-field-titletext'));

        if($value){
            $date = strtotime($value);
        } elseif($this->factoryobj->getSavedVariable($variable)) {
            $date = strtotime($this->factoryobj->getSavedVariable($variable));
        } else {
            $date = time();
        }

        if($date == '-7200'){
            $date = time();
        }

        $yearlist = $this->getYearlist($date);

        $current_date = date('d',$date);
        $current_month = date('M',$date);
        $current_year = date('Y',$date);

        $cols[] = $this->factoryobj->getFieldlist('Jan;{#month_jan#};Feb;{#month_feb#};Mar;{#month_mar#};Apr;{#month_apr#};May;{#month_may#};Jun;{#month_jun#};Jul;{#month_jul#};Aug;{#month_aug#};Sep;{#month_sep#};Oct;{#month_oct#};Nov;{#month_nov#};Dec;{#month_dec#}',
            array('hint' => 'Comment (optional):','style' => 'datepicker','value' => $current_month,
                'variable' => $variable .'_month'
                ));
        $cols[] = $this->factoryobj->getFieldlist('01;1;02;2;03;3;04;4;05;5;06;6;07;7;08;8;09;9;10;10;11;11;12;12;13;13;14;14;15;15;16;16;17;17;18;18;19;19;20;20;21;21;22;22;23;23;24;24;25;25;26;26;27;27;28;28;29;29;30;30;31;31',
            array( 'hint' => 'Comment (optional):','style' => 'datepicker','value' => $current_date,
                'variable' => $variable .'_day'
            ));
        $cols[] = $this->factoryobj->getFieldlist($yearlist,
            array('hint' => 'Comment (optional):','style' => 'datepicker','value' => $current_year,
                'variable' => $variable .'_year'
            ));

        $output[] = $this->factoryobj->getRow($cols,array('style' => 'list_container'));

        return $this->factoryobj->getColumn($output,array('style' => 'mobileproperty_input_available_date'));

    }

    private function getYearlist($date){
        $current_year = date('Y',$date);

        switch($this->years_display){
            case 'next-three':
                $year_array = [$current_year,$current_year+1,$current_year+2,$current_year+3];
                break;

            case 'birth-years':
                $year = 1910;

                while($year <= $current_year){
                    $year_array[] = $year;
                }
                break;
        }


        $yearlist = '';
        if(isset($year_array)){
            foreach($year_array as $year){
                $yearlist .= $year .';' .$year .';';
            }

            $yearlist = substr($yearlist,0,-1);
            return $yearlist;
        }

        return '2017;2017;2018;2018';

    }
}