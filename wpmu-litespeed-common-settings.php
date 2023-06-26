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
        });
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
