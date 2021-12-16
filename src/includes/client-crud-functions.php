<?php

if ( ! defined( 'ABSPATH' ) ) exit;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


use ROOT\controllers\ClientsRecordController;
use ROOT\models\ClientModel;
use ROOT\models\ClientFactModel;
use ROOT\models\FactVentasModel;

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

function remote_user_creator($id){
  if(!isset($_POST['user-in-db'])){
    $ssh = start_ssh();
    start_remote_db();
    $client = new ClientsRecordController(new ClientModel());
    $client_id = $client->createRecord($id);
    update_user_meta($id,'remote-db-user-primary-key',$client_id);
    $ssh->ssh_bridge_close();
  }
  if (isset($_POST['remote-db-user-primary-key'])){
    update_user_meta($id,'remote-db-user-primary-key',$_POST['remote-db-user-primary-key']);
  }
};

function retrieve_user_info($user =null){
    $allTransactions = [];
    $ssh = start_ssh();
    start_remote_db();
    $client = new ClientsRecordController(new ClientModel());
    $found_id = $user==null?get_user_meta(get_current_user_id(),'remote-db-user-primary-key'):get_user_meta($user->ID,'remote-db-user-primary-key',true);
    $client_info = $client->retrieveRecord($found_id);
    if(isset($client_info)){ 
      $transactions_model = $client_info->hasManyThrough(
        FactVentasModel::class,
        ClientFactModel::class,
        'COD_ID',
        'NUM_REG',
        'COD_ID',
        'NUM_REG'
      );
      $transactionRecords = $transactions_model->where('COD_ID',$found_id)->get();
    foreach($transactionRecords as $record){
        array_push($allTransactions,$record->toArray());
    }
    update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_address_1',$client_info->DIRECCION);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_city',$client_info->CIUDAD);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_phone',$client_info->TELEFONO_1);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'credit_limit',$client_info->LIMITE);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'daily_limit',$client_info->LIMITE_D);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_first_name',$client_info->CONTACTO_1);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'all_transactions',$allTransactions);
    }
    $ssh->ssh_bridge_close();
}


function update_user($user =null){
  $ssh = start_ssh();
  start_remote_db();
  $client = new ClientsRecordController(new ClientModel());
  $remoteId = $user==null?get_user_meta(get_current_user_id(),'remote-db-user-primary-key'):get_user_meta($user->ID,'remote-db-user-primary-key',true);
  $client->updateRecord($remoteId);
  $ssh->ssh_bridge_close();
  
}

function delete_user($user =null){
  $ssh = start_ssh();
  start_remote_db();
  $client = new ClientsRecordController(new ClientModel());
  $remoteId = $user==null?get_user_meta(get_current_user_id(),'remote-db-user-primary-key'):get_user_meta($user->ID,'remote-db-user-primary-key',true);
  $client->deleteRecord($remoteId);
  $ssh->ssh_bridge_close();
  
}

