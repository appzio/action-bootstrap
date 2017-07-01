<?php

namespace article\views\components;
use Article\Views\ArticleView;

trait Image {

    /**
     * @param $content string, filename or url
     * @param array $styles 'margin', 'padding', 'orientation', 'background', 'alignment', 'radius', 'opacity',
     * 'orientation', 'height', 'width', 'align', 'crop', 'text-style', 'font-size', 'text-color', 'border-color',
     * 'border-width', 'font-android', 'font-ios', 'background-color', 'background-image', 'background-size',
     * 'color', 'shadow-color', 'shadow-offset', 'shadow-radius', 'vertical-align', 'border-radius', 'text-align',
     * 'lazy', 'floating' (1), 'float' (right | left), 'max-height', 'white-space' (no-wrap)
     * @param array $parameters selected_state, variable, onclick, style, image_fallback (when clicked, change to this image), selected_state,
     * lazy (loads after view), tap_to_open, tap_image (image file name)
     * @return \stdClass
     */

    public function getComponentImage(string $content, array $parameters=array(),array $styles=array()) {
        /** @var ArticleView $this */

        $obj = new \StdClass;
        $obj->type = 'image';
        $obj->content = $content;

        $obj = $this->attachStyles($obj,$styles);
        $obj = $this->attachParameters($obj,$parameters);
        $obj = $this->configureDefaults($obj);

        return $obj;
    }

}


