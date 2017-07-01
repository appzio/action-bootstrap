<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Alertbox extends ArticleComponent {

    public $vars;

    public function template() {

        $id = $this->addParam('id',$this->options);
        $content = $this->addParam('content',$this->options);
        $show_only_once = $this->addParam('show_only_once',$this->options);

        if($this->factoryobj->menuid == $id AND $show_only_once){
            $this->factoryobj->playkeyvaluestorage->set('alertbox'.$id,true);
        }

        $ison = $this->factoryobj->playkeyvaluestorage->get('alertbox'.$id);

        if($ison){
            return false;
        }

        $onclick = new stdClass();
        $onclick->id = $id;
        $onclick->action = 'submit-form-content';

        $options['visibility'] = 'delay';
        $options['visibility_delay'] = '0.4';
        $options['transition'] = 'pop';
        $options['style'] = 'alertbox_text';

        $alert[] = $this->factoryobj->getText($content,$options);
        $options['style'] = 'alertbox_close';

        $close[] = $this->factoryobj->getImage('close-alert-box.png',$options);
        $alert[] = $this->factoryobj->getColumn($close,array('style' => 'alertbox_close_row','onclick' => $onclick));

        if($show_only_once){
            $this->factoryobj->playkeyvaluestorage->set('alertbox'.$id,true);
        }

        $options['style'] = 'alertbox';
        return $this->factoryobj->getRow($alert,$options);
	}

}