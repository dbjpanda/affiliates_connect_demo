<?php

namespace Drupal\affiliates_connect_ebay;

/**
 * Class EbayItem to create individual item object from XML.
 */
class EbayItem {

  /**
   * Ebay item id (It not a product id).
   * @var string
   */
  public $ItemId = '';

  /**
   * Manufacturer/Seller of the product.
   * @var string
   */
  public $Manufacturer = '';

  /**
   * Category of the product.
   * @var string
   */
  public $Category = '';

  /**
   * Title of the product.
   * @var string
   */
  public $Title = '';

  /**
   * URL of the product.
   * @var string
   */
  public $URL = '';


  /**
   * M.R.P of the product.
   * @var string
   */
  public $Price = '';


  /**
   * Currency Code of the product according to the locale.
   * @var string
   */
  public $CurrencyCode = '';

  /**
   * Image urls of the product.
   * @var array
   */
  private $Images = [];

  /**
   * Thumbnail and Small images are the same size.
   */
  const IMAGE_SMALL = 'Small';

  /**
   * Medium Image.
   */
  const IMAGE_MEDIUM = 'Medium';

  /**
   * Large Image.
   */
  const IMAGE_LARGE = 'Large';

  /**
   * Euro currency.
   */
  const CURRENCY_EUR = 'EUR';

  /**
   * US Dollar.
   */
  const CURRENCY_USD = 'USD';

  /**
   * British Pound.
   */
  const CURRENCY_GBP = 'GBP';

  /**
   * Japanese currency.
   */
  const CURRENCY_JPY = 'JPY';

  /**
   * Indian Currency.
   */
  const CURRENCY_INR = 'INR';

  /**
   * Brazilian Real Currency of Brazil.
   */
  const CURRENCY_BRL = 'BRL';

  /**
   * Canadian currency.
   */
  const CURRENCY_CAD = 'CAD';

  /**
   * Chinese Yuan currency of China.
   */
  const CURRENCY_CNY = 'CNY';

  /**
  * Create an instance of EbayItem with a SimpleXMLElement object. (->Items)
  *
  * @param SimpleXMLElement $XML
  * @return EbayItems
  */
  public static function createWithXml($XML) {

    $EbayItem = new EbayItem();

    if(isset($XML->itemId))
    $EbayItem->ItemId = (string) $XML->itemId;

    if(isset($XML->primaryCategory->categoryName))
    $EbayItem->Category = (string) $XML->primaryCategory->categoryName;

    if(isset($XML->title))
    $EbayItem->Title = (string) $XML->title;

    if(isset($XML->sellerInfo->sellerUserName))
    $EbayItem->Manufacturer = (string) $XML->sellerInfo->sellerUserName;

    if(isset($XML->sellingStatus->currentPrice))
    $EbayItem->Price = (string) $XML->sellingStatus->currentPrice;

    if(isset($XML->sellingStatus->currentPrice))
    $EbayItem->CurrencyCode = (string) $XML->sellingStatus->currentPrice['currencyId'];


    if(isset($XML->viewItemURL))
    $EbayItem->URL = (string) $XML->viewItemURL;


    if(isset($XML->galleryInfoContainer)) {
      $EbayItem->Images[EbayItem::IMAGE_SMALL] = EbayImage::createWithXml($XML->galleryInfoContainer->galleryURL[2]);

      $EbayItem->Images[EbayItem::IMAGE_MEDIUM] = EbayImage::createWithXml($XML->galleryInfoContainer->galleryURL[1]);

      $EbayItem->Images[EbayItem::IMAGE_LARGE] = EbayImage::createWithXml($XML->galleryInfoContainer->galleryURL[0]);
    }

    return $EbayItem;
  }

  /**
  * Return currency symbol of $this->CurrencyCode
  *
  * @return string
  */
  public function getCurrency() {
      switch($this->CurrencyCode) {
          case EbayItem::CURRENCY_EUR :
              return 'EUR;';
          break;
          case EbayItem::CURRENCY_USD :
              return '$';
          break;
          case EbayItem::CURRENCY_JPY :
              return '￥';
          break;
          case EbayItem::CURRENCY_GBP :
              return '£';
          break;
          case EbayItem::CURRENCY_INR :
              return '₹';
          break;
          case EbayItem::CURRENCY_BRL :
              return '‎R$';
          break;
          case EbayItem::CURRENCY_CAD :
              return 'CDN$';
          break;
          case EbayItem::CURRENCY_CNY :
              return '￥';
          break;
      }
      return '';
  }

  /**
  * Return an EbayImage object.
  *
  * @param string $size Use constant IMAGE_(.*) of EbayItem class
  * @return EbayImage
  */
  public function getImage($size) {
      return $this->Images[$size];
  }

  public function __toString() {
      return 'EbayItem';
  }
}
