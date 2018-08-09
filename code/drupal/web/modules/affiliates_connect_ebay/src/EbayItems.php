<?php

namespace Drupal\affiliates_connect_ebay;

/**
 * Class EbayItems to create the array of AmazonItem objects.
 */
class EbayItems {

  /**
   * Number of products that Amazon returns.
   * @var string
   */
  public $TotalResults = '';

  /**
   * Number of pages of products that Amazon returns.
   * @var string
   */
  public $TotalPages = '';

  /**
   * URL of Amazon page which contain more products.
   * @var string
   */
  public $MoreSearchResultsUrl = '';

  /**
   * Array of AmazonItem objects
   * @var array
   */
  public $Items = [];

  /**
  * Create an instance of EbayItems with a SimpleXMLElement object.
  *
  * @param SimpleXMLElement $XML
  * @return EbayItems
  */
  public static function createWithXml($XML) {

    $EbayItems = new EbayItems();

    $XML = $XML;

    if ($XML->ack == 'Failure') {
      drupal_set_message(t((string)$XML->errorMessage->error->message), 'error', FALSE);
      return;
    }


    if(isset($XML->paginationOutput->totalEntries))
    $EbayItems->TotalResults = (int) $XML->paginationOutput->totalEntries;
    if(isset($XML->paginationOutput->totalPages))
    $EbayItems->TotalPages = (int) $XML->paginationOutput->totalPages;
    if(isset($XML->itemSearchURL))
    $EbayItems->MoreSearchResultsUrl = (string) $XML->itemSearchURL;

    if (isset($XML->searchResult)) {
      foreach($XML->searchResult->item as $XMLItem)
      $EbayItems->Items[] = EbayItem::createWithXml($XMLItem);
    }

    return $EbayItems;
  }

  public function __toString() {
    return 'EbayItems';
  }
}
