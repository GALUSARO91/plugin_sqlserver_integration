<?php

if ( ! defined( 'ABSPATH' ) ) exit;
use ROOT\sshcontrollers\SSHHandler as SSH;
use Illuminate\Database\Capsule\Manager as Capsule;

function send_error_message($message = null){
  
  return '<div class="notice notice-success is-dismissible">
  <p>'.$message??"an error occurred".'</p></div>';

}

function start_remote_db(){
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

      add_action('admin_notices','send_error_message',1,10);
    }
  }

  function start_ssh(){
    $ssh = new SSH(get_option('ssh_host'),get_option('ssh_user'),get_option('ssh_local_port'),get_option('ssh_remote_host'),get_option('ssh_remote_port'),get_option('ssh_connection_string'));    
    $ssh->ssh_bridge();
    return $ssh;
}


function handle_product_remote_id($query, $query_vars){
        // $test = $query_vars['gcm_id'];
    if ( ! empty( $query_vars['gcm_id'] ) ) {
      $query['meta_query'][] = array(
        'key' => 'gcm_id',
        'value' => esc_attr( $query_vars['gcm_id'] ),
      );
    }

    return $query;
}