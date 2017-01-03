<?php

Yii::import('application.modules.aelogic.article.components.*');

class ArticleBottommenu extends ArticleComponent {


    public function template(){

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

        foreach($menudata as $menuitem){
            $column[] = $this->getItem($menuitem,$count);
        }

        $column[] = $this->factoryobj->getText($this->factoryobj->bottom_menu_id);
        $row[] = $this->factoryobj->getText('',array('height' => '2', 'background-color' => $this->factoryobj->color_topbar_hilite));
        $row[] = $this->factoryobj->getRow($column,array('background-color' => $this->factoryobj->color_topbar));

        $output[] = $this->factoryobj->getColumn($row);
        return $output;

    }

    public function getItem($item,$count)
    {

        $width = $this->factoryobj->screen_width / $count;
        if ($item['icon']) $row[] = $this->factoryobj->getImage($item['icon'], array('height' => 25, 'margin' => '8 0 5 0'));

        $row[] = $this->factoryobj->getText($item['text'], array('color' => $this->factoryobj->colors['top_bar_text_color'], 'font-size' => '10', 'width' => $width, 'text-align' => 'center',
            'margin' => '0 0 8 0'));

        /* set the menu action */
        $onclick = new stdClass();
        $onclick->action = $item['action'];
        $onclick->action_config = $item['action_config'];
        if ($item['open_popup'] == 1) $onclick->open_popup = 1;
        if ($item['sync_open'] == 1) $onclick->sync_open = 1;
        if ($item['sync_close'] == 1) $onclick->sync_close = 1;

        if ($item['action_config'] == $this->factoryobj->action_id AND $item['action'] == 'open-action') {
            return $this->factoryobj->getColumn($row, array('width' => $width, 'text-align' => 'center', 'background-color' => $this->factoryobj->color_topbar_hilite));
        } elseif($item['action_config'] == $this->factoryobj->branchobj->id AND $item['action'] == 'open-branch'){
            return $this->factoryobj->getColumn($row, array('width' => $width, 'text-align' => 'center', 'background-color' => $this->factoryobj->color_topbar_hilite));
        } else {
            return $this->factoryobj->getColumn($row,array('width' => $width,'text-align' => 'center','onclick' => $onclick));
        }




    }


}