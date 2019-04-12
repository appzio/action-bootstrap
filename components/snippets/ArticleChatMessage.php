<?php

Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aechat.models.*');

class ArticleChatMessage extends ArticleComponent
{

    private $current_msg;
    private $user_is_owner;
    private $context_key;

    public function template(){

        $this->current_msg = $this->addParam('current_msg',$this->options,false);
        $this->user_is_owner = $this->addParam('user_is_owner',$this->options,false);
        $this->context_key = $this->addParam('context_key',$this->options,false);

        $total_msgs = $this->addParam('total_msgs',$this->options,false);
        $current_msg_index = $this->addParam('current_msg_index',$this->options,false);
        $userinfo = $this->addParam('userinfo',$this->options,false);
        $hide_time = $this->addParam('hide_time',$this->options,false);

        $date = $this->factoryobj->getLocalizedDate( $this->current_msg['date'] );
        if ( $hide_time ) {
            $date = $this->factoryobj->getLocalizedDate( $this->current_msg['date'], $show_time = false );
        }

        $colitems[] = $this->factoryobj->getText($userinfo['name'] . ', ' . $date, array('style' => 'chat-msg-info'));
        $colitems[] = $this->factoryobj->getText($this->current_msg['msg'],array('style' => 'chat-msg-text'));

        if ( isset($this->current_msg['attachment']) ) {
            $colitems[] = $this->getMessageAttachment();
        }

        $column1 = $this->factoryobj->getColumn(array(
            $this->factoryobj->getImage($userinfo['profilepic'], array('defaultimage' => 'anonymous2.png', 'crop' => 'round',
                'priority' => 9,'imgwidth' => 300, 'imgheight' => 300) )
        ), array( 'style' => 'chat-column-1' ));
        $column2 = $this->factoryobj->getColumn(array(
            $this->factoryobj->getImage('arrow-left.png')
        ), array( 'style' => 'chat-column-2' ));
        $column3 = $this->factoryobj->getColumn(
            $colitems,
            array( 'style' => 'chat-column-3' ));
        $column4 = $this->factoryobj->getColumn(array(
            $this->factoryobj->getImage('arrow-right.png'),
        ), array( 'style' => 'chat-column-2' ));
        $column5 = $this->factoryobj->getColumn(
            $colitems,
            array( 'style' => 'chat-column-5' ));

        if ( $this->user_is_owner ) {
            $output[] = $this->factoryobj->getRow(array($column3, $column4, $column1), array( 'style' => 'chat-row-msg-mine' ));
        } else {
            $output[] = $this->factoryobj->getRow(array($column1, $column2, $column5),array( 'style' => 'chat-row-msg' ));
        }

        if ( $total_msgs == ($current_msg_index+1) AND $this->user_is_owner ) {
            $output[] = $this->checkIfSeen();
        }

        return $this->factoryobj->getRow(array(
            $this->factoryobj->getColumn($output)
        ));
    }

    public function getMessageAttachment() {

        $big_img_params = array(
            'imgwidth' => '900',
            'imgheight' => '900',
            'priority' => 9,
        );

        $image = $this->factoryobj->getImage($this->current_msg['attachment'], $big_img_params);

        $img_params = array(
            'imgwidth' => 300,
            'imgheight' => 300,
            'width' => '96%',
            'radius' => 4,
            'margin' => '4 4 4 4',
            'priority' => '9',
            'tap_to_open' => 1,
            'tap_image' => ''
        );

        if ( isset($image->content) ) {
            $bigimage = $image->content;
            $img_params['tap_image'] = $bigimage;
        }

        return $this->factoryobj->getImage($this->current_msg['attachment'], $img_params);
    }

    public function checkIfSeen() {

        if(!isset($this->current_msg['id'])){
            return false;
        }

        $is_seen = $this->factoryobj->mobilechatobj->checkMessageStatus( $this->current_msg['id'] );

        if ( $is_seen ) {
            return $this->factoryobj->getText( '{#seen#}', array( 'style' => 'message-status-text' ) );
        }

        return $this->factoryobj->getText( '{#delivered#}', array( 'style' => 'message-status-text' ) );
    }

}