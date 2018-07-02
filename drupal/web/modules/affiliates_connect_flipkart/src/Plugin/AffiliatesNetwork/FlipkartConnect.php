<?php

namespace Drupal\affiliates_connect_flipkart\Plugin\AffiliatesNetwork;

use Drupal\affiliates_connect\AffiliatesNetworkInterface;

/**
 * Contains Plugin ID and Plugin definition info for affiliates_connect_flipkart.
 *
 * @AffiliatesNetwork(
 *  id = "affiliates_connect_flipkart",
 *  label = @Translation("Flipkart"),
 *  description = @Translation("Plugin provided by affiliates_connect_flipkart."),
 * )
 */
class FlipkartConnect implements AffiliatesNetworkInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    // Gets the plugin_id of the plugin instance.
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    // Gets the definition of the plugin implementation.
  }

}
