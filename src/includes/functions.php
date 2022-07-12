<?php

if ( ! defined( 'ABSPATH' ) ) exit;
use ROOT\sshcontrollers\sshhandler as SSH;
use Illuminate\Database\Capsule\Manager as Capsule;
Require_once 'error-handler.php';

/* 
  Triggers connectiont to the database
*/

function start_remote_db(){
  
  // TODO: Create test for database connection
  // FIXME: convert database connection to a singleton class pattern
  try{
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
    } catch(\Exception $e){
          myErrorHandler($e);
      
    } catch (\Error $e){
          myErrorHandler($e);
    }
  }

  /* 
    Triggers SSH bridge
  */
  function start_ssh(){
    $ssh = new SSH(
                    get_option('ssh_host'),
                    get_option('ssh_user'),
                    get_option('ssh_local_port'),
                    get_option('ssh_remote_host'),
                    get_option('ssh_remote_port'),
                    get_option('ssh_connection_string')
                  );    
    $ssh->ssh_bridge();
    return $ssh;
}

/* 
  Filters GMC ID
*/

function handle_product_remote_id($query, $query_vars){
  // FIXME: This function is not working
    if ( ! empty( $query_vars['gcm_id'] ) ) {
      $query['meta_query'][] = array(
        'key' => 'gcm_id',
        'value' => esc_attr( $query_vars['gcm_id'] ),
      );
    }

    return $query;
}
