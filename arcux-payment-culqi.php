<?php
/*
  Plugin Name: Arcux Payment Culqi
  Plugin URI: https://arcux.net
  Description: Using a shortcode will generate a payment form with the Culqi payment gateway.
  Version: 1.0.0
  Author: Arcux
  Author URI: https://arcux.net
  * License:     GPL2
  * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

include_once ('lib/resources.php');
include_once ('lib/shortcode_payment.php');
include_once ('lib/init_person.php');
include_once ('lib/list-table-person.php');
include_once ('lib/init_course.php');
include_once ('lib/list-table-course.php');
include_once ('lib/endpoint.php');
include_once ('lib/dashboard.php');

$theme_active = wp_get_theme()->get('Name');
if($theme_active != 'ArcuxT') {
  if ($_SERVER['SERVER_NAME'] == 'localhost') {
    include_once ('lib/culqi-php/culqi');
  } else {
    include_once ('lib/culqi-php/culqi.php');
  }
}

if (!defined('ABSPATH'))
  exit;

if (!class_exists('arcuxpaymentculqi')) {
  class arcuxpaymentculqi {
    function __construct() {
      new initPaymentPerson();
      new iniPaymentCourse();
      new shortcodePayment();
      new initEndPoint();
      new tokensDashboard();
    }
  }
}
new arcuxpaymentculqi;