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

        $rightArrow = $this->factoryobj->getImage('arrow-right-yellow.png');

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
            $rightArrow
        ), array( 'style' => 'chat-column-2' ));
        $column5 = $this->factoryobj->getColumn(
            $colitems,
            array( 'style' => 'chat-column-5' ));

        if ( $this->user_is_owner ) {
            return $this->factoryobj->getRow(array($column3, $column4, $column1), array( 'style' => 'chat-row-msg-mine' ));
        } else {
            return $this->factoryobj->getRow(array($column1, $column2, $column5),array( 'style' => 'chat-row-msg' ));
        }

    }

    public function getMessageAttachment() {

        $use_blur = false;

        if ( !$this->user_is_owner ) {
            $time_to_read = 300;
            $enter_time = $this->getChatEnterTime();

            $seconds_left = $time_to_read - ( time() - $enter_time );
            // $message_visible_until = $enter_time + $time_to_read;

            // This is the difference between the actual time, when the message was originally sent
            // and the time when the person entered the chat section
            $msg_diff = $enter_time - $this->current_msg['date'];

            if ( $seconds_left < $msg_diff ) {
                $use_blur = true;
            }
        }

        $big_img_params = array(
            'imgwidth' => '900',
            'imgheight' => '900',
            'priority' => 9,
        );

        if ( $use_blur ) {
            $big_img_params['blur'] = 1;
        }

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

        if ( $use_blur ) {
            $img_params['blur'] = 1;
        }

        return $this->factoryobj->getImage($this->current_msg['attachment'], $img_params);
    }

    private function getChatEnterTime() {
        $db_times = $this->factoryobj->getSavedVariable( 'entered_chat_timestamp' );

        if ( empty($db_times) ) {
            return 0;
        }

        $times_array = json_decode( $db_times, true );

        if ( isset($times_array[$this->context_key]) ) {
            return $times_array[$this->context_key];
        }

        return 0;
    }

}