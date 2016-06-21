<?php

Yii::import('application.modules.aelogic.article.components.*');

class ArticleShoppinglist extends ArticleComponent {

	// Local Vars

    public $submitvariables;
    public $configobj;
    public $imagesobj;
    public $vars;
    public $varcontent;
    public $submit;

    public $actionobj;
    public $msgadded = false;

    /* shopping list's own params */
    public $title;
    public $delete_all;
    public $share;
    public $header;

    protected function requiredOptions() {
        return array(
        );
    }

    public function template() {
        $this->title = $this->addParam('title',$this->options,'Shopping List');
        $this->delete_all = $this->addParam('delete_all',$this->options,true);
        $this->share = $this->addParam('share',$this->options,true);
        $this->header = $this->addParam('header',$this->options,true);

        $this->handleActions();
        return $this->getList();
    }

    public function handleActions(){

        if(isset($this->submit['menuid'])){
            $menuid = $this->submit['menuid'];

            /* remove individual shopping list items */
            if($menuid > 80808079 AND $menuid < 80808280){
                $this->shoppingListItemRemove($menuid);
            }

            /* empty shopping list */
            if($menuid == 80808060){
                AeplayVariable::updateWithName($this->playid,'shopping_list',' ',$this->gid);
            }
        }
    }

    private function shoppingListItemRemove($id){

        if (isset($this->varcontent['shopping_list']) AND $this->varcontent['shopping_list']) {
            $items = json_decode($this->varcontent['shopping_list']);
            if (is_object($items)) {
                $count = 80808080;

                while($item = each($items)){
                    if($count == $id){
                        $key = $item['key'];
                        unset($items->$key);
                        break;
                    }
                    $count++;
                }

            }
        }

        if(isset($items)){
            AeplayVariable::updateWithName($this->playid,'shopping_list',json_encode($items),$this->gid);
            $this->varcontent['shopping_list'] = json_encode($items);
        }
    }


    public function getList(){
        $count = 80808080;
        $output = array();

        // get the variable content
        $items = (array)json_decode($this->varcontent[$this->content]);

        if(!is_array($items) OR empty($items)){
            return $output;
        }

/*      if($this->header){
            $output = $this->shoppingListHeader();
        }*/

        while($row = each($items)){

            $key = $row['key'];
            $value = $row['value'];
            $value = ceil($value);

            if(isset($row[0])) {
                $obj = new StdClass;
                $obj->type = 'row';
                $obj->row_content = $this->listRow($count,$value,$key);
                $obj->style = 'shopping_list_row';
                $output[] = $obj;
                $output[] = $this->factoryobj->getImage('shoppinglist-divider.png');
                $count++;
            }
        }

        return $output;

    }

    public function listRow($count,$column1,$column2){

        $obj = new StdClass;
        $obj->type = 'msg-plain';
        $obj->style = 'shopping_column_1';
        $obj->content = $column1;
        $output[] = $obj;

        $obj = new StdClass;
        $obj->type = 'msg-plain';
        $obj->style = 'shopping_column_2';
        $obj->content = $column2;
        $output[] = $obj;

        /* delete button */
        $item = new StdClass;
        $item->id = $count;
        $item->image = $this->factoryobj->getImageFileName('remove-2-small.png');
        $item->action = 'submit-form-content';
        $item->action_config = '';
        $item->text = '';

        $output[] = ArticleMenuComponents::getSingleItemMenu($item,$count,'shopping_column_3');

        return $output;
    }

    private function shoppingListHeader(){
        $this->setMyColors();

        $columnprefs = array('text_color' => $this->text_color,'font-size' => 14);

        $column1 = $this->factoryobj->getText('hello world',$columnprefs);
        $column2 = $this->factoryobj->getText('hello world',$columnprefs);

        $output[] = $this->factoryobj->getRow(array($column1,$column2),array(
            'width' => '91%','background_color' => $this->background_color,
            'color' => $this->text_color,'margin' => '5 15 5 15',

        ));

        return $output;
    }



}