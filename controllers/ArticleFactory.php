<?php


Yii::import('application.modules.aegameauthor.models.*');
Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aelogic.article.components.views.*');

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

    public $aspect_ratio;
    public $screen_width;
    public $screen_height;

    public $appinfo;

    public $localizationComponent;

    public $query;                  // this is the original query as api has received it

    /* gets called when object is created & fed with initial values */
    public function playInit() {

        if(!isset($this->playobj->id)){
            return false;
        }

        $this->playid = $this->playobj->id;
        $this->userid = $this->playobj->user_id;

        $this->loadVariables();

        $this->articlemenuobj = new ArticleMenuComponents($this);
        $this->articlemenuobj->imagesobj = $this->imagesobj;

        $menuitems = Aenavigation::getAllAppMenuItems($this->gid);

        foreach($menuitems as $nav){
            $this->tabs[$nav['itemid']] = $nav['item_safe_name'];
            $this->menus[$nav['menu_safe_name']] = $nav['menuid'];
            $this->menuitems[$nav['item_safe_name']] = $nav['itemid'];
        }
    }


    public function actionInit(){

        $this->current_tab = $this->getParam('tabid',$this->submit);
        $this->menuid = $this->getParam('menuid',$this->submit);
        $vars = $this->getParam('variables',$this->submit);

        $this->actionobj = AeplayAction::model()->with('aetask')->findByPk($this->actionid);
        $this->action_id = $this->actionobj->action_id;
        $this->branchobj = Aebranch::model()->findByPk($this->actionobj->aetask->branch_id);

        if(isset($this->branchobj->config)){
            $this->branchconfig = @json_decode($this->branchobj->config);
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

        if(isset($_REQUEST['referring_action'])){
            $this->referring_action = $_REQUEST['referring_action'];
        }

        if(isset($_REQUEST['context'])){
            $this->context = $_REQUEST['context'];
        }

        $this->fbtoken = UserGroupsUseradmin::getFbToken($this->userid);

        if(isset($this->params['fb_login'])){
            $this->fblogin = filter_var($this->params['fb_login'],FILTER_VALIDATE_BOOLEAN);
        } else {
            $this->fblogin = false;
        }

        $this->setScreenInfo();

    }

    public function renderData($actiontype){
        $this->createChildObj($actiontype);

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
            $op->scroll = $this->childobj->errorMsgs+$op->scroll;
        }

        /* save debug to cache, so that it can be shown by the debug or delete if none is set */
        if(!empty($this->childobj->debugMsgs)){
            array_unshift($this->childobj->debugMsgs,$this->actionobj->name);
            Appcaching::setGlobalCache($this->playid .'-debug',$this->childobj->debugMsgs);
        }

        if(isset($this->childobj->rerun_list_branches) AND $this->childobj->rerun_list_branches === true){
            $this->rerun_list_branches = true;
        }

        $this->rewriteconfigs = $this->childobj->rewriteconfigs;
        $this->rewriteactionfield = $this->childobj->rewriteactionfield;

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

    public function setScreenInfo() {

        if(isset($_REQUEST['screen_width']) AND isset($_REQUEST['screen_height'])){
            $this->aspect_ratio = round($_REQUEST['screen_width'] / $_REQUEST['screen_height'],3);
            $this->screen_width = $_REQUEST['screen_width'];
            $this->screen_height = $_REQUEST['screen_height'];

            if(!isset($this->varcontent['screen_width'])){
                AeplayVariable::updateWithName($this->playid,'screen_width',$_REQUEST['screen_width'],$this->gid,$this->userid);
            }

            if(!isset($this->varcontent['screen_height'])){
                AeplayVariable::updateWithName($this->playid,'screen_height',$_REQUEST['screen_height'],$this->gid,$this->userid);
            }

        } elseif(isset($this->varcontent['screen_width']) AND isset($this->varcontent['screen_height'])){
            $this->aspect_ratio = round($this->varcontent['screen_width'] / $this->varcontent['screen_height'],3);
            $this->screen_width = $this->varcontent['screen_width'];
            $this->screen_height = $this->varcontent['screen_height'];
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



    public function setColors(){

        $cache = Appcaching::getActionCache($this->action_id,$this->playid,$this->gid,'tabcolors');

        if($cache){
            $this->color_topbar = $cache['background'];
            $this->color_topbar_hilite = $cache['active'];
            $this->colors = $cache['colors'];
        } else {
            /* take the tab control colors from the action if not defined */
            $colors = Controller::getColors(false,false,$this->action_id);
            $background_color = Helper::normalizeColor($colors->top_bar_color);
            $background_color = substr($background_color,3);
            $colorhelp = new Color($background_color);
            $active_color = $colorhelp->darken();

            $colorarray['background'] = $background_color;
            $colorarray['active'] = $active_color;
            $colorarray['colors'] = $colors;

            $this->color_topbar = $background_color;
            $this->color_topbar_hilite = $active_color;

            foreach($colors as $key => $col){
                $newcolors[$key] = Helper::normalizeColor($col);
            }

            $this->colors = $newcolors;

            Appcaching::setActionCache($this->action_id,$this->gid,'tabcolors',$colorarray);
        }
    }

    public function createChildObj($actiontype){

        if(!$this->available_branches){
            $this->available_branches = Appcaching::getPlayCache('branchlist-article',$this->gid,$this->playid,true);
        }

        $this->class = ucfirst($actiontype);
        $cc = str_replace('Controller','',$this->class);
        $this->imagespath = Yii::getPathOfAlias('application.modules.aelogic.packages.action' .$cc .'.images');
        $this->imagesobj->imagesearchpath[] = $this->imagespath .'/';

        Yii::import('application.modules.aelogic.packages.ActivationEngineAction');

        $dir_root = 'application.modules.aelogic.packages.action' . ucfirst($actiontype);
        $class = ucfirst($actiontype) . 'Controller';
        $path = $dir_root . '.controllers.' . $class;

        $controller_included = false;

        // Check for subcontrollers
        if ( isset($this->configobj->article_action_theme) AND !empty($this->configobj->article_action_theme) ) {
            $theme = $this->configobj->article_action_theme;

            $rootPath = Yii::getPathOfAlias('application.modules.aelogic.packages.action' .$cc);
            $this->imagesobj->imagesearchpath[] = $rootPath .'/themes/' . $theme . '/images/';

            $subpath = $dir_root . '.themes.' . $theme . '.controllers.' . $theme . ucfirst($actiontype) . 'SubController';
            $subfile = Yii::getPathOfAlias($subpath);

            if ( file_exists($subfile . '.php') ) {
                Yii::import($subpath);
                $class = $theme . ucfirst($actiontype) . 'SubController';
                $controller_included = true;
            }
        }

        $file = Yii::getPathOfAlias($path);
        if ( file_exists($file . '.php') ) {
            Yii::import($path);
            $controller_included = true;
        }

        if ( !$controller_included ) {
            return array();
        }

        if ( !$this->actionid ) {
            $this->actionid = $this->getParam('actionid',$this->submit);
        }

        $this->actionInit();

        if (!isset($this->configobj) OR !is_object($this->configobj)) {
            return false;
        }

        $this->childobj = new $class($this);
        $this->moduleAssets();
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
        if ( !empty($this->tabsimages) ) {
            $output = $this->getTabsView();
        } else {
            $output = $this->getDefaultView();
        }

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
                break;
            }
        }
        
        return $output;
    }


    private function getTabsView() {
        $output = array();

        $tabs = $this->tabsimages;
        $onload = array();

        foreach ($tabs as $key => $t) {
            $tabname = 'tab' . $key;

            /* satisfy all others from cache except for the currently active tab */
            //if ( $this->current_tab == $key AND method_exists($this->childobj,$tabname) ) {
                $tabcontent = $this->childobj->$tabname();

                if(isset($tabcontent->onload)){
                    $onload = array_merge($onload,$tabcontent->onload);
                }

                $tabcontent = $this->addTabJson($tabcontent,$key);
                $output[$tabname] = (object) $tabcontent;
/*            } else {
                $output[$tabname] = (object) $this->getTabCache($tabname,$key);
            }*/
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
        $cached = Yii::app()->cache->get( $this->gid .'-modulefiles');

        if($cached == true AND $this->caching == true){
            return true;
        }

        $this->moduleMenus();
        $this->moduleVariables();

        Yii::app()->cache->set( $this->gid .'-modulefiles',true);
        return true;
    }


    private function moduleVariables(){

        $sourcepath = Yii::getPathOfAlias('application.modules.aelogic.packages.action' .$this->class .'.sql');
        $sourcepath = $sourcepath .'/Variables.php';

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

        $sourcepath = Yii::getPathOfAlias('application.modules.aelogic.packages.action' .$this->class .'.sql');
        $sourcepath = $sourcepath .'/Menus.php';

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