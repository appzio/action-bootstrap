<?php

namespace article\views\components;
use Article\Views\ArticleView;

trait Onclick {

    /**
     * @param array $parameters sync_open, sync_close, context,
     * @return \stdClass
     */

    public function getComponentOnclickTab(int $number, array $parameters=array(),array $saveids = array()) {
        /** @var ArticleView $this */

		$obj = new \StdClass;
        $obj->action = 'open-tab';
        $obj->action_config = $number;

        $obj = $this->attachParameters($obj,$parameters);

        return $obj;
	}

}