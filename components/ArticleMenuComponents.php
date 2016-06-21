<?php


class ArticleMenuComponents {

    public $imagesobj;
    public $mainobj;


    public function getItemsWithDelete($output,$items){

        $count = 200;

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
                $count++;

               $obj = new StdClass;
                $obj->type = 'image';
                $obj->content = $this->imagesobj->getAsset('shoppinglist-divider.png');
                $obj->style = 'image';
                $output[] = $obj;

            }
        }

        return $output;
    }


    public function componentMenuItem($config){
        $item = new StdClass;

        if(isset($config['id'])) { $id = $config['id']; } else { $id = 34893838383; }
        if(isset($config['image'])) { $image = $config['image']; } else { $image = ''; }
        if(isset($config['action'])) { $action = $config['action']; } else { $action = 'submit-form-content'; }
        if(isset($config['icon'])) { $icon = $config['icon']; } else { $icon = ''; }
        if(isset($config['action_config'])) { $action_config = $config['action_config']; } else { $action_config = ''; }
        if(isset($config['text'])) { $text = $config['text']; } else { $text = ''; }
        if(isset($config['slug'])) { $slug = $config['slug']; } else { $slug = ''; }
        if(isset($config['call_backend'])) { $call_backend = $config['call_backend']; } else { $call_backend = 0; }
        if(isset($config['open_popup'])) { $open_popup = $config['open_popup']; } else { $open_popup = 0; }
        if(isset($config['sync_open'])) { $sync_open = $config['sync_open']; } else { $sync_open = 0; }
        if(isset($config['sync_close'])) { $sync_close = $config['sync_close']; } else { $sync_close = 0; }
        if(isset($config['style_content'])) { $style_content = $config['style_content']; } else { $style_content = ''; }
        if(isset($config['max_dimensions'])) { $max_dimensions = $config['max_dimensions']; } else { $max_dimensions = 1200; }

        if($icon){
            $icon = $this->imagesobj->getAsset($icon);
        }

        if($image){
            $image = $this->imagesobj->getAsset($image);

        }

        $item->id = $id;
        $item->image = $image;
        $item->icon = $icon;
        $item->state = 'active';
        $item->action = $action;
        $item->action_config = $action_config;

        if($action == 'upload-image'){
            $item->max_dimensions = $max_dimensions;
        }

        if($text){ $item->text = $text; }
        if($slug) {$item->slug = $slug; }
        if($call_backend){$item->call_backend = $call_backend;}
        if($open_popup) {$item->open_popup = $open_popup;}
        if($sync_open) {$item->sync_open = $sync_open;}
        if($sync_close){$item->sync_close = $sync_close;}
        if($style_content){$item->style_content = $style_content;}
        return $item;
    }


    public static function componentMenu($items,$config){

        if(isset($config['id'])) { $id = $config['id']; } else { $id = 2838338; }
        if(isset($config['title'])) { $title = $config['title']; } else { $title = ''; }
        if(isset($config['slug'])) { $slug = $config['slug']; } else { $slug = ''; }
        if(isset($config['style'])) { $style = $config['style']; } else { $style = ''; }
        if(isset($config['style_content'])) { $style_content = $config['style_content']; } else { $style_content = ''; }

        if(!isset($config['style_content']['orientation'])){
            $style_content['orientation'] = 'horizontal';
        }

        $obj = new StdClass;
        $obj->type = 'menu';
        $obj->menu_content = new StdClass;
        $obj->menu_content->id = $id;
        $obj->menu_content->items = $items;

        if($title){        $obj->menu_content->title = $title;}
        if($slug) {        $obj->menu_content->slug = $slug;}
        if($style_content){        $obj->style_content = $style_content;}
        if($style){        $obj->style = $style;}

        return $obj;
    }


    /* can either take a flat array, or array with widhts set */
    public function tabMenu($items,$active_tab,$active_color='#4bab4b',$background_color='#50ca50',$height=3,$textcolor="#000000"){

        $count = 1;
        $output = array();

        $test = $items;
        $test = each($test);

        $styles_active['margin'] = '0 0 0 0';

        /* soon obsolete */
        $styles_active['background_color'] = $active_color;
        $styles_active['font-size'] = $height;

        $styles_active['background-color'] = $active_color;
        $styles_active['font-size'] = $height;

        $styles_inactive['margin'] = '0 0 0 0';
        $styles_inactive['background_color'] = $background_color;
        $styles_inactive['font-size'] = $height;

        $styles_inactive['background-color'] = $background_color;
        $styles_inactive['font-size'] = $height;

        $menustyle['background_color'] = $background_color;
        $menustyle['background-color'] = $background_color;
        $menustyle['width'] = '100%';

        $menuiconstyle['background_color'] = $background_color;
        $menuiconstyle['background-color'] = $background_color;
        $menuiconstyle['padding'] = '10 10 10 10';
        $menuiconstyle['align'] = 'center';

        $menutextstyle['background-color'] = $background_color;
        $menutextstyle['font-size'] = 14;
        $menutextstyle['text-align'] = 'center';
        $menutextstyle['padding'] = '10 5 10 5';
        $menutextstyle['color'] = $textcolor ? $textcolor : $active_color;

        $textmenu = false;

        while($item = each($items)) {
            $id = $item['key'];
            $file = $item['value'][0];
            $width = $item['value'][1];

            $styles_inactive['width'] = $width;
            $styles_active['width'] = $width;

            $active = new StdClass;
            $active->type = 'msg-plain';
            $active->style_content = $styles_active;
            $active->content = ' ';

            $inactive = new StdClass;
            $inactive->type = 'msg-plain';
            $inactive->style_content = $styles_inactive;
            $inactive->content = ' ';

            $menutextstyle['width'] = $width;

            if ($count == $active_tab) {
                $tabs[] = $active;

                if(strstr($file,'.png')){
                    $menu = $this->componentMenuItem(array('id' => $id,'image' => $file,'action' => 'open-tab','action_config' => $id,'style_content' => $menuiconstyle));
                } else {
                    $textmenu = true;
                    //$menutextstyle['background-color'] = $active_color;
                    $menu = $this->mainobj->getText($file,$menutextstyle);

                    //
                    //$menu = $this->componentMenuItem(array('id' => $id,'text' => $file,'action' => 'open-tab','action_config' => $id,'style_content' => $menutextstyle));
                }

                $menu_items[] = $menu;
            } else {
                $tabs[] = $inactive;
                if(strstr($file,'.png')) {
                    $menu = $this->componentMenuItem(array('id' => $id, 'image' => $file, 'action' => 'open-tab', 'action_config' => $id, 'style_content' => $menuiconstyle));
                } else {
                    $menutextstyle['onclick'] = new StdClass();
                    $menutextstyle['onclick']->id = $id;
                    $menutextstyle['onclick']->action_config = $id;
                    $menutextstyle['onclick']->action = 'open-tab';
                    $menutextstyle['background-color'] = $background_color;
                    $menu = $this->mainobj->getText($file,$menutextstyle);

                }

                $menu_items[] = $menu;
            }

            $count++;
        }


        /* the actual outputted code */
        if(isset($tabs) AND isset($menu_items)){

            if($textmenu){
                $output[] = $this->mainobj->getRow($menu_items);
            } else {
                $output[] = $this->componentMenu($menu_items,array('style_content' => $menustyle));
            }

            $obj = new StdClass;
            $obj->type = 'row';
            $obj->row_content = $tabs;
            $output[] = $obj;
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
        $item->image = $this->imagesobj->getAsset('remove-2-small.png');
        $item->state = 'active';
        $item->action = 'submit-form-content';

        $output[] = ArticleMenuComponents::getSingleItemMenu($item,$count,'shopping_column_3');

        return $output;
    }


    public function getMenuWithImage($items){
        $output = array();
        $count = 0;
        $items = array_reverse($items,true);

        while($row = each($items) AND $count < 7){
            if(isset($row[1]['action_id'])) {
                $obj = new StdClass;
                $obj->type = 'row';

                if(isset($row[1]['back_button'])){
                    $back = 1;
                } else {
                    $back = 0;
                }

                $id = $row[1]['actionid'];

                $obj->row_content = ArticleMenuComponents::getRow($row[1],$id,$back);
                $obj->style = 'single_row_center';
                $output[] = $obj;
                $count++;
            }
        }

        if($count == 7){
                $obj = new StdClass;
                $obj->type = 'row';
                $obj->row_content = $this->unlockRows($count, $count);
                $obj->style = 'single_row_center';
                $output[] = $obj;
                $count++;
        } else {
            while($count < 7) {
                $obj = new StdClass;
                $obj->type = 'row';
                $obj->row_content = $this->emptyRows($count, $count);
                $obj->style = 'single_row_center';
                $output[] = $obj;
                $count++;
            }
        }


        return $output;
    }


    public function unlockRows($count,$basenumber){
        $output = array();
        //* text */
        $item = new StdClass;
        $item->id = $basenumber;
        $item->state = 'active';
        $item->action = 'open-action';
        $item->icon = $this->imagesobj->getAsset('heart-green-menu.png');
        $item->text = 'Unlock more slots';
        $item->sync_open = 1;

        $output[] = ArticleMenuComponents::getSingleItemMenu($item, $basenumber, 'bookmarks_column_1_off');

        /* delete button */
        $item = new StdClass;
        $item->id = $basenumber;
        $item->image = $this->imagesobj->getAsset('locked-menu-icon.png');
        $item->state = 'active';
        $item->action = 'open-action';
        $item->sync_open = 1;
        $basenumber++;

        $output[] = ArticleMenuComponents::getSingleItemMenu($item,$basenumber,'bookmarks_column_2_off');


        $obj = new StdClass;
        $obj->type = 'msg-plain';
        $obj->style = 'bookmarks_column_3_off';
        $obj->content = '';
        $output[] = $obj;

        return $output;
    }


    public function emptyRows($count,$basenumber){

        $output = array();

            //* text */
            $item = new StdClass;
            $item->id = $basenumber;
            $item->state = 'active';
            $item->action = 'open-action';
            $item->icon = $this->imagesobj->getAsset('heart-green-menu.png');
            $item->text = 'Available slot';
            $item->sync_open = 1;

            $output[] = ArticleMenuComponents::getSingleItemMenu($item, $basenumber, 'bookmarks_column_1_off');

            /* delete button */
            $item = new StdClass;
            $item->id = $basenumber;
            $item->image = $this->imagesobj->getAsset('unlocked3.png');
            $item->state = 'active';
            $item->action = 'open-action';
            $item->sync_open = 1;
            $basenumber++;

            $output[] = ArticleMenuComponents::getSingleItemMenu($item,$basenumber,'bookmarks_column_2_off');


        $obj = new StdClass;
            $obj->type = 'msg-plain';
            $obj->style = 'bookmarks_column_3_off';
            $obj->content = '';
            $output[] = $obj;


        return $output;
    }


    public function getRow($row,$id,$backbutton=0){

        //* text */
        $item = new StdClass;
        $item->id = $id;
        $item->state = 'active';
        $item->action = 'open-action';
        if(isset($row['icon'])){
            $item->icon = $row['icon'];
        } else {
            $item->icon = '';
        }
        $item->action_config = $row['action_id'];
        $item->text = $row['name'];
        $item->sync_open = 1;
        $item->back_button = $backbutton;

        $output[] = ArticleMenuComponents::getSingleItemMenu($item,$id,'bookmarks_column_1');

        /* delete button */
        $item = new StdClass;
        $item->id = $id;
        $item->image = $this->imagesobj->getAsset('remove-2-small.png');
        $item->state = 'active';
        $item->action = 'submit-form-content';

        $output[] = ArticleMenuComponents::getSingleItemMenu($item,$id,'bookmarks_column_2');

        $obj = new StdClass;
        $obj->type = 'msg-plain';
        $obj->style = 'bookmarks_column_3';
        $obj->content = '';
        $output[] = $obj;

        return $output;
    }


    public function getSingleImageMenuItem($id,$style,$image,$action='submit-form-content',$fallbackimage = false,$config=''){

        $image = $this->imagesobj->getAsset($image);

        $item = new StdClass;
        $item->id = $id;
        $item->image = $image;
        $item->state = 'active';
        $item->action = $action;
        $item->action_config = $config;
        $item->sync_open = 1;
        $item->back_button = 1;

        if($fallbackimage){
            $item->image_fallback = $fallbackimage;
        }

        $obj = new StdClass;
        $obj->type = 'menu';

        $obj->menu_content = new StdClass;
        $obj->menu_content->id = $id;
        $obj->menu_content->items[] = $item;

        if(is_object($style)){
            $obj->style_content = $style;
        } else {
            $obj->style = $style;
        }

        return $obj;
    }


    public static function getSingleItemMenu($item,$id,$style){
        $obj = new StdClass;
        $obj->type = 'menu';

        $obj->menu_content = new StdClass;
        $obj->menu_content->id = $id;
        $obj->menu_content->title = 'test';
        $obj->menu_content->slug = 'test';
        $obj->menu_content->items[] = $item;
        $obj->style = $style;
        return $obj;
    }


    public static function getPlainMenu($items){
        /* NOTE: fake id's are built based on the class name and action id */
        $class = get_called_class();
        $basenumber = crc32($class);
        $basenumber = $basenumber .'22222';

        while($row = each($items)){

            if(isset($row[1]['action_id'])){
                $item = new StdClass;
                $item->id = $basenumber;

                if(isset($row[1]['image'])){
                    $item->image = $row[1]['image'];
                } else {
                    $item->image = '';
                }

                $item->image = '';
                $item->state = 'active';
                $item->action = 'open-action';
                $item->action_config = $row[1]['action_id'];
                $item->text = $row[1]['name'];

                if(isset($row[1]['icon'])){
                    $item->icon = $row[1]['icon'];
                } else {
                    $item->icon = '';
                }

                $item->sync_open = 1;
                $item->style = 'bookmarks_menu';
                $basenumber++;
                $output[] = $item;
            }
        }

        if(isset($output)){
            $basenumber = $basenumber*2;
            $obj = new StdClass;
            $obj->type = 'menu';
            $obj->style = 'bookmarks_menu';
            $obj->menu_content = new StdClass;
            $obj->menu_content->id = $basenumber;
            $obj->menu_content->items = $output;
            return $obj;
        } else {
            return false;
        }
    }
    

}