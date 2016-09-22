<?php

Yii::import('application.modules.aelogic.article.components.*');

class ArticleGallery extends ArticleComponent {

    // Local Vars
    public $basepath;
    public $abspath;

    public $varcontent;

    // Cache - the content which the opening window will show
    public $gallerycache;

    // App Data
    public $gid;
    public $action_id;
    public $imagesobj;

    public $debug;
    public $open_action;
    public $open_in_popup;
    public $grid_spacing;

    public $required_params = array('images', 'viewer_id');

    public $defaults = array(
        'dir' => 'images',
        'columns' => 3,
        'defaultimage' => 'photo-placeholder.jpg',
    );

    public function template() {

        $this->options['defaultimage'] = $this->factoryobj->getImageFileName($this->options['defaultimage']);
        $this->debug = $this->addParam('debug',$this->options,false);
        $this->open_action = $this->addParam('open_action',$this->options,false);
        $this->open_in_popup = $this->addParam('open_in_popup',$this->options,true);
        $this->grid_spacing = $this->addParam('grid_spacing',$this->options,false);

        $exclude = $this->addParam('exclude',$this->options,false);

        $this->gallerycache = array();
        $output = array();

        $this->basepath = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']);
        $this->abspath = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']) . '/documents/games/' . $this->gid . '/' . $this->options['dir'] . '/';

        /* NOTE: fake id's are built based on the class name and action id */
        $class = get_called_class();
        $basenumber = intval(md5( $class ));
        $basenumber = $basenumber . $this->action_id;

        $images = json_decode( $this->options['images'], true );
        $basenumber = $basenumber*2;

        if(!is_array($images)){
            return $output;
        }

        $newimagearray = $this->cleanImagesArray($images,$exclude);

        if(!is_array($newimagearray)){
            return $output;
        }

        /* slice the images array into chunks */
        $columns = array_chunk($newimagearray,$this->options['columns'], true);

        //file_put_contents('debugdata.txt',json_encode($newimagearray),FILE_APPEND);
        $count = 1;

        foreach ($columns as $items) {
            $basenumber++;

            $obj = new StdClass;
            $obj->type = 'menu';
            $obj->menu_content = new StdClass;
            $obj->menu_content->id = $basenumber;

            if($this->grid_spacing){
                $sty = new StdClass();
                $sty->margin = '2 2 0 2';
                $obj->style_content = new StdClass();
                $obj->style_content->orientation = 'horizontal';
                $obj->style_content->margin = '2 4 0 4';
                $n = 'children-style';
                $obj->style_content->$n = $sty;
            } else {
                $obj->style = 'menu_gallery';
            }

            foreach ($items as $item) {
                unset($menu_item);
                $basenumber++;

                $image = $item['image'];

                // Cache images to the local server directory
                $images = $this->cacheGalleryImage($image);

                // Fill the Cache object
                $this->setGalleryCache($item, $images, $basenumber);

                $menu_item = new StdClass;
                $menu_item->id = $basenumber;

                // $menu_item->image = $image;
                $menu_item->image = $images['small'];

                $menu_item->state = 'active';
                $menu_item->action = 'open-action';

                if ($this->open_action) {
                    $menu_item->action_config = $item['action_id'];
                } else {
                    $menu_item->action_config = $this->options['viewer_id'];
                    $menu_item->sync_open = 1;
                }

                if ($this->open_in_popup) {
                    $menu_item->open_popup = 1;
                } else {
                    $menu_item->back_button = 1;
                }

                $obj->menu_content->items[] = $menu_item;

                $count++;
            } // eo one column

            if(isset($menu_item)){
                $output[] = $obj;
                unset($menu_item);
            }

            unset($obj);

        } // eo columns

        // Set Cache
        // NOTE: not using Appcaching, because this is always needed to show the images
        if(get_class(Yii::app()->getComponent('cache')) == 'CDummyCache'){
            Yii::app()->setComponent('cache', new CFileCache());
        }

        Yii::app()->cache->set( $this->action_id .'-' .'galleryitems', $this->gallerycache );
        return $output;
    }


    private function cleanImagesArray($images,$exclude){
        $count = 1;
        $newimagearray = array();

        /* first check which images are ok to be used in the gallery */
        foreach($images as $item) {

            if(isset($item['pic'])) {
                $image = $this->factoryobj->getImageFileName($item['pic'], array('debug' => false,'imgwidth' => 400,'imgheight'=> 400, 'imgcrop' => 'yes'));
            } elseif (isset($item['image_src'])) {
                $image = $item['image_src'];
            } else {
                $image = false;
            }

            if (isset($item['pic']) AND $item['pic'] == 'photo-placeholder.jpg') {
                $image = false;
            }

            if ($exclude AND $item['action_id'] == $exclude) {
                $image = false;
            }

            if ($exclude AND $exclude == 1 AND $count == 1) {
                $image = false;
            }

            if ($image) {
                $item['image'] = $image;
                $newimagearray[] = $item;
            }

            $count++;
        }

        //file_put_contents('debugdata2.txt',json_encode($newimagearray),FILE_APPEND);
        return $newimagearray;
    }

    private function cacheGalleryImage( $image ) {

        if ( empty($image)  ) {
            return false;
        }

        $images = array();

        $img_name = basename( $image );

        if ( $img_name == $this->options['defaultimage'] ) {
            return false;
        }

        $sizes = array(
            'small' => array(
                'width'  => 400,
                'height' => 400,
                'crop'   => 'yes',
            ),
            'big' => array(
                'width'  => 640,
                'height' => 400,
                'crop'   => 'yes',
            ),
        );

        foreach ($sizes as $key => $dim) {
            $params = array('imgwidth' => $dim['width'],'imgheight' => $dim['height'],'imgcrop' => $dim['crop']);
            $picture = $this->imagesobj->getAsset($image,$params);
            $images[$key] = $picture;
        }

        return $images;
    }


    private function setGalleryCache( $item, $images, $basenumber ) {

        if ( empty($images) ) {
            return;
        }

        $this->gallerycache[$basenumber]['image'] = $images['big'];

        $item_data = array(
            'user', 'date', 'comment', 'like_count','chat_content','action_id'
        );

        foreach ($item_data as $itd) {
            if ( isset($item[$itd]) ) {
                $this->gallerycache[$basenumber][$itd] = $item[$itd];
            }
        }

    }

}