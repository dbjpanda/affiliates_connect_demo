<?php

namespace Drupal\affiliates_connect_ebay\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\affiliates_connect\Form\AffiliatesConnectSettingsForm;
use Drupal\affiliates_connect_ebay\EbayLocale;

/**
 * Class AffiliatesEbaySettingsForm.
 */
class AffiliatesEbaySettingsForm extends AffiliatesConnectSettingsForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'affiliates_connect_ebay_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array_merge(
      parent::getEditableConfigNames(),
      ['affiliates_connect_ebay.settings']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('affiliates_connect_ebay.settings');

    $form['ebay_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Affiliates Connect Ebay Settings'),
      '#open' => TRUE,
      '#description' => $this->t('If you are not Ebay Affiliate, Please Sign
      up for Ebay Affiliate program here: <a href="@affiliate-marketing">@affiliate-marketing</a>',
          ['@affiliate-marketing' => 'https://developer.ebay.com/']),
    ];

    $form['ebay_settings']['native_api'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Native API'),
      '#description' => $this->t('Enable Affiliate Marketing using tracking ID'),
      '#default_value' => $config->get('native_api'),
    ];


    $form['ebay_settings']['native_api_form'] = [
      '#type' => 'details',
      '#title' => $this->t('API Token'),
      '#open' => TRUE,
      '#states' => [
        "visible" => [
          "input[name='native_api']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['ebay_settings']['native_api_form']['locale'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Locale'),
      '#options' => $this->getLocale(),
      '#empty_option' => 'Select Locale',
      '#default_value' => $config->get('locale'),
    ];

    $form['ebay_settings']['native_api_form']['service_endpoint'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Service Endpoints'),
      '#options' => [
        'production' => $this->t('Production'),
        'sandbox' => $this->t('Sandbox'),
      ],
      '#empty_option' => 'Select',
      '#default_value' => $config->get('service_endpoint'),
      '#states' => [
        "required" => [
          "input[name='native_api']" => ["checked" => TRUE],
        ],
      ],
    ];

    $form['ebay_settings']['native_api_form']['ebay_app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#default_value' => $config->get('ebay_app_id'),
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

    $form['ebay_settings']['native_api_form']['search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Search'),
      '#description' => $this->t('Enable to search product.'),
      '#default_value' => $config->get('search'),
    ];

    $form['ebay_settings']['native_api_form']['data_storage'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Data Storage'),
      '#description' => $this->t('Enable to store searched product data in your site database.'),
      '#default_value' => $config->get('data_storage'),
    ];

    $form['ebay_settings']['native_api_form']['data_storage_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Plugin Update'),
      '#open' => TRUE,
      '#states' => [
        "visible" => [
          "input[name='data_storage']" => ["checked" => TRUE],
        ],
      ],
      '#description' => $this->t('Update selected fields of the products'),
    ];


    $form['ebay_settings']['native_api_form']['data_storage_form']['full_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Full Content'),
      '#default_value' => $config->get('full_content'),
    ];

    $form['ebay_settings']['native_api_form']['data_storage_form']['available'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Availability'),
      '#default_value' => $config->get('available'),
    ];

    $form['ebay_settings']['native_api_form']['data_storage_form']['price'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Price'),
      '#default_value' => $config->get('price'),
    ];

    $form['ebay_settings']['native_api_form']['data_storage_form']['size'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Size'),
      '#default_value' => $config->get('size'),
    ];

    $form['ebay_settings']['native_api_form']['data_storage_form']['color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Color'),
      '#default_value' => $config->get('color'),
    ];

    $form['ebay_settings']['native_api_form']['data_storage_form']['offers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Offers'),
      '#default_value' => $config->get('offers'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('affiliates_connect_ebay.settings')
      ->set('native_api', $values['native_api'])
      ->set('locale', $values['locale'])
      ->set('ebay_app_id', $values['ebay_app_id'])
      ->set('service_endpoint', $values['service_endpoint'])
      ->set('search', $values['search'])
      ->set('data_storage', $values['data_storage'])
      ->set('full_content', $values['full_content'])
      ->set('price', $values['price'])
      ->set('available', $values['available'])
      ->set('size', $values['size'])
      ->set('color', $values['color'])
      ->set('offers', $values['offers'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  public function getLocale()
  {
    return EbayLocale::getLocale();
  }
}
