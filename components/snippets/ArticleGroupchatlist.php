<?php

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aechat.models.*');

class ArticleGroupchatlist extends ArticleComponent {


    /* prevents from listing chats that are already shown in the view, simple list of id's */
    public $expended_connection_ids;
    public $mode;
    public $show_users_count;
    public $show_chat_tags;
    public $separator_styles;
    public $return_array;
    public $allow_delete;
    public $return_data_array;
    public $filter;
    public $filter_distance;

    public function template(){

        $this->mode = $this->addParam('mode', $this->options, 'mychats');
        $this->show_users_count = $this->addParam('show_users_count', $this->options, false);
        $this->show_chat_tags = $this->addParam('show_chat_tags', $this->options, false);
        $this->return_array = $this->addParam('return_array', $this->options, false);
        $this->allow_delete = $this->addParam('allow_delete', $this->options, true);

        /* this will return extra data you can use in view to do filtering*/
        $this->filter = $this->addParam('filter', $this->options, false);
        $this->filter_distance = $this->addParam('filter_distance', $this->options, false);

        $this->separator_styles = $this->addParam('separator_styles', $this->options, array(
            'margin' => '8 20 4 20',
            'background-color' => '#BABABA',
            'height' => '1',
            'opacity' => '0.4'
        ));

        if(!is_object($this->factoryobj->mobilechatobj)){
            $this->factoryobj->initMobileChat(false,false);
        }

        if(stristr($this->factoryobj->menuid,'delete_chat_')){
            $id = str_replace('delete_chat_','',$this->factoryobj->menuid);
            Aechat::model()->deleteAllByAttributes(array('id' => $id,'owner_play_id' => $this->playid));
        }

        $matches = $this->factoryobj->mobilechatobj->getGroupChats($this->mode,$this->filter);

        return $this->groupChats($matches);
    }

    public function groupChats($matches){

        if ( empty($matches) ) {
            $othertxt['style'] = 'chat-heading-no-chats';
            return $this->factoryobj->getText('{#no_group_chats#}', $othertxt);
        }

        foreach ($matches as $res) {
            $out[] = $this->groupChatItem($res);
            $out[] = $this->factoryobj->getText('', $this->separator_styles);
            $this->expended_connection_ids[$res] = true;
        }

        array_pop($out);

        if(isset($out)){
            if($this->return_array){
                return $out;
            } else {
                return $this->factoryobj->getColumn($out);
            }
        }
    }

    public function groupChatItem($contextkey){

        $onclick = new StdClass();
        $onclick->action = 'open-action';
        $onclick->id = $contextkey;
        $onclick->back_button = true;
        $onclick->sync_open = true;
        $onclick->viewport = 'bottom';
        // $onclick->action_config = $this->factoryobj->requireConfigParam('chat');
        $onclick->action_config = $this->factoryobj->getConfigParam('chat');

        $chatinfo = Aechatusers::getChatInfo($contextkey);
        $chatid = isset($chatinfo[0]['chat_id']) ? $chatinfo[0]['chat_id'] : false;
        $chatname = isset($chatinfo[0]['title']) ? $chatinfo[0]['title'] : false;
        $chatowner = isset($chatinfo[0]['owner_play_id']) ? $chatinfo[0]['owner_play_id'] : false;

        $users = Aechatusers::getChatUserslist($contextkey);

        $names = '';
        $profilepics = array();

        foreach ($users as $user){
            $vars = AeplayVariable::getArrayOfPlayvariables($user);
            $name = $this->getFirstName($vars);
            $names .= $name .', ';
            $profilepic = isset($vars['profilepic']) ? $vars['profilepic'] : 'anonymous2.png';
            $profilepics[] = $profilepic;
        }

        $names = $this->getFormattedNames( $names );

        $imageparams['style'] = 'round_image_imate_stacked';
        $imageparams['imgwidth'] = '250';
        $imageparams['imgheight'] = '250';
        $imageparams['priority'] = 9;
        $piccount = 0;

        foreach ($profilepics AS $pic){
            if($piccount < 2 OR count($profilepics) == 3){
                $finalrow[] = $this->factoryobj->getImage($pic,$imageparams);
            }
            $piccount++;
        }

        $left = count($profilepics)-2;

        if($left > 1){
            $imageparams['style'] = 'round_image_stacked_text';
            $finalrow[] = $this->factoryobj->getText('+'.$left,$imageparams);
        }

        if($piccount == 1){
            $finalrow[] = $this->factoryobj->getVerticalSpacer(116);
        }elseif($piccount == 2){
            $finalrow[] = $this->factoryobj->getVerticalSpacer(86);
        } elseif($piccount == 3) {
            $finalrow[] = $this->factoryobj->getVerticalSpacer(60);
        } else {
            $finalrow[] = $this->factoryobj->getVerticalSpacer(60);
        }

        $total_users = count( $users );
        $participants = ( $total_users > 1 ? '{#participants#}' : '{#participant#}' );

        $row[] = $this->factoryobj->getText($chatname, array( 'style' => 'imate_title' ));

        if ( $this->show_users_count ) {
            $row[] = $this->factoryobj->getText($total_users . ' ' . $participants, array( 'style' => 'imate_title_subtext' ));
        }

        if ( $this->show_chat_tags ) {
            $tags = $this->getFormattedTags( $chatinfo );
            if ( $tags ) {
                $row[] = $this->factoryobj->getText($tags, array( 'style' => 'imate_title_subtext_bigger' ));
            }
        } else {
            $row[] = $this->factoryobj->getText($names, array( 'style' => 'imate_title_subtext' ));
        }

        $col[] = $this->factoryobj->getColumn($row,array('width' => '180','onclick' => $onclick,'vertical-align' => 'middle'));

        if($chatowner AND $chatowner == $this->playid AND $this->allow_delete){
            $add = new stdClass();
            $add->id = 'delete_chat_'.$chatid;
            $add->action = 'submit-form-content';

            $col[] = $this->factoryobj->getImage('threedotmenu-icon.png',array('margin' => '15 10 10 10','height' => '32','opacity' =>'0.5'));
            $swipe[] = $this->factoryobj->getRow($col, array( 'vertical-align' => 'middle' ));
            array_pop($col);
            $col[] = $this->factoryobj->getImage('apple-delete-icon.png',array('margin' => '15 10 10 10','onclick' => $add,'height' => '32'));
            $swipe[] = $this->factoryobj->getRow($col, array( 'vertical-align' => 'middle' ));

            $finalrow[] = $this->factoryobj->getSwipearea($swipe,array('animate' => 1));
        } else {
            $finalrow[] = $this->factoryobj->getColumn($col);
        }

        $lastcol[] = $this->factoryobj->getRow($finalrow, array('onclick' => $onclick));
        $lastcol[] = $this->factoryobj->getText('',array('margin' => '8 20 0 20','height' => '1','opacity' => '0.4'));

        return $this->factoryobj->getRow( $lastcol );
    }

    public function getFormattedNames( $names ) {
        if ( empty($names) ) {
            return '';
        }

        $names = substr($names,0,-2);

        if(strlen($names) > 35){
            $names = substr($names,0,32) .'...';
        }

        return $names;
    }

    public function getFormattedTags( $chatinfo ) {
        if ( empty($chatinfo[0]['tags']) ) {
            return '';
        }

        $active_tags = '';
        $tags = json_decode( $chatinfo[0]['tags'] );

        foreach ($tags as $tag => $tag_is_active) {
            if ( !$tag_is_active ) {
                continue;
            }

            $tag = str_replace('_', ' ', $tag);
            
            $active_tags .= ucfirst($tag) . ' ';
        }

        $active_tags = substr($active_tags, 0, -2);

        if(strlen($active_tags) > 31){
            $active_tags = substr($active_tags, 0, 28) . '...';
        }

        return $active_tags;
    }

    public function getFirstName($vars){
        $name = isset($vars['real_name']) ? $vars['real_name'] : false;

        if ( empty($name) ) {
            return false;
        }

        $firstname = explode(' ', trim($vars['real_name']));
        $firstname = $firstname[0];
        return $firstname;
    }

}