<?php

namespace Drupal\affiliates_connect_flipkart\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\affiliates_connect\Form\AffiliatesConnectSettingsForm;
use Drupal\affiliates_connect_flipkart\Controller\FlipkartNativeController;

/**
 * Class AffiliatesFlipkartSettingsForm.
 */
class AffiliatesFlipkartSettingsForm extends AffiliatesConnectSettingsForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'affiliates_connect_flipkart_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array_merge(
      parent::getEditableConfigNames(),
      ['affiliates_connect_flipkart.settings']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('affiliates_connect_flipkart.settings');

    $form['flipkart_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Affiliates Connect Flipkart Settings'),
      '#open' => TRUE,
      '#description' => $this->t('If you are not Flipkart Affiliate, Please Sign
      up for Flipkart Affiliate program here: <a href="@affiliate-marketing">@affiliate-marketing</a>',
          ['@affiliate-marketing' => 'https://affiliate.flipkart.com/']),
    ];

    $form['flipkart_settings']['native_api'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Native API'),
      '#description' => $this->t('Enable Affiliate Marketing using affiliate api'),
      '#default_value' => $config->get('native_api'),
    ];

    $form['flipkart_settings']['native_api_form'] = [
      '#type' => 'details',
      '#title' => $this->t('API Token'),
      '#open' => TRUE,
      '#states' => [
        "visible" => [
          "input[name='native_api']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['native_api_form']['flipkart_tracking_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Affiliate Tracking ID'),
      '#default_value' => $config->get('flipkart_tracking_id'),
      '#size' => 60,
      '#maxlength' => 60,
      '#states' => [
        "required" => [
          "input[name='native_api']" => ["checked" => TRUE],
        ],
      ],
      '#machine_name' => [
        'exists' => [
          $this,
          'exists',
        ],
      ],
    ];

    $form['flipkart_settings']['native_api_form']['flipkart_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#default_value' => $config->get('flipkart_token'),
      '#size' => 60,
      '#maxlength' => 60,
      '#states' => [
        "required" => [
          "input[name='native_api']" => ["checked" => TRUE],
        ],
      ],
      '#machine_name' => [
        'exists' => [
          $this,
          'exists',
        ],
      ],
    ];


    $form['flipkart_settings']['scraper_api'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Scraper API'),
      '#description' => $this->t('Enable to use Scraper API to overcome limitation of Affiliate API'),
      '#default_value' => $config->get('scraper_api'),
    ];

    $form['flipkart_settings']['data_storage'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Data Storage'),
      '#description' => $this->t('Enable to store product data in your site database.'),
      '#default_value' => $config->get('data_storage'),
    ];

    $form['flipkart_settings']['data_storage_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Plugin Storage Token'),
      '#open' => TRUE,
      '#states' => [
        "visible" => [
          "input[name='data_storage']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['affiliate_import'] = [
      '#type' => 'select',
      '#title' => $this->t('Import period for Affiliate API'),
      '#options' => [
        'every_day' => $this->t('Everyday'),
        'every_15_days' => $this->t('Every 15 days'),
        'every_week' => $this->t('Every week'),
        'every_week' => $this->t('Every month'),
      ],
      '#attributes' => ['class' => ['select-bbq-selector']],
      '#empty_option' => 'Select',
      '#default_value' => $config->get('affiliate_import'),
      '#states' => [
        "required" => [
          "input[name='data_storage']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['submit'] = [
      '#type' => 'submit',
      '#name' => 'native_import',
      '#value' => $this->t('Import Now'),
    ];


    $form['flipkart_settings']['data_storage_form']['scraper_import'] = [
      '#type' => 'select',
      '#title' => $this->t('Import period for Scraper API'),
      '#options' => [
        'every_day' => $this->t('Everyday'),
        'every_15_days' => $this->t('Every 15 days'),
        'every_week' => $this->t('Every week'),
        'every_week' => $this->t('Every month'),
      ],
      '#attributes' => ['class' => ['select-bbq-selector']],
      '#empty_option' => 'Select',
      '#default_value' => $config->get('scraper_import'),
      '#states' => [
        "required" => [
          "input[name='data_storage']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['content_scrape'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Scrape Content'),
      '#description' => $this->t('Scrape content on a daily basis (Update)'),
      '#default_value' => $config->get('content_scrape'),
    ];

    $form['flipkart_settings']['data_storage_form']['content_scrape_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Content Scrape Token'),
      '#open' => TRUE,
      '#states' => [
        "visible" => [
          "input[name='content_scrape']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['content_scrape_form']['scrape_timer'] = [
      '#type' => 'select',
      '#title' => $this->t('Import period for content scraping'),
      '#options' => [
        'every_30_mins' => $this->t('Every 30 mins'),
        'every_hour' => $this->t('Every 1 hour'),
        'every_2_hours' => $this->t('Every 2 hours'),
        'every_5_hours' => $this->t('Every 5 hours'),
        'every_10_hours' => $this->t('Every 10 hours'),
        'every_15_hours' => $this->t('Every 15 hours'),
        'every_20_hours' => $this->t('Every 20 hours'),
      ],
      '#attributes' => ['class' => ['select-bbq-selector']],
      '#empty_option' => 'Select',
      '#default_value' => $config->get('scrape_timer'),
      '#states' => [
        "required" => [
          "input[name='content_scrape']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['content_scrape_form']['full_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Full Content'),
      '#default_value' => $config->get('full_content'),
      '#states' => [
        "required" => [
          "input[name='content_scrape_form']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['content_scrape_form']['reviews'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reviews'),
      '#default_value' => $config->get('reviews'),
      '#states' => [
        "required" => [
          "input[name='content_scrape_form']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['content_scrape_form']['available'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Availability'),
      '#default_value' => $config->get('available'),
      '#states' => [
        "required" => [
          "input[name='content_scrape_form']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['content_scrape_form']['size'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Size'),
      '#default_value' => $config->get('size'),
      '#states' => [
        "required" => [
          "input[name='content_scrape_form']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['content_scrape_form']['color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Color'),
      '#default_value' => $config->get('color'),
      '#states' => [
        "required" => [
          "input[name='content_scrape_form']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['content_scrape_form']['offers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Offers'),
      '#default_value' => $config->get('offers'),
      '#states' => [
        "required" => [
          "input[name='content_scrape_form']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['flipkart_settings']['data_storage_form']['content_scrape_form']['others'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Others'),
      '#default_value' => $config->get('others'),
      '#states' => [
        "required" => [
          "input[name='content_scrape_form']" => ["checked" => TRUE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('affiliates_connect_flipkart.settings')
      ->set('native_api', $values['native_api'])
      ->set('flipkart_tracking_id', $values['flipkart_tracking_id'])
      ->set('flipkart_token', $values['flipkart_token'])
      ->set('scraper_api', $values['scraper_api'])
      ->set('data_storage', $values['data_storage'])
      ->set('affiliate_import', $values['affiliate_import'])
      ->set('scraper_import', $values['scraper_import'])
      ->set('content_scrape', $values['content_scrape'])
      ->set('scrape_timer', $values['scrape_timer'])
      ->set('full_content', $values['full_content'])
      ->set('reviews', $values['reviews'])
      ->set('available', $values['available'])
      ->set('size', $values['size'])
      ->set('color', $values['color'])
      ->set('offers', $values['offers'])
      ->set('others', $values['others'])
      ->save();
    parent::submitForm($form, $form_state);

    $fk_affiliate_id = $values['flipkart_tracking_id'];
    $token = $values['flipkart_token'];
    $button_clicked = $form_state->getTriggeringElement()['#name'];

    if ($button_clicked == 'native_import') {
      $batch = [];
      $flipkart_native = new FlipkartNativeController();
      $categories = $flipkart_native->categories();
      $total_categories = count($categories);
      $con = 0;
      foreach ($categories as $key => $value) {
        $operations[] = [[$this, 'startBatchImporting'], [$key, $value]];
      }
      $batch = [
        'title' => t('Importing products from @num categories', ['@num' => $total_categories]),
        'init_message' => $this->t('Importing..'),
        'operations' => $operations,
        'progressive' => TRUE,
        'finished' => [$this, 'batchFinished'],
        'batch_rediect' => '/admin/config/affiliates-connect/overview',
      ];
      batch_set($batch);
    }
  }

  /**
   * Batch for Importing of products.
   */
  public function startBatchImporting($key, $value, &$context) {
    $flipkart_native = new FlipkartNativeController();
    $categories = $flipkart_native->products($value);
    $context['results']['processed']++;
    $context['message'] = 'Completed importing category : ' . $key;
  }

  /**
   * Batch finished callback.
   */
  public function batchFinished($success, $results, $operations) {
    if ($success) {
     drupal_set_message($this->t("The products are successfully imported from flipkart."));
    }
    else {
      $error_operation = reset($operations);
      drupal_set_message($this->t('An error occurred while processing @operation with arguments : @args', array('@operation' => $error_operation[0], '@args' => print_r($error_operation[0], TRUE))));
    }
  }

}
