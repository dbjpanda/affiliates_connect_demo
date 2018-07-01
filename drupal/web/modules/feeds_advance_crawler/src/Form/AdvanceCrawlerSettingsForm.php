<?php

namespace Drupal\feeds_advance_crawler\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AdvanceCrawlerSettingsForm.
 */
class AdvanceCrawlerSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'feeds_advance_crawler.settings',
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'advance_crawler_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('feeds_advance_crawler.settings');

    $form['nodejs_host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Node.js server host'),
      '#default_value' => $config->get('nodejs_host'),
      '#size' => 40,
      '#required' => TRUE,
      '#description' => $this->t('The hostname of the Node.js server for Advance Feeds Crawler.'),
    ];
    $form['nodejs_port'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Node.js server port'),
      '#default_value' => $config->get('nodejs_port'),
      '#size' => 10,
      '#required' => TRUE,
      '#description' => $this->t('The port of the Node.js server.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('feeds_advance_crawler.settings')
      ->set('nodejs_host', $values['nodejs_host'])
      ->set('nodejs_port', $values['nodejs_port'])
      ->save();
    parent::submitForm($form, $form_state);
  }
}
