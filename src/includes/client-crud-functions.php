<?php

if ( ! defined( 'ABSPATH' ) ) exit;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ROOT\controllers\ClientsRecordController;
use ROOT\controllers\ClientsDestinyController;
use ROOT\models\ClientModel;
use ROOT\models\ClientsDestinyModel;
use ROOT\models\ClientFactModel;
use ROOT\models\FactVentasModel;

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

function remote_user_creator($id){
  if(!isset($_POST['user-in-db'])){
    $ssh = start_ssh();
    start_remote_db();
    $client = new ClientsRecordController(new ClientModel());
    $args = [
      "Cod_Emp" => "01",
      "COD_SUC" => "01",
      "COD_ZON" => "01",
      "NOMBRE" => $_POST['first_name']." ".$_POST['last_name'],
      "EMAIL" => $_POST['email'],
      "CUENTA" => '1103-01-01',
    ];
    // TODO: Add Ruc, limite de credito, plazo
    $client_id = $client->createRecord($_POST['remote-db-user-primary-key'],$args);
    update_user_meta($id,'remote-db-user-primary-key',$client_id);
    $ssh->ssh_bridge_close();
  }
/*   if (isset($_POST['remote-db-user-primary-key'])){
    update_user_meta($id,'remote-db-user-primary-key',$_POST['remote-db-user-primary-key']);
  } */
};

function retrieve_user_info($user =null){
  global $wpdb;
    $allTransactions = [];
    $ssh = start_ssh();
    start_remote_db();
    $client = new ClientsRecordController(new ClientModel());
    $user_id = $user!=null?$user->ID:get_current_user_id();
    $found_id = get_user_meta($user_id,'remote-db-user-primary-key',true);
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
      $transactionRecords = $transactions_model->where('COD_ID',$found_id)->get();//FIXME: change this to the new view Esteban made
    foreach($transactionRecords as $record){
        array_push($allTransactions,$record->toArray());
    }
    update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_address_1',$client_info->DIRECCION);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_city',$client_info->CIUDAD);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_phone',$client_info->TELEFONO_1);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'credit_limit',$client_info->LIMITE);
    // update_user_meta($user == null ?get_current_user_id():$user->ID,'daily_limit',$client_info->LIMITE_D);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_first_name',$client_info->CONTACTO_1);
    update_user_meta($user == null ?get_current_user_id():$user->ID,'all_transactions',$allTransactions);

    $destiny_address_source = new ClientsDestinyController(new ClientsDestinyModel());
    $destiny_address_results = $destiny_address_source->retrieveRecord($found_id)->toArray();
    $destiny_table=$wpdb->prefix.'ocwma_billingadress';
    $shipping_addresses = $wpdb->get_results("SELECT * FROM {$destiny_table} WHERE type='billing' AND userid={$user_id}");
    crosscheck_addresess($destiny_address_results,$shipping_addresses,$destiny_address_source,false);
  }
    $ssh->ssh_bridge_close();
}


function update_user($user =null){
  global $wpdb;
  $ssh = start_ssh();
  start_remote_db();
  $client = new ClientsRecordController(new ClientModel());
  $remoteId = $user==null?get_user_meta(get_current_user_id(),'remote-db-user-primary-key'):get_user_meta($user,'remote-db-user-primary-key',true);
  $args=[
    'timestamps' => false,
    'NOMBRE'=>  $_POST['first_name']." ".$_POST['last_name'],
    'DIRECCION'=>$_POST['billing_address_1'],
    'CIUDAD'=> $_POST['billing_city'],
    'TELEFONO_1'=>$_POST['billing_phone'],
    'EMAIL'=>$_POST['email'],
  ];

  $client->updateRecord($remoteId,$args);
  $destiny_address_source = new ClientsDestinyController(new ClientsDestinyModel());
  $destiny_address_results = $destiny_address_source->retrieveRecord($remoteId)->toArray();
  $destiny_table=$wpdb->prefix.'ocwma_billingadress';
  $shipping_addresses = $wpdb->get_results("SELECT * FROM {$destiny_table} WHERE type='billing' AND userid={$user}");
  crosscheck_addresess($destiny_address_results,$shipping_addresses,$destiny_address_source,true);
  $ssh->ssh_bridge_close();
  
}

function delete_user($user =null){
  $ssh = start_ssh();
  start_remote_db();
  $client = new ClientsRecordController(new ClientModel());
  $remoteId = $user==null?get_user_meta(get_current_user_id(),'remote-db-user-primary-key'):get_user_meta($user,'remote-db-user-primary-key',true);
  $client->deleteRecord($remoteId);
  $ssh->ssh_bridge_close();
  
}

function crosscheck_addresess($remote_addresses,$local_addresses,$remote_queryobject,$update){
  global $wpdb;
  $array_size = count($remote_addresses) <=> count ($local_addresses);
  switch ($array_size) {
      case -1:
        $missing_address=[];
        foreach($local_addresses as $local_address){
          $found=[];
          foreach($remote_addresses as $remote_address){
              $parsed_data = unserialize($local_address->userdata);
              if($remote_address['COD_ID'] == get_user_meta($local_address->userid,'remote-db-user-primary-key',true)&&$remote_address['Direccion'] == $parsed_data['shipping_address_1']){
                array_push($found,(int) true);
             } else {
              array_push($found, (int) false);
            }
          }
          if(array_sum($found)==0){
            $unserialized_data = unserialize($local_address->userdata);
            array_push($missing_address,array(
                  "COD_ID" => get_user_meta($local_address->userid,'remote-db-user-primary-key',true),
                  "Direccion" =>$unserialized_data['shipping_address_1'],
                  // "REM_ID" =>$local_address->id
            ));
          }
        }
        array_walk($missing_address,function($address,$key,$remote_queryobject){
          $remote_queryobject->updateRecord($address['COD_ID'],$address);
        },$remote_queryobject);

      break;
      case 0:
          $i=0;
          foreach($remote_addresses as $remote_address){
            $localid_record =$local_addresses[$i]->id;
            $destiny_table=$wpdb->prefix.'ocwma_billingadress';
            $parsed_data = unserialize($local_addresses[$i]->userdata);
            if($update){
              $remote_address_args=[
                'COD_ID'=> get_user_meta($local_addresses[$i]->userid,'remote-db-user-primary-key',true),
                'Direccion'=>  $parsed_data['shipping_address_1'],
                // 'REM_ID' => $local_addresses[$i]->id,
              ];
              $remote_queryobject->updateRecord($remote_address_args['COD_ID'],$remote_address_args);
            }else{
              $parsed_data['shipping_address_1'] = $remote_address['Direccion'];
              $data_for_update=[
                'id'=>$local_addresses[$i]->id,
                'userid'=>$local_addresses[$i]->userid,
                'userdata' => serialize($parsed_data),
                'type'=>$local_addresses[$i]->type,
                'Defalut'=>$local_addresses[$i]->Defalut
              ];
              $updates = $wpdb->update($destiny_table,$data_for_update,['id'=>$localid_record]);
            }
            $i++;
          }
        
    break;
    case 1:
      $missing_address=[];
      foreach($remote_addresses as $remote_address){
        $found=[];
        foreach($local_addresses as $local_address){
            $parsed_data = unserialize($local_address->userdata);
            if($remote_address['COD_ID'] == get_user_meta($local_address->userid,'remote-db-user-primary-key',true)&&$remote_address['Direccion'] == $parsed_data['shipping_address_1']){
              array_push($found, (int)true);
             } else {
              array_push($found, (int)false);
            }
        }
        if(array_sum($found)==0){
          $destiny_table=$wpdb->prefix.'usermeta';
          $user = $wpdb->get_var("SELECT user_id FROM {$destiny_table} WHERE meta_value ={$remote_address['COD_ID']}");
          $address_data = [
              "userid" => $user,
              "type" => "billing",
              "Defalut" => 0,
              "userdata" =>serialize([
                "reference_field" => "",
                "billing_first_name" =>"",
                "billing_last_name" => "",
                "billing_address_1" =>$remote_address['Direccion'],
                "billing_address_2"=>"",
                "billing_company" => "",
                "billing_city" => "",
                "billing_state" =>"",
                "billing_postcode"=>"",
                "billing_country" =>get_user_meta($user,"billing_country",true),
              ])
          ];
          array_push($missing_address,$address_data); 
        }
      }
      
      array_walk($missing_address,function($address){
        global $wpdb;
        $table = $wpdb->prefix.'ocwma_billingadress';
        $wpdb->replace($table,$address);
      });
    break;

  }


}


