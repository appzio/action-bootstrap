<?php

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aechat.models.*');

class ArticleChatFooter extends ArticleComponent
{

    // Local Vars
    public $submitvariables;
    public $configobj;
    public $imagesobj;
    public $vars;
    public $varcontent;

    private $chatid;
    private $chat_info;
    private $disable_chat;
    private $other_user_play_id;
    private $hide_pic_button;
    private $pic_permission;
    private $userlist;

    public function template(){

        $this->chatid = $this->addParam('chatid',$this->options,false);
        $this->chat_info = $this->addParam('chat_info',$this->options,false);
        $this->disable_chat = $this->addParam('disable_chat',$this->options,false);
        $this->other_user_play_id = $this->addParam('other_user_play_id',$this->options,false);
        $this->hide_pic_button = $this->addParam('hide_pic_button',$this->options,false);
        $this->pic_permission = $this->addParam('pic_permission',$this->options,false);
        $this->userlist = $this->addParam('userlist',$this->options,false);

        if(isset($this->chat_info->blocked) AND $this->chat_info->blocked == 1){
            $output = array();
            $output[] = $this->factoryobj->getText('{#this_chat_has_ended#}',array('style' => 'chat-msg-text-centered'));
            $output[] = $this->factoryobj->getSpacer(10);
            return $output;
        }

        if($this->disable_chat === true){
            $output = array();
            $output[] = $this->factoryobj->getText('{#sorry_message_limit_reached#}',array('style' => 'chat-msg-text-centered'));
            return $output;
        }

        $output = array();

        $name = $this->getUsername();

        if ( empty($name) ) {
            $output[] = $this->factoryobj->getText( '{#please_finish_up_your_registration#}', array(
                'style' => 'chat-error'
            ));
            return $output;
        }

        $chat_owner_play_id = ( isset($this->chat_info->owner_play_id) ? $this->chat_info->owner_play_id : 0 );

        if ( !empty($this->userlist) AND !ctype_digit($this->chatid) AND ($chat_owner_play_id != $this->playid) ) {
            $onclick = new stdClass();
            $onclick->action = 'submit-form-content';
            $onclick->id = 'join_chat';
            $onclick->viewport = 'bottom';

            $output[] = $this->factoryobj->getText( '{#join_chat#}', array( 'style' => 'general_button_style_red', 'onclick' => $onclick ) );
            return $output;
        }

        $output[] = $this->factoryobj->getSpacer(1, array(
            'background-color' => $this->factoryobj->color_topbar,
            'margin' => '0 20 0 20','opacity' => '0.3'
        ));

        $output[] = $this->factoryobj->getImage('invisible-divider.png',array('max-height' => '350','margin' => '0 0 0 0','variable' => $this->factoryobj->getVariableId('chat_upload_temp')));
        $hint = isset($this->options['hint']) ? $this->options['hint'] : '{#write_a_message#}';

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
            'padding' => '4 4 4 4',
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

        $output[] = $this->factoryobj->getRow($columns,array('margin' => '0 20 0 20','vertical-align' => 'middle','height' => '70'));

        return $output;
    }

    private function getPhotoUploadButton(){

        if ( $this->hide_pic_button ) {
            return $this->factoryobj->getVerticalSpacer(50);
        }

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

    private function getUsername() {

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