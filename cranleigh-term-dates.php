<?php
/*
Plugin Name: Cranleigh Term Dates
Plugin URI: https://github.com/cranleighschool/cranleigh-term-dates
Description: Term Dates helper plugin
Version: 1.0.1
Author: Fred Bradley
Author URI: http://fred.im/
License: GPL2
*/

namespace CranleighSchool\TermDates;

require_once 'vendor/autoload.php';

$admin     = new Admin();
$shortcode = new Shortcode();

