<?php

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aechat.models.*');

class ArticleChat extends ArticleComponent {

    // Local Vars
    public $submitvariables;
    public $configobj;
    public $imagesobj;
    public $vars;
    public $varcontent;

    public $actionobj;

    public $msgadded = false;
    public $submit;

    public $playobj;
    public $userid;

    public $chat_content;

    public $custom_play_id;
    public $otheruser;
    public $save_match;
    public $notify;
    public $use_server_time;
    
    public $pic_permission;
    public $hide_pic_button;
    public $strip_urls;

    public $required_params = array( 'context', 'context_key' );

    public $context;
    public $context_key;
    public $disable_header = false;

    public $other_user_play_id;

    public $chatid;

    public $limit_monologue;
    public $disable_chat = false;

    public $can_invite_others;
    public $hide_time;

    public $name_mode;
    public $chat_id;
    public $chat_info;

    /* for group chats */
    public $userlist;

    public $total_messages;
    public $top_button;

    public $name_suffix;

    private $current_msg;
    private $current_user_unmatched;

    protected function requiredOptions() {
        return array();
    }

    public function array_flatten($arrays) { 

        $result = array(); 

        foreach ($arrays as $array) {
            foreach ($array as $arr) {
                $result[] = $arr;
            }
        }

        return $result; 
    }

    public function template() {
        
        $this->factoryobj->rewriteActionField( 'keep_scroll_in_bottom', 1 );
        $this->factoryobj->rewriteActionField( 'poll_update_view', 'all' );

        // Init the Chat based on the currently requested context
        $this->custom_play_id = isset($this->options['custom_play_id']) ? $this->options['custom_play_id'] : $this->playid;

        $this->otheruser = $this->addParam('otheruser',$this->options,false);
        $this->context = $this->addParam('context',$this->options,false);
        $this->context_key = $this->addParam('context_key',$this->options,false);
        $this->limit_monologue = $this->addParam('limit_monologue',$this->options,false);
        $this->disable_header = $this->addParam('disable_header',$this->options,false);
        $this->can_invite_others = $this->addParam('can_invite_others',$this->options,false);
        $this->userlist = $this->addParam('userlist',$this->options,false);
        $this->top_button = $this->addParam('top_button',$this->options,false);

        // App specific settings
        $this->save_match = $this->addParam('save_match',$this->options,false);
        $firstname_only = $this->addParam('firstname_only',$this->options,false);
        $this->hide_time = $this->addParam('hide_time',$this->options,false);
        $this->notify = $this->addParam('notify',$this->options,false);
        $this->use_server_time = $this->addParam('use_server_time',$this->options,false);

        $this->pic_permission = $this->addParam('pic_permission',$this->options,false);
        $this->hide_pic_button = $this->addParam('hide_pic_button',$this->options,false);
        $this->strip_urls = $this->addParam('strip_urls',$this->options,false);
        $this->chat_id = $this->addParam('chat_id',$this->options,false);
        $this->current_user_unmatched = $this->addParam('current_user_unmatched',$this->options,false);

        $this->name_suffix = $this->addParam('name_suffix',$this->options,false);

        if($this->factoryobj->getConfigParam('name_mode')){
            $this->name_mode = $this->factoryobj->getConfigParam('name_mode');
        } elseif($firstname_only){
            $this->name_mode = 'firstname';
        } else {
            $this->name_mode = 'default';
        }

        // Add the user to the chat on demand
        if ( $this->factoryobj->menuid == 'join_chat' ) {
            $this->factoryobj->initMobileChat( $this->context, $this->context_key );
            $this->userlist = Aechatusers::getChatUserslist( $this->context_key );
            Appcaching::removeGlobalCache( 'chatheader-' . $this->chat_id );
        } else {
            $this->factoryobj->initMobileChat( $this->context, $this->context_key, false, $this->chat_id );
        }

        // Remove the user from this chat
        if ( $this->factoryobj->menuid == 'leave-chat' ) {
            Aechatusers::removeUser( $this->chat_id, $this->playid );
            $this->userlist = Aechatusers::getChatUserslist( $this->context_key );
            Appcaching::removeGlobalCache( 'chatheader-' . $this->chat_id );
        }

        if($this->factoryobj->mobilechatobj->error_state == true){
            $this->factoryobj->mobilechatobj->addChat($this->context,$this->context_key,$this->otheruser,'fromarticle');
        }

        /* we look for the user's playid using the chat id */
        $otheruser = explode('-chat-',$this->context_key);

        if(count($otheruser) == 2){
            foreach($otheruser as $user){
                if($user != $this->playid){
                    if(is_numeric($user)){
                        $this->other_user_play_id = $user;
                    }
                }
            }
        }

        $this->chat_info = $this->factoryobj->mobilechatobj->getChat( $this->chat_id );
        
        $this->saveChatMsg();

        $page = ( $this->factoryobj->getVariable( 'tmp_chat_page' ) ? $this->factoryobj->getVariable( 'tmp_chat_page' ) : 1 );

        if ( $this->factoryobj->menuid == 'get-next-page' ) {
            $page = $page + 1;
            $this->factoryobj->saveVariable( 'tmp_chat_page', $page );
        }

        /*
        if ( isset($this->submit['next_page_id']) ) {
            $page = $this->submit['next_page_id'];
        }

        $num_rec_per_page = 5;
        $start_from = ($page-1) * $num_rec_per_page;

        $content = $this->factoryobj->mobilechatobj->getChatContent( $start_from, $num_rec_per_page );
        */

        $content = $this->factoryobj->mobilechatobj->getChatContent();
        $this->total_messages = count( $content );

        $content = array_chunk($content, 15);

        $offset = '-' . $page;
        $length = $page;
        $content = array_slice($content, $offset, $length);

        // revamp the content
        $content = $this->array_flatten( $content );

        $this->disableChat($content);


        if ( !empty($content) ) {
            $this->chat_content['msgs'] = $content;
        }

        $data = new StdClass();
        $this->chatid = $this->factoryobj->mobilechatobj->getChatId();

        /* header */
        if ( $this->pic_permission ) {
            $data->header[] = $this->handlePicPermission();
        }

        /* NOTE: header might also return false */
        $headerdata = $this->getPersonInfo();

        if($headerdata){
            $data->header[] = $headerdata;
            $data->header[] = $this->factoryobj->getImage( 'chat-heading-line.png', array(
                'imgwidth' => '1440',
                'width' => '100%',
            ));
        }

        $storage = new AeplayKeyvaluestorage();
        $storage->play_id = $this->playid;
        $matches = $storage->valueExists('two-way-matches',$this->other_user_play_id);
        
        // Look whether the chat is disabled for a certain player
        $chat_flag = $storage->findByAttributes(array(
            'play_id' => $this->playid,
            'key' => 'chat-flag',
        ));

        if ( !empty($chat_flag) AND $chat_flag->value == '1' AND !$matches ) {
            $data->scroll = $this->getChatError();
        } else {
            $data->scroll = $this->getChat();

            if ( !$this->current_user_unmatched ) {
                $data->footer = $this->factoryobj->getFooter(array(
                    'chatid' => $this->chatid,
                    'chat_info' => $this->chat_info,
                    'disable_chat' => $this->disable_chat,
                    'other_user_play_id' => $this->other_user_play_id,
                    'hide_pic_button' => $this->hide_pic_button,
                    'pic_permission' => $this->pic_permission,
                    'userlist' => $this->userlist,
                ));
            }

        }

        $this->factoryobj->initMobileMatching( $this->other_user_play_id, true );

        return $data;
    }

    public function disableChat($content){

        if(isset($this->chat_info->blocked) AND $this->chat_info->blocked == 1){
            $this->disable_chat = true;
        }

        if($this->limit_monologue){
            $reverse = array_reverse($content);
            $count = 1;
            $totalcount = 0;

            foreach ($reverse AS $item){
                if($totalcount > $this->limit_monologue){
                    break;
                }

                if($item['user'] == $this->playid){
                    $count++;
                } else {
                    $count = 0;
                }
            }

            if($count > $this->limit_monologue){
                $this->disable_chat = true;
            }
        }
    }

    public function getChatError() {
        $output = array();

        $output[] = $this->factoryobj->getText('Your plan has ended!', array(
            'padding' => '20 20 20 20',
            'font-size' => '18',
            'text-align' => 'center',
            'color' => '#ffffff',
        ));

        return $output;
    }

    public function getChat() {

        $this->markMsgsAsRead();

        $items = $this->renderChatMsgs();

        // Still some work to be done here ..
        $next_page_id = 2;

        if ( isset($this->submit['next_page_id']) ) {
            $next_page_id = $this->submit['next_page_id'] + 1;
        }
        $output[] = $this->factoryobj->getInfinitescroll( $items, array( 'next_page_id' => $next_page_id ) );

        $output = array();

        $output[] = $this->factoryobj->getSpacer( 15 );

        foreach ($items as $item) {
            $output[] = $item;
        }

        $output[] = $this->factoryobj->getSpacer( 10 );

        if ( $this->current_user_unmatched ) {
            $userinfo = $this->getUserInfo();
            $output[] = $this->factoryobj->getText($userinfo['name'] . ' {#unmatched_you#}', array(
                'padding' => '10 0 20 0',
                'font-size' => '16',
                'text-align' => 'center',
                'color' => '#808080',
            ));
        }

        return $output;
    }

    public function getPersonInfo(){

        if($this->userlist){
            return $this->getGroupChatHeader($this->userlist);
        }

        $userinfo = $this->getUserInfo();
        $id = $this->other_user_play_id;

        $name = ucfirst($userinfo['name']);
        $profilepic = $userinfo['profilepic'];
        $vars = $userinfo['vars'];

        if($this->disable_header){
            $this->factoryobj->rewriteActionConfigField('subject',$name);
            return false;
        } else {
            $string = $this->factoryobj->localizationComponent->smartLocalize('{#chat_with#}');
            $this->factoryobj->rewriteActionField('subject', $string . ' ' . $name);
        }

        $name = isset($vars['city']) ? $name . ', ' . $vars['city'] : $name;

        $imageparams['crop'] = 'round';
        $imageparams['width'] = '40';
        $imageparams['margin'] = '0 10 0 0';
        $imageparams['priority'] = 9;
        $imageparams['imgwidth'] = 250;
        $imageparams['imgheight'] = 250;
        $imageparams['onclick'] = new StdClass();
        $imageparams['onclick']->action = 'open-action';
        $imageparams['onclick']->id = $id;
        $imageparams['onclick']->back_button = true;
        $imageparams['onclick']->sync_open = true;
        $imageparams['onclick']->action_config = $this->factoryobj->getConfigParam('detail_view');

        if ( $this->current_user_unmatched ) {
            $imageparams['blur'] = '1';
        }

        if(isset($vars['private_photos']) AND $vars['private_photos']){
            $test = AeplayKeyvaluestorage::model()->findByAttributes(array('play_id' => $id, 'key' => 'two-way-matches','value' => $this->playid));

            if(!is_object($test)){
                $profilepic = 'sila-private-photos.png';
            }
        }

        $columns[] = $this->factoryobj->getImage($profilepic, $imageparams);
        $columns[] = $this->factoryobj->getText($name . $this->name_suffix, array(
            'color' => $this->factoryobj->colors['top_bar_text_color'],
            'font-size' => 15,
            'text-align' => 'left',
        ));

        if($this->can_invite_others == true){
            $columns[] = $this->factoryobj->getImage('add-user-group.png',array('margin' => '8 14 8 8','onclick' => $this->factoryobj->getOnclick('tab2', true)));
        }

        if($this->top_button){
            $btn[] = $this->top_button;
            $columns[] = $this->factoryobj->getColumn($btn, array(
                'padding' => '0 10 0 0',
                'text-align' => 'right',
                'float' => 'right',
                'floating' => 1,
                'vertical-align' => 'middle',
                'width' => '32%'
            ));
        }

        return $this->factoryobj->getRow($columns, array(
            'padding' => ( $this->factoryobj->getConfigParam('hide_menubar') ? '10 0 10 15' : '0 0 5 15' ),
            'vertical-align' => 'middle',
            'height' => '60',
            'background-color' => $this->factoryobj->color_topbar,
        ));
    }

    private function getGroupChatHeader($users){
        $name = isset($this->chat_info->title) ? $this->chat_info->title : '{#untitled#}';
        $this->factoryobj->rewriteActionField('subject',$this->factoryobj->localizationComponent->smartLocalize($name));

        if($this->disable_header){
            return false;
        }

        $cache = Appcaching::getGlobalCache('chatheader-'.$this->chat_id);
        //$cache = false;

        if($cache){
            return $cache;
        }

        $names = '';
        $profilepics = array();

        foreach ($users as $user){
            $userinfo = $this->getUserInfo($user);
            $names .= $userinfo['name'] .', ';
            $profilepics[] = $userinfo['profilepic'];
        }

        if($names){
            $names = mb_substr($names,0,-2);

            if(strlen($names) > 35){
                $names = mb_substr($names,0,32) .'...';
            }
        }

        $imageparams['style'] = 'round_image_imate_stacked';
        $imageparams['imgwidth'] = '250';
        $imageparams['imgheight'] = '250';
        $imageparams['priority'] = 9;
        $piccount=1;

        foreach ($profilepics AS $pic){
            if($piccount < 3 OR count($profilepics) == 3){
                $col[] = $this->factoryobj->getImage($pic,$imageparams);
            }
            $piccount++;
        }

        $left = count($profilepics)-2;

        if($left > 1){
            $imageparams['style'] = 'round_image_stacked_text';
            $col[] = $this->factoryobj->getText('+'.$left,$imageparams);
        }


        $col[] = $this->factoryobj->getVerticalSpacer(30);

        $subtext['color'] = '#ffffff';
        $subtext['font-size'] = '12';

        $rowparams['padding'] = '0 0 5 13';
        $rowparams['height'] = '80';
        $rowparams['vertical-align'] = 'middle';
        $rowparams['background-color'] = $this->factoryobj->color_topbar;
        //$rowparams['onclick'] = $this->factoryobj->getOnclick('tab2', true);
        $rowparams['width'] = '100%';

        $onclick_leave = new StdClass();
        $onclick_leave->id = 'leave-chat';
        $onclick_leave->action = 'submit-form-content';

        if ( $this->chat_info->owner_play_id != $this->playid AND Aechatusers::checkUser( $this->chat_id, $this->playid ) ) {
            $col[] = $this->factoryobj->getImage('exit-round.png', array( 'opacity' => '0.8', 'height' => '50','margin' => '10 70 0 0','floating'=>'1','float' => 'right', 'onclick' => $onclick_leave ));
            $col[] = $this->factoryobj->getImage('adduser-round.png', array( 'opacity' => '0.8', 'height' => '50','margin' => '10 10 0 0','floating'=>'1','float' => 'right', 'onclick' => $this->factoryobj->getOnclick('tab2',true) ));
        } elseif($this->chat_info->owner_play_id == $this->playid) {
            $col[] = $this->factoryobj->getImage('adduser-round.png', array( 'opacity' => '0.8', 'height' => '50','margin' => '10 10 0 0','floating'=>'1','float' => 'right', 'onclick' => $this->factoryobj->getOnclick('tab2',true) ));
        }

        $ret = $this->factoryobj->getRow($col,$rowparams);

        Appcaching::setGlobalCache('chatheader-'.$this->chat_id,$ret,600);
        return $ret;
    }

    private function renderChatMsgs() {

        $output = array();

        if ( !isset($this->chat_content['msgs']) OR empty($this->chat_content['msgs']) OR !$this->chat_content['msgs'] ) {
            $output[] = $this->factoryobj->getText( '{#no_comments_yet#}', array( 'style' => 'chat-no-comments' ) );
            return $output;
        }

        $onclick = new StdClass();
        $onclick->id = 'get-next-page';
        $onclick->action = 'submit-form-content';
        $onclick->viewport = 'bottom';

        // $msgs = (object) array_reverse( $this->chat_content['msgs'] );
        $msgs = (object) $this->chat_content['msgs'];
        $count = count( $this->chat_content['msgs'] );

        if ( $count < $this->total_messages ) {
            $output[] = $this->factoryobj->getText( '{#load_more#}', array( 'style' => 'load-more-btn', 'onclick' => $onclick ) );
        }

        foreach ($msgs as $i => $msg) {
            $this->current_msg = $msg;

            if ( !isset($this->current_msg['user']) OR !$this->current_msg['user']){
                continue;
            }

            $userinfo = $this->getUserInfo( $this->current_msg['user'] );

            if ( $this->msgadded === true) {
                $this->msgadded = false;
            }

            $output[] = $this->factoryobj->getChatMessage(array(
                'current_msg' => $this->current_msg,
                'userinfo' => $userinfo,
                'user_is_owner' => $this->userIsOwner(),
                'hide_time' => $this->hide_time,
                'context_key' => $this->context_key,
                'current_msg_index' => $i,
                'total_msgs' => $count,
            ));

        }

        return $output;
    }

    private function userIsOwner( $message = false ) {

        if ( empty($message) ) {
            $message = $this->current_msg;
        }

        if ( is_array($message) ) {
            $message = (object) $message;
        }

        if ( $message->user == $this->playid ) {
            return true;
        }

        return false;
    }

    private function getUserInfo( $id = null ) {

        if ( empty($id) ) {
            $id = $this->other_user_play_id;
        }
        
        $profilepic = 'anonymous2.png';

        $cachename = 'uservars-' . $id;
        $vars = Appcaching::getGlobalCache($cachename);
        
        if ( !$vars ) {
            $vars = AeplayVariable::getArrayOfPlayvariables($id);
            Appcaching::setGlobalCache($cachename,$vars,120);
        }

        switch($this->name_mode){
            case 'invisible';
                $name = '';
                break;

            case 'nickname';
                $name = isset($vars['screen_name']) ? $vars['screen_name'] : $this->factoryobj->localizationComponent->smartLocalize('{#anonymous#}');
                break;

            case 'firstname';
            case 'first_name';

                if ( isset($vars['first_name']) AND !empty($vars['first_name']) ) {
                    $name = $vars['first_name'];
                } else if ( isset($vars['real_name']) AND !empty($vars['real_name']) ) {
                    $name = $this->getChatName($vars['real_name']);
                } else {
                    $name = '{#anonymous#}';
                }

                break;

            case 'last_name';
                $name = isset($vars['real_name']) ? $this->getChatName($vars['real_name'], 'last') : '{#anonymous#}';
                break;

            case 'company':
                $name = isset($vars['company']) ? $vars['company'] : $this->getFullname( $vars );
                break;

            default:
                $name = $this->getFullname( $vars );
                break;
        }

        $profilepic = isset($vars['profilepic']) ? $vars['profilepic'] : $profilepic;

        if(isset($vars['private_photos']) AND $vars['private_photos']){
            $test = AeplayKeyvaluestorage::model()->findByAttributes(array('play_id' => $id, 'key' => 'two-way-matches','value' => $this->playid));

            if(!is_object($test)){
                $profilepic = 'sila-private-photos.png';
            }
        }
        
        return array(
            'profilepic' => $profilepic,
            'name'       => $name,
            'vars'       => $vars
        );
    }

    private function getFullname( $vars ) {
        
        if ( isset($vars['real_name']) AND $vars['real_name'] ) {
            $name = $vars['real_name'];
        } else if (  isset($vars['name']) AND $vars['name'] ) {
            $name = $vars['name'];
        } else {
            $name = '{#anonymous#}';
        }

        return $name;
    }

    private function saveChatMsg(){

        if ( !isset($this->factoryobj->menuid) OR $this->factoryobj->menuid != 'submit-msg' ) {
            return false;
        }

        if ( !$this->getUsername() ){
            return false;
        }

        if(isset($this->chat_info->blocked) AND $this->chat_info->blocked == 1){
            return false;
        }

        $msg = $this->submitvariables;

        $var = AeplayVariable::getArrayOfPlayvariables($this->playid);

        // Do nothing if both Image and Message are empty
        if (
            ( !isset($var['chat_upload_temp']) AND empty($var['chat_upload_temp']) ) AND
            empty($msg['66666660'])
        ) {
            return false;
        }

        $username = $this->getUsername();
        $pic = 'anonymous.png';

        if ( $this->strip_urls AND $msg ){
            $msg = $this->stripUrls($msg);
        }

        if (isset($this->varcontent['profilepic'])) {
            $pic = $this->varcontent['profilepic'];
        }

        $new['name'] = $username;
        $new['date'] = ( $this->use_server_time ? time() : Helper::getCurrentTime() );
        $new['profilepic'] = $this->factoryobj->getImageFileName($pic);
        $new['msg'] = $msg['66666660'];
        $new['user'] = $this->playid;

        if ( isset($var['chat_upload_temp']) AND $var['chat_upload_temp'] ) {
            $new['attachment'] = $var['chat_upload_temp'];
            AeplayVariable::deleteWithName($this->custom_play_id,'chat_upload_temp',$this->gid);
        }

        $this->chat_content['msgs'][] = $new;
        $this->saveData();
        return $this->chat_content;
    }

    public function saveData(){
        $this->factoryobj->initMobileMatching( $this->other_user_play_id );

        $msg = end($this->chat_content['msgs']);
        $message_text = $msg['msg'];

        $current_time = ( $this->use_server_time ? time() : Helper::getCurrentTime() );
        $time = date('Y-m-d H:i:s', $current_time);

        $message_id = $this->factoryobj->mobilechatobj->addMessage( $message_text, $time );

        if ( isset($msg['attachment']) and !empty($msg['attachment']) ) {
            $this->factoryobj->mobilechatobj->addAttachment( $message_id, $msg['attachment'] );
        }

        if ( $this->other_user_play_id ) {

            $blocked = isset($this->chat_info->blocked) ? $this->chat_info->blocked : 0;

            if(!$blocked){
                if($this->factoryobj->getConfigParam('save_match_when_chatting')){
                    $this->factoryobj->mobilematchingobj->saveMatch();
                }

                $notify = AeplayVariable::fetchWithName($this->other_user_play_id, 'notify', $this->gid);

                if ( $notify ) {
                    $notification_text = $this->getChatName($msg['name']) . ': ' . $message_text;
                    $title = $this->factoryobj->localizationComponent->smartLocalize('{#message_from#} ') . $this->getChatName($msg['name']);
                    Aenotification::addUserNotification( $this->other_user_play_id, $title, $notification_text, 1, $this->gid );
                }

                $this->factoryobj->mobilematchingobj->addNotificationToBanner('msg');
            }
        }

    }

    public function getChatName( $name, $type = 'first' ){
        if ( empty($name) ) {
            return '{#anonymous#}';
        }

        if ( !strstr($name, ' ') ) {
            return $name;
        }

        $name_pieces = explode(' ', trim($name));

        if ( $type == 'first' ) {
            return $name_pieces[0];
        } else if ( $type == 'last' AND isset($name_pieces[1]) ) {
            return $name_pieces[1];
        }    
    }

    private function handlePicPermission(){
        $pointer_me = $this->factoryobj->mobilechatobj->context_key .'-'.$this->playid;


        if(isset($this->factoryobj->menuid) AND $this->factoryobj->menuid == 'pic_permission'){
            $this->factoryobj->appkeyvaluestorage->set($pointer_me,1);
        }

        $other = $this->factoryobj->appkeyvaluestorage->get($pointer_me);

        if(!$other){
            return $this->factoryobj->getTextbutton('{#this_user_can_send_me_pictures#}',array('id' => 'pic_permission','small_text' => true,'icon' => 'icon-cam-ok.png'));
        }
    }

    public function markMsgsAsRead() {
        
        if ( empty($this->chat_content['msgs']) OR !isset($this->submit['poll']) OR !isset($this->submit['actionid']) ) {
            return false;
        }

        $play_action_obj = AeplayAction::model()->findByPk( $this->submit['actionid'] );

        if ( empty($play_action_obj) ) {
            return false;
        }

        $db_chat_action_id = $play_action_obj['action_id'];
        $chat_action_id = $this->factoryobj->getActionidByPermaname( 'chat' );

        // Check if this is a "chat poll" request
        if ( $db_chat_action_id != $chat_action_id ) {
            return false;
        }

        foreach ($this->chat_content['msgs'] as $message) {
            
            if ( $this->userIsOwner( $message ) ) {
                continue;
            }

            // Do not update the message
            if ( !isset($message['msg_is_read']) OR $message['msg_is_read'] == 1 ) {
                continue;
            }

            $this->factoryobj->mobilechatobj->updateMessageStatus( $message['id'] );
        }

    }

    private function stripUrls($msg){
        $msg = preg_replace('|https?://www\.[a-z\.0-9]+|i', '{#url_removed#}', $msg);
        $msg = preg_replace('|http?://www\.[a-z\.0-9]+|i', '{#url_removed#}', $msg);
        $msg = preg_replace('|http?://[a-z\.0-9]+|i', '{#url_removed#}', $msg);
        $msg = preg_replace('|https?://[a-z\.0-9]+|i', '{#url_removed#}', $msg);
        $msg = preg_replace('|www\.[a-z\.0-9]+|i', '{#url_removed#}', $msg);
        return $msg;
    }

    public function getUsername() {

        $options = array(
            'real_name', 'name', 'screen_name', 'surname'
        );

        foreach ($options as $option) {
            if ( isset($this->varcontent[$option]) AND !empty($this->varcontent[$option]) ) {
                return $this->varcontent[$option];
            }
        }

        return false;
    }

}