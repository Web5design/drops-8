<?php

/**
 * @file
 * Definition of Drupal\node\Tests\NodeTypeInitalLanguageTest.
 */

namespace Drupal\node\Tests;

/**
 * Tests related to node type initial language.
 */
class NodeTypeInitialLanguageTest extends NodeTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('language', 'field_ui');

  public static function getInfo() {
    return array(
      'name' => 'Node type initial language',
      'description' => 'Tests node type initial language settings.',
      'group' => 'Node',
    );
  }

  function setUp() {
    parent::setUp();

    $web_user = $this->drupalCreateUser(array('bypass node access', 'administer content types', 'administer languages'));
    $this->drupalLogin($web_user);
  }

  /**
   * Tests the node type initial language defaults, and modify them.
   *
   * The default initial language must be the site's default, and the language
   * locked option must be on.
   */
  function testNodeTypeInitialLanguageDefaults() {
    $this->drupalGet('admin/structure/types/manage/article');
    $this->assertOptionSelected('edit-language-configuration-langcode', 'site_default', 'The default inital language is the site default.');
    $this->assertFieldChecked('edit-language-configuration-language-hidden', 'Language selector is hidden by default.');

    // Tests if the language field cannot be rearranged on the manage fields tab.
    $this->drupalGet('admin/structure/types/manage/article/fields');
    $language_field = $this->xpath('//*[@id="field-overview"]/*[@id="language"]');
    $this->assert(empty($language_field), 'Language field is not visible on manage fields tab.');

    $this->drupalGet('node/add/article');
    $this->assertNoField('langcode', 'Language is not selectable on node add/edit page by default.');

    // Adds a new language and set it as default.
    $edit = array(
      'predefined_langcode' => 'hu',
    );
    $this->drupalPost('admin/config/regional/language/add', $edit, t('Add language'));
    $edit = array(
      'site_default' => 'hu',
    );
    $this->drupalPost('admin/config/regional/language', $edit, t('Save configuration'));

    // Tests the initial language after changing the site default language.
    // First unhide the language selector.
    $edit = array(
      'language_configuration[language_hidden]' => FALSE,
    );
    $this->drupalPost('admin/structure/types/manage/article', $edit, t('Save content type'));
    $this->drupalGet('node/add/article');
    $this->assertField('langcode', 'Language is selectable on node add/edit page when language not hidden.');
    $this->assertOptionSelected('edit-langcode', 'hu', 'The inital language is the site default on the node add page after the site default language is changed.');

    // Tests if the language field can be rearranged on the manage fields tab.
    $this->drupalGet('admin/structure/types/manage/article/fields');
    $language_field = $this->xpath('//*[@id="language"]');
    $this->assert(!empty($language_field), 'Language field is visible on manage fields tab.');

    // Tests if the language field can be rearranged on the manage display tab.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $language_display = $this->xpath('//*[@id="language"]');
    $this->assert(!empty($language_display), 'Language field is visible on manage display tab.');
    // Tests if the language field is hidden by default.
    $this->assertOptionSelected('edit-fields-language-type', 'hidden', 'Language is hidden by default on manage display tab.');

    // Changes the inital language settings.
    $edit = array(
      'language_configuration[langcode]' => 'en',
    );
    $this->drupalPost('admin/structure/types/manage/article', $edit, t('Save content type'));
    $this->drupalGet('node/add/article');
    $this->assertOptionSelected('edit-langcode', 'en', 'The inital language is the defined language.');
  }

  /**
   * Tests Language field visibility features.
   */
  function testLanguageFieldVisibility() {
    $langcode = LANGUAGE_NOT_SPECIFIED;

    // Creates a node to test Language field visibility feature.
    $edit = array(
      'title' => $this->randomName(8),
      "body[$langcode][0][value]" => $this->randomName(16),
    );
    $this->drupalPost('node/add/article', $edit, t('Save'));
    $node = $this->drupalGetNodeByTitle($edit['title']);
    $this->assertTrue($node, 'Node found in database.');

    // Loads node page and check if Language field is hidden by default.
    $this->drupalGet('node/' . $node->nid);
    $language_field = $this->xpath('//div[@id=:id]/div', array(
      ':id' => 'field-language-display',
    ));
    $this->assertTrue(empty($language_field), 'Language field value is not shown by default on node page.');

    // Changes Language field visibility to true and check if it is saved.
    $edit = array(
      'fields[language][type]' => 'content',
    );
    $this->drupalPost('admin/structure/types/manage/article/display', $edit, t('Save'));
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertOptionSelected('edit-fields-language-type', 'content', 'Language field has been set to visible.');

    // Loads node page and check if Language field is shown.
    $this->drupalGet('node/' . $node->nid);
    $language_field = $this->xpath('//div[@id=:id]/div', array(
      ':id' => 'field-language-display',
    ));
    $this->assertFalse(empty($language_field), 'Language field value is shown on node page.');
  }
}
