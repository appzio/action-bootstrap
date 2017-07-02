<?php

namespace Article\Views\Components;
use Article\Views\ArticleView;

trait Text {

    /**
     * @param $content string, no support for line feeds
     * @param array $styles 'margin', 'padding', 'orientation', 'background', 'alignment', 'radius', 'opacity',
     * 'orientation', 'height', 'width', 'align', 'crop', 'text-style', 'font-size', 'text-color', 'border-color',
     * 'border-width', 'font-android', 'font-ios', 'background-color', 'background-image', 'background-size',
     * 'color', 'shadow-color', 'shadow-offset', 'shadow-radius', 'vertical-align', 'border-radius', 'text-align',
     * 'lazy', 'floating' (1), 'float' (right | left), 'max-height', 'white-space' (no-wrap)
     * @param array $parameters selected_state, variable, onclick, style
     * @return \stdClass
     */

    public function getComponentText(string $content, array $parameters=array(),array $styles=array()) {
        /** @var ArticleView $this */

		$obj = new \StdClass;
        $obj->type = 'msg-plain';
        $obj->content = $content;

        $obj = $this->attachStyles($obj,$styles);
        $obj = $this->attachParameters($obj,$parameters);
        $obj = $this->configureDefaults($obj);

        return $obj;
	}

}