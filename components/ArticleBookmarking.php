<?php

/*
    general class for managing user specific bookmarks of actions
    if you bookmark an action, remember that it has to be visible to user (triggered)
    this class does not deal with visibility at all for now

*/

class ArticleBookmarking extends ArticleComponent {

    public $variables;
    public $varcontent;

    public $bookmarks;
    public $bookmarks_array;

    /* important, this is not the play_action id, but ae_game_branch_action */
    public $action_id;
    public $actionid;

    public $required_params = array('action','bookmarkvar');
    public $bookmark_var_name;

    public $actionobj;
    public $imagesobj;

    public $configobj;
    public $submit;
    public $menus;
    public $updateNotifications;

    public $userid;
    public $userlist;
    public $notification_action;

    public function template(){

        $action = $this->options['action'];
        $this->bookmark_var_name = $this->options['bookmarkvar'];
        $this->bookmarks = $this->addParam($this->bookmark_var_name,$this->options,array());
        $this->init();

        $this->updateNotifications = $this->addParam('updatenotifications',$this->options,false);
        $this->notification_action = $this->addParam('notification_action',$this->options,'recipe');
        $this->actionid = $this->addParam('actionid',$this->options,$this->actionid);

        $this->removeBookmarks();

        switch($action){
            case 'getstatus':
                return $this->bookmarkStatus();
                break;

            case 'save':
                return $this->bookmarkSave();
                break;

            case 'remove':
                return $this->bookmarkRemove();
                break;

            case 'list':
                return $this->getBookmarks($this->bookmarks);
                break;
        }
    }

    public function removeBookmarks(){

        $up = false;

        if(isset($this->submit['menuid'])){
            $menuid = $this->submit['menuid'];

            if($menuid == 520){
                $this->deleteAllBookmarks();
                $up = true;
            }

            if(isset($this->bookmarks->$menuid)){
                $this->bookmarkRemove($menuid);
                $up = true;
            }
        }
        
        if($up){
            $this->savebookmarks();
            $this->updateAction(-1);
            $this->flushBookmarksCache();
        }
    }


    public function init(){
        $this->bookmarks = @json_decode($this->varcontent[$this->bookmark_var_name]);

        if(is_object($this->bookmarks)){
            while($bookmark = each($this->bookmarks)){
                $key = $bookmark['key'];
                $value = $bookmark['value'];
                $this->bookmarks_array[$key] = $value;
            }
        } else {
            $this->bookmarks_array = array();
        }

    }

    public function bookmarkStatus(){
        $actionid = $this->actionid;

        if ( isset($this->bookmarks->$actionid) ) {
            return true;
        }

        return false;
    }


    /* two ways to remove, either from a list or with a actionid */
    public function deleteAllBookmarks(){

        $array = (array)$this->bookmarks;
        $array = array_reverse($array);

        while($bookmark = each($array)){
            $key = $bookmark['key'];
            $this->bookmarkRemove($key);
        }

        return true;
    }

    /* two ways to remove, either from a list or with a actionid */
    public function bookmarkRemove($actionid=false){
        if(!$actionid){
            $actionid = $this->actionid;
        }

        if(isset($this->bookmarks->$actionid)){
            unset($this->bookmarks->$actionid);
        }

        $this->savebookmarks();
        $this->updateAction(-1);
        return true;
    }

    public function bookmarkSave(){
        $actionid = $this->actionid;

        if(is_object($this->bookmarks)){
            if(isset($this->bookmarks->$actionid)){
                return true;
            } else {
                $this->bookmarks->$actionid = true;
            }
        } else {
            $this->bookmarks = new StdClass;
            $this->bookmarks->$actionid = true;
        }

        $this->savebookmarks();
        $this->updateAction('1');
        $this->updateNotification();
        $this->flushBookmarksCache();
        return true;
    }

    /* will update authors notification variable */
    private function updateNotification(){

        if(isset($this->configobj->user) AND is_numeric($this->configobj->user)){
            $user = $this->configobj->user;
        } elseif(isset($this->configobj->author) AND is_numeric($this->configobj->author)){
            $user = $this->configobj->author;
        }

        if(isset($user)){
            $userinfo = $this->factoryobj->getUserVariables($user);
            $notifications = isset($userinfo['notifications']) ? $userinfo['notifications']:false;
        }

        if(!isset($notifications) OR !$notifications){
            $notifications = array();
        } else {
            $notifications = (array)json_decode($notifications);
        }

        $add = true;

        if(!empty($notifications)){
            foreach($notifications as $notify){
                $notify = (array)$notify;

                if(isset($notify['action_id']) AND isset($notify['user_id']) AND $notify['action_id'] == $this->action_id AND $notify['user_id'] == $this->userid){
                    $add = false;
                }
            }
        }

        reset($notifications);

        if($add AND isset($this->configobj->subject) AND isset($this->configobj->user)){
            $item['action_id'] = $this->action_id;
            $item['user_id'] = $this->userid;
            $item['subject'] = $this->configobj->subject;
            $item['action'] = $this->notification_action;
            $notifications[] = $item;
            AeplayVariable::updateWithId(false,$this->vars['notifications'],json_encode($notifications),$this->configobj->user,$this->gid);
        }

    }

    private function flushBookmarksCache(){

    }

    public function bookmarksRender($bookmarkdata,$mode='images'){
        $output = array();

        $bookmarkdata = (array)@json_decode($bookmarkdata);

        if(isset($bookmarkdata) and is_array($bookmarkdata) AND !empty($bookmarkdata)){
            $output = $this->getBookmarks($bookmarkdata);
        }

        return $output;

    }

    public function getBookmarks($bookmarkdata,$style = 'bm'){

        $output = array();
        $items = array();

        $bookmarkdata = (array)$bookmarkdata;
        $count = 0;

        while($bookmark = each($bookmarkdata)){

            $key = $bookmark[0];

            $action = AeplayAction::model()->with('aetask')->findByAttributes(array('id' => $key, 'play_id' => $this->playid));
            $arr = array();

            if(is_object($action)){

                $config = @json_decode($action->aetask->config);

                if(isset($config->subject) AND $config->subject){
                    $title = $config->subject;
                } else {
                    $title = $action->aetask->name;
                }

                if(is_object($config) AND isset($config->image_portrait) AND $config->image_portrait){
                    $icon = $this->factoryobj->getImageFileName($config->image_portrait, array( 'imgwidth' => '200', 'imgheight' => '200', 'imgcrop' => 'round', 'defaultimage' => 'anonymous2.png' ) );
                } else {
                    $icon = 'anonymous2.png';
                }

                $type = Aeactiontypes::model()->findByPk($action->aetask->type_id);

                if($type->title == 'Mobile Gallery'){
                    $type = 'Inspiration Gallery';
                } else {
                    $type = $type->title;
                }

                $arr['action'] = 'open-action';
                $arr['action_config'] = $action->aetask->id;
                $arr['back_button'] = 1;

                $columns[] = $this->factoryobj->getImage($icon,array(
                    'imgwidth' => '400', 'imgheight' => '400',
                    'crop' => 'round', 'defaultimage' => 'anonymous2.png',
                    'width' => 50, 'height' => 50,
                    'border-color' => '#51d048', 'border-width' => '2', 'border-radius' => '25',
                    'vertical-align' => 'middle'));

                $columns[] = $this->factoryobj->getText($type .':
' .$title,array('margin' => '0 50 0 9','onclick' => $arr,'vertical-align' => 'middle','font-size' => 13));
                $columns[] = $this->factoryobj->getImageButton('del-bookmark.png',$action->id,false,array('width' => 50,'height' => 50,'vertical-align' => 'middle','floating' => '1', 'float' => 'right'));
                $items[] = $this->factoryobj->getRow($columns,array('margin' => '18 35 5 15','width' => '89%'));
                unset($columns);
                $count++;
            }
        }

        while($count < 6){
            $columns[] = $this->factoryobj->getImage('unlocked4.png',array(
                'crop' => 'round', 'defaultimage' => 'anonymous2.png',
                'width' => 50, 'height' => 50,'margin' => '0 4 0 3'));

            $columns[] = $this->factoryobj->getText('Available bookmark slot',array('margin' => '0 50 0 9','color' => '#a9a9a9','font-size' => 13));
            //$columns[] = $this->factoryobj->getImageButton('del-bookmark.png',123,false,array('width' => 50,'height' => 50));
            $items[] = $this->factoryobj->getRow($columns,array('margin' => '18 35 5 12','width' => '89%'));

            unset($columns);
            $count++;
        }


        $output[] = $this->factoryobj->getColumn($items);

        return $output;
    }


    private function savebookmarks(){
        $bookmarks = json_encode($this->bookmarks);
        AeplayVariable::updateWithName($this->playid,$this->bookmark_var_name,$bookmarks,$this->gid);
    }

    private function updateAction($num){

        $id = $this->action_id;

        // $action = AeplayAction::model()->findByPk($id);

        $action = Aeaction::model()->findByPk($id);
        $config = @json_decode($action->config);

        if(isset($config->bookmarks)){
            $config->bookmarks = $config->bookmarks+$num;
            $action->config = json_encode($config);
            $action->update();
        }

    }


}