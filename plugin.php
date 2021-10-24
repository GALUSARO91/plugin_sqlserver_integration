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

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ROOT\sshcontrollers\SSHHandler as SSH;
use Illuminate\Database\Capsule\Manager as Capsule;
use ROOT\controllers\ClientsRecordControler;
use ROOT\models\BaseModel;

// use ROOT\models\Clients;
//initiallize logger
$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

// declare option names


$GLOBALS['rm_db_option_names'] = array('ssh_host','ssh_port','ssh_user','ssh_local_port','ssh_remote_host','ssh_remote_port','ssh_connection_string', 'remote_db','db_username','db_password','db_conecction_string');

try{
  function start_remote_db(){
    $capsule = new Capsule;
    $connection_array = array(
      'driver' => 'sqlsrv',
      'host' => get_option('db_conecction_string'),
      'database' => str_replace('_',' ',get_option('remote_db')),
      'username' => get_option('db_username'),
      'password' => get_option('db_password'),
      'charset' => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'prefix' => ''
    );
    
  $capsule->addConnection($connection_array);

  $capsule->setAsGlobal();
  
  $capsule->bootEloquent();

  }

  function start_ssh(){
    $ssh = new SSH(get_option('ssh_host'),get_option('ssh_user'),get_option('ssh_local_port'),get_option('ssh_remote_host'),get_option('ssh_remote_port'),get_option('ssh_connection_string'));    
    $ssh->ssh_bridge();
    return $ssh;
}

function delete_remote_db_options($option){
  delete_option($option);

}

function remove_all_options(){

  array_walk($GLOBALS['rm_db_option_names'],'delete_remote_db_options');

}

function remote_user_creator($id){
    $ssh = start_ssh();
    start_remote_db();
    $client = new ClientsRecordControler(new BaseModel('CLIENTES'));
    $client_id = $client->createRecord($id);
    // remote_db_user_primary_key_update($client_id);
    update_user_meta($id,'remote-db-user-primary-key',$client_id);
    $ssh->ssh_bridge_close();

};

// TODO: End CRUD for clients
// TODO: End CROD for products
// TODO: End CRUD for transactions 


  add_action('show_user_profile','remote_db_user_primary_key');
  add_action('edit_user_profile', 'remote_db_user_primary_key');
  add_action('user_new_form','remote_db_user_primary_key');
  add_action('personal_options_update','remote_db_user_primary_key_update');
  add_action('edit_user_profile_update','remote_db_user_primary_key_update');
  add_action('admin_init', 'remote_db_plugin_register_settings');
  add_action( 'admin_menu', 'remote_db_plugin_admin_page' );
  // add_action('updated_option','start_ssh', 10, 1);
  add_action('user_register', 'remote_user_creator',1);
  register_deactivation_hook( __FILE__, 'remove_all_options' );

} 

catch(\Exception $e){

    $log->warning('Exception : '.$e->getMessage());

} catch(\Error $e){

   $log->error('Error : '.$e->getMessage());
}
?>