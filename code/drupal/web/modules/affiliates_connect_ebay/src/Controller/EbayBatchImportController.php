<?php

namespace Drupal\affiliates_connect_ebay\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\affiliates_connect\Entity\AffiliatesProduct;
use Drupal\affiliates_connect\AffiliatesNetworkManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EbayBatchImportController for Batch Importing.
 */
class EbayBatchImportController extends ControllerBase {

  /**
   * The affiliates network manager.
   *
   * @var \Drupal\affiliates_connect\AffiliatesNetworkManager
   */
  private $affiliatesNetworkManager;

  /**
   * The Ebay Instance.
   *
   * @var \Drupal\affiliates_connect_ebay\Plugin\AffiliatesNetwork\EbayConnect
   */
  private $ebay;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.affiliates_network')
    );
  }

  /**
   * AffiliatesConnectController constructor.
   *
   * @param \Drupal\affiliates_connect\AffiliatesNetworkManager $affiliatesNetworkManager
   *   The affiliates network manager.
   */
  public function __construct(AffiliatesNetworkManager $affiliatesNetworkManager) {
    $this->affiliatesNetworkManager = $affiliatesNetworkManager;
    $this->ebay = $this->affiliatesNetworkManager->createInstance('affiliates_connect_ebay');
    $this->ebay->setCredentials(
      $this->config('affiliates_connect_ebay.settings')->get('ebay_app_id'),
      $this->config('affiliates_connect_ebay.settings')->get('service_endpoint'),
      $this->config('affiliates_connect_ebay.settings')->get('locale')
    );
  }

  /**
   * Start Batch Processing.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request Object
   */
  public function startBatch(Request $request)
  {
    // If enabled native_apis
    $config = $this->config('affiliates_connect_ebay.settings');
    if (!$config->get('native_api')) {
      drupal_set_message($this->t('Configure Ebay native api to import data'), 'error', FALSE);
      return $this->redirect('affiliates_connect_ebay.settings');
    }

    if (!$config->get('data_storage')) {
      drupal_set_message($this->t('Data Storage is not enabled'), 'error', FALSE);
      return $this->redirect('affiliates_connect_ebay.settings');
    }


    $params = $request->query->all();

    $batch = [];

    $operations = [];
    $title = '';
    $products = $this->fetchOne($params);
    if (!isset($products->TotalPages)) {
      drupal_set_message($this->t('No products found to import'), 'status', FALSE);
    }
    $total_pages = $products->TotalPages;

    if ($total_pages > 100) {
      // Limitation of Ebay
      $total_pages = 100;
    } else {
      drupal_set_message($this->t('No products found to import'), 'status', FALSE);
      return;
    }
    for ($i = 1; $i <= $total_pages; $i++) {
      $operations[] = [[get_called_class(), 'startBatchImporting'], [$params, $i]];
    }
    $title = $this->t('Importing products from @num pages', ['@num' => $total_pages]);

    $batch = [
      'title' => $title,
      'init_message' => $this->t('Importing..'),
      'operations' => $operations,
      'progressive' => TRUE,
      'finished' => [get_called_class(), 'batchFinished'],
    ];
    batch_set($batch);
    return batch_process('/admin/config/affiliates-connect/overview');
  }


  /**
   * Batch for Importing of products.
   *
   * @param string $key
   * @param string $value
   * @param $context
   */
  public static function startBatchImporting($params, $i, &$context) {
    sleep(2);
    $categories = Self::importingProducts($params, $i);
    $context['results']['processed']++;
    $context['message'] = 'Completed importing pages : ' . $i;
  }

  /**
   * Batch finished callback.
   *
   * @param $success
   * @param $results
   * @param $operations
   */
  public static function batchFinished($success, $results, $operations) {
    if ($success) {
     drupal_set_message(t("The products are successfully imported from Ebay."));
    }
    else {
      $error_operation = reset($operations);
      drupal_set_message(t('An error occurred while processing @operation with arguments : @args', array('@operation' => $error_operation[0], '@args' => print_r($error_operation[0], TRUE))), 'error');
    }
  }

  /**
   * To fetch first page.
   *
   * @param string $keyword
   * @param string $category
   *
   * @return /Drupal/affiliates_connect_ebay/EbayItems
   */
  public function fetchOne($params) {
    $products;
    if ($params['category'] == 'keyword' && isset($params['keyword'])) {
      $products = $this->ebay->findItemsByKeywords($params['keyword'])->execute()->getResults();
    } else {
      $products = $this->ebay->findItemsByCategory($params['category'])->execute()->getResults();
    }
    return $products;
  }

  /**
   * To fetch first page.
   *
   * @param string $keyword
   * @param string $category
   * @param int $i
   *
   */
  public function importingProducts($params, $i)
  {
    $ebay = \Drupal::service('plugin.manager.affiliates_network')->createInstance('affiliates_connect_ebay');
    $config = \Drupal::configFactory()->get('affiliates_connect_ebay.settings');
    $app_id = $config->get('ebay_app_id');
    $service_endpoint = $config->get('service_endpoint');
    $locale = $config->get('locale');

    $ebay->setCredentials($app_id, $service_endpoint, $locale);
    $ebay->setOption('paginationInput.pageNumber', $i);
    $products;
    if ($params['category'] == 'keyword' && isset($params['keyword'])) {
      $products = $ebay->findItemsByKeywords($params['keyword'])->execute()->getResults();
    } else {
      $products = $ebay->findItemsByCategory($params['category'])->execute()->getResults();
    }
    foreach ($products->Items as $key => $value) {
      $product = Self::buildImportData($value);
      AffiliatesProduct::createOrUpdate($product, $config);
    }

  }

  /**
   * To fetch first page.
   *
   * @param /Drupal/affiliates_connect_ebay/EbayItems $product_data
   *
   * @return array
   *
   */
  public function buildImportData($product_data) {
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
