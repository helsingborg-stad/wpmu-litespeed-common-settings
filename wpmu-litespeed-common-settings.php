<?php

/*
Plugin Name:    WPMU Litespeed Common Settings
Description:    Adds input field for a google maps api key.
Version:        1.0
Author:         Sebastian Thulin
*/

namespace WPMULitespeedCommonSettings;

class WPMULitespeedCommonSettings
{
  /**
   * The prefix for option keys.
   * @var string
   */
  private $optionPrefix = 'litespeed.conf';

  /**
   * The WordPress database object.
   * @var \wpdb
   */
  private $db;

  /**
   * Initializes the database and registers action hooks.
   * @return void
   */
  public function __construct()
  {
    add_action('init', array($this, 'initDB'));
    add_action('init', array($this, 'applyOptionFilters'));
    add_action('admin_init', array($this, 'removeOptionsPage'), 99999);
  }

  /**
   * Removes the LiteSpeed Options page from the WordPress admin menu.
   * This function is only executed on subsites, not the main site.
   */
  public function removeOptionsPage() {

    // Check if it's the main site
    if (is_main_site()) {
      return;
    }

    //Hide "Settings → LiteSpeed Cache".
    remove_submenu_page('options-general.php', 'litespeed-cache-options');

    //Hide "LiteSpeed Cache".
    remove_menu_page('litespeed');
    //Hide "LiteSpeed Cache → Dashboard".
    remove_submenu_page('litespeed', 'litespeed');
    //Hide "LiteSpeed Cache → Presets".
    remove_submenu_page('litespeed', 'litespeed-presets');
    //Hide "LiteSpeed Cache → General".
    remove_submenu_page('litespeed', 'litespeed-general');
    //Hide "LiteSpeed Cache → Cache".
    remove_submenu_page('litespeed', 'litespeed-cache');
    //Hide "LiteSpeed Cache → CDN".
    remove_submenu_page('litespeed', 'litespeed-cdn');
    //Hide "LiteSpeed Cache → Image Optimization".
    remove_submenu_page('litespeed', 'litespeed-img_optm');
    //Hide "LiteSpeed Cache → Page Optimization".
    remove_submenu_page('litespeed', 'litespeed-page_optm');
    //Hide "LiteSpeed Cache → Database".
    remove_submenu_page('litespeed', 'litespeed-db_optm');
    //Hide "LiteSpeed Cache → Crawler".
    remove_submenu_page('litespeed', 'litespeed-crawler');
    //Hide "LiteSpeed Cache → Toolbox".
    remove_submenu_page('litespeed', 'litespeed-toolbox');
  }


  /**
   * Initializes the WordPress database object.
   * @return void
   */
  public function initDB()
  {
    $this->globalToLocal('wpdb', 'db');
  }

  /**
   * Applies option filters to retrieve LiteSpeed options for non-main sites.
   * @return void
   */
  public function applyOptionFilters()
  {
    if (is_main_site()) {
      return;
    }

    $options = $this->getLiteSpeedOptionKeys();

    if ($options && !empty($options)) {
      foreach ($options as $option) {
        $optionValue = $option->option_value;
        $optionName  = $option->option_name;

        add_filter('option_' . $optionName, function ($value, $option) use ($optionValue) {
          return $optionValue;
        }, 10, 2);
      }
    }
  }

  /**
   * Retrieves LiteSpeed option keys from the database.
   * @return array
   */
  private function getLiteSpeedOptionKeys(): array
  {
    $optionsTable = $this->db->get_blog_prefix(BLOG_ID_CURRENT_SITE) . "options";

    $metaKeys = (array) $this->db->get_results(
      $x = str_replace(
        "[LKR]",
        "%",
        $this->db->prepare(
          "SELECT option_name, option_value FROM " . $optionsTable . " WHERE option_name LIKE %s LIMIT 300",
          [
            $this->db->esc_like($this->optionPrefix) . "[LKR]"
          ]
        )
      )
    );

    return $metaKeys;
  }

  /**
   * Creates a local copy of the global instance.
   * The target variable should be defined in the class header as private or public.
   * @param string $global The name of the global variable that should be made local.
   * @param string|null $local Handle the global with the name of this string locally.
   * @return bool Returns true if the global variable was successfully copied to a local variable, false otherwise.
   */
  private function globalToLocal($global, $local = null)
  {
    global $$global;

    if (is_null($$global)) {
      return false;
    }

    if (is_null($local)) {
      $this->$global = $$global;
    } else {
      $this->$local = $$global;
    }

    return true;
  }
}

new \WPMULitespeedCommonSettings\WPMULitespeedCommonSettings();
