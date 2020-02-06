<?php

namespace Drupal\Tests\enum\Functional;

use Drupal\Core\Url;
use Drupal\Tests\UnitTestCase;

/**
 * Simple test to ensure that main page loads with module enabled.
 *
 * @group enum
 */
class LoadTest extends UnitTestCase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['enum'];

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Tests that the home page loads with a 200 response.
   */
  public function testLoad() {
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->statusCodeEquals(200);
  }

}
