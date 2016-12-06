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
    
    public $pic_permission;
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

    /* for group chats */
    public $userlist;

    public $total_messages;

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
        $this->factoryobj->rewriteActionField( 'poll_update_view', 'scroll' );

        // Init the Chat based on the currently requested context
        $this->custom_play_id = isset($this->options['custom_play_id']) ? $this->options['custom_play_id'] : $this->playid;

        $this->otheruser = $this->addParam('otheruser',$this->options,false);
        $this->context = $this->addParam('context',$this->options,false);
        $this->context_key = $this->addParam('context_key',$this->options,false);
        $this->limit_monologue = $this->addParam('limit_monologue',$this->options,false);
        $this->disable_header = $this->addParam('disable_header',$this->options,false);
        $this->can_invite_others = $this->addParam('can_invite_others',$this->options,false);
        $this->userlist = $this->addParam('userlist',$this->options,false);

        // App specific settings
        $this->save_match = $this->addParam('save_match',$this->options,false);
        $firstname_only = $this->addParam('firstname_only',$this->options,false);
        $this->hide_time = $this->addParam('hide_time',$this->options,false);
        $this->notify = $this->addParam('notify',$this->options,false);

        $this->pic_permission = $this->addParam('pic_permission',$this->options,false);
        $this->strip_urls = $this->addParam('strip_urls',$this->options,false);

        if($this->factoryobj->getConfigParam('name_mode')){
            $this->name_mode = $this->factoryobj->getConfigParam('name_mode');
        } elseif($firstname_only){
            $this->name_mode = 'firstname';
        } else {
            $this->name_mode = 'default';
        }

        $this->factoryobj->initMobileChat( $this->context, $this->context_key );

        if($this->factoryobj->mobilechatobj->error_state == true){
            $this->factoryobj->mobilechatobj->addChat($this->context,$this->context_key,$this->otheruser);

            /* only one-on-one chats should be created automatically if they are missing */
            /*
                if(strstr($this->context_key,'-chat-')){
                    $this->factoryobj->mobilechatobj->addChat($this->context,$this->context_key,$this->otheruser);
                    if($this->factoryobj->mobilechatobj->error_state == true){
                        return false;
                    }
                } else {
                    return false;
                }
            */
        }

        /* we look for the user's playid using from the chat id */
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

        $object = new StdClass();
        $this->chatid = $this->factoryobj->mobilechatobj->getChatId();

        /* header */
        if ( !$this->disable_header ) {
            if ( $this->pic_permission ) {
                $object->header[] = $this->handlePicPermission();
            }

            $object->header[] = $this->getMyMatchItem( $this->other_user_play_id );
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
            // $complete = new StdClass();
            // $complete->action = 'list-branches';
            // $this->data->onload[] = $complete;
            $object->scroll = $this->getChatError();
        } else {
            $object->scroll = $this->getChat();
            $object->footer = $this->getFooter();
        }

        $this->factoryobj->initMobileMatching( $this->other_user_play_id,true );
        return $object;
    }

    public function disableChat($content){
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

        return $output;
    }

    public function getMyMatchItem( $id ){

        if($this->userlist){
            return $this->getGroupChatHeader($this->userlist);
        }

        $userinfo = $this->getUserInfo($id);
        $name = $userinfo['name'];
        $profilepic = $userinfo['profilepic'];
        $vars = $userinfo['vars'];

        if($this->disable_header){
            $this->factoryobj->rewriteActionConfigField('subject',$name);
        } else {
            $string = $this->factoryobj->localizationComponent->smartLocalize('{#chat_with#}');
            $this->factoryobj->rewriteActionField('subject',$string.' ' .$name);
        }

        $name = isset($vars['city']) ? $name.', '.$vars['city'] : $name;

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

        $rowparams['padding'] = '0 0 5 15';
        $rowparams['height'] = '50';
        $rowparams['vertical-align'] = 'middle';
        $rowparams['background-color'] = $this->factoryobj->color_topbar;

        $textparams['color'] = $this->factoryobj->colors['top_bar_text_color'];
        $textparams['font-size'] = 15;
        $textparams['text-align'] = 'left';

        $columns[] = $this->factoryobj->getImage($profilepic, $imageparams);
        $columns[] = $this->factoryobj->getText($name, $textparams);

        if($this->can_invite_others == true){
            $columns[] = $this->factoryobj->getImage('add-user-group.png',array('margin' => '8 14 8 8','onclick' => $this->factoryobj->getOnclick('tab2', true)));
        }

        return $this->factoryobj->getRow($columns,$rowparams);
    }


    private function getGroupChatHeader($users){

        $cache = Appcaching::getGlobalCache('chatheader-'.$this->chatid);
        if($cache){
            return $cache;
        }

        $names = '';
        $profilepics = array();
        $count = 0;

        foreach ($users as $user){
            $userinfo = $this->getUserInfo($user);
            $names .= $userinfo['name'] .', ';
            $profilepics[] = $userinfo['profilepic'];
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

        $textparams['color'] = '#ffffff';
        $textparams['font-size'] = '12';


        $col[] = $this->factoryobj->getVerticalSpacer(30);

        $chatinfo = Aechat::model()->findBYPk($this->chatid);

        $name = isset($chatinfo->title) ? $chatinfo->title : '{#untitled#}';

        $this->factoryobj->rewriteActionField('subject',$this->factoryobj->localizationComponent->smartLocalize($name));

        $row[] = $this->factoryobj->getText('{#group_chat_with#}',$textparams);

        $subtext['color'] = '#ffffff';
        $subtext['font-size'] = '12';
        $row[] = $this->factoryobj->getText($names,$subtext);
        $col[] = $this->factoryobj->getColumn($row,array('vertical-align' => 'middle','margin' => '0 0 0 25','width' => '180'));

        $rowparams['padding'] = '0 0 5 13';
        $rowparams['height'] = '80';
        $rowparams['vertical-align'] = 'middle';
        $rowparams['background-color'] = $this->factoryobj->color_topbar;
        $rowparams['onclick'] = $this->factoryobj->getOnclick('tab2', true);

        $col[] = $this->factoryobj->getImage('beak-icon.png',array('margin' => '22 20 22 10','floating' => '1','float' => 'right'));
        $ret = $this->factoryobj->getRow($col,$rowparams);
        Appcaching::setGlobalCache('chatheader-'.$this->chatid,$ret,600);
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

            if ( !isset($msg['user']) OR !$msg['user']){
                continue;
            }

            $seen_text = '';
            if ( $count == ($i+1) AND $this->userIsOwner( $msg ) ) {
                $seen_text = $this->checkIfSeen( $msg );
            }

            $userInfo = $this->getUserInfo( $msg['user'] );

            if ( $this->msgadded === true) {
                $this->msgadded = false;
            }

            $date = $this->factoryobj->getLocalizedDate( $msg['date'] );
            if ( $this->hide_time ) {
                $date = $this->factoryobj->getLocalizedDate( $msg['date'], $show_time = false );
            }

            $colitems[] = $this->factoryobj->getText($userInfo['name'] . ', ' . $date, array('style' => 'chat-msg-info'));
            $colitems[] = $this->factoryobj->getText($msg['msg'],array('style' => 'chat-msg-text'));


            if ( isset($msg['attachment']) ) {

                $img_params = array('imgwidth' => 300, 'imgheight' => 300, 'width' => '96%', 'radius' => 4, 'margin' => '4 4 4 4', 'priority' => '9',
                    'tap_to_open' => 1,'tap_image' => '');

                $image = $this->factoryobj->getImage($msg['attachment'],array('imgwidth' => '900','imgheight' => '900'));
                if(isset($image->content)){
                    $bigimage = $image->content;
                    $img_params['tap_image'] = $bigimage;
                }
                
                $colitems[] = $this->factoryobj->getImage($msg['attachment'], $img_params);
            }

            $column1 = $this->factoryobj->getColumn(array(
                    $this->factoryobj->getImage($userInfo['profilepic'], array('defaultimage' => 'anonymous2.png', 'crop' => 'round') )
                ), array( 'style' => 'chat-column-1' ));
            $column2 = $this->factoryobj->getColumn(array(
                    $this->factoryobj->getImage('arrow-left.png')
                ), array( 'style' => 'chat-column-2' ));
            $column3 = $this->factoryobj->getColumn(
                    $colitems,
                array( 'style' => 'chat-column-3' ));
            $column4 = $this->factoryobj->getColumn(array(
                    $this->factoryobj->getImage('arrow-right.png')
                ), array( 'style' => 'chat-column-2' ));
            $column5 = $this->factoryobj->getColumn(
                    $colitems,
                array( 'style' => 'chat-column-5' ));

            if ( $this->userIsOwner( $msg ) ) {
                $output[] = $this->factoryobj->getRow(array($column3, $column4, $column1), array( 'style' => 'chat-row-msg-mine' ));
                if ( $seen_text ) {
                    $output[] = $seen_text;
                }
            } else {
                $output[] = $this->factoryobj->getRow(array($column1, $column2, $column5),array( 'style' => 'chat-row-msg' ));
            }

            unset($colitems);
        }

        return $output;
    }


    private function userIsOwner( $message ) {

        if ( is_array($message) ) {
            $message = (object) $message;
        }

        if ( $message->user == $this->playid ) {
            return true;
        }

        return false;
    }


    private function getUserInfo( $id ) {
        
        $profilepic = 'anonymous2.png';

        $cachename = 'uservars-'.$id;
        $vars = Appcaching::getGlobalCache($cachename);
        
        if(!$vars){
            $vars = AeplayVariable::getArrayOfPlayvariables($id);
            Appcaching::setGlobalCache($cachename,$vars,1400);
        }
        
        switch($this->name_mode){
            case 'invisible';
                $name = '';
                break;

            case 'nickname';
                $name = isset($vars['screen_name']) ? $vars['screen_name'] : '{#anonymous#}';
                break;

            case 'firstname';
                $name = isset($vars['real_name']) ? $this->getFirstName($vars['real_name']) : '{#anonymous#}';
                break;

            default:
                $name = isset($vars['real_name']) ? $vars['real_name'] : '{#anonymous#}';
                break;
        }

        $profilepic = isset($vars['profilepic']) ? $vars['profilepic'] : $profilepic;

        return array(
            'profilepic' => $profilepic,
            'name'       => $name,
            'vars'       => $vars
        );
    }

    private function saveChatMsg(){

        if ( !isset($this->factoryobj->menuid) OR empty($this->factoryobj->menuid) ) {
            return false;
        }

        if ( !isset($this->varcontent['name']) AND !isset($this->varcontent['real_name']) ){
            return false;
        }

        // Make sure all previously uploaded images are deleted
        // AeplayVariable::deleteWithName($this->custom_play_id,'chat_upload_temp',$this->gid);

        $msg = $this->submitvariables;

        $var = AeplayVariable::getArrayOfPlayvariables($this->playid);

        // Do nothing if both Image and Message are empty
        if (
            ( !isset($var['chat_upload_temp']) AND empty($var['chat_upload_temp']) ) AND
            empty($msg['66666660'])
        ) {
            return false;
        }

        $username = isset($this->varcontent['real_name']) ? $this->varcontent['real_name'] : $this->varcontent['name'];
        $pic = 'anonymous.png';

        if ( $this->strip_urls AND $msg ){
            $msg = $this->stripUrls($msg);
        }

        if (isset($this->varcontent['profilepic'])) {
            $pic = $this->varcontent['profilepic'];
        }

        $new['name'] = $username;
        $new['date'] = Helper::getCurrentTime();
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

        $current_time = Helper::getCurrentTime();
        $time = date('Y-m-d H:i:s', $current_time);

        $message_id = $this->factoryobj->mobilechatobj->addMessage( $message_text, $time );

        if ( isset($msg['attachment']) and !empty($msg['attachment']) ) {
            $this->factoryobj->mobilechatobj->addAttachment( $message_id, $msg['attachment'] );
        }

        if ( $this->other_user_play_id ) {
            if($this->factoryobj->getConfigParam('save_match_when_chatting')){
                $this->factoryobj->initMobileMatching( $this->other_user_play_id, true );
                $this->factoryobj->mobilematchingobj->saveMatch();
            }

            $notify = AeplayVariable::fetchWithName($this->playid,'notify',$this->gid);

            if ( $notify ) {
                $notification_text = $this->getFirstName($msg['name']) . ': ' . $message_text;
                $title = $this->factoryobj->localizationComponent->smartLocalize('{#message_from#} ') . $this->getFirstName($msg['name']);
                Aenotification::addUserNotification( $this->other_user_play_id, $title, $notification_text,0,$this->gid );
            }

            $this->factoryobj->mobilematchingobj->addNotificationToBanner('msg');
        }

        // Ditto related only
        // Probably not the best place to handle it - should be using hooks instead
        if ( isset($this->varcontent['active_date_id']) AND !empty($this->varcontent['active_date_id']) ) {
            Yii::import('application.modules.aelogic.packages.actionMobiledates.models.*');
            $requestsobj = new MobiledatesModel();
            $request_id = $this->varcontent['active_date_id'];

            $request = $requestsobj->findByPk( $request_id );
            
            if ( $request ) {
                $request->row_last_updated = time();
                $request->update();
            }
        }

    }


    public function getFirstName($name){
        if (!strstr($name, ' ')) {
            return $name;
        } elseif($name) {
            $firstname = explode(' ', trim($name));
            $firstname = $firstname[0];
            return $firstname;
        } else {
            return 'Anonymous';
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

    private function getSubmitButton(){
        $onclick = new stdClass();
        $onclick->action = 'submit-form-content';
        $onclick->id = 'submit-msg';
        $onclick->sync_upload = 1;
        $onclick->viewport = 'bottom';

        if($this->factoryobj->getConfigParam('actionimage4')){
            $btn = $this->factoryobj->getConfigParam('actionimage4');
            return $this->getBtn($btn,$onclick,false);

        } else {
            $btn = 'chat-icon-send.png';
            return $this->getBtn($btn,$onclick,true,true);

        }

    }

    private function getBtn($icon,$onclick,$filled=true,$small=false){
        if($filled){
            if($small){
                $options = array(
                    'width' => '20',
                    'height' => '20',
                    'float' => 'center',
                    'floating' => '1',
                    'margin' => '0 0 0 15',
                );

            } else {
                $options = array(
                    'width' => '30',
                    'height' => '30',
                    'float' => 'center',
                    'floating' => '1',
                    'margin' => '0 0 0 10',
                );

            }

            $image[] = $this->factoryobj->getImage($icon,$options);

            return $this->factoryobj->getColumn($image,array(
                'width' => '50','height' => '50','background-color' => $this->factoryobj->color_topbar,
                'vertical-align' => 'middle',' text-align' => 'center','align' => 'center','border-radius' => '25',
                'onclick' => $onclick
            ));

        } else {
            $image[] = $this->factoryobj->getImage($icon,array('width' => '25','height' => '25',
                'float' => 'center',
                'floating' => '1',
            ));

            return $this->factoryobj->getColumn($image,array(
                'width' => '50','height' => '50','onclick' => $onclick,
                'vertical-align' => 'middle',' text-align' => 'center'
            ));

        }
        
    }

    private function getPhotoUploadButton(){

        /* if permission is required from both users for sending pictures */
        if($this->pic_permission){
            $pointer = $this->factoryobj->mobilechatobj->context_key .'-' .$this->other_user_play_id;
            $other = $this->factoryobj->appkeyvaluestorage->get($pointer);
            if(!$other){
                return $this->factoryobj->getVerticalSpacer(50);
            }
        }

        $onclick = new stdClass();
        $onclick->action = 'upload-image';
        $onclick->sync_upload = 1;
        $onclick->viewport = 'bottom';
        $onclick->max_dimensions = '600';
        $onclick->allow_delete = true;
        $onclick->variable = $this->factoryobj->getVariableId('chat_upload_temp');

        if($this->factoryobj->getConfigParam('actionimage5')){
            return $this->getBtn( $this->factoryobj->getConfigParam('actionimage5'),$onclick,false);
        } else {
            $camera = 'chat-icon-photo.png';
            return $this->getBtn( $camera,$onclick,true);

        }

    }

    private function getFooter(){

        if($this->disable_chat === true){
            $output = array();
            $output[] = $this->factoryobj->getText('{#sorry_message_limit_reached#}',array('style' => 'chat-msg-text-centered'));
            return $output;
        }

        $this->debug = false;
        $output = array();

        if ( isset($this->varcontent['name']) AND $this->varcontent['name']
            OR isset($this->varcontent['real_name']) AND $this->varcontent['real_name']
        ) {


            $output[] = $this->factoryobj->getSpacer(1,array('background-color' => $this->factoryobj->color_topbar
                    ,'margin' => '0 20 0 20','opacity' => '0.3'
            ));
            $output[] = $this->factoryobj->getImage('invisible-divider.png',array('max-height' => '350','margin' => '0 0 0 0','variable' => $this->factoryobj->getVariableId('chat_upload_temp')));
            $hint = isset($this->options['hint']) ? $this->options['hint'] : '{#write_a_message#}';
            //$hint = 'chatid:'.$this->factoryobj->mobilechatobj->getChatId() .'other:' .$this->other_user_play_id . 'mine: ' .$this->playid;

            $args = array(
                'submit_menu_id' => 'submit-msg',
                'hint' => $hint,
                'variable' => '66666660',
                'value' => '',
                'activation' => 'keep-open',
                'background-color' => '#ffffff',
                'font-size' => '12',
                'font-style' => 'italic',
                'color' => '#474747',
                'padding' => '15 4 4 4',
                'height' => '50',
                'border-radius' => '4',
                'vertical-align' => 'middle',
            );
            
            $width = $this->factoryobj->screen_width - 160;

            $columns[] = $this->factoryobj->getColumn(array(
                    $this->factoryobj->getFieldtextarea( '', $args )
                ), array( 'width' => $width,'vertical-align' => 'middle' ));

            $columns[] = $this->factoryobj->getVerticalSpacer('10');
            $columns[] = $this->getPhotoUploadButton();
            $columns[] = $this->factoryobj->getVerticalSpacer('10');
            $columns[] = $this->getSubmitButton();

            $output[] = $this->factoryobj->getRow($columns,array('margin' => '9 20 10 20','vertical-align' => 'middle','height' => '70'));

        } else {
            if ( isset($this->configobj->profile_action_id) ) {
                $output[] = $this->factoryobj->getImagebutton( 'btn-create-profile.png', 'create_button_id', false, array( 'action' => 'open-action', 'config' => $this->configobj->profile_action_id, 'style' => 'booking-menu' ) );
            } else {
                $output[] = $this->factoryobj->getMenu( 'create_profile', array( 'style' => 'footer_menu' ) );
            }
        }

        return $output;
    }

    public function checkIfSeen( $message ) {

        if(!isset($message['id'])){
            return false;
        }

        $is_seen = $this->factoryobj->mobilechatobj->checkMessageStatus( $message['id'] );

        if ( $is_seen ) {
            return $this->factoryobj->getText( '{#seen#}', array( 'style' => 'message-status-text' ) );
        }

        return $this->factoryobj->getText( '{#delivered#}', array( 'style' => 'message-status-text' ) );
    }

    public function markMsgsAsRead() {

        if ( empty($this->chat_content['msgs']) ) {
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

}