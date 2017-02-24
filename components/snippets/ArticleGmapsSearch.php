<?php

Yii::import('application.modules.aelogic.article.components.*');

/**
 * GMaps class ver 0.1
 * 
 * Gets geo-informations from the Google Maps API
 * 
 */
class ArticleGmapsSearch {

    const MAPS_HOST = 'maps.googleapis.com';

    private $data;
    private $key;
    private $baseUrl;
    public $search_param;
    
    /**
     * Construct
     *
     * @param string $key
     */
    function __construct ($key='')
    {
        $this->key= $key;
        $this->baseUrl= "https://" . self::MAPS_HOST . "/maps/api/geocode/json?key=" . $this->key;
    }

    public function getInfoLocation() {

        if ( empty($this->search_param) ) {
            die( 'Please enter a search parameter' );
        }

        return $this->connect();
    }

    /**
     * connect to Google Maps
     */
    private function connect() {
        $request_url = $this->baseUrl . '&address=' . urlencode($this->search_param);

        $contents = @file_get_contents( $request_url );

        if ( empty($contents) ) {
            return false;
        }

        $data = json_decode( $contents, true );

        if ( !isset($data['results']) ) {
            return false ;
        }

        $this->data = $data['results'][0];

        return true;
    }

    /**
    * Get the RAW request data
    */
    public function getData() {
        return $this->data;
    }

    public function getCoords() {

        if ( !isset($this->data['geometry']['location']) ) {
            return false;
        }

        return $this->data['geometry']['location'];
    }

    public function getAddress() {

        if ( !isset($this->data['formatted_address']) ) {
            return false;
        }

        return $this->data['formatted_address'];
    }

    public function getAddressComponent( $component ) {

        if ( !isset($this->data['address_components']) ) {
            return false;
        }

        $map = array();

        foreach ($this->data['address_components'] as $mc) {
            if ( isset($mc['types'][0]) ) {
                $map[$mc['types'][0]] = $mc;
            }
        }

        if ( !isset($map[$component]) ) {
            return false;
        }

        return $map[$component];
    }    

}