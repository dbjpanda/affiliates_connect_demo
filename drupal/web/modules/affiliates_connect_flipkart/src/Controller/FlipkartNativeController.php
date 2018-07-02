<?php

namespace Drupal\affiliates_connect_flipkart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\affiliates_connect\Entity\AffiliatesProduct;

/**
 * Use Native API of Flipkart to collect data.
 */
class FlipkartNativeController extends ControllerBase {

  /**
   * The Guzzle client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;


  /**
   * Collect response from the url.
   *
   * @param string $url
   *   An url where request is to be made
   * @param  array $options
   *   Containing headers
   *
   * @return \Guzzle\Http\Message\Response
   *   A Guzzle response.
   */
  public function get($url, $options) {

    $client = new Client();

    try {
      $response = $client->get($url, $options);
    }
    catch (RequestException $e) {
      $args = ['%site' => $url, '%error' => $e->getMessage()];
      throw new \RuntimeException($this->t('This %site seems to be broken because of error "%error".', $args));
    }
    return $response;
  }

  /**
   * Fetching categories from the Category API.
   *
   * @return array
   *   A collection of categories along with category url
   */
  public function categories()
  {
    $fk_affiliate_id = $this->config('affiliates_connect_flipkart.settings')->get('flipkart_tracking_id');
    $token = $this->config('affiliates_connect_flipkart.settings')->get('flipkart_token');

    $header = [
      'Fk-Affiliate-Id' => $fk_affiliate_id,
      'Fk-Affiliate-Token' => $token,
      'Accept' => 'application/json',
    ];

    $url = 'https://affiliate-api.flipkart.net/affiliate/api/' . $fk_affiliate_id . '.json';

    $response = $this->get($url, ['headers' => $header]);
    $body = $response->getBody();
    $body = json_decode($body, true);

    $categories = [];
    foreach ($body['apiGroups']['affiliate']['apiListings'] as $key => $value) {
      $categories[$key] = $value['availableVariants']['v1.1.0']['get'];
    }
    return $categories;
  }

  /**
   * Collect products data from Product APIs.
   *
   * @param string $product_url
   *   An url where request is to be made
   */
  public function products($product_url)
  {

    $fk_affiliate_id = $this->config('affiliates_connect_flipkart.settings')->get('flipkart_tracking_id');
    $token = $this->config('affiliates_connect_flipkart.settings')->get('flipkart_token');

    $header = [
      'Fk-Affiliate-Id' => $fk_affiliate_id,
      'Fk-Affiliate-Token' => $token,
      'Accept' => 'application/json',
    ];
    $response = $this->get($product_url, ['headers' => $header]);
    $products_data = $response->getBody();
    $products_data = json_decode($products_data, true);
    foreach ($products_data['products'] as $key => $value) {
      try {
        $uid = \Drupal::currentUser()->id();

        $product = AffiliatesProduct::create([
          'uid' => $uid,
          'name' => $value['productBaseInfoV1']['title'],
          'plugin_id' => 'affiliates_connect_flipkart',
          'product_id' => $value['productBaseInfoV1']['productId'],
          'product_description' => $value['productBaseInfoV1']['productDescription'],
          'image_urls' => $value['productBaseInfoV1']['imageUrls']['400x400'],
          'product_family' => $value['productBaseInfoV1']['categoryPath'],
          'currency' => $value['productBaseInfoV1']['maximumRetailPrice']['currency'],
          'maximum_retail_price' => $value['productBaseInfoV1']['maximumRetailPrice']['amount'],
          'vendor_selling_price' => $value['productBaseInfoV1']['flipkartSellingPrice']['amount'],
          'vendor_special_price' => $value['productBaseInfoV1']['flipkartSpecialPrice']['amount'],
          'product_url' => $value['productBaseInfoV1']['productUrl'],
          'product_brand' => $value['productBaseInfoV1']['productBrand'],
          'in_stock' => $value['productBaseInfoV1']['inStock'],
          'cod_available' => $value['productBaseInfoV1']['codAvailable'],
          'discount_percentage' => $value['productBaseInfoV1']['discountPercentage'],
          'offers' => implode(',', $value['productBaseInfoV1']['offers']),
          'size' => $value['productBaseInfoV1']['attributes']['size'],
          'color' => $value['productBaseInfoV1']['attributes']['color'],
          'seller_name' => $value['productShippingInfoV1']['sellerName'],
          'seller_average_rating' => $value['productShippingInfoV1']['sellerAverageRating'],
          'additional_data' => '',
          'status' => 1,
        ]);
        $product->save();
      }
      catch (Exception $e) {
        echo $e->getMessage();
      }
    }
  }
}
