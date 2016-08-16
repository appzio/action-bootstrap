<?php


Yii::import('application.modules.aegameauthor.models.*');
Yii::import('application.modules.aegameauthor.components.*');
Yii::import('application.modules.aelogic.article.components.*');

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

    }

    public function saveVariables(){
        ArticleModel::saveVariables($this->submitvariables,$this->playid);
        $this->loadVariableContent();
        return true;
    }

    public function copyVariable($from,$to){
        AeplayVariable::updateWithName($this->playid,$to,$this->getSavedVariable($from),$this->gid,$this->userid);
        $this->loadVariableContent(true);
    }

    public function initKeyValueStorage(){
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

    public function saveVariable($variable,$value){
        if ( !is_numeric($variable) ) {
            $variable = $this->getVariableId($variable);
        }

        AeplayVariable::updateWithId($this->playid,$variable,$value);
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


    public function returnComponent($name,$type,$content=false,$params=array()){
        $name = str_replace(' ', '_', ucwords(str_replace('_', ' ', $name)));

        if($type == 'field') {
            $class = 'Article_View_' . $name;
        } else {
            $class = 'Article' . ucfirst($name);
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
    public function getTwoWayChatId($id){

        if($id < $this->playid){
            $chatid = $id.'-chat-'.$this->playid;
        } else {
            $chatid = $this->playid.'-chat-'.$id;
        }

        return $chatid;

    }

    /* setters and getters should go here */

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
        $onclick = new StdClass();
        $onclick->action = 'push-permission';
        return $onclick;
    }

    public function getVariableId($varname){
        if(isset($this->vars[$varname])){
            return $this->vars[$varname];
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

    public function getSubmitVariable($varid){
        if(isset($this->submitvariables[$varid])){
            return $this->submitvariables[$varid];
        } else {
            return false;
        }
    }

    public function initMobileMatching($otheruserid=false,$debug=false){
        Yii::import('application.modules.aelogic.packages.actionMobilematching.models.*');

        if($debug){
        }

        $this->mobilematchingobj = new MobilematchingModel();
        $this->mobilematchingobj->playid_thisuser = $this->playid;
        $this->mobilematchingobj->playid_otheruser = $otheruserid;
        $this->mobilematchingobj->gid = $this->gid;
        $this->mobilematchingobj->actionid = $this->actionid;
        $this->mobilematchingobj->initMatching($otheruserid,true);
    }

    public function initMobileChat( $context, $context_key, $otheruserid = false ){
        Yii::import('application.modules.aechat.models.*');

        $this->mobilechatobj = new Aechat();
        $this->mobilechatobj->play_id = $this->playid;
        $this->mobilechatobj->gid = $this->gid;
        $this->mobilechatobj->context = $context;
        $this->mobilechatobj->context_key = $context_key;
        
        // $this->mobilechatobj->actionid = $this->actionid;
        // $this->mobilechatobj->playid_otheruser = $otheruserid;

        $this->mobilechatobj->initChat();
    }

    public function getSubmittedVariableByName($varname,$default=false){

        if (isset($this->submitvariables[$this->getVariableId($varname)])) {
            return $this->submitvariables[$this->getVariableId($varname)];
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


    public function getTabs($content,$params=array(),$divider=false,$indicatorontop=false){
        /* you can configure the needed params here */

        if(count($content) == 1){
            $width = $this->screen_width;
            $fontsize = '14';
        } elseif(count($content) == 2){
            $width = round($this->screen_width/2,0);
            $fontsize = '14';
        } elseif(count($content) == 3){
            $width = round($this->screen_width/3,0);
            $fontsize = '14';
        } elseif(count($content) == 4){
            $width = round($this->screen_width/4,0);
            $fontsize = '12';
        } elseif(count($content) == 5){
            $width = round($this->screen_width/5,0);
            $fontsize = '10';
        } else {
            $width = round($this->screen_width/6,0);
            $fontsize = '10';
        }

        $count = 1;

        foreach($content as $item){
            $onclick = new StdClass();
            $onclick->action = 'open-tab';
            $onclick->action_config = $count;
            $onclick->id = $count .'11';

            $btn1 = $this->getText($item,array('padding' => '10 10 10 10',
                'color' => $this->colors['top_bar_text_color'],'text-align' => 'center',
                'onclick' => $onclick,'font-size' => $fontsize,'font-ios' => 'Roboto-Regular'
                ));

            if($this->current_tab == $count){
                $btn2 = $this->getText('',array('height' => '3','background-color' => $this->color_topbar_hilite,'width' => $width));
            } else {
                $btn2 = $this->getText('',array('height' => '3','background-color' => $this->color_topbar,'width' => $width));
            }

            if($indicatorontop){
                $btn = array($btn2,$btn1);
            } else {
                $btn = array($btn1,$btn2);

            }

            $col[] = $this->getColumn($btn,array('width' => $width));
            unset($btn);

            if($divider){
                $col[] = $this->getVerticalSpacer(1,array('background-color' => $this->colors['top_bar_text_color']));
            }

            $count++;

        }

        if(isset($col)){
            $row = $this->getRow($col,array('background-color' => $this->color_topbar));
            return $row;
        }

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
        $file = $this->getImageFileName($filename,$params);

        if($file){
            return $this->returnComponent('image','field',$file,$params);
        } else {
            return $this->getError('Image not found');
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

    public function getAlertBox($content,$id,$markread=false){

        if($this->menuid == $id){
            $this->playkeyvaluestorage->set('alertbox'.$id,true);
        }

        $ison = $this->playkeyvaluestorage->get('alertbox'.$id);

        if($ison){
            return false;
        }

        $onclick = new StdClass();
        $onclick->id = $id;
        $onclick->action = 'submit-form-content';

        $alert[] = $this->getText($content,array('style' => 'alertbox_text'));
        $close[] = $this->getText('x',array('style' => 'alertbox_close'));
        $alert[] = $this->getColumn($close,array('style' => 'alertbox_close_row','onclick' => $onclick));

        if($markread){
            $this->playkeyvaluestorage->set('alertbox'.$id,true);
        }

        return $this->getRow($alert,array('style' => 'alertbox'));

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




    public function getCheckbox($varname, $title, $error = false, $params = false){
        $styles = array(
            'width' => '120',
            'text-align' => 'left',
            'font-ios' => 'Roboto-Regular',
            'font-android' => 'Roboto',
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

        if($mycountry){
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

        $onclick = new stdClass();
        $onclick->action = 'open-tab';
        $onclick->action_config = '2';

        $data[] = $this->getText($mycountrycode,array('style' => 'phone_register_field_country','onclick' => $onclick));
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
                $this->getFieldtext($var,  $textfieldparams ),
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


    public function getSpacer($height){
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

    public function getHairline($color){
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
        if($submitmenu){
            return $this->getButtonWithIcon('f-icon.png', $id, '{#sign_in_with_facebook#}', array('style' => 'facebook_button_style'),array('style' => 'fbbutton_text_style'));
        } else {
            return $this->getButtonWithIcon('f-icon.png', $id, '{#sign_in_with_facebook#}', array('style' => 'facebook_button_style','action' => 'fb-login','sync_open' => true),array('style' => 'fbbutton_text_style'));
        }
    }

    public function getButtonWithIcon($image,$id,$text,$buttonstyle=array(),$textstyle=array()){
        $params['priority'] = 1;
        $params['height'] = '30';
        $params['vertical-align'] = 'middle';
        $params['image'] = $this->getImageFileName($image,$params);

        $buttonstyle['onclick'] = new StdClass();
        $buttonstyle['onclick']->id = $id;
        $buttonstyle['onclick']->action = $this->addParam('action',$buttonstyle,'submit-form-content');
        $buttonstyle['onclick']->config = $this->addParam('config',$buttonstyle,'');
        $buttonstyle['onclick']->sync_open = $this->addParam('sync_open',$buttonstyle,'');

        $img = $this->getImage($image,$params);

        //$column[] = $this->getColumn(array($img),array('vertical-align' => 'middle','width' => '30'));
        $column[] = $img;
        $column[] = $this->getVerticalSpacer('10');
        $column[] = $this->getText($text,$textstyle);

        if($params['image']){
            return $this->getRow($column,$buttonstyle);
        } else {
            return $this->getError('Image not found');
        }
    }


    public function getImagebutton($image,$id,$fallbackimage=false,$params=array()){
        $params['priority'] = 1;
        $params['image'] = $this->getImageFileName($image,$params);
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

    public function moduleChat($params){
        return $this->returnComponent('chat','module',false,$params);
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

    public function getLocalizedDate( $format, $stamp ) {

        if ( !is_int($stamp) ) {
            $stamp = strtotime( $stamp );
        }

        $months = array(
            '{#january#}', '{#february#}', '{#march#}', '{#april#}', '{#may#}', '{#june#}', '{#july#}', '{#august#}', '{#september#}', '{#october#}', '{#november#}', '{#december#}'
        );

        $days = array(
            '{#monday#}', '{#tuesday#}', '{#wednesday#}', '{#thursday#}', '{#friday#}', '{#saturday#}', '{#sunday#}',
        );

        // -- equals "day"
        // - equals "month"
        $replaces = array(
            'D' => '{--N}',
            'M' => '{-n}',
        );

        $result = str_replace(
            array_keys($replaces), 
            array_values($replaces), 
            $format
        );

        $date_int = date( $result, $stamp );

        $days_rp = preg_replace_callback('~({--\d})~', function( $matches ) use($days) {
            $entry = $matches[0];
            $num = filter_var($entry, FILTER_SANITIZE_NUMBER_INT);
            $num = str_replace('--', '', $num);
            return $days[$num - 1];
        }, $date_int);

        $final = preg_replace_callback('~({-\d})~', function( $matches ) use($months) {
            $entry = $matches[0];
            $num = filter_var($entry, FILTER_SANITIZE_NUMBER_INT);
            $num = str_replace('-', '', $num);
            return $months[$num - 1];
        }, $days_rp);

        return $final;
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