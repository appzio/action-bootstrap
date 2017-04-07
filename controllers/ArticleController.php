<?php


Yii::import('application.modules.aegameauthor.models.*');
Yii::import('application.modules.aegameauthor.components.*');
Yii::import('application.modules.aegameauthor.components.snippets.*');
Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aelogic.article.components.snippets.*');
Yii::import('application.modules.aeapi.models.*');

class ArticleController {

    public $allvars = array();
    public $datafile;

    /* @var ImagesController */
    public $imagesobj;
    public $menuid;
    public $mode;
    public $actionobj;
    public $configobj;
    public $tabs = array();
    public $current_tab;

    public $menus = array();
    public $menuitems;

    public $branchobj;
    public $branchconfig;

    public $actionid;
    public $action_id;

    public $submitvariables;

    public $vars = array();
    public $varcontent = array();
    public $varcontent_byid = array();

    public $gid;
    public $class;

    public $playid;
    public $bookmarkobj;

    public $userid;
    public $playobj;

    public $submit;
    public $caching = false;         // set off when developing
    public $tabsmenu_json;
    public $articlemenuobj;

    public $articleComponent;
    public $childobj;

    public $tabmode = 'top'; // top, bottom or off (set manually)

    public $debug;
    public $errorMsgs = array();
    public $debugMsgs = array();

    public $userlist;
    public $available_branches;

    public $rerun_list_branches = false;  // this is a special directive which basically redoes the entire listbranches

    public $color_topbar;           // shorthand for most common
    public $color_topbar_hilite;    // shorthand for most common
    public $colors;                 // all colors of the action

    public $params;
    public $fblogin;
    public $fbtoken;
    public $lang;

    public $mobilesettings;
    public $rewriteconfigs;
    public $rewriteactionfield;

    public $referring_action;
    public $context;

    public $api_version;
    public $query;

    public $aspect_ratio;
    public $screen_width;
    public $screen_height;

    public $appinfo;

    public $imagespath;
    public $imagesearchpath;

    public $current_playid;
    public $current_gid;

    public $permanames;

    /* actual layout code, often redeclared in controllers */
    public $data;

    /* global used to keep track of unread count between actions */
    public $msgcount;

    /* you can put objects that can be reused by actions during the same call
        (mainly list branches, where we might get a lot of repeat calls for some inits
     */

    public $recycleable_objects = array();
    public $global_recyclable = array('msgcount');

    /* automatically created bottom menu gets put here. To disable it for
    some tab or view, you can simply set it empty (it gets created upon init) */
    public $bottom_menu_json;

    /* by settings this to true, you can have api output only msg ok
    this is used for certain async functions where we don't want the client
    to do an update of its view */
    public $no_output = false;

    /* @var Localizationapi */
    public $localizationComponent;

    /* @var MobilematchingModel */
    public $mobilematchingobj;

    /* @var Aechat */
    public $mobilechatobj;

    /* @var AeplayDatastorage */
    public $playdatastorage;

    /* @var AeplayKeyvaluestorage */
    public $playkeyvaluestorage;

    /* @var AegameKeyvaluestorage */
    public $appkeyvaluestorage;

    /** @var MobileloginModel */
    public $loginmodel;

    /* this is related to mixing data between apps */
    public $fake_play_error = false;
    
    /* bottom navigation, which is handled entirely on server side */
    public $bottom_menu_id;

    public $bottom_notifications;

    /* these are used to save parameters for clicks */
    public $click_parameters_to_save;
    public $click_parameters_saved;

    public $to_session_storage;
    public $session_storage = array();

    public function __construct($obj){

        /* this exist to make the referencing of
        passed objects & variables easier */

        while($n = each($this)){
            $key = $n['key'];
            if(isset($obj->$key) AND !$this->$key){
                $this->$key = $obj->$key;
            }
        }

        $this->initKeyValueStorage();

        if($this->bottom_menu_id){
            $this->bottom_menu_json = true;
        }

        $this->permanameCache();

    }

    /* this will get the lookup table for permaname => actionid
        its using cache, because not all init functions populate this array.
        Lookup table is updated upon listbranches only.
     */

    public function permanameCache(){
        $cachename = 'permaname-cache-'.$this->gid;
        // Appcaching::removeGlobalCache( $cachename );
        $cache = Appcaching::getGlobalCache($cachename);

        if(!empty($cache)){
            $this->permanames = $cache;
        }
    }

    public function saveVariables($exclude=false){
        ArticleModel::saveVariables($this->submitvariables,$this->playid,$exclude);
        $this->loadVariableContent();
        return true;
    }

    public function copyVariable($from,$to){
        AeplayVariable::updateWithName($this->playid,$to,$this->getSavedVariable($from),$this->gid,$this->userid);
        $this->loadVariableContent(true);
    }

    public function initKeyValueStorage(){
        if(isset($this->appkeyvaluestorage) AND $this->appkeyvaluestorage->game_id){
            return true;
        }

        /* user-specific values ( extended storage ) */
        $this->playdatastorage = new AeplayDatastorage();
        $this->playdatastorage->play_id = $this->playid;

        /* user-specific values */
        $this->playkeyvaluestorage = new AeplayKeyvaluestorage();
        $this->playkeyvaluestorage->play_id = $this->playid;

        /* app specific values */
        $this->appkeyvaluestorage = new AegameKeyvaluestorage();
        $this->appkeyvaluestorage->game_id = $this->gid;
    }


    /* this function treats variable as a json list where it removes a value if it exists */

    public function removeFromVariable($variable,$value){
        $var = $this->getSavedVariable($variable);

        if($var){
            $var = json_decode($var,true);
            if(is_array($var) AND !empty($var)){
                if(in_array($value,$var)){
                    $key = array_search($value,$var);
                    unset($var[$key]);
                    $var = json_encode($var);
                    $this->saveVariable($variable,$var);
                } else {
                    return false;
                }
            }
        }
        
    }


    /* this function treats variable as a json list where it adds a value
        note that if variable includes a string, it will overwrite it */

    public function addToVariable($variable,$value){

        $var = $this->getSavedVariable($variable);

        if($var){
            $var = json_decode($var,true);
            if(is_array($var) AND !empty($var)){
                if(in_array($value,$var)){
                    return false;
                } else {
                    array_push($var,$value);
                }
            }
        }

        if(!is_array($var) OR empty($var)){
            $var = array();
            array_push($var,$value);
        }

        $var = json_encode($var);
        $this->saveVariable($variable,$var);


    }

    /* by default, this */
    public function updateMsgCount(){
        print_r($this->appinfo);die();

    }

    public function saveVariable($variable,$value){
        if ( !is_numeric($variable) ) {
            $varid = $this->getVariableId($variable);

            if(!$varid AND $value){
                $new = new Aevariable;
                $new->game_id = $this->gid;
                $new->name = $variable;
                $new->insert();
                $varid = $new->getPrimaryKey();
            }
        } else {
            $varid = $variable;
        }

        AeplayVariable::updateWithId($this->playid,$varid,$value);
        $this->loadVariableContent(true);
    }

    public function saveRemoteVariable( $variablename, $value, $playid ){
        AeplayVariable::updateWithName( $playid, $variablename, $value, $this->gid );
        $this->loadVariableContent(true);
    }

    public function deleteVariable($variablename){
        AeplayVariable::deleteWithName($this->playid,$variablename,$this->gid);
        $this->loadVariableContent(true);
    }

    public function loadVariables(){
        $this->vars = ArticleModel::getVariables($this->gid);
    }

    public function loadVariableContent($force=false){
        $this->varcontent = ArticleModel::getVariableContent($this->playid);
    }

    public function saveTags($temp_variable_name,$savevariable_name){
        foreach($this->submitvariables as $key=>$val){
            if(stristr($key,$temp_variable_name.'_')){
                $id = str_replace($temp_variable_name.'_','',$key);
                $savearray[$id] = $val;
            }
        }

        if(isset($savearray)){
            $this->saveVariable($savevariable_name,json_encode($savearray));
        }
    }

    public function setBranchList($list){
        if(!empty($list)){
            foreach($list as $item){
                $this->available_branches[$item] = true;
                Appcaching::setPlayCache('branchlist-article',$this->available_branches,$this->playid,$this->gid);
            }
        }
    }

    public function loadBranchList(){
        if(empty($this->available_branches)){
            $list = Apibranches::getActiveBranches($this->playid,$this->gid,$this->api_version,$this->query,$this->userid);
            $this->setBranchList($list);
        }
    }

    public function logout(){
        $this->saveVariable('logged_in','0');
        $this->saveVariable('fb_universal_login','0');
        $this->deleteVariable('instagram_token');
        $this->deleteVariable('instagram_temp_token');
        $this->deleteVariable('twitter_token');
        $this->deleteVariable('twitter_token_secret');
        $this->deleteVariable('oauth_raw_info');
        $this->deleteVariable('access_token');
        $this->deleteVariable('refresh_token');
        if($this->getSavedVariable('login_branch_id') AND $this->getSavedVariable('register_branch_id')){
            AeplayBranch::activateBranch($this->getSavedVariable('login_branch_id'),$this->playid);
            AeplayBranch::activateBranch($this->getSavedVariable('register_branch_id'),$this->playid);
        }
    }

    public function fakePlay($force = false){

        if($this->getConfigParam('use_false_id') OR $force){
            if($this->getSavedVariable('faux_pid') AND $this->getSavedVariable('faux_gid')){
                $obtest = Aeplay::model()->findByPk($this->getSavedVariable('faux_pid'));

                if(!is_object($obtest)){
                    $this->logout();
                    $this->fake_play_error = true;
                    return false;
                } else {
                    $this->current_playid = $this->getSavedVariable('faux_pid');
                    $this->current_gid = $this->getSavedVariable('faux_gid');

                    if($this->current_gid){
                        if(is_object($this->imagesobj)){
                            $this->imagesobj->secondary_gid = $this->current_gid;
                        }
                    }

                    return true;
                }
            } else {
                //$this->logout();
                $this->fake_play_error = true;
            }
        }

        if(!$this->current_playid AND !$this->current_gid){
            $this->current_playid = $this->playid;
            $this->current_gid = $this->gid;
        }

    }


    public function initLoginModel(){

        if(isset($this->loginmodel->userid) AND $this->loginmodel->userid){
            return true;
        }

        Yii::import('application.modules.aelogic.packages.actionMobilelogin.models.*');

        $this->loginmodel = new MobileloginModel();
        $this->loginmodel->userid = $this->userid;
        $this->loginmodel->playid = $this->playid;
        $this->loginmodel->gid = $this->gid;
        $this->loginmodel->password = $this->getSavedVariable('password');
        $this->loginmodel->fbid = $this->getSavedVariable('fbid');
        $this->loginmodel->fbtoken = $this->getSavedVariable('fb_token');
        $this->loginmodel->password = $this->getSavedVariable('password');

        if($this->getConfigParam('login_branch')){
            $this->saveVariable('login_branch_id',$this->getConfigParam('login_branch'));
        }

        if($this->getConfigParam('register_branch')){
            $this->saveVariable('register_branch_id',$this->getConfigParam('register_branch'));
        }

    }


    /*
    this will simply copy an asset from actions assets to images
    directory without doing any modification. This way the image
    can be used without any preprosessing and be referenced with its
    original name. NOTE: as the state of images directory is no permanet,
    this call should be included with any method requiring the asset every time
    */


    public function copyAssetWithoutProcessing($filename){
        $target = Controller::getImagesPath($this->gid) . $filename;

        if ( file_exists($target) ) {
            return true;
        }

        foreach ($this->imagesobj->imagesearchpath as $place){
            $sourcepath = $place.$filename;
            if(file_exists($sourcepath)){
                copy($sourcepath,$target);
            }
        }
    }
    

    /* this will return image filename that the client understands
        you can feed variable, filename, or clearname

    $isvar=false,$width=640,$height=false,$crop='yes',$defaultimage=false,$debug=false
    */


    /**
     * @param $image
     * @param array $params isvar, width, height, crop, defaultimage, debug
     * @return bool
     */

    public function getImageFileName($image,$params=array()){

        $isvar = $this->addParam('isvar',$params,false);  // you can use variable id
        $actionimage = $this->addParam('actionimage',$params,false); // you can use action field name portrait_image for example

        $defaultimage = $this->addParam('defaultimage',$params,false);
        $debug = $this->addParam('debug',$params,false);
        $width = $this->addParam('imgwidth',$params,640);
        $height = $this->addParam('imgheight',$params,640);

        if($this->addParam('imgcrop',$params,false)){
            $crop = $this->addParam('imgcrop',$params,false);
        } else {
            $crop = false;
        }

        $params['crop'] = $crop;
        $params['width'] = $width;
        $params['height'] = $height;
        $params['actionid'] = $this->addParam('actionid',$params,$this->actionid);
        
        if(isset($this->branchobj->id)){
            $params['branchid'] = $this->branchobj->id;
        }

        if(isset($this->branchobj->asset_loading) AND $this->branchobj->asset_loading AND !isset($params['priority'])){
            switch($this->branchobj->asset_loading){
                case 'default':
                    break;

                case 'before_start':
                    $params['priority'] = 1;
                    break;

                case 'nopreloading':
                    $params['priority'] = 3;
                    break;

                case 'notinassetlist':
                    $params['not_to_assetlist'] = true;
                    break;

            }
        }

        if ( empty($image) ) {
            return $this->imagesobj->getAsset($defaultimage,$params);
        }

        if ($isvar === true) {
            if(isset($this->varcontent[$image])){
                $basename = basename($this->varcontent[$image]);
                // we need to rewrite the params not to include "isvar"
                return $this->getImageFileName($basename,array('imgwidth'=>$width,'imgheight'=>$height,'imgcrop'=>$crop,'debug' => $debug));
            } else {
                return $defaultimage;
            }
        } elseif($actionimage) {
            if(isset($this->configobj->$image)){
                $basename = basename($this->configobj->$image);

                return $this->getImageFileName($basename,array('imgwidth'=>$width,'imgheight'=>$height,'imgcrop'=>$crop,'debug' => $debug));
            } else {
                return $defaultimage;
            }
        } elseif(is_string($image)) {
            $file = $this->imagesobj->getAsset($image,$params);
        } else {
            return false;
        }

        if($file){
            return $file;
        } else {
            return $this->imagesobj->getAsset($defaultimage,$params);
        }

    }

    public function askMonitorRegion($region){
        if($region){
            if($this->dialogPointer($region)){
                $action = new stdClass();
                $action->action = 'monitor-region';
                $action->region = new stdClass();
                $action->monitor_inside_beacons = 1;
                $action->region->beacon_id = $this->getConfigParam('monitor_region');
                //$this->data->onload[] = $action;
                $this->sessionSet('region-monitoring-started',true);
            }

            $errors = json_decode($this->getSavedVariable('location_errors'),true);

            if(isset($errors['bluetooth']) AND $errors['bluetooth']){
                $alert = $this->getAlertBox('{#we_recommend_turning_bluetooth_on#}','error-bluetooth',true);

                if($alert){
                    $this->data->scroll[] = $alert;
                }
            }
        }
    }

    public function askLocation(){
        $pointer = 'location-';
        if($this->dialogPointer($pointer)){
            $this->data->onload[] = $this->getOnclick('location');
        }
    }

    public function askPushPermission(){

        if($this->getSavedVariable('system_source') == 'client_iphone'){
            $pointer = 'pushpermission-';

            if($this->dialogPointer($pointer)){
                $pusher = $this->getOnclick('push-permission');
                $this->data->onload[] = $pusher;
            }
        }
    }

    private function dialogPointer($key){
        $key = 'pointer-'.$key;
        $pointer = $this->sessionGet($key);

        if(!$pointer){
            $this->sessionSet($key.'-tries',1);
            $this->sessionSet($key,time());
            return true;
        }

        $tries = $this->sessionGet($key.'-tries');
        $this->sessionSet($key.'-tries',$tries+1);

        if($tries < 3){
            $this->sessionSet($key,time());
            return true;
        }

        if($pointer+600 < time()){
            $this->sessionSet($key,time());
            return true;
        }

        return false;
    }
    
    public function getSettingsTitle($title, $columnparams = false, $show_border = true){
        $output[] = $this->getText(strtoupper($title),array('style' => 'form-field-section-title'));
            
        if ( $show_border ) {
            $output[] = $this->getText('',array('height' => '1','background-color' => '#b5b5b5','margin' => '0 0 10 0'));
        }
        
        return $this->getColumn($output,$columnparams);
    }


    public function getTextFieldWithTitle($submitvarname,$title,$value,$hint=false){
        $col[] = $this->getText(strtoupper($title),array('style' => 'form-field-titletext'));
        $col[] = $this->getFieldtext($value,array('variable' => $submitvarname,'hint' => $hint,'style' => 'form-field-textfield'));
        $col[] = $this->getText('',array('style' => 'form-field-separator'));
        return $this->getColumn($col,array('style' => 'form-field-row'));
    }

    /* will swap background with one of the action's assets */
    public function configureBackground($imagefield='actionimage1'){
        if ( $this->getConfigParam( $imagefield ) ) {
            $image_file = $this->getConfigParam($imagefield);
            $this->rewriteActionField('background_image_portrait',$image_file);
        }
    }

    public function getOverriddenComponent( $class ) {

        if ( !isset($this->configobj->article_action_theme) ) {
            return false;
        }

        $has_overridden_file = false;

        $theme = $this->configobj->article_action_theme;

        $dirs = array(
            'views', 'snippets'
        );

        foreach ($dirs as $dir) {
            $source = 'application.modules.aelogic.packages.action'. $this->class .'.themes.'. $theme .'.views.'. $theme . '_' . $class;
            $path = Yii::getPathOfAlias( $source );
            if ( file_exists( $path . '.php' ) ) {
                Yii::import( $source );
                $has_overridden_file = true;
            }
        }

        if ( !$has_overridden_file ) {
            return false;
        }

        return $theme . '_' . $class;
    }

    public function returnComponent($name,$type,$content=false,$params=array()){
        $name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $name)));

        if($type == 'field') {
            $class = 'Article_View_' . $name;
        } else {
            $class = 'Article' . ucfirst($name);
        }

        $tmp_class = $this->getOverriddenComponent( $class );
        if ( $tmp_class ) {
            $class = $tmp_class;
        }

        $field = new $class($this);

        $field->type = $name;
        $field->content = $content;
        $field->options = $params;
        $field->factoryobj = $this;
        return $field->getTemplate();
    }

    /**
    * Views
    */

    public function setError($msg){
        $this->errorMsgs[] = $this->getError($msg);
    }

    public function addToDebug($msg){
        $this->debugMsgs[] = $msg;
        

    }

    public function runLogic(){
        $controller = new LogicController(__FILE__,__LINE__ .'(runLazyLogic');
        $controller->branchlist = Apibranches::getActiveBranches($this->playid,$this->gid,$this->api_version,$this->query,$this->userid);
        $controller->onlyplay_id = $this->playid;
        $controller->runLogic();
    }


    /* generates a unique chat id between two users */
    public function getTwoWayChatId($id,$playid=false){

        if(!$playid){
            $playid = $this->playid;
        }

        if($id < $playid){
            $chatid = $id.'-chat-'.$playid;
        } else {
            $chatid = $playid.'-chat-'.$id;
        }

        return $chatid;

    }

    /* setters and getters should go here */


    public function getClickParam($param){
        if(isset($this->click_parameters_saved[$param])){
            return $this->click_parameters_saved[$param];
        } else {
            return false;
        }
    }

    public function requireConfigParam($param,$customerror=false){
        if(isset($this->configobj->$param)){
            return $this->configobj->$param;
        } else {
            if($customerror){
                $this->setError($customerror);
            } else {
                $this->setError('Parameter '.$param .' not configured');
            }
            return false;
        }
    }

    public function getConfigParam($param,$default=false){

        if (isset($this->configobj->$param)) {
            return $this->configobj->$param;
        } elseif ($default) {
            return $default;
        }
        
        return false;
    }

    public function collectPushPermission(){
        $cachename = $this->playid.$this->userid.'-notify';
        $updated = Appcaching::getGlobalCache($cachename);

        if(!$updated OR !$this->getSavedVariable('perm_push')){
            $onclick = new stdClass();
            $onclick->action = 'push-permission';
            Appcaching::setGlobalCache($cachename,true,640);
            return $onclick;
        } else {
            return false;
        }

    }

    public function updateLocation($interval){

        $updated = $this->getSavedVariable('location_update');

        if($updated+$interval < time()){
            $onclick = new stdClass();
            $onclick->action = 'ask-location';
            $this->saveVariable('location_update',time());
            return $onclick;
        }

        return false;
    }

    public function getOnclickTabAndSave($idname,$id,$tabnumber=2,$back_button=true,$tab_change_parameters=false){
        $params['id'] = $idname;
        $params['params'][$idname] = $id;
        $params['save_async'] = true;

        $onclick[] = $this->getOnclick('submit',false,$params);
        $onclick[] = $this->getOnclick('tab'.$tabnumber,$back_button,$tab_change_parameters);

        return $onclick;
    }

    /**
    * Returns all necessary "events" for a proper redirect from one action to another
    * 
    * @param integer $action_id - the action, where we would redirect the user
    * 
    * @return array of events
    */
    public function getRedirect( $action_id ) {
        $reset_onload = $this->getOnclick( 'id', false, 'reset-form' );
        $onload = $this->getOnclick( 'action', false, $action_id );
        return array( $reset_onload, $onload );
    }

    public function getAppUrl($actionid,$menuid){
        $from = isset($this->appinfo->name) ? $this->appinfo->name : 'Appzio';

        $config = json_decode($this->mobilesettings->config_main,true);
        if(isset($config['app_url'])){
            $appurl = $config['app_url'];
        } else {
            $appurl = $from;
        }

        $link = $appurl .'://';

        if($actionid){
            $link .= 'action_id?'.$actionid;
        }

        if($menuid){
            $link .= '&menuid='.$menuid;
        }

        return $link;
    }

    public function getOnclick($case='tab1',$back=false,$param=false){
        $onclick = new StdClass();

        switch($case){
            case 'tab1':
                $onclick->action = 'open-tab';
                $onclick->action_config = '1';
                break;

            case 'tab2':
                $onclick->action = 'open-tab';
                $onclick->action_config = '2';
                break;

            case 'tab3':
                $onclick->action = 'open-tab';
                $onclick->action_config = '3';
                break;

            case 'tab4':
                $onclick->action = 'open-tab';
                $onclick->action_config = '4';
                break;

            case 'complete-action':
                $onclick->action = 'complete-action';
                break;

            case 'list-branches':
                $onclick->action = 'list-branches';
                break;

            case 'action':
                $onclick->action = 'open-action';
                $onclick->action_config = $param;
                break;

            case 'push-permission':
                $onclick->action = 'push-permission';
                break;

            case 'go-home':
                $onclick->action = 'go-home';
                break;

            case 'close-popup':
                $onclick->action = 'close-popup';
                break;

            case 'location':
                $onclick->action = 'ask-location';
                break;

            case 'id':
                $onclick->action = 'submit-form-content';
                $onclick->id = $param;
                break;

            case 'url':
                $onclick->action = 'open-url';
                $onclick->action_config = $param;
                break;
            
            /* this is a special case where we can save also id's or some other info with the request */
            case 'submit':
                $identifier = md5(serialize($param));

                if(isset($param['params'])){
                    if(isset($param['async_save']) AND $param['async_save']) {
                        $this->sessionSetArray($param['params']);
                        $param['saving_async'] = 1;
                    }

                    if(isset($param['sync_save']) AND $param['sync_save']) {
                        $this->sessionSetArray($param['params']);
                    }

                    $this->click_parameters_to_save[$identifier] = $param;
                }

                $onclick->action = 'submit-form-content';
                $onclick->id = $identifier;
                break;

        }

        if($back){
            $onclick->back_button = 1;
        }

        return $onclick;

    }

    public function sessionSetArray($array){
        if(is_array($array) AND !empty($array)){
            foreach($array as $key=>$value) {
                $this->sessionSet($key,$value);
            }
        }
    }

    public function sessionSet($key,$value){
        $this->to_session_storage[$key] = $value;
    }

    public function sessionGet($key){
        if(isset($this->session_storage[$key])){
            return $this->session_storage[$key];
        } elseif(isset($this->to_session_storage[$key])) {
            return $this->to_session_storage[$key];
        } else {
            return false;
        }
    }

    public function getVariableId($varname){
        if(isset($this->vars[$varname])){
            return $this->vars[$varname];
        } else {
            return false;
        }
    }

    /* collects location once */
    public function getCollectLocation(){
        $cache = Appcaching::getGlobalCache('location-asked'.$this->playid);

        if(!$cache){
            $menu2 = new StdClass();
            $menu2->action = 'ask-location';
            Appcaching::setGlobalCache('location-asked'.$this->playid,true);
            return $menu2;
        } else {
            return false;
        }
    }

    public function getFullPageLoader($color='#000000',$text=false){
        $color = $color ? $color : '#000000';
        $text = $text ? $text : '{#loading#}';
        $col[] = $this->getSpacer('80');
        $col[] = $this->getLoader('',array('color' => $color));
        $col[] = $this->getText($text,array('style' => 'loader-text'));
        return $this->getColumn($col,array('text-align' => 'center','width' => '100%','align' => 'center'));
    }

    public function getVariable($varid){
        if(isset($this->submitvariables[$varid]) AND $this->submitvariables[$varid]){
            return $this->submitvariables[$varid];
        } elseif(isset($this->varcontent[$varid]) AND $this->varcontent[$varid]) {
            return $this->varcontent[$varid];
        } elseif(isset($this->varcontent_byid[$varid]) AND $this->varcontent_byid[$varid]) {
            return $this->varcontent_byid[$varid];
        } elseif(isset($this->varcontent[$varid])) {
            return false;
        }
    }


    /* returns mapping between permanent name & action id */
    public function getActionidByPermaname($name){
        if(isset($this->permanames[$name])){
            return $this->permanames[$name];
        }

        return false;
    }

    public function validateEmail($email){
        $validator = new CEmailValidator;
        $validator->checkMX = true;

        if($email) {
            $email = rtrim( $email );
            if ($validator->validateValue($email)) {
                return 1;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function validateWebsite($url){
        if(strlen($url) < 4){
            return false;
        }

        if(!stristr($this->getSavedVariable('website'),'http')){
            $url = 'http://'.$url;
        }

        $url = parse_url($url);
        if (!isset($url["host"])) return false;
        return !(gethostbyname($url["host"]) == $url["host"]);
    }


    public function getSubmitVariable($varid){
        if(isset($this->submitvariables[$varid])){
            return $this->submitvariables[$varid];
        } else {

            $id = $this->getVariableId($varid);

            if(isset($this->submitvariables[$id])){
                return $this->submitvariables[$id];
            }

            return false;
        }
    }

    public function initMobileMatching($otheruserid=false,$debug=false){

        // if(isset($this->mobilematchingobj->playid_thisuser) AND $this->mobilematchingobj->playid_thisuser){
        //     return true;
        // }

        Yii::import('application.modules.aelogic.packages.actionMobilematching.models.*');

        if($debug){
        }

        $this->mobilematchingobj = new MobilematchingModel();
        $this->mobilematchingobj->playid_thisuser = $this->playid;
        $this->mobilematchingobj->playid_otheruser = $otheruserid;
        $this->mobilematchingobj->gid = $this->gid;
        $this->mobilematchingobj->actionid = $this->actionid;
        $this->mobilematchingobj->uservars = $this->varcontent;
        $this->mobilematchingobj->initMatching($otheruserid,true);
    }

    public function initMobileChat( $context, $context_key, $otheruserid = false, $chat_id = 0 ){

        if(isset($this->mobilechatobj->current_chat_id) AND $this->mobilechatobj->current_chat_id){
            return true;
        }
        Yii::import('application.modules.aechat.models.*');

        $this->mobilechatobj = new Aechat();
        $this->mobilechatobj->play_id = $this->playid;
        $this->mobilechatobj->gid = $this->gid;
        $this->mobilechatobj->game_id = $this->gid;
        $this->mobilechatobj->context = $context;
        $this->mobilechatobj->context_key = $context_key;
        $this->mobilechatobj->uservars = $this->varcontent;
        
        // If Chat ID is provided, users would be able to only preview the chat
        if ( $chat_id ) {
            $this->mobilechatobj->current_chat_id = $chat_id;
        } else {
            // $this->mobilechatobj->actionid = $this->actionid;
            // $this->mobilechatobj->playid_otheruser = $otheruserid;

            $this->mobilechatobj->initChat();
        }
        
    }

    public function getSubmittedVariableByName($varname,$default=false)
    {

        if (isset($this->submitvariables[$this->getVariableId($varname)])) {
            return $this->submitvariables[$this->getVariableId($varname)];
        } elseif(isset($this->submitvariables[$varname])){
            return $this->submitvariables[$varname];
        } elseif ($default) {
            return $default;
        }

        return false;
    }



    public function getSavedVariable($varname,$default=false){

        if (isset($this->varcontent[$varname])) {
            return $this->varcontent[$varname];
        } elseif ($default) {
            return $default;
        }

        return false;
    }



    public function requireVariable($varname,$customerror=false){
        if(isset($this->submitvariables[$varname])){
            return $this->submitvariables[$varname];
        } elseif(isset($this->varcontent[$varname])) {
            return $this->varcontent[$varname];
        } else {
            if($customerror){
                $this->setError($customerror);
            } else {
                $this->setError('Variable '.$varname .' not found');
            }
            return false;
        }
    }


    /* components go here */
    public function getBottomMenu(){
        return $this->returnComponent('bottommenu','module');
    }

    public function getBottomNotifications($count){
        return $this->returnComponent('bottomnotifications','module',$count);
    }

    public function getText($content,$params=array()){
        /* you can configure the needed params here */
        return $this->returnComponent('text','field',$content,$params);
    }

    public function getStatisticsBox($content,$params=array()){
        /* you can configure the needed params here */
        return $this->returnComponent('statisticsbox','field',$content,$params);
    }


    public function getSwipeNavi($totalcount,$currentitem,$params){
        /* you can configure the needed params here */
        $params['totalcount'] = $totalcount;
        $params['currentitem'] = $currentitem;
        return $this->returnComponent('swipenavi','field','',$params);
    }

    /* @normally used */
    public function getSelectorListField($params){
        $params['mode'] = 'field';
        return $this->returnComponent('selectorlist','module',false,$params);
    }

    public function getSelectorListing($params){
        $params['mode'] = 'list';
        return $this->returnComponent('selectorlist','module',false,$params);
    }

    public function formkitTags($title,$items,$params=false,$error=false){
        $params['title'] = $title;
        $params['items'] = $items;
        $params['error'] = $error;
        return $this->returnComponent('formkittags','field','',$params);
    }

    public function formkitDate(string $title,string $variable,$params=false,$error=false){
        $params['title'] = $title;
        $params['error'] = $error;
        $params['variable'] = $variable;
        return $this->returnComponent('formkitdate','field','',$params);
    }

    public function formkitCheckboxes($title,$items,$params=array(),$error=''){
        $params['title'] = $title;
        $params['items'] = $items;
        $params['error'] = $error;
        return $this->returnComponent('formkitradiobuttons','field','',$params);
    }

    public function formkitRadiobuttons($title,$items,$params=false,$error=false){
        $params['title'] = $title;
        $params['items'] = $items;
        $params['error'] = $error;
        return $this->returnComponent('formkitradiobuttons','field','',$params);
    }

    public function formkitTitle($title){
        $params['title'] = $title;
        return $this->returnComponent('formkittitle','field','',$params);
    }

    public function formkitCheckbox($variable,$title,$params=false,$error=false){
        $params['title'] = $title;
        $params['variable'] = $variable;
        $params['error'] = $error;
        return $this->returnComponent('formkitcheckbox','field','',$params);
    }

    public function formkitField($variable,$title,$hint,$type=false,$error=false,$value=false){
        $params['title'] = $title;
        $params['hint'] = $hint;
        $params['variable'] = $variable;
        $params['type'] = $type;
        $params['error'] = $error;
        return $this->returnComponent('formkitfield','field',$value,$params);
    }

    public function formkitTextarea($variable,$title,$hint,$type=false,$error=false){
        $params['title'] = $title;
        $params['hint'] = $hint;
        $params['variable'] = $variable;
        $params['type'] = $type;
        $params['error'] = $error;
        return $this->returnComponent('formkittextarea','field','',$params);
    }


    public function formkitSlider($title,$variablename,$defaultvalue,$minvalue,$maxvalue,$step){
        $params['title'] = $title;
        $params['variable'] = $variablename;
        $params['default'] = $defaultvalue;
        $params['minvalue'] = $minvalue;
        $params['maxvalue'] = $maxvalue;
        $params['step'] = $step;
        return $this->returnComponent('formkitslider','field','',$params);
    }

    public function formkitBox( $params = false ){
        return $this->returnComponent('formkitbox','field','',$params);
    }

    public function getTabs($content,$params=array(),$divider=false,$origin_tab=false){
        /* you can configure the needed params here */

        // Supported indicator modes: top / bottom / fulltab

        $params['divider'] = $divider;
        $params['content'] = $content;
        $params['params'] = $params;
        $params['origin_tab'] = $origin_tab;

        return $this->returnComponent('formkittabs','field','',$params);
    }

    public function getRoundedTabs($content,$params=array()){
        /* you can configure the needed params here */

        // Supported indicator modes: top / bottom / fulltab

        $params['content'] = $content;
        $params['params'] = $params;

        return $this->returnComponent('formkitroundtabs','field','',$params);
    }


    public function rewriteActionConfigField($field,$newcontent){
        $this->rewriteconfigs[$field] = $newcontent;
    }

    public function rewriteActionField($field,$newcontent){
        $this->rewriteactionfield[$field] = $newcontent;
    }

    public function getFieldtextarea($content,$params=array()){
        return $this->returnComponent('fieldtextarea','field',$content,$params);
    }

    public function getImage($filename,$params=array()){

        if(isset($params['use_filename']) AND $params['use_filename'] == 1){
            $file = $filename;
        } else {
            $file = $this->getImageFileName($filename,$params);
        }

        // Check if $filename is an external URL
        if ( empty($file) ) {
            if (filter_var($filename, FILTER_VALIDATE_URL) !== false) {
                $file = $filename;
            }
        }

        if($file){
            return $this->returnComponent('image','field',$file,$params);
        } else {
            return $this->getError('Image not found ');
        }
    }

    public function getMenu($content,$params=array()){
        return $this->returnComponent('menu','field',$content,$params);
    }

    public function getLoader($content,$params=array()){
        return $this->returnComponent('loader','field',$content,$params);
    }

    public function getBanner($content,$params=array()){

        if($content == false){
            $mobilesettings = json_decode($this->mobilesettings->config_main);
            $adsense = $mobilesettings->google_adsense;
            if(!$adsense){
                return $this->getText('Advertising configution missing');
            }

            $content = $adsense;
        }

        return $this->returnComponent('banner','field',$content,$params);
    }

    public function getInterstitial($content,$params=array()){
        return $this->returnComponent('interstitial','field',$content,$params);
    }

    public function getRow($content,$params=array()){
        return $this->returnComponent('row','field',$content,$params);
    }

    public function getColumn($content,$params=array()){
        return $this->returnComponent('column','field',$content,$params);
    }

    public function getFieldupload($content,$params=array()){
        return $this->returnComponent('fieldupload','field',$content,$params);
    }

    public function getFieldtext($content,$params=array()){
        return $this->returnComponent('fieldtext','field',$content,$params);
    }


    /* this will produce a simple alert box with default styles
        user has an option to close it and it will not be shown again
    */

    public function getAlertBox($content,$id,$show_only_once=false){

        $params['id'] = $id;
        $params['content'] = $content;
        $params['show_only_once'] = $show_only_once;

        return $this->returnComponent('alertbox','field',$content,$params);
    }

    public function getListOfCities(){
        $path = Yii::getPathOfAlias('application.modules.aelogic.packages.actionMobileregister2.sql');
        $file = $path .'/countriesToCities.json';
        $cities = file_get_contents($file);
        $cities = json_decode($cities,true);
        return $cities;
    }

    public function getCountryCodes(){
        $path = Yii::getPathOfAlias('application.modules.aelogic.packages.actionMobileregister2.sql');
        $file = $path .'/countrycodes.json';
        $cities = file_get_contents($file);
        $cities = json_decode($cities,true);
        $output = array();

        foreach ($cities['countries'] as $country){
            $name = $country['name'];
            $output[$name] = $country['code'];
        }

        return $output;
    }

    /* note: you need to determine elsewhere whether this list needs to be updated */
    public function getNearbyCities($force_update=false){

        if(!$this->getSavedVariable('lat') OR !$this->getSavedVariable('lon')){
            return array();
        }

        $cities = json_decode($this->getSavedVariable('nearby_cities'),true);

        if(!$cities OR !is_array($cities) OR $force_update){
            $cities = ThirdpartyServices::getNearbyCities($this->getSavedVariable('lat'),$this->getSavedVariable('lon'),$this->gid);
            $this->saveVariable('nearby_cities',json_encode($cities));
        }

        if(!is_array($cities)){
            return array();
        }

        return $cities;
    }


    public function getCheckbox($varname, $title, $error = false, $params = false){
        $styles = array(
            'width' => '120',
            'text-align' => 'left'
        );

        if ( !empty($params) ) {
            $styles = array_merge($styles, $params);
        }

        $columns[] = $this->getText($title, $styles);
        $columns[] = $this->getFieldonoff($this->getSavedVariable($varname),array(
            'value' => $this->getSavedVariable($varname),
            'variable' => $this->getVariableId($varname))
        );

        if ($error) {
            $row[] = $this->getRow($columns, array('margin' => '5 10 5 30'));
            $row[] = $this->getText($error, array( 'style' => 'register-text-step-error'));
            return $this->getColumn($row);
        } else {
            return $this->getRow($columns, array('margin' => '5 10 5 30'));
        }
    }


    /* little helper function for generating a field with an icon */
    public function getPhoneNumberField($image='phone-icon-register.png',$id,$fieldname,$error=false,$type='text',$submit_menu_id=false,$textfield_inputtype=false){

        $params['style'] = ( $error ? 'field-with-icon-error-phone' : 'field-with-icon-phone' );

        $class = ( $error ? 'field-icon-column-right-error' : 'field-icon-column-right' );

        $countrycodes = $this->getCountryCodes();
        $mycountry = $this->getSavedVariable('country');

        if(stristr($this->menuid,'countryselected_')){
            $mycountry = str_replace('countryselected_','',$this->menuid);
        }

        if($mycountry AND isset($countrycodes[$mycountry])){
            $mycountrycode = $countrycodes[$mycountry];
        } else {
            $mycountrycode = '+44';
        }

        $textfieldparams['submit_menu_id'] = $submit_menu_id;
        $textfieldparams['style'] = 'phone_register_field_number';
        $textfieldparams['hint'] = $fieldname;
        $textfieldparams['id'] = $id;
        $textfieldparams['variable'] = $id;
        $textfieldparams['input_type'] = 'number';

        $data[] = $this->getColumn(array($this->getImage( $image )), array( 'style' => 'field-icon-column-left' ));

        $var = $this->getSubmitVariable($id) ? $this->getSubmitVariable($id) : $this->getVariable($id);

        $onclick1 = new stdClass();
        $onclick1->action = 'submit-form-content';
        $onclick1->id = 'save-variables';

        $onclick2 = new stdClass();
        $onclick2->action = 'open-tab';
        $onclick2->action_config = '2';

        $data[] = $this->getText($mycountrycode,array('style' => 'phone_register_field_country','onclick' => array($onclick1,$onclick2)));
        $data[] = $this->getFieldtext($var,  $textfieldparams );

        $output = $this->getRow( $data, $params );

        if($error){
            $err = $this->getText($error,array( 'style' => 'register-text-step-error'));
            $output = $this->getColumn(array($output,$err));
        }

        return $output;
    }




    /* little helper function for generating a field with an icon */
    public function getFieldWithIcon($image='icon_email.png',$id,$fieldname,$error=false,$type='text',$submit_menu_id=false,$textfield_inputtype=false){

        $params['style'] = ( $error ? 'field-with-icon-error' : 'field-with-icon' );

        $class = ( $error ? 'field-icon-column-right-error' : 'field-icon-column-right' );

        $textfieldparams['submit_menu_id'] = $submit_menu_id;
        $textfieldparams['style'] = 'register_field';
        $textfieldparams['hint'] = $fieldname;
        $textfieldparams['id'] = $id;
        $textfieldparams['variable'] = $id;

        if($textfield_inputtype){
            $textfieldparams['input_type'] = $textfield_inputtype;
        }

        $column_image = $this->getColumn(array(
            $this->getImage( $image )
        ), array( 'style' => 'field-icon-column-left' ));

        if ( $type == 'password' ) {
            $column_data = $this->getColumn(array(
                $this->getFieldPassword('', array( 'style' => 'register_field', 'hint' => $fieldname, 'id' => $id, 'variable' => $id, 'submit_menu_id' => $submit_menu_id )),
            ), array( 'style' => $class ));
        } else {
            $var = $this->getSubmitVariable($id) ? $this->getSubmitVariable($id) : $this->getVariable($id);
            $column_data = $this->getColumn(array(
                $this->getFieldtext(rtrim($var),  $textfieldparams),
            ), array( 'style' => $class,'submit_menu_id' => $submit_menu_id ));
        }

        $data = array(
            $column_image, $column_data
        );

        $output = $this->getRow( $data, $params );

        if($error){
            $err = $this->getText($error,array( 'style' => 'register-text-step-error'));
            $output = $this->getColumn(array($output,$err));
        }

        return $output;
    }

    public function controlRefreshAction(){
        $onload['action'] = 'submit-form-content';
        return $onload;
    }

    public function getFieldPassword($content,$params=array()){
        return $this->returnComponent('fieldpassword','field',$content,$params);
    }

    public function getFieldonoff($content,$params=array()){
        return $this->returnComponent('fieldonoff','field',$content,$params);
    }

    public function getFieldlist($content,$params=array()){
        return $this->returnComponent('fieldlist','field',$content,$params);
    }

    public function getMusic($content,$params=array()){
        return $this->returnComponent('music','field',$content,$params);
    }

    public function getVideo($content,$params=array()){
        return $this->returnComponent('video','field',$content,$params);
    }

    public function getProgress($content,$params=array()){
        return $this->returnComponent('progress','field',$content,$params);
    }

    public function getTimer($content,$params=array()){
        return $this->returnComponent('timer','field',$content,$params);
    }

    public function getSwipearea($content,$params=array()){
        return $this->returnComponent('swipearea','field',$content,$params);
    }

    public function getSwipestack($content,$params=array()){
        return $this->returnComponent('swipestack','field',$content,$params);
    }

    public function getRangeslider($content,$params=array()){
        return $this->returnComponent('rangeslider','field',$content,$params);
    }

    public function getInfinitescroll($content,$params=array()){
        return $this->returnComponent('infinitescroll','field',$content,$params);
    }

    public function getHtml($content,$params=array()){
        return $this->returnComponent('html','field',$content,$params);
    }

    public function getHtmltext($content,$params=array()){
        return $this->returnComponent('html','field',$content,$params);
    }

    public function getRefresh($content,$params=array()){
        $params['style'] = $this->addParam('style',$params,'recipe_chat_nocomments');
        $output[] = $this->getImagebutton('refresh.png','667',false,array('style' => 'refresh_menu'));
        $output[] = $this->getText($content,array('style' => 'chat_load_more'));
        return $output;
    }

    public function getCustomTopBar($content,$params=array()){
        $params['colors'] = $this->colors;
        return $this->returnComponent('topbar','field',$content,$params);
    }

    public function getCompleteAction(){
        $out = new StdClass();
        $out->action = 'complete-action';
        return $out;
    }


    public function getSpacer($height,$params=array()){
        $params['height'] = $height;
        return $this->returnComponent('text','field','',$params);
    }

    public function getVerticalSpacer($width,$params=array()){
        $params['width'] = $width;
        return $this->returnComponent('text','field','',$params);
    }

    public function getFooterButton($content,$params=array()){
        $params['colors'] = $this->addParam('colors',$params,$this->colors);
        return $this->returnComponent('button','field',$content,$params);
    }

    public function getButton($content,$params=array()){
        $params['colors'] = $this->addParam('colors',$params,$this->colors);
        return $this->returnComponent('button','field',$content,$params);
    }

    public function getHairline($color, $params=array()){
        $params['height'] = '1';
        $params['width'] = '100%';
        $params['background-color'] = $color;
        return $this->returnComponent('text','field','',$params);
    }

    public function getTextbutton($content,$params=array()){
        $params['colors'] = $this->addParam('colors',$params,$this->colors);
        return $this->returnComponent('textbutton','field',$content,$params);
    }

    public function getFacebookSignInButton($id,$submitmenu=false){
        $styles = array( 'style' => 'fbbutton_text_style' );

        if($submitmenu){
            return $this->getButtonWithIcon('f-icon.png', $id, '{#sign_in_with_facebook#}', array('style' => 'facebook_button_style'), $styles);
        } else {
            return $this->getButtonWithIcon('f-icon.png', $id, '{#sign_in_with_facebook#}', array('style' => 'facebook_button_style','action' => 'fb-login','sync_open' => true), $styles);
        }
    }

    public function getGoogleSignInButton($id,$submitmenu=false){
        $styles = array( 'style' => 'fbbutton_text_style' );

        if($submitmenu){
            return $this->getButtonWithIcon('g-icon.png', $id, '{#sign_in_with_google#}', array('style' => 'google_button_style'), $styles);
        } else {
            return $this->getButtonWithIcon('g-icon.png', $id, '{#sign_in_with_google#}', array('style' => 'google_button_style','action' => 'google-login','sync_open' => true), $styles);
        }
    }


    public function getInstagramSignInButton($actionid){

        $onclick1 = new StdClass();
        $onclick1->action = 'submit-form-content';
        $onclick1->id = 'show-loader';

        $onclick2 = new StdClass();
        $onclick2->id = 'insta';
        $onclick2->action = 'open-action';
        $onclick2->action_config = $actionid;
        $onclick2->sync_close = 1;

        return $this->getButtonWithIcon('insta-logo.png', 'insta', '{#sign_in_with_instagram#}', array('style' => 'instagram_button_style'),array('style' => 'instagram_text_style'),array($onclick1,$onclick2));
    }

    public function getTwitterSignInButton(){

        $onclick2 = new StdClass();
        $onclick2->id = 'twitter';
        $onclick2->action = 'twitter-login';
        $onclick2->sync_open = 1;

        return $this->getButtonWithIcon('twitter-icon.png', 'insta', '{#sign_in_with_twitter#}', array('style' => 'twitter_button_style'),array('style' => 'fbbutton_text_style'),$onclick2);
    }


    public function getOauthSignIn(){

        // myapp://open?action_id=12329&menuid=menuid

        $md5 = md5($this->playid .$this->gid);
        $url = $this->getConfigParam('app_link') .'://open?action_id=' .$this->getConfigParam('action_id') .'&menuid=' .$md5;

        $onclick2 = new StdClass();
        $onclick2->id = 'link';
        $onclick2->action = 'open-url';
        $onclick2->sync_open = 1;
        $onclick2->action_config = $url;

        return $onclick2;
    }




    public function getButtonWithIcon($image,$id,$text,$buttonparams=array(),$textparams=array(),$onclick=false){
        $params['priority'] = 1;
        $params['height'] = '28';
        $params['vertical-align'] = 'middle';
        $params['image'] = $this->getImageFileName($image,$params);

        if($onclick) {
            $buttonparams['onclick'] = $onclick;
        } else {
            $buttonparams['onclick'] = new StdClass();
            $buttonparams['onclick']->id = $id;
            $buttonparams['onclick']->action = $this->addParam('action',$buttonparams,'submit-form-content');
            $buttonparams['onclick']->config = $this->addParam('config',$buttonparams,'');
            $buttonparams['onclick']->sync_open = $this->addParam('sync_open',$buttonparams,'');
        }

        $img = $this->getImage($image,$params);
        if(!isset($buttonparams['style']) AND !isset($buttonparams['background-color'])){
            $buttonparams['background-color'] = $this->color_topbar;
            $buttonparams['text-align'] = 'center';
            $buttonparams['font-size'] = '14';
            $buttonparams['margin'] = '15 40 40 40';
            $buttonparams['height'] = '50';
            $buttonparams['border-radius'] = '8';
            $buttonparams['vertical-align'] = 'middle';

            $textparams['color'] = $this->colors['top_bar_text_color'];
        }


        //$column[] = $this->getColumn(array($img),array('vertical-align' => 'middle','width' => '30'));
        $column[] = $img;
        $column[] = $this->getVerticalSpacer('10');
        $column[] = $this->getText($text,$textparams);


        if($params['image']){
            return $this->getRow($column,$buttonparams);
        } else {
            return $this->getError('Image not found');
        }
    }


    public function getImagebutton($image,$id,$fallbackimage=false,$params=array()){

        if(isset($params['use_filename']) AND $params['use_filename'] == 1){
            $file = $image;
        } else {
            $file = $this->getImageFileName($image, $params);
        }

        $params['priority'] = 1;
        $params['image'] = $file;
        $params['id'] = $id;
        $params['fallbackimage'] = $this->getImageFileName($fallbackimage,$params);
        $params['action'] = $this->addParam('action',$params,'submit-form-content');
        $params['config'] = $this->addParam('config',$params,'');
        $params['style'] = $this->addParam('style',$params,'');

        if($params['image']){
            return $this->returnComponent('imagebutton','field',false,$params);
        } else {
            return $this->getError('Image not found');
        }

    }

    /**
    * Modules
    */

    public function getFacebookRegisterOrInvite(){

            $token = UserGroupsUseradmin::getFbToken($this->userid);

            if($token){
                $opts['onclick'] = new StdClass();
                $opts['onclick']->action = 'fb-invite';
                $opts['onclick']->fb_title = 'Invite!';
                $opts['onclick']->fb_message = "Check this app out!";
                return $this->getImage('fb-invite-en.png',$opts);
            } else {
                $opts['onclick'] = new StdClass();
                $opts['onclick']->action = 'fb-login';
                $opts['onclick']->fb_title = 'Connect!';
                $opts['onclick']->fb_message = "Connect with Facebook";
                return $this->getImage('fb-connect-en.png',$opts);
            }

    }

    public function moduleBookmarking($action='list',$params=array()){
        $params['action'] = $action;
        $params['bookmarkvar'] = $this->addParam('bookmarkvar',$params,'bookmarks');
        return $this->returnComponent('bookmarking','module',false,$params);
    }

    /* general accessor for snippets */
    public function getSnippet($snippet,$params=array()){
        return $this->returnComponent($snippet,'module',false,$params);
    }

    public function moduleChat($params){
        return $this->returnComponent('chat','module',false,$params);
    }
    
    public function moduleGroupChatList($params){
        return $this->returnComponent('groupchatlist','module',false,$params);
    }

    public function getError($msg){
        $params['text_color'] = '#d61b1b';
        return $this->getText($msg,$params);
    }

    public function moduleGallery($params=array()){
        return $this->returnComponent('gallery','module',false,$params);
    }

    public function moduleShoppinglist($varname='shoppinglist',$params=array()){
        return $this->returnComponent('shoppinglist','module',$varname,$params);
    }

    public function getUserInfoWithBookmark($userid=false,$dataobj=false,$narrow=false){
        $params['userid'] = $userid;
        $params['narrow'] = $narrow;
        $params['dataobj'] = $dataobj;
        return $this->returnComponent('userinfo','module',false,$params);
    }


    public function getUserPic($userid,$dimensions=100,$crop='round'){

        if($userid == false){
            $this->loadVariableContent();
            if(isset($this->varcontent['profilepic']) AND $this->varcontent['profilepic']){
                $userpic = $this->varcontent['profilepic'];
            } else {
                $userpic = 'anonymous2.png';
            }
        } else {
            $vars = AeplayVariable::getArrayOfPlayvariables($this->playid);
            if (isset($vars['profilepic']) AND $vars['profilepic']) {
                $userpic = $vars['profilepic'];
            } else {
                $userpic = 'anonymous2.png';
            }
        }

        if($userpic == '' OR !$userpic){
            $userpic = 'anonymous2.png';
        }

        $pic = $this->getImageFileName($userpic,array('defaultimage' => 'anonymous2.png'));

        if($pic){
            return $pic;
        } else {
            return $this->getError('Image not found');
        }
    }

    public function getPushPermissionMenu(){

        if($this->menuid == 'update-push-permission') {
            $this->saveVariable('push_permission_asked',1);
        } else {
            if($this->getSavedVariable('system_push_id')){ return false; }
            if($this->getSavedVariable('push_permission_asked')){ return false; }
            if($this->getSavedVariable('system_source') != 'client_iphone'){ return false; }
            $actionid = $this->getConfigParam('push_permission');

            if($actionid){
                $onclick = new StdClass();
                $onclick->action = 'open-action';
                $onclick->id = $actionid;
                $onclick->open_popup = true;
                $onclick->sync_close = true;
                $onclick->action_config = $actionid;
                $onclick->config = $actionid;
                $onclick2 = new StdClass();
                $onclick2->action = 'submit-form-content';
                $onclick2->id = 'update-push-permission';

                return array($onclick2,$onclick);
            }

        }

        return false;
    }


    /* these are semi-permanent states for either play or specific action
        if you need permanence, play variables are a way to go

        Note that these will usually persist longer than the session
    */

    public function savePlayState($name,$value){
        $name = $this->playid .$name;
        Yii::app()->setGlobalState($name,$value);
    }

    public function getPlayState($name){
        $name = $this->playid .$name;
        return Yii::app()->getGlobalState($name);
    }

    public function saveActionState($name,$value){
        $name = $this->playid .$this->actionid .$name;
        Yii::app()->setGlobalState($name,$value);
    }

    public function getActionState($name){
        $name = $this->playid .$this->actionid .$name;
        return Yii::app()->getGlobalState($name);
    }

    /*  NOTE ABOUT CACHE MANAGEMENT
       you need to manually flush caches whenever user can do something that would
       change the state of the view / action or entire app
       flushCacheApp you should no need in normal circumstances
   */

    /* invalidate cache for a particular tab (state changed) */
    public function flushCacheTab($num){
        Appcaching::flushActionTab($this->actionid,$num,$this->playid);
        $this->reloadData();
    }

    /* invalidate cache for a particular action (state changed) */
    public function flushCacheAction(){
        Appcaching::removeActionCache($this->playid,$this->actionid);
        $this->reloadData();
    }

    public function flushCacheUsersApp(){
        Appcaching::flushPlayCache($this->playid,$this->gid);
    }

    public function flushCacheApp(){
        Appcaching::flushAppCache($this->gid);
    }

    public function addParam($name,$params,$default=false){
        if(isset($params[$name])){
            return $params[$name];
        } else {
            return $default;
        }
    }

    public function reloadData(){
        $this->loadVariables();
        $this->loadVariableContent();
        $this->actionobj = AeplayAction::model()->with('aetask')->findByPk($this->actionid);
        $this->configobj = json_decode($this->actionobj->aetask->config);
    }

    public function saveAction(){
        $action = Aeaction::model()->findByPk($this->action_id);
        $action->config = json_encode($this->configobj);
        $action->update();
        $this->reloadData();
    }

    /*
    * Retrieve all variables, which belong to a certain "playid"
    * If you intend to use this method without passing a parameter,
    * you may consider referring to $this->varcontent instead
    */
    public function getPlayVariables( $playid = false ) {

        if ( empty($playid) ) {
            $playid = $this->playid;
        }

        $cache = Appcaching::getUserCache($playid,$this->gid,'playvariables');

        if ( $cache ) {
            return $cache;
        }

        $vars = AeplayVariable::getArrayOfPlayvariables($playid);
        Appcaching::setUserCache($playid,$this->gid,'playvariables',$vars);

        return $vars;
    }

    public function getLocalizedDate( $stamp, $show_time = true ) {
        $day = '{#' .date('l',$stamp) .'#}';
        $month = '{#' .date('F',$stamp) .'#}';
        $daynumber = date('j',$stamp);

        if($daynumber == 1){
            $extension = 'st';
        }elseif($daynumber == 3) {
            $extension = 'nd';
        }elseif($daynumber == 3) {
            $extension = 'rd';
        }else {
            $extension = 'th';
        }

        $time = date( 'H:i', $stamp );
        $output = $day . ', '. $daynumber . $extension . ' {#of#} ' . $month;
        if ( $show_time ) {
            $output .= ' @ ' . $time;
        }
        
        return $output;
    }





    /* depreceated */
    public function getUserVariables($userid){
        $cache = Appcaching::getUserCache($userid,$this->gid,'variables');
        
        if ( $cache ) {
            return $cache;
        }

        $vars = AeplayVariable::getArrayOfUserVariables($this->gid, $userid);
        Appcaching::setUserCache($userid,$this->gid,'variables',$vars);
        
        return $vars;
    }

}