<?php

namespace Drupal\unl_user\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\unl_user\PersonDataQuery;

/**
 * Tests for the unl_user module.
 * @group unl_user
 */
class PersonDataQueryTest extends WebTestBase
{

  /**
   * Modules to install
   *
   * @var array
   */
  public static $modules = array('unl_user');

  /**
 * Tests that we can get user data
 */
  public function testGetUserData() {
    $query = new PersonDataQuery();

    $result = $query->getUserData('test-user2');

    $this->assertEqual($result['uid'], 'test-user2', 'uid should be set');
    
    $this->assertStandardPersonRecord($result);
  }

  /**
   * Tests that we can search for users
   */
  public function testSearch() {
    $query = new PersonDataQuery();

    $result = $query->search('test');
    
    $this->assertTrue(is_array($result), 'should return an array');
    
    foreach ($result as $record) {
      $this->assertStandardPersonRecord($record);
    }
  }

  /**
   * Add a set of assertions to ensure a standard person record response
   * 
   * @param $record
   */
  protected function assertStandardPersonRecord($record) {
    $this->assertTrue(is_array($record), 'result should be an array');
    $this->assertTrue(!empty($record['uid']), 'uid should be set');
    $this->assertTrue(!empty($record['mail']), 'an email address should be provided');
    $this->assertTrue(is_array($record['data']['unl']), 'should have a UNL data set');
    $this->assertEqual($record['data']['unl']['source'], 'directory.unl.edu', 'The source should be directory.unl.edu for testing');
    $this->assertEqual(
      array_keys($record['data']['unl']),
      ['fullName', 'affiliations', 'primaryAffiliation', 'department', 'major', 'studentStatus', 'source'],
      'Data should have a standard structure');
  }
}