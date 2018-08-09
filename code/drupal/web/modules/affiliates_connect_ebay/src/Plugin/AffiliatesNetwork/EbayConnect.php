<?php

namespace Drupal\affiliates_connect_ebay\Plugin\AffiliatesNetwork;

use Drupal\affiliates_connect\AffiliatesNetworkBase;
use Drupal\affiliates_connect_ebay\EbayItems;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Url;

/**
 * Contains Plugin ID and Plugin definition info for affiliates_connect_ebay.
 *
 * @AffiliatesNetwork(
 *  id = "affiliates_connect_ebay",
 *  label = @Translation("Ebay"),
 *  description = @Translation("Plugin provided by affiliates_connect_ebay."),
 * )
 */
class EbayConnect extends AffiliatesNetworkBase implements EbayConnectInterface {
  /**
   * Stores the options used in this request.
   *
   * @var array
   */
  protected $options = [];

  /**
   * Stores the generated signature link.
   *
   * @var array
   */
  protected $url;

  /**
   * Stores the results of this request when executed.
   *
   * @var array
   */
  protected $results = [];

  /**
   * The App ID (or Client ID) for the Product Advertising API account.
   *
   * @var string
   */
  protected $app_id;

  /**
   * The path to the endpoint for making Product Advertising API requests.
   *
   * @var string
   */
  protected $service;

  /**
   * Location
   * @var [type]
   */
  protected $locale;

  /**
   * The domain of the endpoint for making Product Advertising API requests.
   *
   * @var string
   */
  protected $serviceEndpoint = '.ebay.com/services/search/FindingService/v1';

  protected $response_format;


  /**
   * Set Credentials.
   *
   * @param string $app_id
   *   The App ID (or Client ID) for the Product Advertising API account.
   * @param string $service_endpoint
   *   The path to the endpoint for making Product Advertising API requests.
   * @param string $locale
   *   Location
   * @param string $response_format
   *   Response format like XML, JSON
   */
  public function setCredentials(string $app_id, string $service_endpoint, string $locale = 'IN', string $response_format = 'XML') {
    $this->app_id = $app_id;
    if ($service_endpoint == 'production') {
      $this->service = 'svcs';
    } else {
      $this->service = 'svcs.sandbox';
    }
    $this->response_format = $response_format;
    $this->locale = $locale;
  }

  /**
   * @inheritdoc
   */
  public function prepareLink() {
    if (empty($this->options['SECURITY-APPNAME'])) {
      if (empty($this->app_id)) {
        throw new \InvalidArgumentException('Missing APP Id. Need to be passed as an option or set in the constructor.');
      }
      else {
        $this->setOption('SECURITY-APPNAME', $this->app_id);
      }
    }

    // Add the locale
    $global_id = 'EBAY-' . $this->locale;

    // Add a Service Version.
    $this->options['SERVICE-VERSION'] = '1.0.0';
    $this->options['GLOBAL-ID'] =  $global_id;
    $this->options['outputSelector(0)'] = 'SellerInfo';
    $this->options['outputSelector(1)'] = 'GalleryInfo';
    $this->options['RESPONSE-DATA-FORMAT'] = $this->response_format;
    $endpoint = 'https://' . $this->service . $this->serviceEndpoint;
    $url = Url::fromUri($endpoint, ['query' => $this->options]);
    $this->url = $url->toString() . '&REST-PAYLOAD';
  }

  /**
   * @inheritdoc
   */
  public function execute() {

    $data = $this->get($this->url, []);
    if (isset($data) && $data->getStatusCode() == 200) {
      $XML = new \SimpleXMLElement($data->getBody());
      $this->cleanResult($XML);
    }
    return $this;
  }

  /**
   * @inheritdoc.
   */
  public function setOption($name, $value) {
    if (empty($name)) {
      throw new \InvalidArgumentException('Invalid option name: ' . $name);
    }
    $this->options[$name] = $value;
    return $this;
  }

  /**
   * @inheritdoc.
   */
  public function setOptions(array $options) {
    foreach($options as $name => $value) {
      if (empty($name)) {
        throw new \InvalidArgumentException('Invalid option name: ' . $name);
      }
      $this->setOption($name, $value);
    }
    return $this;
  }

  /**
   * @inheritdoc.
   */
  public function getResults() {
    return $this->results;
  }

  /**
   * @inheritdoc.
   */
  public function getLink() {
    return $this->url;
  }

  /**
   * @inheritdoc.
   */
  public function findItemsByCategory(string $category_id)
  {
    $this->setOptions([
      'OPERATION-NAME' => 'findItemsByCategory',
      'categoryId' => $category_id,
    ]);
    $this->prepareLink();
    return $this;
  }

  /**
   * @inheritdoc.
   */
  public function findItemsByKeywords(string $keyword) {
    $this->setOptions([
      'OPERATION-NAME' => 'findItemsByKeywords',
      'keywords' => $keyword,
    ]);
    $this->prepareLink();
    return $this;
  }

  /**
   * @inheritdoc.
   */
  public function cleanResult($XML)
  {
    $this->results = EbayItems::createWithXml($XML);
  }

  /**
   * @inheritdoc.
   */
  public function unsetOption(string $name)
  {
    unset($this->options[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function search(ImmutableConfig $config, string $keyword)
  {
    $this->setCredentials(
      $config->get('ebay_app_id'),
      $config->get('service_endpoint'),
      $config->get('locale')
    );

    $products = $this->findItemsByKeywords($keyword)->execute()->getResults();
    $response = [];
    foreach ($products->Items as $key => $product) {
      $response[] = $this->buildData($product);
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function buildData($product_data) {
    $product = [
      'name' => $product_data->Title,
      'plugin_id' => 'affiliates_connect_ebay',
      'product_id' => $product_data->ItemId,
      'product_description' => '',
      'image_urls' => $product_data->getImage('Small')->URL,
      'product_family' => $product_data->Category,
      'currency' => $product_data->getCurrency(),
      'maximum_retail_price' => $product_data->Price,
      'vendor_selling_price' => $product_data->Price,
      'vendor_special_price' => $product_data->Price,
      'product_url' => $product_data->URL,
      'product_brand' => '',
      'in_stock' => TRUE,
      'cod_available' => TRUE,
      'discount_percentage' => '',
      'product_warranty' => '',
      'offers' => '',
      'size' => '',
      'color' => '',
      'seller_name' => $product_data->Manufacturer,
      'seller_average_rating' => '',
      'additional_data' => '',
    ];
    return $product;
  }

}
