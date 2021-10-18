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

// use PDO;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ROOT\sshcontrollers\SSHHandler as SSH;
use ROOT\controllers\ClientsController;
use Illuminate\Database\Capsule\Manager as Capsule;
use ROOT\models\Clients;
//initiallize logger
$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

// declare option names


$GLOBALS['rm_db_option_names'] = array('ssh_host','ssh_port','ssh_user','ssh_local_port','ssh_remote_host','ssh_remote_port','ssh_connection_string', 'remote_db','db_username','db_password','db_conecction_string');

// $GLOBALS['remote_user_controller'] = null;

try{

  // $op = (int)shell_exec('nohup ssh -vvv -nNT -L 5000:localhost:1433 -i ~/.ssh/id_rsa luisgabriel@208.96.130.176 > /dev/null 2>&1 & echo $!');
    // $conn = new PDO("mssql:host= 127.0.0.1,5000\ADMINISTRADOR;Database = Scm GCM TranspUlt", "luisgabriel","GabRod91" );
    // $ssh = new SSH("208.96.130.176","luisgabriel","-vvv -nNT -L 5000:localhost:1433 -i ~/.ssh/id_rsa");

  // $conn = new PDO("sqlsrv:Server = 127.0.0.1,5000\ADMINISTRADOR;Database = Scm Prueba3", "luisgabriel","GabRod91" );

  function start_remote_db(){
    // FIXME: function triggers an error while login in sql server - function works, bridge needs to be reset first
    global $capsule, $log;
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
/*   array_walk($connection_array,function($value, $key) use ($log){
    $log->info("$key has the following value: $value \n");
  }); */

  }

  function start_ssh_and_remote_db($option){
    // global $capsule;
    // FIXME: Bridge falls often - create and destroy bridge for each transaction

    if(in_array($option,$GLOBALS['rm_db_option_names'])){

    $ssh = new SSH(get_option('ssh_host'),get_option('ssh_user'),get_option('ssh_local_port'),get_option('ssh_remote_host'),get_option('ssh_remote_port'),get_option('ssh_connection_string'));    

    $ssh->ssh_bridge();

    /* if(!isset($capsule)){
        start_remote_db();
    }
 */

    }

}

function delete_remote_db_options($option){
  delete_option($option);

}

function remove_all_options(){

  array_walk($GLOBALS['rm_db_option_names'],'delete_remote_db_options');

}

function remote_user_creator($id){
  
  
    // global $capsule;
    // if(!isset($capsule)){
      start_remote_db();
  // }
    $client = new ClientsController(new Clients());
    return $client->create_client($id);
    // $capsule = null;
      

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
  add_action('updated_option','start_ssh_and_remote_db', 10, 1); //FIXME: Add a new hook to activate plugin
  add_action('user_register', 'remote_user_creator',1);
  register_deactivation_hook( __FILE__, 'remove_all_options' );

} 

catch(\Exception $e){

    $log->warning('Exception : '.$e->getMessage());

} catch(\Error $e){

   $log->error('Error : '.$e->getMessage());
}
?>