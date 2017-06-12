<?php

/*

    This is the main controller of the action.

*/

Yii::import('application.modules.aegameauthor.models.*');
Yii::import('application.modules.aelogic.article.components.*');
Yii::import('application.modules.aelogic.packages.actionMobileregister.models.*');
Yii::import('application.modules.aelogic.packages.actionMobilelogin.models.*');

class MobileexampleController extends ArticleController {

    public $data;
    public $theme;

    public $dataobj;


    /* this will create the model and feed the contents of the controller to the model
       This way its easy to access things like getSavedVariable and other data helpers
    */

    public function init(){
        $dataobj = new MobileexampleModel();
        $dataobj->factoryInit($this);
    }

	public function getExampleString(){
		return $this->getText('Hello from the main controller');
	}


}