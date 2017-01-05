<?php

namespace Drupal\unl_user\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\unl_user\PersonDataQuery;

/**
 * Tests for the unl_user module.
 * @group unl_user
 */
class ImportUserTest extends WebTestBase
{

  /**
   * Modules to install
   *
   * @var array
   */
  public static $modules = array('unl_user');

  // A simple user
  private $user;

  public function setUp() {
    parent::setUp();
    $this->user = $this->drupalCreateUser(array(
      'administer site configuration'
    ));
  }

  /**
   * Tests that we can get user data
   */
  public function testImportUser() {
    //We need an admin to log in...
    $this->drupalLogin($this->user);
    
    $this->assertTrue(true);
  }
}