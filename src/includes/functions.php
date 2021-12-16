<?php

if ( ! defined( 'ABSPATH' ) ) exit;
use ROOT\sshcontrollers\SSHHandler as SSH;
use Illuminate\Database\Capsule\Manager as Capsule;

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


