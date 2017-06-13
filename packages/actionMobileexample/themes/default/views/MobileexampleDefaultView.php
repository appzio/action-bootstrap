<?php

/*

    this is a dynamic article action, which is launched either by
    Apiaction.php (component)
    Updatevariables (api method)
    Refreshaction (api method)

    If its called by either api method, the object is passed on to Apiaction.php eventually.

    Either RenderData or DynamicUpdate are called

    It should return json which gets put into the layoutconfig

    Data saving on picture submit is little complicated, because we upload
    async from the client. So once user has submitted a photo, we launch
    an async process to deal with that and to eventually add it to the action.
    Process is not perfect, as we rely on temporary variable values that might
    get overwritten if user uploads two photos very quickly after one another.

*/

Yii::import('application.modules.aegameauthor.models.*');
Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aelogic.packages.actionMobileregister.models.*');
Yii::import('application.modules.aelogic.packages.actionMobilelogin.models.*');

class MobileexampleDefaultView extends MobileexampleDefaultController {

    public $data;
    public $theme;

    public function tab1(){
        $this->data = new StdClass();
        $this->setHeader();
        $this->data->scroll[] = $this->getText('Hello World!');
        $this->data->scroll[] = $this->getExampleString();
        return $this->data;
    }

    public function tab2(){
        $this->data = new StdClass();
        $this->setHeader();
        $this->data->scroll[] = $this->getText('Hello World 2!');
        return $this->data;
    }

    public function tab3(){
        $this->data = new StdClass();
        $this->setHeader();
        $value = $this->getSubmitVariable('searchterm') ? $this->getSubmitVariable('searchterm') : '';
        $row[] = $this->getImage('search-icon-for-field.png',array('height' => '25'));

        Yii::import('application.modules.aelogic.packages.actionMobileexample.models.*');


        $row[] = $this->getFieldtext($value,array('style' => 'example_searchbox_text',
            'hint' => '{#free_text_search#}','submit_menu_id' => 'searchbox','variable' => 'searchterm',
            //'suggestions' => MobileexampleAccessor::getInitialWordList(10),
            'id' => 'something',
            'suggestions_style_row' => 'example_list_row','suggestions_text_style' => 'example_list_text',
            'submit_on_entry' => '1',
            ));
        $col[] = $this->getRow($row,array('style' => 'example_searchbox'));
        $col[] = $this->getTextbutton('Search',array('style' => 'example_searchbtn','id' => 'dosearch'));
        $this->data->header[] = $this->getRow($col,array('background-color' => $this->color_topbar));

        //$this->os->whatever->soon = 'ykis';

        $this->data->scroll[] = $this->getLoader('Loading',array('color' => '#000000','visibility' => 'onloading'));

        if($this->menuid == 'searchbox'){
            if(isset($this->submitvariables['searchterm']) AND strlen($this->submitvariables['searchterm']) > 0){
                $searchterm = $this->submitvariables['searchterm'];
                $wordlist = MobileexampleAccessor::getLetter($searchterm,10);

                foreach($wordlist as $word){
                    $this->data->scroll[] = $this->getText($word);
                }
            }
        } else {
            $this->data->scroll[] = $this->getText('This should submit on each entry',array(
                'visibility' => 'delay','visibility_delay' => '0.5',
                'transition' => 'pop', 'time_to_live' => '5'
            ));
        }

        $this->data->scroll[] = $this->getText('This should appear after a bit',array(
            'visibility' => 'delay','visibility_delay' => '1.5','transition' => 'fade'));

/*        $value = $this->getSubmitVariable('searchterm') ? $this->getSubmitVariable('searchterm') : '';
        $row[] = $this->getImage('search-icon-for-field.png',array('height' => '25'));
        $row[] = $this->getFieldtext($value,array('style' => 'example_searchbox_text',
            'hint' => '{#free_text_search#}','id' => 'searchbox','variable' => 'searchterm',
            'suggestions' => MobileexampleAccessor::getInitialWordList(10),
            'suggestions_style_row' => 'example_list_row','suggestions_text_style' => 'example_list_text',
            'submit_on_entry' => '1'
        ));
        $col[] = $this->getRow($row,array('style' => 'example_searchbox'));
        $col[] = $this->getTextbutton('Search',array('style' => 'example_searchbtn','id' => 'dosearch'));
        $this->data->scroll[] = $this->getRow($col,array('background-color' => $this->color_topbar));*/


        return $this->data;
    }

    public function setHeader(){
        $this->data->header[] = $this->getTabs(array('tab1' => 'Main','tab2' => 'Form','tab3' => 'SmartInput'));
    }


}