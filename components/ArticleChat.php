<?php

Yii::import('application.modules.aelogic.article.components.*');

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

    public $firstname_only;

    public $pic_permission;
    public $strip_urls;

    public $required_params = array( 'context', 'context_key' );

    public $context;
    public $context_key;

    public $other_user_play_id;

    public $chatid;

    protected function requiredOptions() {
        return array();
    }

    public function template() {

        $this->factoryobj->rewriteActionField( 'keep_scroll_in_bottom', 1 );

        // Init the Chat based on the currently requested context
        $this->custom_play_id = isset($this->options['custom_play_id']) ? $this->options['custom_play_id'] : $this->playid;

        $this->otheruser = $this->addParam('otheruser',$this->options,false);
        $this->context = $this->addParam('context',$this->options,false);
        $this->context_key = $this->addParam('context_key',$this->options,false);
        
        $this->factoryobj->initMobileChat( $this->context, $this->context_key );
        
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

        /*
        $page = 1;

        if ( isset($this->submit['next_page_id']) ) {
            $page = $this->submit['next_page_id'];
        }

        $num_rec_per_page = 10;
        $start_from = ($page-1) * $num_rec_per_page;
        $content = $this->factoryobj->mobilechatobj->getChatContent( $start_from, $num_rec_per_page );
        */

        $this->saveChatMsg();
        $content = $this->factoryobj->mobilechatobj->getChatContent();

        // App specific settings
        $this->save_match = $this->addParam('save_match',$this->options,false);
        $this->firstname_only = $this->addParam('firstname_only',$this->options,false);
        $this->notify = $this->addParam('notify',$this->options,false);

        $this->pic_permission = $this->addParam('pic_permission',$this->options,false);
        $this->strip_urls = $this->addParam('strip_urls',$this->options,false);

        if ( !empty($content) ) {
            $this->chat_content['msgs'] = $content;
        }

        $object = new StdClass();

        if($this->pic_permission){
            $object->header[] = $this->handlePicPermission();
        }

        $object->scroll = $this->getChat();
        $object->footer = $this->getFooter();

        $this->chatid = $this->factoryobj->mobilechatobj->getChatId();

        $this->factoryobj->initMobileMatching( $this->other_user_play_id,true );
        return $object;
    }

    private function getChat() {

        $this->markMsgsAsRead();

        $items = $this->renderChatMsgs();

        // Still some work to be done here ..
        // $next_page_id = 2;

        // if ( isset($this->submit['next_page_id']) ) {
        //     $next_page_id = $this->submit['next_page_id'] + 1;
        // }
        // $output[] = $this->factoryobj->getInfinitescroll( $items, array( 'next_page_id' => $next_page_id ) );

        $output = array();

        foreach ($items as $item) {
            $output[] = $item;
        }

        $output[] = $this->factoryobj->getSpacer( 10 );

        return $output;
    }


    private function renderChatMsgs() {

        $output = array();

        if ( !isset($this->chat_content['msgs']) OR empty($this->chat_content['msgs']) OR !$this->chat_content['msgs'] ) {
            $output[] = $this->factoryobj->getText( '{#no_comments_yet#}', array( 'style' => 'chat-no-comments' ) );
            return $output;
        }

        $msgs = (object) $this->chat_content['msgs'];
        $count = count( $msgs );

        foreach ($msgs as $i => $msg) {

            if ( !isset($msg['name']) OR !is_string($msg['name']) ) {
                continue;
            }

            $seen_text = '';
            if ( $count == $i AND $this->userIsOwner( $msg ) ) {
                $seen_text = $this->checkIfSeen( $msg );
            }

            $userInfo = $this->getUserInfo( $msg );

            if ( $this->msgadded === true) {
                $this->msgadded = false;
            }

            $date = $this->factoryobj->getLocalizedDate( 'D, j. \of M @ H:i', $msg['date'] );

            $img_params = array('imgwidth' => 640, 'imgheight' => 400, 'width' => '96%', 'radius' => 4, 'margin' => '4 4 4 4');
            $colitems[] = $this->factoryobj->getText($userInfo['name'] . ', ' . $date, array('style' => 'chat-msg-info'));

            $colitems[] = $this->factoryobj->getText($msg['msg'],array('style' => 'chat-msg-text'));
            
            if ( isset($msg['attachment']) ) {
                $colitems[] = $this->factoryobj->getImage($msg['attachment'], $img_params);
            }

            $column1 = $this->factoryobj->getColumn(array(
                    $this->factoryobj->getImage($userInfo['profilepic'], array('defaultimage' => 'anonymous2.png', 'crop' => 'round') )
                ), array( 'style' => 'chat-column-1' ));
            $column2 = $this->factoryobj->getColumn(array(
                    $this->factoryobj->getImage('arrow-beak2.png')
                ), array( 'style' => 'chat-column-2' ));
            $column3 = $this->factoryobj->getColumn(
                    $colitems,
                array( 'style' => 'chat-column-3' ));
            $column4 = $this->factoryobj->getColumn(array(
                    $this->factoryobj->getImage('flipped-beak.png')
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


    private function getUserInfo( $user_msg ) {
        
        $profilepic = 'anonymous2.png';
        $name = 'Anonymous';

        if ( isset($user_msg) AND $user_msg ) {
            // $userinfo = $this->factoryobj->getUserVariables($user_msg);

            if ( isset($user_msg['profilepic']) ) {
                $profilepic = $user_msg['profilepic'];
            }

            if ( isset($user_msg['name']) ) {

                $name = $user_msg['name'];

                if ( $this->firstname_only ) {
                    $name = $this->getFirstName($name);
                }
            }
        }

        return array(
            'profilepic' => $profilepic,
            'name'       => $name,
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

        $username = isset($this->varcontent['name']) ? $this->varcontent['name']:$this->varcontent['real_name'];
        $pic = 'anonymous.png';

        if ( $this->firstname_only ) {
            $username = $this->getFirstName($username);
        }

        if ( $this->strip_urls AND $msg ){
            $msg = $this->stripUrls($msg);
        }

        if (isset($this->varcontent['profilepic'])) {
            $pic = $this->varcontent['profilepic'];
        }

        $new['name'] = $username;
        $new['date'] = time();
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
            $this->factoryobj->initMobileMatching( $this->other_user_play_id, true );
            $this->factoryobj->mobilematchingobj->saveMatch();

            $notify = AeplayVariable::fetchWithName($this->playid,'notify',$this->gid);

            if ( $notify ) {
                $notification_text = $this->getFirstName($msg['name']) . ': ' . $message_text;
                $title = 'Message from ' . $this->getFirstName($msg['name']);
                LogicNotifications::sendSinglePush( $this->other_user_play_id, $title, $notification_text );
            }

            $this->factoryobj->mobilematchingobj->addNotificationToBanner('msg');
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
            $this->factoryobj->appkeyvaluestorage->set($pointer_me,true);
        }

        $other = $this->factoryobj->appkeyvaluestorage->get($pointer_me);

        if(!$other){
            return $this->factoryobj->getTextButton('{#this_user_can_send_me_pictures#}',array('id' => 'pic_permission','small_text' => true,'icon' => 'icon-cam-ok.png'));
        }
    }

    private function getImageButton(){
        $options = array(
            'action' => 'upload-image',
            'sync_upload' => true,
            'viewport' => 'bottom',
            'max_dimensions'=> '600',
            'allow_delete' => true,
            'variable' => $this->factoryobj->getVariableId('chat_upload_temp')
        );

        return $this->factoryobj->getColumn(array(
            $this->factoryobj->getImagebutton( 'camera.png','969698', false, $options ),
        ), array( 'width' => '13%','margin'=>'0 5 0 7' ));
    }

    private function getFooter(){

        $this->debug = false;
        $output = array();

        if ( isset($this->varcontent['name']) AND $this->varcontent['name']
            OR isset($this->varcontent['code']) AND $this->varcontent['code']
            OR isset($this->varcontent['real_name']) AND $this->varcontent['real_name']
        ) {

            $output[] = $this->factoryobj->getImage('hairline2.png',array('margin' => '0 0 4 0','variable' => $this->factoryobj->getVariableId('chat_upload_temp')));
            $hint = isset($this->options['hint']) ? $this->options['hint'] : '{#write_a_message#}';
            //$hint = 'chatid:'.$this->factoryobj->mobilechatobj->getChatId() .'other:' .$this->other_user_play_id . 'mine: ' .$this->playid;

            $columns[] = $this->factoryobj->getColumn(array(
                    $this->factoryobj->getFieldTextarea( '', array('hint' => $hint, 'style' => 'chat-comment-field', 'variable' => '66666660' ,'value' => ''))
                ), array( 'style' => 'chat-comment-field-wrap' ));

            /* if permission is required from both users for sending pictures */
            if($this->pic_permission){

                $pointer = $this->factoryobj->mobilechatobj->context_key .'-' .$this->other_user_play_id;
                $other = $this->factoryobj->appkeyvaluestorage->get($pointer);

                if($other){
                    $columns[] = $this->getImageButton();
                } else {
                    $columns[] = $this->factoryobj->getText('', array( 'width' => '13%','margin'=>'0 5 0 7' ));
                }

            } else {
                $columns[] = $this->getImageButton();
            }

            $columns[] = $this->factoryobj->getColumn(array(
                    $this->factoryobj->getImagebutton( 'sendbutton.png', 'submit-msg', false, array( 'sync_upload' => 1, 'viewport' => 'bottom' ) )
                ), array( 'width' => '13%','margin'=>'0 10 0 2' ));

            $output[] = $this->factoryobj->getRow($columns,array('margin' => '10 0 10 0'));

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

            if ( !isset($message['msg_is_read']) ) {
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