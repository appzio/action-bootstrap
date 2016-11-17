<?php

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aechat.models.*');

class ArticleGroupchatlist extends ArticleComponent {


    /* prevents from listing chats that are already shown in the view, simple list of id's */
    public $expended_connection_ids;
    public $mode;

    public function template(){

        $this->mode = $this->addParam('mode',$this->options,'mychats');

        if(!is_object($this->factoryobj->mobilechatobj)){
            $this->factoryobj->initMobileChat(false,false);
        }

        if(stristr($this->factoryobj->menuid,'delete_chat_')){
            $id = str_replace('delete_chat_','',$this->factoryobj->menuid);
            Aechat::model()->deleteAllByAttributes(array('id' => $id,'owner_play_id' => $this->playid));
        }

        $matches = $this->factoryobj->mobilechatobj->getGroupChats($this->mode);
        return $this->groupChats($matches);

    }

    public function groupChats($matches){

        if ( empty($matches) ) {
            $othertxt['style'] = 'imate_title_nomatch';
            return $this->factoryobj->getText('{#no_group_chats#}', $othertxt);
        }

        foreach ($matches as $key => $res) {
            $out[] = $this->groupChatItem($res);
            $out[] = $this->factoryobj->getText('',array('margin' => '8 20 4 20','background-color' => '#BABABA','height' => '1','opacity' => '0.4'));
            $this->expended_connection_ids[$res] = true;
            unset($columns);
            unset($s);
        }

        array_pop($out);

        if(isset($out)){
            return $this->factoryobj->getColumn($out);
        }
    }

    

    public function groupChatItem($contextkey){

        $onclick = new StdClass();
        $onclick->action = 'open-action';
        $onclick->id = $contextkey;
        $onclick->back_button = true;
        $onclick->sync_open = true;
        $onclick->viewport = 'bottom';
        $onclick->action_config = $this->factoryobj->requireConfigParam('chat');

        $textparams['style'] = 'imate_title';

        $chatinfo = Aechatusers::getChatInfo($contextkey);
        $chatid = isset($chatinfo[0]['chat_id']) ? $chatinfo[0]['chat_id'] : false;
        $chatname = isset($chatinfo[0]['title']) ? $chatinfo[0]['title'] : false;
        $chatowner = isset($chatinfo[0]['owner_play_id']) ? $chatinfo[0]['owner_play_id'] : false;

        $users = Aechatusers::getChatUserslist($contextkey);
        $names = '';
        $profilepics = array();
        $count = 0;

        foreach ($users as $user){
            $vars = AeplayVariable::getArrayOfPlayvariables($user);
            $name = $this->getFirstName($vars);
            $names .= $name .', ';
            $profilepic = isset($vars['profilepic']) ? $vars['profilepic'] : 'anonymous2.png';
            $profilepics[] = $profilepic;
            $count++;
        }

        if($names){
            $names = substr($names,0,-2);

            if(strlen($names) > 35){
                $names = substr($names,0,32) .'...';
            }
        }

        $imageparams['style'] = 'round_image_imate_stacked';
        $imageparams['imgwidth'] = '250';
        $imageparams['imgheight'] = '250';
        $imageparams['priority'] = 9;
        $piccount=0;

        foreach ($profilepics AS $pic){
            if($piccount < 3 OR count($profilepics) == 3){
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
            $finalrow[] = $this->factoryobj->getVerticalSpacer(83);
        } elseif($piccount == 3) {
            $finalrow[] = $this->factoryobj->getVerticalSpacer(53);
        } else {
            $finalrow[] = $this->factoryobj->getVerticalSpacer(0);
        }


        $row[] = $this->factoryobj->getText($chatname,$textparams);
        $textparams['style'] = 'imate_title_subtext';
        $row[] = $this->factoryobj->getText($names,$textparams);

        $col[] = $this->factoryobj->getColumn($row,array('width' => '180','onclick' => $onclick,'vertical-align' => 'middle'));

        if($chatowner AND $chatowner == $this->playid){
            $add = new stdClass();
            $add->id = 'delete_chat_'.$chatid;
            $add->action = 'submit-form-content';

            $col[] = $this->factoryobj->getImage('threedotmenu-icon.png',array('margin' => '15 10 10 10','height' => '32','opacity' =>'0.5'));
            $swipe[] = $this->factoryobj->getRow($col);
            array_pop($col);
            $col[] = $this->factoryobj->getImage('apple-delete-icon.png',array('margin' => '15 10 10 10','onclick' => $add,'height' => '32'));
            $swipe[] = $this->factoryobj->getRow($col);

            $finalrow[] = $this->factoryobj->getSwipearea($swipe,array('animate' => 1));
        } else {
            $finalrow[] = $this->factoryobj->getRow($col);

        }

        $lastcol[] = $this->factoryobj->getRow($finalrow,array('onclick' => $onclick));
        $lastcol[] = $this->factoryobj->getText('',array('margin' => '8 20 0 20','height' => '1','opacity' => '0.4'));

        return $this->factoryobj->getColumn($lastcol);


    }

    public function getFirstName($vars){
        $name = isset($vars['real_name']) ? $vars['real_name'] : false;
        if($name){
            $firstname = explode(' ', trim($vars['real_name']));
            $firstname = $firstname[0];
            return $firstname;
        } else {
            return false;
        }
    }


}