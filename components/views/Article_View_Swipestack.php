<?php

Yii::import('application.modules.aelogic.article.components.*');

class Article_View_Swipestack extends ArticleComponent {

    public function template() {
        $obj = new StdClass;
        $obj->type = 'swipestack';

        $obj->swipe_content = $this->content;

        $params = array(
        	'swipe_content','overlay_left','overlay_right','rightswipeid','leftswipeid'
        );

        foreach ($params as $param) {
            if ( isset($this->options[$param]) ) {
                $obj->$param  = $this->options[$param];
            }
        }

        return $obj;
    }

}

/*
"type":"swipestack","swipe_content": [
        {
            "type":"msg-plain",
          "content":"card1",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        },
        {
            "type":"msg-plain",
          "content":"card2",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        },
        {
            "type":"msg-plain",
          "content":"card3",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        },
        {
            "type":"msg-plain",
          "content":"card4",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        },
        {
            "type":"msg-plain",
          "content":"card5",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card6",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card7",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card8",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card9",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card10",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card11",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card12",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card13",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card14",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card15",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
        ,
        {
            "type":"msg-plain",
          "content":"card16",
          "style_content":{"text-align":"center", "border-color":"#000000","background-color":"#ffffff"}
        }
      ],
      "style_content":{"background-color":"#ff00ff", "width":"300", "height":"300", "margin":"50 0 0 50"},
      "overlay_left":{"type":"msg-plain", "content":"NO"},
      "overlay_right":{"type":"msg-plain", "content":"YES"}*/
