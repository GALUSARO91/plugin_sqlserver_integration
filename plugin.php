<?php
/**
 * Plugin Name:       Remote db plugin
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Connects remotely to a database.
 * Version:           1.0.0 - Alpha
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Luis Rodriguez
 * Author URI:        https://nicageek.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       my-basics-plugin
 * Domain Path:       /languages
 */

// Initialize classes and files attached
if ( ! defined( 'ABSPATH' ) ) exit;
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ .'/src/includes/admin-page-layouts.php';
require_once __DIR__ .'/src/includes/admin-page-functions.php';
require_once __DIR__ .'/src/includes/functions.php';
require_once __DIR__ .'/src/includes/client-crud-functions.php';
require_once __DIR__ .'/src/includes/product-crud-functions.php';
require_once __DIR__ .'/src/includes/order-crud-functions.php';
require_once __DIR__ .'/src/includes/myaccountfunctions.php';


use Monolog\Logger;
use Monolog\Handler\StreamHandler;


//initiallize logger
$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

// declare option names

$GLOBALS['rm_db_option_names'] = array('ssh_host','ssh_port','ssh_user','ssh_local_port','ssh_remote_host','ssh_remote_port','ssh_connection_string', 'remote_db','db_username','db_password','db_conecction_string');

try{

  // Connect to remote database

// Boot SSH bridge

function delete_remote_db_options($option){
  delete_option($option);

}

/* function register_transaction_history(){
wp_enqueue_script('transaction-history-script',plugins_url('/src/includes/js/transaction-history.js',__FILE__ ),array('jquery','columnsToPrint','records'), '1.0', true);
} */
function remove_all_options(){

  array_walk($GLOBALS['rm_db_option_names'],'delete_remote_db_options');

}

//Note: Disabling auto-save so plugin can work correctly saving orders

function disable_autosave() {
  wp_deregister_script( 'autosave' );
  }

  //Link all actions to their respective hooks
  add_action('pre_get_posts','retrieve_order_info',10,1);
  add_action('show_user_profile','remote_db_user_primary_key');
  add_action('edit_user_profile', 'remote_db_user_primary_key');
  add_action('show_user_profile','retrieve_user_info');
  add_action('edit_user_profile', 'retrieve_user_info');
  add_action('user_new_form','remote_db_user_primary_key');
  add_action('personal_options_update','remote_db_user_primary_key_update');
  add_action('edit_user_profile_update','remote_db_user_primary_key_update');
  add_action('personal_options_update','update_user',1);
  add_action('edit_user_profile_update','update_user',1);
  add_action('admin_init','remote_db_plugin_register_settings');
  add_action('admin_menu','remote_db_plugin_admin_page' );
  add_action('user_register','remote_user_creator',1);
  add_action('woocommerce_account_content','retrieve_user_info');
  add_action('delete_user','delete_user',1);
  // register_deactivation_hook( __FILE__, 'remove_all_options' );
  add_action('init','add_transactions_endpoint');
  add_action('init','disable_autosave');
  add_filter('query_vars','transactions_query_vars',0);
  add_filter('woocommerce_account_menu_items','add_transactions_endpoint_link_my_account');
  add_action('woocommerce_account_transaction-history_endpoint', 'get_transaction_history_content',10,1);  
  add_action('wp_enqueue_scripts','transaction_history_records',10,1);  
  add_action('save_post_product','remote_product_creator',10,1);
  add_action('before_delete_post','delete_product',10,1);
  add_action('pre_get_posts','retrieve_product_info',10,1);
  add_action('save_post','remote_order_creator',20,1);
  add_action('before_delete_post','delete_order',10,1);
  
  // add_filter('woocommerce_product_data_store_cpt_get_products_query', 'handle_product_remote_id',5, 2 );

} 

catch(\Exception $e){

    $log->warning('Exception : '.$e->getMessage());

} catch(\Error $e){

   $log->error('Error : '.$e->getMessage());
}
?>