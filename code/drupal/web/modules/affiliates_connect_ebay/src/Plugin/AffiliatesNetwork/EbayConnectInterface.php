<?php

namespace Drupal\affiliates_connect_ebay\Plugin\AffiliatesNetwork;

use Drupal\affiliates_connect\AffiliatesNetworkInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Provides an interface for Ebay Affiliates network plugin.
 */
interface EbayConnectInterface extends AffiliatesNetworkInterface {

  /**
   * Prepares the API link.
   *
   * @return null
   */
  public function prepareLink();


  /**
   * Return the API link.
   *
   * @return EbayConnect url
   */
  public function getLink();


  /**
   * Executes the associated operation.
   *
   * @return SimpleXMLElement
   */
  public function execute();

  /**
   * @param string $name
   *   The name of the option to set.
   * @param string $value
   *   The value for that option.
   *
   * @return EbayConnect
   */
  public function setOption($name, $value);

  /**
   * @param array $options
   *   Options in the form of (string) optionName => (string) optionValue.
   *
   * @return EbayConnect
   */
  public function setOptions(array $options);

  /**
   * Returns the result of an Ebay request.
   *
   * @return array
   */
  public function getResults();

  /**
   * Returns the Clean formateed data.
   *
   * @param SimpleXMLElement $XML SimpleXMLElement object.
   *
   * @return EbayItems
   */
  public function cleanResult($XML);

  /**
   * Unset the elements from options array.
   *
   * @param string $name Name of the key.
   */
  public function unsetOption(string $name);

  /**
   * Finds the item/product by category id.
   * @param  string $category_id Category id
   * @return EbayConnect
   */
  public function findItemsByCategory(string $category_id);

  /**
   * Finds the item/product by keywords.
   * @param  string $keyword keyword to search any item
   * @return EbayConnect
   */
  public function findItemsByKeywords(string $keyword);

  /**
   * Search functionality
   *  @param ImmutableConfig $config
   *  configuration of the plugin
   * @param  string $keyword
   *  keyword to be searched
   * @return array
   *  Array of products
   */
  public function search(ImmutableConfig $config, string $keyword);

  /**
   * Create the array with appropriate data.
   * @param   /Drupal/affiliates_connect_ebay/EbayItems $product_data
   * @return array
   */
  public function buildData($product_data);

}
