<?php

namespace Drupal\Tests\affiliates_connect_ebay\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Core\Url;

/**
 * Check if our defined routes and config form are working correctly or not.
 *
 * @group affiliates_connect
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ConfigFormTest extends BrowserTestBase {

  /**
   * An admin user used for this test.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * The permissions of the admin user.
   *
   * @var string[]
   */
  protected $adminUserPermissions = [
    'administer affiliates product entities',
    'add affiliates product entities',
    'delete affiliates product entities',
    'edit affiliates product entities',
    'view published affiliates product entities',
    'view unpublished affiliates product entities',
    'access administration pages',
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'affiliates_connect',
    'affiliates_connect_ebay'
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser($this->adminUserPermissions);
    // For admin
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test the availability of affiliates_connect_ebay on overview page.
   */
  public function testEbayPlugin() {
    $this->drupalGet(URL::fromRoute('affiliates_connect.overview'));
    $this->assertResponse(200);
    $this->assertSession()->pageTextContains('Plugin provided by affiliates_connect_ebay.');
  }

  /**
   * Test the Config form affiliates_connect_ebay plugin.
   */
  public function testFlipkartPluginConfigForm() {
    $this->drupalGet(URL::fromRoute('affiliates_connect_ebay.settings'));
    $this->assertResponse(200);
    // Test the form elements exist and have defaults.
    $config = $this->config('affiliates_connect_ebay.settings');
    $this->assertFieldByName(
      'ebay_app_id',
      $config->get('ebay_app_id'),
      'App ID has the defult value'
    );

    $checkbox = $this->xpath('//input[@name="save_searched_products"]');
    $checked = $checkbox[0]->isChecked();
    $this->assertIdentical($checked, false, "Checkbox save_searched_products is unchecked");

    // Test form submission.
    $checkboxes = $this->xpath('//input[@type="checkbox"]');
    foreach ($checkboxes as $checkbox) {
      $checkbox->check();
    }

    $formdata = [
      'ebay_app_id' => 'loremipsum_id',
      'service_endpoint' => 'production',
      'locale' => 'IN',
      'data_storage' => 'every_day',
    ];
    $this->submitForm($formdata, 'Save configuration');
    // Get new config
    $config = $this->config('affiliates_connect_ebay.settings');
    $this->assertFieldByName(
      'ebay_app_id',
      $config->get('ebay_app_id'),
      'App Id matched'
    );
    $this->assertFieldByName(
      'service_endpoint',
      $config->get('service_endpoint'),
      'Token matched'
    );
    $this->assertFieldByName(
      'locale',
      $config->get('locale')
    );
    $this->assertFieldByName(
      'data_storage',
      $config->get('data_storage')
    );
    // Get all checkboxes
    $checkboxes = $this->xpath('//input[@type="checkbox"]');
    $this->assertIdentical(count($checkboxes), 16, 'Correct number of checkboxes found.');
    foreach ($checkboxes as $checkbox) {
      $checked = $checkbox->isChecked();
      $name = (string) $checkbox->getAttribute('name');
      $this->assertIdentical($checked, $name == 'native_api' || $name == 'search' || $name == 'data_storage' || $name == 'full_content' || $name == 'price' || $name == 'available' || $name == 'size' || $name == 'color' || $name == 'offers' || $name == 'fallback_scraper' || $name == 'save_searched_products' || $name == 'cloaking' || $name == 'enable_hits_analysis' || $name == 'append_affiliate_id' || $name == 'no_follow' || $name == 'robots', format_string('Checkbox %name correctly checked', ['%name' => $name]));
    }
  }

  /**
   * Test the affiliates_connect_ebay search page.
   */
  public function testAmazonSearchPage() {
    $this->drupalGet(URL::fromRoute('affiliates_connect_ebay.search'));
    $this->assertResponse(200);
  }
}
