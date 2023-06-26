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

  private $optionSuffix = '';

  public function __construct()
  {
  }

  public function filterOption() {
   
  }
}

new \WPMULitespeedCommonSettings\WPMULitespeedCommonSettings();