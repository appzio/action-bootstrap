<?php

Yii::import('application.modules.aelogic.article.components.*');

class ArticleUserinfo extends ArticleComponent {

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
    public $save_action_id;
    public $userlist;

    protected function requiredOptions() {
        return array(
        );
    }

    public function template() {

        $status = $this->factoryobj->moduleBookmarking('getstatus');
        $userid = $this->addParam('userid',$this->options,false);
        $narrow = $this->addParam('narrow',$this->options,false);

        $style = array(
            'font-size' => 13,
            'margin'=> '24 0 0 10',
            'color'=> '#474747',
            'text_color'=> '#474747'
        );

        //$textstyle = $this->addParam('text_style',$this->options,$style);

        if($userid){
            $user = $userid;
        } elseif(isset($this->configobj->author) AND $this->configobj->author){
            $user = $this->configobj->author;
        } else {
            $user = false;
        }

        if($user) {

            $userinfo = $this->factoryobj->getUserVariables($user);

            if($userinfo){

                if(isset($userinfo['totalpoints']) > 0){
                    $userinfo = $userinfo['name'] .', ' .$userinfo['totalpoints'] .'pt';
                } elseif(isset($userinfo['name'])) {
                    $userinfo = $userinfo['name'];
                }
            } else {
                $userinfo = false;
            }

            if(!$userinfo){
                if(isset($userinfo['real_name'])){
                    $userinfo = $userinfo['real_name'];
                }
            }
            
            if($userinfo AND is_string($userinfo)){
                $userpic = $this->factoryobj->getUserPic($user);
                $columns[] = $this->factoryobj->getImage( $userpic,array('crop' => 'round','width' => '80'));
                $columns[] = $this->factoryobj->getText( 'Author: ' . $userinfo ,$style);
            } else {
                $columns[] = $this->factoryobj->getImage('anonymous2.png',array('crop' => 'round','width' => '80'));
                $columns[] = $this->factoryobj->getText( 'Author: Anonymous',$style);
            }
        } else {
            $columns[] = $this->factoryobj->getText( '',array('width'=>'60%','margin'=>'35 0 0 10','alignment' => 'left')+$style);
        }

        $menustyle = array('width' => '50','float' => 'right','floating' => 1, 'text-align' => 'right','margin' => '15 0 0 0');
        if($status == 1){
            $columns[] = $this->factoryobj->getImagebutton('heart-icon-red.png',200,'heart-icon-grey.png',$menustyle);
        } else {
            $columns[] = $this->factoryobj->getImagebutton('heart-icon-grey.png',201,'heart-icon-red.png',$menustyle);
        }

        $row = $this->factoryobj->getRow($columns,array('margin' => '-40 30 15 10','width' => '95%','alignment' => 'left','floating' => 1));

        return $row;
    }


}