<?php

namespace Drupal\affiliates_connect_ebay\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\affiliates_connect\AffiliatesNetworkManager;
use Drupal\affiliates_connect_ebay\EbayLocale;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\affiliates_connect\Entity\AffiliatesProduct;

/**
 * Class EbayItemSearchForm.
 */
class EbayItemSearchForm extends FormBase {

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
   * The search data
   * @var array|null
   */
  protected $result;


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.affiliates_network'),
      $container->get('user.private_tempstore')
    );
  }

  /**
   * AffiliatesConnectController constructor.
   *
   * @param \Drupal\affiliates_connect\AffiliatesNetworkManager $affiliatesNetworkManager
   *   The affiliates network manager.
   */
  public function __construct(AffiliatesNetworkManager $affiliatesNetworkManager, PrivateTempStoreFactory $temp_store_factory) {
    $this->affiliatesNetworkManager = $affiliatesNetworkManager;
    $this->results = $temp_store_factory->get('ebay_search');
    $this->ebay = $this->affiliatesNetworkManager->createInstance('affiliates_connect_ebay');
    $this->ebay->setCredentials(
      $this->config('affiliates_connect_ebay.settings')->get('ebay_app_id'),
      $this->config('affiliates_connect_ebay.settings')->get('service_endpoint'),
      $this->config('affiliates_connect_ebay.settings')->get('locale')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'affiliates_connect_ebay_search';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('affiliates_connect_ebay.settings');

    // query get params
    $query = \Drupal::request()->query->all();
    $data = $this->results->get('data');
    $keyword = $this->results->get('keyword');
    $category = $this->results->get('category');


    if (isset($query['page'])) {
      $this->ebay->setOption('paginationInput.pageNumber', $query['page'] + 1);
      if ($category == 'keyword') {
        $data = $this->ebay->findItemsByKeywords($keyword)->execute()->getResults();
      } else {
        $data = $this->ebay->findItemsByCategory($category)->execute()->getResults();
      }
      $this->results->set('data', $data);
    }

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
          'class' => ['container-inline'],
      ],
    ];

    $form['container']['category'] = [
      '#type' => 'select',
      '#options' => $this->buildCategories(),
      '#attributes' => ['class' => ['button']],
      '#empty_option' => 'Choose a Category',
      '#default_value' => $this->results->get('category'),
      '#required' => TRUE,
    ];

    $form['container']['keyword'] = [
      '#type' => 'textfield',
      '#default_value' => $this->results->get('keyword'),
      '#size' => 60,
      '#maxlength' => 60,
      '#placeholder' => 'Enter a keyword',
      '#required' => TRUE,
    ];

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Search'),
    ];

    if ($this->results->get('data')) {

        $total_items = (int) $this->results->get('data')->TotalResults;
        if ($total_items) {
          if ($total_items > 10000) {
            $total_items = 10000;
          }
          pager_default_initialize($total_items, 100);
        }


      if ($config->get('data_storage')) {

        $form['table'] = [
          '#type' => 'tableselect',
          '#header' => $this->getHeader(),
          '#options' => $this->buildRows(),
          '#multiple' => true,
          '#empty' => $this->t('No products found'),
        ];

        $form['pager'] = [
          '#type' => 'pager'
        ];

        $form['import'] = [
          '#type' => 'submit',
          '#name' => 'import',
          '#button_type' => 'primary',
          '#value' => $this->t('Import'),
        ];

        $form['import_all'] = [
          '#type' => 'submit',
          '#name' => 'import_all',
          '#button_type' => 'primary',
          '#value' => $this->t('Import All'),
        ];
      } else {
        $form['table'] = [
          '#type' => 'table',
          '#header' => $this->getHeader(),
          '#rows' => $this->buildRows(),
          '#multiple' => true,
          '#empty' => $this->t('No products found'),
        ];

        $form['pager'] = [
          '#type' => 'pager',
          '#quantity' => 10
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // query get params
    \Drupal::request()->query->set('page', 0);


    $keyword = $values['keyword'];
    $category = $values['category'];

    if (!$this->config('affiliates_connect_ebay.settings')->get('native_api')) {
      drupal_set_message($this->t('Configure Ebay native api to import data'), 'error', FALSE);
      $this->results->set('data', '');
      $this->results->set('keyword', '');
      $this->results->set('category', '');
      return $this->redirect('affiliates_connect_ebay.settings');
    }

    $button_clicked = $form_state->getTriggeringElement()['#name'];
    if ($button_clicked == 'import_all') {
      $query = [
        'category' => $category,
        'keyword' => $keyword,
      ];
      $form_state->setRedirect('affiliates_connect_ebay.batch_import', $query);
    } elseif ($button_clicked == 'import') {
      $selected_names = array_filter($values['table']);
      $this->importProducts($selected_names);
      return;
    }

    $this->results->set('keyword', $keyword);
    $this->results->set('category', $category);

  }

  /**
   * Add the header to the table
   * @return array header fields
   */
  public function getHeader()
  {
    $header = [
     'image' => $this->t('Image'),
     'name' => $this->t('Product Name'),
     'mrp' => $this->t('M.R.P'),
   ];
   return $header;
  }
  /**
   * Build the rows for the table
   * @return array fetched data from the API
   */
  public function buildRows()
  {
    $row = [];
    $data = $this->results->get('data');
    if (!$data->TotalResults) {
      return;
    }
    foreach ($data->Items as $key => $item) {
      $row[$key+1] = [
        'image' => [
          'data' => [
            '#prefix' => '<div><img src="' . $item->getImage('Small')->URL . '" width=30 height=40> &nbsp;&nbsp;',
            '#suffix' => '</div>',
          ],
        ],
        'name' => [
          'data' => [
            '#prefix' => '<a href="' . $item->URL . '">' . $item->Title . '</a>'
          ],
        ],
        'mrp' => ($item->Price) ? $item->getCurrency() . $item->Price : '-',
      ];
    }
    return $row;
  }

  /**
   * Build the Categories on the basis of locale.
   * @return array
   */
  public function buildCategories() {
    $locale = $this->config('affiliates_connect_ebay.settings')->get('locale');
    $categories = EbayLocale::getCategories($locale);
    $categories['keyword'] = 'Search by Keyword only.';
    return $categories;
  }

  /**
   * Save data to the Product Entity.
   * @param  array $element Seleted table rows.
   */
  public function importProducts($element)
  {
    $config = \Drupal::configFactory()->get('affiliates_connect_ebay.settings');
    $data = $this->results->get('data');
    foreach ($element as $value) {
      $product = $this->buildImportData($data->Items[$value - 1]);
      AffiliatesProduct::createOrUpdate($product, $config);
    }
    drupal_set_message($this->t('Products are imported successfully'), 'status', FALSE);
  }

  /**
   * Create the array with appropriate data.
   * @param   /Drupal/affiliates_connect_ebay/AmazonItems $product_data
   * @return array
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
