<?php


Yii::import('application.modules.aegameauthor.models.*');
Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aelogic.article.components.views.*');
Yii::import('application.modules.aelogic.article.components.snippets.*');

class ArticleFactory {

    public $allvars = array();
    public $datafile;
    public $imagesobj;
    public $menuid;
    public $mode;
    public $actionobj;
    public $configobj;
    public $tabs = array();

    public $current_tab;

    public $menus = array();
    public $actionid;

    public $imagespath;
    public $original_images_path;
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
    public $tabsimages;

    /** @var ArticleMenuComponents */
    public $articlemenuobj;

    /** @var ArticleController */
    public $childobj;

    public $actiondata;
    public $debug;

    public $menuitems;

    public $branchobj;
    public $branchconfig;

    public $mobilesettings;

    public $action_id;
    public $users;

    public $userlist;
    public $available_branches;
    public $rerun_list_branches;

    public $color_topbar;           // shorthand for most common
    public $color_topbar_hilite;    // shorthand for most common
    public $colors;                 // all colors of the action

    public $params;
    public $fblogin;
    public $fbtoken;

    public $lang;
    public $rewriteconfigs;
    public $rewriteactionfield;

    public $referring_action = false;
    public $context = false;

    public $api_version;
    public $build_version;
    public $app_version;

    public $aspect_ratio;
    public $screen_width;
    public $screen_height;

    public $deviceparams;

    public $appinfo;

    public $localizationComponent;

    public $query;
    public $checkSumCheckParams;        // this is the original query as api has received it

    /* by settings this to true, you can have api output only msg ok
    this is used for certain async functions where we don't want the client
    to do an update of its view */
    public $no_output = false;
    public $bottom_menu_id;
    public $bottom_notifications;
    public $click_parameters_saved;
    public $model_name;

    /* key value pairs, works like a session, but uses cache (mostly redis), so can actually persist between sessions */
    public $session_storage;
    public $click_cache;
    public $recycleable_objects;
    public $incoming_recycleable_objects;
    public $chat_msgcount = false;
    public $branchdata;
    public $branch_id;

    /* the actual session is saved by api default controller, not here */
    public $session_cache_out;
    public $userinfo;
    public $is_a_view = false;

    /* @var ArticlePreprocessor */
    public $articleProcessor;

    /* this is either client_iphone or client_android */
    public $client_device;
    public $actionRootDir;

    /* gets called when object is created & fed with initial values */
    public function playInit() {

        if(!isset($this->playobj->id)){
            return false;
        }

        $this->playid = $this->playobj->id;

        /* this is generally a bad idea, as this might result and incorrect userid in theory */
        if(!$this->userid){
            $this->userid = $this->playobj->user_id;
        }

        $this->loadVariables();

        $this->articlemenuobj = new ArticleMenuComponents($this);
        $this->articlemenuobj->imagesobj = $this->imagesobj;

        $this->articleProcessor = new ArticlePreprocessor();
        $this->articleProcessor->gid = $this->gid;
        $this->articleProcessor->playid = $this->playid;
        $this->articleProcessor->mobileobj = $this->mobilesettings;
        $this->articleProcessor->playobj = $this->playobj;

        $menuitems = Aenavigation::getAllAppMenuItems($this->gid);

        foreach($menuitems as $nav){
            $this->tabs[$nav['itemid']] = $nav['item_safe_name'];
            $this->menus[$nav['menu_safe_name']] = $nav['menuid'];
            $this->menuitems[$nav['item_safe_name']] = $nav['itemid'];
        }

        $cachename = $this->playid.'-' .$this->userid.'-playcache';
        $this->session_storage = Appcaching::getGlobalCache($cachename);
        $this->updateChatCount();

        /* note: this has been changed from having client providing it every call,
        which can potentially lead to out-of-sync issue in regards to facebook logged in status */
        if(isset($this->params['fb_login'])){
            $this->fblogin = $this->params['fb_login'];
            if($this->fblogin == "false" OR $this->fblogin == false){
                $this->fblogin = 0;
            }
        }elseif(isset($this->varcontent['fb_login'])){
            $this->fblogin = $this->varcontent['fb_login'];
            if($this->fblogin == "false" OR $this->fblogin == false){
                $this->fblogin = 0;
            }
        } else {
            $this->fblogin = false;
        }

    }

    public function saveViewStyles(){
        $this->articleProcessor->saveStyles($this->playobj);
    }

    private function updateChatCount(){

        $conf = json_decode($this->appinfo->visual_config_params);

        if(isset($conf->bottom_notifications) AND $conf->bottom_notifications){
            Yii::import('application.modules.aechat.models.*');

            $chat = new Aechat();
            $chat->play_id = $this->playid;
            $chat->gid = $this->gid;
            $chat->game_id = $this->gid;
            $this->chat_msgcount = $chat->getUsersUnreadCount();
        }

    }

    /* its possible to link parameters to a click, the parameters are decoded here */
    public function setCurrentMenuId(){
        $menuid = $this->getParam('menuid',$this->submit);
        $cache = Appcaching::getGlobalCache($this->playid.'menuparams');
        $this->click_cache = $cache;

        if(strlen($menuid) == 32){

            if(isset($cache[$menuid]['id'])){
                $this->menuid = $cache[$menuid]['id'];
                if(isset($cache[$menuid]['params'])){
                    $this->click_parameters_saved = $cache[$menuid]['params'];
                }
            }
        }

        if(!$this->menuid){
            $this->menuid = $this->getParam('menuid',$this->submit);
        }

    }

    public function clickSavers(){

        /* atsave function can be added to model to handle the input saving. If it exists, api will output only ok. ie. its saved for async */
        if($this->model_name AND $this->menuid){
            if(method_exists($this->model_name,'atsave'.ucfirst($this->menuid))){
                $function = 'atsave'.$this->menuid;
                $class = $this->model_name;
                $class::$function($this);
                return false;
            }
        }

        $cachename = $this->getParam('menuid',$this->submit);
        if(isset($this->click_cache[$cachename]['save_async'])){
            if(isset($this->click_cache[$cachename]['params'])){
                $this->sessionStorageSaver($this->click_cache[$cachename]['params']);
            }
            return false;
        }

        return true;

    }

    public function sessionStorageSaver($data=array()){
        if(is_array($this->session_storage)){
            $this->session_storage = array_merge($this->session_storage,$data);
        } else {
            $this->session_storage = $data;
        }
    }

    public function actionInit(){

        $this->current_tab = $this->getParam('tabid',$this->submit) ? $this->getParam('tabid',$this->submit) : 1;
        $this->setCurrentMenuId();
        $vars = $this->getParam('variables',$this->submit);

        $this->actionobj = (object)$this->actionobj;
        $this->branch_id = $this->actionobj->levelid;
        $this->action_id = $this->actionobj->taskid;
        $this->actionid = $this->actionobj->playtaskid;

        /*      if(!isset($this->actionobj->action_id)){
            return false;
        }*/

        if(isset($this->branchdata[$this->branch_id])){
            $this->branchobj = (object)$this->branchdata[$this->branch_id];
        } else {
            $this->branchobj = Aebranch::model()->findByPk($this->branch_id);
        }
        
        if(isset($this->branchobj->config)){
            $this->branchconfig = @json_decode($this->branchobj->config);
        }

        if(!$this->clickSavers()){
            return false;
        }

        // makes sure module images are in right place
        if(isset($vars) AND is_array($vars)){
            foreach($vars as $var){
                $key = key($var);
                $value = $var[$key];
                $this->submitvariables[$key] = $value;
            }
        } else {
            $this->submitvariables = array();
        }

        $this->setColors();
        $this->tabMenu();
        $this->setBottomMenuId();

        if(isset($_REQUEST['referring_action'])){
            $this->referring_action = $_REQUEST['referring_action'];
        }

        if(isset($_REQUEST['context'])){
            $this->context = $_REQUEST['context'];
        }

        $this->setScreenInfo();
        $this->setVersionInfo();

        return true;
    }

    public function renderData($actiontype){

        /* if childobj returns */
        if(!$this->createChildObj($actiontype)){
            $op = new stdClass();
            $this->no_output = true;
            return $op;
        }


        if(method_exists($this->childobj,'init')){
            $this->childobj->init();
        }

        if ( isset($this->childobj->debug) AND $this->childobj->debug === true ) {
            $this->debug = true;
        }

        if(isset($this->childobj->tabsmenu_images) AND !empty($this->childobj->tabsmenu_images)){
            $this->tabsimages = $this->childobj->tabsmenu_images;
        } else {
            $this->tabsimages = array();
        }

        $op = $this->getViews();


        /* output any errors to view */
        if(!empty($this->childobj->errorMsgs)){
            if(isset($op->scroll) AND is_array($op->scroll)){
                $op->scroll = $this->childobj->errorMsgs+$op->scroll;
            } else {
                $op = new StdClass();
                $op->scroll = $this->childobj->errorMsgs;
            }
        }

        if(!empty($this->childobj->click_parameters_to_save)){
            if(is_array($this->click_cache)){
                $params = $this->click_cache + $this->childobj->click_parameters_to_save;
            } else {
                $params = $this->childobj->click_parameters_to_save;
            }

            Appcaching::setGlobalCache($this->playid.'menuparams',$params);
        }

        /* save debug to cache, so that it can be shown by the debug or delete if none is set */
        if(!empty($this->childobj->debugMsgs)){
            /* array_unshift($this->childobj->debugMsgs,$this->actionobj->name);

            $cache = Appcaching::getGlobalCache($this->playid .'-debug');

            if(is_array($cache)){
                $this->childobj->debugMsgs = $cache+$this->childobj->debugMsgs;
            }*/

            Appcaching::setGlobalCache($this->playid .'-debug',$this->childobj->debugMsgs);
        }

        if(isset($this->childobj->rerun_list_branches) AND $this->childobj->rerun_list_branches === true){
            $this->rerun_list_branches = true;
        }

        if(isset($this->childobj->rewriteconfigs)){
            $this->rewriteconfigs = $this->childobj->rewriteconfigs;
        }

        if(isset($this->childobj->rewriteactionfield)){
            $this->rewriteactionfield = $this->childobj->rewriteactionfield;
        }

        if($this->childobj->no_output){
            $this->no_output = $this->childobj->no_output;
        }

        if($this->childobj->recycleable_object_names){

            foreach($this->childobj->recycleable_object_names as $name){
                $obs[$name] = $this->childobj->$name;
            }

            if(isset($obs)){
                $this->childobj->recycleable_objects = $obs;
                $this->recycleable_objects = $obs;
            } else {
                $this->childobj->recycleable_objects = array();
            }
        }

        if(is_array($this->session_storage) AND is_array($this->childobj->session_storage)){
            $this->session_storage = array_merge($this->session_storage,$this->childobj->session_storage);
        } elseif(is_array($this->childobj->session_storage)) {
            $this->session_storage = $this->childobj->session_storage;
        } else {
            $this->session_storage = array();
        }

        return $op;
    }


    /* note: this should be called only when needed!! */
    public function initUserInfo(){

        $cache = Appcaching::getAppCache('userlist',$this->gid);

        if($cache){
            return $cache;
        }

        $out = array();
        $this->userlist = AeplayVariable::getArrayOfUserVariables($this->gid,false,'name');

        if ( empty($this->userlist) ) {
            return false;
        }

        if(is_array($this->userlist)) {

            foreach ($this->userlist as $userid => $user) {
                foreach ($user as $key => $var) {
                    if (strstr($key, 'points_')) {
                        $out[$userid]['totalpoints'] = isset($out[$userid]['totalpoints']) ? $out[$userid]['totalpoints'] + $var : $var;
                    }

                    $out[$userid][$key] = $var;
                }

                $out[$userid]['totalpoints'] = isset($out[$userid]['totalpoints']) ? $out[$userid]['totalpoints'] : 0;
                $out[$userid]['userid'] = isset($userid) ? $userid : 0;
            }

            $this->userlist = $out;
            Appcaching::setAppCache('userlist', $this->userlist, $this->gid);
        }

    }

    public function setVersionInfo() {

        if(isset($this->deviceparams['build_version'])){
            $this->build_version = $this->deviceparams['build_version'];
            $this->app_version = $this->deviceparams['app_version'];
            $this->api_version = $this->deviceparams['api_version'];
        } else {
            $this->version_build = 1;
            $this->version_app = 1;
            $this->version_api = 2.0;
        }

        if(isset($this->deviceparams['system_source'])){
            $this->client_device = $this->deviceparams['system_source'];
        } elseif(isset($this->varcontent['system_source'])){
            $this->client_device = $this->varcontent['system_source'];
        }
    }

    public function setScreenInfo() {

        if(is_string($this->deviceparams)){
            $this->deviceparams = json_decode($this->deviceparams,true);
        }

        if(isset($this->deviceparams['screen_width']) AND isset($this->deviceparams['screen_height'])){
            $this->screen_width = $this->deviceparams['screen_width'];
            $this->screen_height = $this->deviceparams['screen_height'];
        }elseif(isset($_REQUEST['screen_width']) AND isset($_REQUEST['screen_height'])){
            $this->screen_width = $_REQUEST['screen_width'];
            $this->screen_height = $_REQUEST['screen_height'];
        } elseif(isset($this->varcontent['screen_width']) AND isset($this->varcontent['screen_height']) AND $this->varcontent['screen_width'] > 0 AND $this->varcontent['screen_height'] > 0){
            $this->screen_width = $this->varcontent['screen_width'];
            $this->screen_height = $this->varcontent['screen_height'];
        } else {
            $this->screen_width = 750;
            $this->screen_height = 1136;
        }


        
        $this->aspect_ratio = round($this->screen_width / $this->screen_height,3);

        if(!isset($this->varcontent['screen_height'])){
            AeplayVariable::updateWithName($this->playid,'screen_height',$this->screen_height,$this->gid,$this->userid);
        } elseif(isset($this->varcontent['screen_height']) AND $this->varcontent['screen_height'] != $this->screen_height){
            AeplayVariable::updateWithName($this->playid,'screen_height',$this->screen_height,$this->gid,$this->userid);
        }

        if(!isset($this->varcontent['screen_width'])){
            AeplayVariable::updateWithName($this->playid,'screen_width',$this->screen_width,$this->gid,$this->userid);
        } elseif(isset($this->varcontent['screen_width']) AND $this->varcontent['screen_width'] != $this->screen_width){
            AeplayVariable::updateWithName($this->playid,'screen_width',$this->screen_width,$this->gid,$this->userid);
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
            $this->setBranchlist($list);
        }
    }


    /*

    This is the colors array

    [background_color] => #FFFFFFFF
    [top_bar_color] => #FF34A343
    [top_bar_text_color] => #FFFFFFFF
    [top_bar_icon_color] => #FFFFFFFF
    [button_color] => #FF34A343
    [button_icon_color] => #FFFFFFFF
    [button_text_color] => #FFFFFFFF
    [button_more_info_color] => #FF000000
    [button_more_info_icon_color] => #FFFFFFFF
    [button_more_info_text_color] => #FFFFFFFF
    [item_color] => #FFFFCDD2
    [item_text_color] => #FFFFFFFF
    [text_color] => #FF000000
    [icon_color] => #FF000000
    [side_menu_color] => #FF34A343
    [side_menu_text_color] => #FFFFFFFF
    [top_menu_color] => #FF34A343
    [top_menu_text_color] => #FFFFFFFF*/

    public function setColors(){

        $cachename = $this->gid.'-colors';
        $cached = Appcaching::getGlobalCache($cachename);

        if(isset($cached['time']) AND time() + 600 < $cached['time']){
            $cached = array();
        }

        if(isset($cached['actions'][$this->action_id]) AND isset($cached['actions'][$this->action_id]['background_color'])
                AND isset($cached['actions'][$this->action_id]['active'])){

            $this->color_topbar = $cached['actions'][$this->action_id]['background_color'];
            $this->color_topbar_hilite = $cached['actions'][$this->action_id]['active'];
            $this->colors = $cached['actions'][$this->action_id];
        } else {
            /* take the tab control colors from the action if not defined */
            $colors = Controller::getColors(false,false,$this->action_id);
            $background_color = Helper::normalizeColor($colors->top_bar_color);
            $background_color = substr($background_color,3);
            $colorhelp = new Color($background_color);
            $active_color = $colorhelp->darken();
            $newcolors = array();

            $colorarray['background'] = $background_color;
            $colorarray['active'] = $active_color;
            $colorarray['colors'] = $colors;

            $this->color_topbar = '#'.$background_color;
            $this->color_topbar_hilite = '#'.$active_color;

            foreach($colors as $key => $col){
                $newcolors[$key] = Helper::normalizeColor($col);
            }

            $newcolors['active'] = $this->color_topbar_hilite;
            $newcolors['background_color'] = $this->color_topbar;
            $this->colors = $newcolors;
            $cached['actions'][$this->action_id] = $newcolors;

            Appcaching::setGlobalCache($cachename,$cached,600);
        }
    }

    public function createChildObj($actiontype){
        $controller_included = false;
        $this->is_a_view = false;

        if(!$this->available_branches){
            $this->available_branches = Appcaching::getPlayCache('branchlist-article',$this->gid,$this->playid,true);
        }

        $this->class = ucfirst($actiontype);
        $cc = str_replace('Controller','',$this->class);
        $rootPath = Yii::getPathOfAlias('application.modules.aelogic.packages.action' .$cc);

        /* imports */
        Yii::import('application.modules.aelogic.packages.ActivationEngineAction');
        $dir_root = 'application.modules.aelogic.packages.action' . ucfirst($actiontype);
        $this->actionRootDir = $dir_root;

        Yii::import($dir_root . '.controllers.*');
        Yii::import($dir_root . '.models.*');
        Yii::import($dir_root . '.views.*');

        if ( isset($this->configobj->article_action_theme) AND !empty($this->configobj->article_action_theme) ) {
            $theme = $this->configobj->article_action_theme;
            $themes_dir_root = $dir_root . '.themes.' . $theme;
            Yii::import($themes_dir_root . '.controllers.*');
            Yii::import($themes_dir_root . '.views.*');
            Yii::import($themes_dir_root . '.models.*');
        } else {
            $theme = false;
            $themes_dir_root = false;
        }

        $model_alias = $dir_root .'.models.'.ucfirst($actiontype) .'Model';
        $model_file = Yii::getPathOfAlias($model_alias);

        if(file_exists($model_file .'.php')){
            $this->model_name = ucfirst($actiontype) .'Model';
        }

        /* original images */
        $this->original_images_path = Controller::getOriginalImagesPath( $this->gid );
        $this->imagesobj->imagesearchpath[] = $this->original_images_path;

        /* images */
        $this->imagespath = Yii::getPathOfAlias('application.modules.aelogic.packages.action' .$cc .'.images');
        $this->imagesobj->imagesearchpath[] = $this->imagespath .'/';

        /* theme images (needs to be first to override) */
        if ( isset($this->configobj->article_action_theme) AND !empty($this->configobj->article_action_theme) ) {
            $this->imagesobj->imagesearchpath[] = $rootPath .'/themes/' . $theme . '/images/';
        }

        /* component default images */
        $searchpath = Yii::getPathOfAlias('application.modules.aelogic.components.images');
        $this->imagesobj->imagesearchpath[] = $searchpath .'/';

        /* main inclusion code */
        $class = $this->inclusions($actiontype);


        $this->setupChecksumChecker($actiontype);

        if ( !$class ) {
            return array();
        }

        if ( !$this->actionid ) {
            $this->actionid = $this->getParam('actionid',$this->submit);
        }

        /* if action init returns false, we will return ok right away
            its used for savers that bypass lot of the initing
        */
        if(!$this->actionInit()){
            return false;
        }

        if (!isset($this->configobj) OR !is_object($this->configobj)) {
            return false;
        }

        $this->childobj = new $class($this);

        /* objects coming from Apiaction with some object content */
        if($this->incoming_recycleable_objects){

            foreach($this->incoming_recycleable_objects as $key=>$value){
                if(property_exists($this->childobj,$key)) {
                    $this->childobj->$key = $value;
                }
            }

            foreach($this->childobj->global_recycleable as $key=>$value){
                if(property_exists($this->childobj,$key)) {
                    $this->childobj->$key = $value;
                }
            }
        }

        $this->moduleAssets();

        return true;
    }

    /*
        find out the name for the controller
        be very very careful with this section, as the lookup order
        is crucial frot he functioning of the app

        the naming has been changed in June 2017

        namings & the inclusion order:

            // mode inside a theme
            [Mode][Actionname][Theme][Controller | View]

            // mode inside a main folder
            [Mode][Actionname][Controller | View]

            // main file inside a theme
            [Actionname][Theme][Controller | View]

            // main file on main folder
            [Actionname][Controller | View]

    */

    public function inclusions($actiontype){

        $class = false;

        /* mode inside a theme */
        if(isset($this->configobj->mode) AND !empty($this->configobj->mode) AND isset($this->configobj->article_action_theme) AND !empty($this->configobj->article_action_theme)) {
            $modeclass = ucfirst($this->configobj->mode) .ucfirst($actiontype) .ucfirst($this->configobj->article_action_theme) .'Controller';
            $class = $this->checkClassFile($modeclass,$this->configobj->article_action_theme);
        }

        /* start from the themes mode controller - OLD */
        if(isset($this->configobj->mode) AND !empty($this->configobj->mode) AND isset($this->configobj->article_action_theme) AND !empty($this->configobj->article_action_theme)) {
            $modeclass = $this->configobj->article_action_theme .ucfirst($this->configobj->mode) .ucfirst($actiontype);
            $class = $this->checkClassFile($modeclass,$this->configobj->article_action_theme);
        }

        /* if not found try the themes sub controller */
        if(!$class AND isset($this->configobj->article_action_theme) AND !empty($this->configobj->article_action_theme)){
            $modeclass = $this->configobj->article_action_theme.$this->class.'SubController';
            $class = $this->checkClassFile($modeclass,$this->configobj->article_action_theme);
        }


        /* mode inside a main folder */
        if(!$class AND isset($this->configobj->mode) AND !empty($this->configobj->mode) ){
            $modeclass = ucfirst($this->configobj->mode) .ucfirst($actiontype) .'Controller';
            $class = $this->checkClassFile($modeclass);
        }

        /* if not found try the modes sub controller */
        if(!$class AND isset($this->configobj->mode) AND !empty($this->configobj->mode)){
            $modeclass = $this->configobj->mode .ucfirst($actiontype).'Sub';
            $class = $this->checkClassFile($modeclass);
        }

        /* traverse to mode main controller */
        if(!$class AND isset($this->configobj->mode) AND !empty($this->configobj->mode) ){
            $modeclass = ucfirst($this->configobj->mode) .ucfirst($actiontype);
            $class = $this->checkClassFile($modeclass);
        }

        /* main file inside a theme */
        if(!$class AND isset($this->configobj->article_action_theme) AND !empty($this->configobj->article_action_theme)){
            $modeclass = ucfirst($actiontype) .ucfirst($this->configobj->article_action_theme) .'Controller';
            $class = $this->checkClassFile($modeclass,$this->configobj->article_action_theme);
        }

        /* if not found go to the main controller */
        if(!$class){
            $modeclass = $this->class.'Controller';
            $class = $this->checkClassFile($modeclass);
        }

        return $class;
    }

    /* checks whether file exists and if it does, returns the class name */
    public function checkClassFile($modeclass,$theme=false){

        $dir = $this->actionRootDir;

        if($theme){
            $dir .= '.themes.'.$theme;
        }

        $view = str_replace('Controller','View',$modeclass);
        $file = Yii::getPathOfAlias($dir.'.views.'.$view);

        if ( file_exists($file . '.php') ) {
            $this->is_a_view = true;
            return $view;
        }

        $file = Yii::getPathOfAlias($dir.'.controllers.'.$modeclass);

        if ( file_exists($file . '.php') ) {
            return $modeclass;
        }


        return false;
    }

    public function setupChecksumChecker($actiontype){
        $model = ucfirst($actiontype) . 'Model';
        $rootPath = Yii::getPathOfAlias('application.modules.aelogic.packages.action' .ucfirst($actiontype) .'.models.'.$model);

        if(file_exists($rootPath.'.php')){
            if(class_exists($model)){
                if(method_exists($model,'SetChecksumChecker')){
                    $this->checkSumCheckParams = $model::SetChecksumChecker($this->playid,$this->userid);
                }
            }
        }
    }

    public function loadVariables(){
        if(empty($this->vars)) {
            $vars = Aevariable::model()->findAllByAttributes(array('game_id' => $this->gid));

            foreach ($vars as $var) {
                $name = $var->name;
                $this->vars[$name] = $var->id;
            }
        }
    }

    private function getViews(){

        /* preloading views */

        if(method_exists($this->childobj,'tab2')){
            $output = $this->getTabsView();
        } else {
            $output = $this->getDefaultView();
        }

        return $output;
    }


    /* extracts a bottom menu */
    public function setBottomMenuId(){
        $visualconfig = json_decode($this->appinfo->visual_config_params);

        /* as we are reusing the same factory object with listbranches, this needs to be set false first */
        $this->bottom_menu_id = false;

        if(isset($this->configobj->bottom_menu_id)){

            if($this->configobj->bottom_menu_id == 'none'){
                return false;
            }

            if(is_numeric($this->configobj->bottom_menu_id) AND $this->configobj->bottom_menu_id > 0){
                $this->bottom_menu_id = $this->configobj->bottom_menu_id;
                return true;
            }
        }


        /* traversing to banch */
        if(isset($this->branchconfig->bottom_menu_id)){
            if($this->branchconfig->bottom_menu_id == 'none'){
                return false;
            }

            if(is_numeric($this->branchconfig->bottom_menu_id) AND $this->branchconfig->bottom_menu_id > 0){
                $this->bottom_menu_id = $this->branchconfig->bottom_menu_id;
                return true;
            }
        }

        /* traversing to app config*/
        if(isset($visualconfig->bottom_menu_id) AND $visualconfig->bottom_menu_id){
            if(is_numeric($visualconfig->bottom_menu_id) AND $visualconfig->bottom_menu_id > 0){
                $this->bottom_menu_id = $visualconfig->bottom_menu_id;
                return true;
            }
        }

        return false;

    }

    public function getBottomMenu($output,$id){
        echo($id);die();
        return $output;
    }

    /**
    * This method would try to retrieve the default output/response from a certain component
    */
    private function getDefaultView() {
        $output = array();

        $methods = array(
            'tab1', 'getData'
        );

        foreach ($methods as $method) {
            if ( method_exists($this->childobj, $method) ) {
                $output = call_user_func( array( $this->childobj, $method ) );
                if($this->is_a_view){
                    $output = $this->articleProcessor->process($output);
                }
                break;
            }
        }

        $output = $this->bottomNotifications($output);
        $output = $this->bottomMenu($output);

        return $output;
    }

    private function bottomNotifications($output,$key=1){

        if($this->chat_msgcount === false OR $this->chat_msgcount == 0){
            return $output;
        }

        if(!is_object($output)){
            $output = new stdClass();
        }

        $notifications_obj = $this->childobj->getBottomNotifications($this->chat_msgcount);

        // Notifications object is disabled
        if ( empty($notifications_obj) ) {
            return $output;
        }

        $bottom_notifications[] = $notifications_obj;

        if(!isset($output->footer)){
            $output->footer = $bottom_notifications;
        } else {
            $output->footer = array_merge($output->footer,$bottom_notifications);
        }

        return $output;
    }

    private function bottomMenu($output,$key=1){
        /* bottom menu which is created on article controllers init stage */
        if($this->childobj->bottom_menu_json){
            if(!is_object($output)){
                $output = new stdClass();
            }

            $bottom_menu = $this->childobj->getBottomMenu();

            if(!isset($output->footer)){
                $output->footer = $bottom_menu;
            } else {
                $output->footer = array_merge($output->footer,$bottom_menu);
            }
        }

        $output = $this->addTabJson($output,$key);
        return $output;
    }


    private function getTabsView() {
        $output = array();

        $tabs = $this->tabsimages;
        $onload = array();
        $key = 1;

        while ($key < 6) {
            $tabname = 'tab' . $key;

            /* satisfy all others from cache except for the currently active tab */
            if(method_exists($this->childobj,$tabname) ) {
                $tabcontent = $this->childobj->$tabname();

                if (isset($tabcontent->onload)) {
                    $onload = array_merge($onload, $tabcontent->onload);
                }

                if($this->validateTabFormat($tabcontent)){
                    $tabcontent = $this->bottomMenu($tabcontent,$key);
                    $tabcontent = $this->bottomNotifications($tabcontent,$key);
                    $output[$tabname] = (object)$tabcontent;
                } else {
                    $tabcontent = new stdClass();
                    $obj = new stdClass;
                    $obj->type = 'msg-plain';
                    $obj->content = 'Either header, scroll, footer or onload has a wrong data type (should be array)!';
                    $tabcontent->scroll[] = $obj;
                    $output[$tabname] = (object)$tabcontent;
                }

            }

            $key++;
        }


        if(!empty($onload)) {
            $actions = array();
            foreach ($onload as $item) {

                if (isset($item->action) AND isset($actions[$item->action])) {

                } elseif(isset($item->action)) {
                    $output['onload'][] = $item;
                    $actions[$item->action] = true;
                }

            }
        }

        if($this->debug){
           // print_r($output);die();
        }

        return $output;
    }

    /* rudimentary data type validation for segments */
    private function validateTabFormat($tabcontent){
        $segments = array('header','scroll','footer','onload','command');

        if(!is_object($tabcontent)){
            return false;
        }

        foreach($segments as $segment){
            if(isset($tabcontent->$segment)){
                if(!is_array($tabcontent->$segment)){
                    return false;
                }
            }
        }

        return true;
    }


    private function getTabCache($tabname,$num){

        $cache = Appcaching::getActionTab($this->actionid,$num,$this->playid,$this->gid);

        if($cache){
            return $cache;
        } elseif(method_exists($this->childobj,$tabname)) {
            $tab = $this->childobj->$tabname();
            $tab = $this->addTabJson($tab,$num);
            Appcaching::setActionTab($this->actionid,$num,$this->playid,$tab,$this->gid);
            return $tab;
        } else {
            return array();
        }
    }


    /* this will return a view in right order and containing header,scroll and footer */
    public function normaliseView($view){
        $output = new StdClass();

        if(isset($view->header)) { $output->header = $view->header; } else { $output->header = array(); }
        if(isset($view->scroll)) { $output->scroll = $view->scroll; } else { $output->scroll = array(); }
        if(isset($view->footer)) { $output->footer = $view->footer; } else { $output->footer = array(); }

        return $output;
    }


    private function addNotificationsJson($tabcontent,$num){

    }

    /* rewrite the tab content to include the tab menu either in header, footer or just in the variable */
    private function addTabJson($tabcontent,$num){
        $menu = $this->tabMenu($num);

        if(!is_array($menu)){
            return $tabcontent;
        }

        /* to get them out in right order */
        $output = $this->normaliseView($tabcontent);

        if($this->childobj->tabmode == 'top'){
            if(empty($output->header)){
                $output->header = $menu;
            } else {
                $output->header = array_merge($menu,$output->header);
            }

            return $output;

        } elseif($this->childobj->tabmode == 'bottom'){
            if(empty($output->footer)){
                $output->footer = $menu;
            } else {
                $output->footer = array_merge($output->footer,$menu);
            }

            return $output;
        } else {

            $this->tabsmenu_json = $menu;
            return true;
        }

    }


    private function getParam($name,$submit){
        if(isset($submit[$name])) {
            return $submit[$name];
        } elseif(isset($submit['query'][$name])) {
            return $submit['query'][$name];
        } elseif(isset($_REQUEST[$name])){
            return $_REQUEST[$name];
        } elseif(isset($_REQUEST['query'][$name])){
            return $_REQUEST['query'][$name];
        } else {
            return false;
        }
    }


    public function tabMenu($num=false,$background_color=false,$active_color=false){

        if(empty($this->tabsimages)){
            return false;
        }

        /* determine the menu */
        if(!($num)){
            $num = 1;
        }

        if(!$background_color){ $background_color = $this->color_topbar; }
        if(!$active_color) { $active_color = $this->color_topbar_hilite; }

        $this->articlemenuobj->mainobj = $this->childobj;
        $output = $this->articlemenuobj->tabMenu($this->tabsimages,$num,$active_color,$background_color);
        return $output;
    }

    public function cleanCache($actionid,$class){
        $cacheidentifier = $class .$actionid;
        Yii::app()->cache->delete( $cacheidentifier );
        return true;
    }

    /* handles outputting (for saving of cache) */
    public function articleOutput($actionid,$data,$submit=false){

        if($this->caching AND $submit == false){
            $class = get_called_class();
            $cacheidentifier = $class .$actionid;
            Yii::app()->cache->set( $cacheidentifier,$data );
        }

        return $data;
    }

    public function flushModuleAssets(){
        Yii::app()->cache->set( $this->gid .'-modulefiles',false);
        $this->moduleAssets();
    }

    /* these handle creating data that actions might need like assets, variables and menus */

    private function moduleAssets(){
        $cachename = $this->gid.$this->action_id.'-factory-modulefiles';
        $cached = Appcaching::getGlobalCache($cachename);

        if(time() + 300 < $cached){
            return true;
        }

        if($cached == true AND $this->caching == true){
            return true;
        }

        $this->moduleMenus();
        $this->moduleVariables();

        Appcaching::setGlobalCache($cachename,time(),300);
        return true;
    }

    /* deprecated. Variables are created on the fly when needed */
    private function moduleVariables(){

        $cachename = $this->gid.$this->action_id.'-factory-modulevars';
        $cached = Appcaching::getGlobalCache($cachename);

        if(time() + 400 < $cached){
            return true;
        }

        $sourcepath = Yii::getPathOfAlias('application.modules.aelogic.packages.action' .$this->class .'.sql');
        $sourcepath = $sourcepath .'/Variables.php';
        Appcaching::setGlobalCache($cachename,time(),400);

        if(file_exists($sourcepath)){

            require_once($sourcepath);

            if(isset($variables) AND is_array($variables)) {

                while ($variable = each($variables)) {
                    $variable = $variable[1];
                    $varobj = Aevariable::model()->findByAttributes(array('game_id' => $this->gid,'name' => $variable));
                    if(!is_object($varobj)){
                        Aevariable::addGameVariable($this->gid,$variable);
                    }
                }
            }

            return true;
        } else {
            return true;
        }
    }

    private function moduleMenus(){
        $cachename = $this->gid.$this->action_id.'-factory-modulemenus';
        $cached = Appcaching::getGlobalCache($cachename);

        if(time() + 330 < $cached){
            return true;
        }

        $sourcepath = Yii::getPathOfAlias('application.modules.aelogic.packages.action' .$this->class .'.sql');
        $sourcepath = $sourcepath .'/Menus.php';
        Appcaching::setGlobalCache($cachename,time(),300);

        if(file_exists($sourcepath)){

            require_once($sourcepath);

            if(isset($menus) AND is_array($menus)) {

                while ($menu = each($menus)) {
                    $name = $menu[0];
                    $items = $menu[1]['items'];

                    $nav = Aenavigation::model()->findByAttributes(array('app_id' => $this->gid, 'name' => $name));

                    if (!is_object($nav)) {
                        $this->createMissingMenu($name, $items);
                    }
                }
            }
        } else {
            return true;
        }
    }

    private function createMissingMenu($name,$items){
        $nav = new Aenavigation();
        $nav->app_id = $this->gid;
        $nav->name = $name;
        $nav->safe_name = $name;
        $nav->state = 0;
        $nav->insert();
        $id = $nav->getPrimaryKey();
        $count = 1;

        while($menu = each($items)){
            $menu = $menu[1];
            $navitem = new AenavigationItem();
            $navitem->menu_id = $id;
            $navitem->name = $menu['shortname'];
            $navitem->safe_name = $menu['shortname'];
            $navitem->item_order = $count;
            $navitem->state = 'active';
            $navitem->action = $menu['action'];
            $navitem->action_config = $menu['action_config'];
            $navitem->image = $menu['image'];
            $navitem->open_popup = $menu['open_popup'];
            $navitem->insert();
        }
    }

}