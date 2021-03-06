<?php

/**
 * @file
 * Definition of Drupal\image\Tests\ImageFieldValidateTest.
 */

namespace Drupal\image\Tests;

/**
 * Test class to check for various validations.
 */
class ImageFieldValidateTest extends ImageFieldTestBase {
  public static function getInfo() {
    return array(
      'name' => 'Image field validation tests',
      'description' => 'Tests validation functions such as min/max resolution.',
      'group' => 'Image',
    );
  }

  /**
   * Test min/max resolution settings.
   */
  function testResolution() {
    $field_name = strtolower($this->randomName());
    $min_resolution = 50;
    $max_resolution = 100;
    $instance_settings = array(
      'max_resolution' => $max_resolution . 'x' . $max_resolution,
      'min_resolution' => $min_resolution . 'x' . $min_resolution,
    );
    $this->createImageField($field_name, 'article', array(), $instance_settings);

    // We want a test image that is too small, and a test image that is too
    // big, so cycle through test image files until we have what we need.
    $image_that_is_too_big = FALSE;
    $image_that_is_too_small = FALSE;
    foreach ($this->drupalGetTestFiles('image') as $image) {
      $info = image_get_info($image->uri);
      if ($info['width'] > $max_resolution) {
        $image_that_is_too_big = $image;
      }
      if ($info['width'] < $min_resolution) {
        $image_that_is_too_small = $image;
      }
      if ($image_that_is_too_small && $image_that_is_too_big) {
        break;
      }
    }
    $nid = $this->uploadNodeImage($image_that_is_too_small, $field_name, 'article');
    $this->assertText(t('The specified file ' . $image_that_is_too_small->filename . ' could not be uploaded. The image is too small; the minimum dimensions are 50x50 pixels.'), 'Node save failed when minimum image resolution was not met.');
    $nid = $this->uploadNodeImage($image_that_is_too_big, $field_name, 'article');
    $this->assertText(t('The image was resized to fit within the maximum allowed dimensions of 100x100 pixels.'), 'Image exceeding max resolution was properly resized.');
  }
}
