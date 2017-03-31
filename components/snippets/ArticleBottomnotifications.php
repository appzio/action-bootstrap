<?php

Yii::import('application.modules.aelogic.article.components.*');

class ArticleBottomnotifications extends ArticleComponent {


    public function template(){

        if(!$this->factoryobj->getActionidByPermaname('chats')){
            return $this->factoryobj->getText('');
        }

        $action = $this->factoryobj->getActionidByPermaname('chats');

        $onclick = new stdClass();
        $onclick->action = 'open-action';
        $onclick->sync_open = 1;
        $onclick->back_button = 1;
        $onclick->action_config = $action;

        $txtcolor = $this->factoryobj->colors['top_bar_text_color'];
        $bg = $this->factoryobj->color_topbar;
        $count = $this->content;

        $col[] = $this->factoryobj->getText($count,array('background-color' => $txtcolor,'color' => $bg,'padding' => '0 10 0 10','border-radius' => '6','font-size' => '13'));
        $col[] = $this->factoryobj->getText('{#you_have#} ' .$count .' {#new_messages#}',array('color' => $txtcolor,'padding' => '2 4 2 4','font-size' => '13'));

        return $this->factoryobj->getRow($col,array('background-color' => $bg,'padding' => '9 0 9 0','text-align' => 'center','onclick' => $onclick));



        return $this->factoryobj->getText($this->content,array('height' => '40','onclick' => $onclick));

        foreach ($this->factoryobj->menus['menus'] as $menu){
            if($menu['id'] == $this->factoryobj->bottom_menu_id){
                $menudata = $menu['items'];
            }
        }

        if(!isset($menudata)){
            $output[] = $this->factoryobj->getText('Menu not defined correctly',array('text-align' => 'center'));
            return $output;
        }

        $count = count($menudata);
        $counter = 1;

        foreach($menudata as $menuitem){
            /* show flag */
            if($menuitem['slug'] == 'mailbox' AND $this->factoryobj->msgcount){
                $menuitem['flag'] = $this->factoryobj->msgcount;
            }
            $column[] = $this->getItem($menuitem,$count,$counter);
            $counter++;
        }

        if(isset($column)){
            $row[] = $this->factoryobj->getText('',array('height' => '2', 'background-color' => $this->factoryobj->color_topbar_hilite));
            $row[] = $this->factoryobj->getRow($column,array('background-color' => $this->factoryobj->color_topbar));
        } else {
            $row[] = $this->factoryobj->getText('No menu items found',array('height' => '2', 'background-color' => $this->factoryobj->color_topbar_hilite));
        }

        $output[] = $this->factoryobj->getColumn($row,array('height' => '60'));
        return $output;

    }

    public function getItem($item,$count,$current)
    {

        if($current == $count){
            $width = round($this->factoryobj->screen_width / $count,0);
            $others = $width*($count-1);
            $width = $this->factoryobj->screen_width - $others;
        } else {
            $width = round($this->factoryobj->screen_width / $count,0);
        }

        if ($item['icon']) $row[] = $this->factoryobj->getImage($item['icon'], array('height' => 25, 'margin' => '8 0 5 0'));

        $row[] = $this->factoryobj->getText($item['text'], array(
            'color' => $this->factoryobj->colors['top_bar_text_color'], 'font-size' => '10', 'width' => $width, 'text-align' => 'center',
            'margin' => '0 0 8 0'));

        /* set the menu action */
        $onclick = new stdClass();
        $onclick->action = $item['action'];
        $onclick->action_config = $item['action_config'];
        $onclick->transition = 'fade';
        if ($item['open_popup'] == 1) $onclick->open_popup = 1;
        if ($item['sync_open'] == 1) $onclick->sync_open = 1;
        if ($item['sync_close'] == 1) $onclick->sync_close = 1;

        /* add a number flag on the icon */
        if(isset($item['flag']) AND $item['flag']){
            $some[] = $this->factoryobj->getText($item['flag'],array(
                'font-size' => '11','background-color' => '#F80F26','color' => '#ffffff','padding' => '3 6 3 6','border-radius' => '4',
                'border-color' => '#ffffff','text-align' => 'center'
            ));
            $row[] = $this->factoryobj->getColumn($some,array('height' => '21','width' => $width/2,'text-align' => 'right','margin' => '4 0 0 0','floating' => 1,'float' => 'right'));
        }

        if ($item['action_config'] == $this->factoryobj->action_id AND $item['action'] == 'open-action') {
            return $this->factoryobj->getColumn($row, array('width' => $width, 'text-align' => 'center', 'background-color' => $this->factoryobj->color_topbar_hilite,'height' => '60','onclick' => $onclick));
        } elseif($item['action_config'] == $this->factoryobj->branchobj->id AND $item['action'] == 'open-branch'){
            return $this->factoryobj->getColumn($row, array('width' => $width, 'text-align' => 'center', 'background-color' => $this->factoryobj->color_topbar_hilite,'height' => '60','onclick' => $onclick));
        } else {
            return $this->factoryobj->getColumn($row,array('width' => $width,'text-align' => 'center','onclick' => $onclick,'height' => '60'));
        }




    }


}