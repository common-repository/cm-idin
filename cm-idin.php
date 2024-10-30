<?php
/*
Plugin Name: CM iDIN for WooCommerce
Plugin URI: https://www.pronamic.eu/plugins/cm-idin/
Description: With iDIN a merchant can identify the consumers name and address and verify if the age is 18+.

Version: 1.0.2
Requires at least 4.7

Author: CM
Author URI: https://www.cm.nl/

Text Domain: cm-idin
Domain Path: /languages/

License: GPL-3.0

GitHub URI: https://github.com/cmdisp/idin-wordpress
*/

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

CM_IDIN_Plugin::instance( __FILE__ );

function cm_idin_plugin() {
	return CM_IDIN_Plugin::instance();
}
