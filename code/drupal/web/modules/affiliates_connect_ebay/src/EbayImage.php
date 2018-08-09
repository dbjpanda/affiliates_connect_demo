<?php

namespace Drupal\affiliates_connect_ebay;

/**
* Class EbayImage to generate image url
*/
class EbayImage {

    /**
     * URL of the picture.
     * @var string
     */
    public $URL = '';

    /**
    * Create an instance of EbayItem with a SimpleXMLElement object.
    *
    * @param SimpleXMLElement $XML
    * @return EbayImage
    */
    public static function createWithXml($XML) {
        $image = new EbayImage();
        $image->URL = (string) $XML;
        return $image;
    }

    public function __toString() {
        return 'EbayImage';
    }
}
