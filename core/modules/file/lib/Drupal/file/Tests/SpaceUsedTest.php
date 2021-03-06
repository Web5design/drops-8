<?php

/**
 * @file
 * Definition of Drupal\file\Tests\SpaceUsedTest.
 */

namespace Drupal\file\Tests;

/**
 *  This will run tests against the file_space_used() function.
 */
class SpaceUsedTest extends FileManagedTestBase {
  public static function getInfo() {
    return array(
      'name' => 'File space used tests',
      'description' => 'Tests the file_space_used() function.',
      'group' => 'File API',
    );
  }

  function setUp() {
    parent::setUp();

    // Create records for a couple of users with different sizes.
    $file = array('uid' => 2, 'uri' => 'public://example1.txt', 'filesize' => 50, 'status' => FILE_STATUS_PERMANENT);
    drupal_write_record('file_managed', $file);
    $file = array('uid' => 2, 'uri' => 'public://example2.txt', 'filesize' => 20, 'status' => FILE_STATUS_PERMANENT);
    drupal_write_record('file_managed', $file);
    $file = array('uid' => 3, 'uri' => 'public://example3.txt', 'filesize' => 100, 'status' => FILE_STATUS_PERMANENT);
    drupal_write_record('file_managed', $file);
    $file = array('uid' => 3, 'uri' => 'public://example4.txt', 'filesize' => 200, 'status' => FILE_STATUS_PERMANENT);
    drupal_write_record('file_managed', $file);

    // Now create some non-permanent files.
    $file = array('uid' => 2, 'uri' => 'public://example5.txt', 'filesize' => 1, 'status' => 0);
    drupal_write_record('file_managed', $file);
    $file = array('uid' => 3, 'uri' => 'public://example6.txt', 'filesize' => 3, 'status' => 0);
    drupal_write_record('file_managed', $file);
  }

  /**
   * Test different users with the default status.
   */
  function testFileSpaceUsed() {
    // Test different users with default status.
    $this->assertEqual(file_space_used(2), 70);
    $this->assertEqual(file_space_used(3), 300);
    $this->assertEqual(file_space_used(), 370);

    // Test the status fields
    $this->assertEqual(file_space_used(NULL, 0), 4);
    $this->assertEqual(file_space_used(NULL, FILE_STATUS_PERMANENT), 370);

    // Test both the user and status.
    $this->assertEqual(file_space_used(1, 0), 0);
    $this->assertEqual(file_space_used(1, FILE_STATUS_PERMANENT), 0);
    $this->assertEqual(file_space_used(2, 0), 1);
    $this->assertEqual(file_space_used(2, FILE_STATUS_PERMANENT), 70);
    $this->assertEqual(file_space_used(3, 0), 3);
    $this->assertEqual(file_space_used(3, FILE_STATUS_PERMANENT), 300);
  }
}
