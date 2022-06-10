<?php

if ( ! defined( 'ABSPATH' ) ) exit;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ROOT\controllers\clientsrecordcontroller;
use ROOT\controllers\ClientsDestinyController;
use ROOT\models\clientmodel;
use ROOT\models\clientsdestinymodel;
use ROOT\models\clientfactmodel;
use ROOT\models\factventasmodel;
use ROOT\models\carteramodel;

include_once __DIR__.'/error-handler.php';

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

function remote_user_creator($id){
  try{
    if(!isset($_POST['user-in-db'])){
      $ssh = start_ssh();
      start_remote_db();
      $client = new clientsrecordcontroller(new clientmodel());
      $args = [
        "Cod_Emp" => "01",
        "COD_SUC" => "01",
        "COD_ZON" => "01",
        "NOMBRE" => $_POST['first_name']." ".$_POST['last_name'],
        "EMAIL" => $_POST['email'],
        "CUENTA" => '1103-01-01',
        'NUM_RUC' => $_POST['NUM_RUC'],
        'PLAZO' => $_POST['PLAZO'],
        'LIMITE' => $_POST['LIMITE'],
      ];
      $client_id = $client->createRecord($_POST['remote-db-user-primary-key'],$args);
      update_user_meta($id,'remote-db-user-primary-key',$client_id);
      update_user_meta( $id,'NUM_RUC', $_POST['NUM_RUC']);
      update_user_meta( $id,'PLAZO', $_POST['PLAZO']);
      update_user_meta( $id,'LIMITE', $_POST['LIMITE']);
      $ssh->ssh_bridge_close();
    }
  }
/*   if (isset($_POST['remote-db-user-primary-key'])){
    update_user_meta($id,'remote-db-user-primary-key',$_POST['remote-db-user-primary-key']);
  } */
  catch(\Error $e){
      myErrorHandler($e);
  }catch(\Exception $e){
      myErrorHandler($e);
} 
};

function retrieve_user_info($user =null){
  try{
      global $wpdb;
      $allTransactions = [];
      $ssh = start_ssh();
      start_remote_db();
      $client = new clientsrecordcontroller(new clientmodel());
      $user_id = $user!=null?$user->ID:get_current_user_id();
      $found_id = get_user_meta($user_id,'remote-db-user-primary-key',true);
      $client_info = $client->retrieveRecord($found_id);
      if(isset($client_info)){ 
        $transactions_model = new carteramodel();
        $transactionRecords = $transactions_model->where('COD_ID',$found_id)->get();
      foreach($transactionRecords as $record){
          array_push($allTransactions,$record->toArray());
      }
      update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_address_1',$client_info->DIRECCION);
      update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_city',$client_info->CIUDAD);
      update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_phone',$client_info->TELEFONO_1);
      update_user_meta($user == null ?get_current_user_id():$user->ID,'credit_limit',$client_info->LIMITE);
      update_user_meta($user == null ?get_current_user_id():$user->ID,'billing_first_name',$client_info->CONTACTO_1);
      update_user_meta($user == null ?get_current_user_id():$user->ID,'all_transactions',$allTransactions);

      $destiny_address_source = new ClientsDestinyController(new clientsdestinymodel());
      $destiny_address_results = $destiny_address_source->retrieveRecord($found_id)->toArray();
      $destiny_table=$wpdb->prefix.'ocwma_billingadress';
      $billing_addresses = $wpdb->get_results("SELECT * FROM {$destiny_table} WHERE type='billing' AND userid={$user_id}");
      crosscheck_addresess($destiny_address_results,$billing_addresses,$destiny_address_source,false);
    }
      $ssh->ssh_bridge_close();

  }catch(\Error $e){
      myErrorHandler($e);
  }catch(\Exception $e){
      myErrorHandler($e);
  } 
}


function update_user($user =null){
  try{
    global $wpdb;
    $ssh = start_ssh();
    start_remote_db();
    $client = new clientsrecordcontroller(new clientmodel());
    $remoteId = $user==null?get_user_meta(get_current_user_id(),'remote-db-user-primary-key'):get_user_meta($user,'remote-db-user-primary-key',true);
    $args=[
      'timestamps' => false,
      'NOMBRE'=>  $_POST['first_name']." ".$_POST['last_name'],
      'DIRECCION'=>$_POST['billing_address_1'],
      'CIUDAD'=> $_POST['billing_city'],
      'TELEFONO_1'=>$_POST['billing_phone'],
      'EMAIL'=>$_POST['email'],
      'NUM_RUC' => $_POST['NUM_RUC'],
      'PLAZO' => $_POST['PLAZO'],
      'LIMITE' => $_POST['LIMITE'],
    ];
    $client->updateRecord($remoteId,$args);
    $destiny_address_source = new ClientsDestinyController(new clientsdestinymodel());
    $destiny_address_results = $destiny_address_source->retrieveRecord($remoteId)->toArray();
    $destiny_table=$wpdb->prefix.'ocwma_billingadress';
    $billing_addresses = $wpdb->get_results("SELECT * FROM {$destiny_table} WHERE type='billing' AND userid={$user}");
    crosscheck_addresess($destiny_address_results,$billing_addresses,$destiny_address_source,true);
    $ssh->ssh_bridge_close();
  }catch(\Error $e){
      myErrorHandler($e);
  }catch(\Exception $e){
      myErrorHandler($e);
  } 
  
}

function delete_user($user =null){
  try{
      $ssh = start_ssh();
      start_remote_db();
      $client = new clientsrecordcontroller(new clientmodel());
      $remoteId = $user==null?get_user_meta(get_current_user_id(),'remote-db-user-primary-key'):get_user_meta($user,'remote-db-user-primary-key',true);
      $client->deleteRecord($remoteId);
      $ssh->ssh_bridge_close();
  }catch(\Error $e){
      myErrorHandler($e);
  }catch(\Exception $e){
      myErrorHandler($e);
  } 
  
}

function delete_user_destiny(){
  global $wpdb;
  $destiny_table=$wpdb->prefix.'ocwma_billingadress';

        if( isset($_REQUEST['action']) && $_REQUEST['action']=="delete_ocma"){

            $destiny_id = sanitize_text_field($_REQUEST['did']);
            $destiny = $wpdb->get_results("SELECT * FROM {$destiny_table} where id = '{$destiny_id}'"); //FIXME: returns and array instead of a value
            $user_id = $destiny[0]->userid;
            $remote_db_user_id = get_user_meta($user_id,'remote-db-user-primary-key',true); 
            $unserialized_address_data = unserialize($destiny[0]->userdata); //FIXME: Data is within an array
            $destiny_args = [
              'COD_ID' => $remote_db_user_id,
              "Direccion" =>$unserialized_address_data['billing_address_1']
            ];
            $ssh = start_ssh();
            start_remote_db();
            $destiny_address_source = new ClientsDestinyController(new clientsdestinymodel());
            $destiny_address_source->deleteRecord($destiny_args['COD_ID'],$destiny_args);
            $ssh->ssh_bridge_close();
        }
}

function crosscheck_addresess($remote_addresses,$local_addresses,$remote_queryobject,$update){
  try{   
    global $wpdb;
    $array_size = count($remote_addresses) <=> count ($local_addresses);
    switch ($array_size) {
        case -1:
          $missing_address=[];
          foreach($local_addresses as $local_address){
            $found=[];
            foreach($remote_addresses as $remote_address){
                $parsed_data = unserialize($local_address->userdata);
                if($remote_address['COD_ID'] == get_user_meta($local_address->userid,'remote-db-user-primary-key',true)&&$remote_address['Direccion'] == $parsed_data['billing_address_1']){
                  array_push($found,(int) true);
              } else {
                array_push($found, (int) false);
              }
            }
            if(array_sum($found)==0){
              $unserialized_data = unserialize($local_address->userdata);
              array_push($missing_address,array(
                    "COD_ID" => get_user_meta($local_address->userid,'remote-db-user-primary-key',true),
                    "Direccion" =>$unserialized_data['billing_address_1'],
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
                  'Direccion'=>  $parsed_data['billing_address_1'],
                  // 'REM_ID' => $local_addresses[$i]->id,
                ];
                $remote_queryobject->updateRecord($remote_address_args['COD_ID'],$remote_address_args);
              }else{
                $parsed_data['billing_address_1'] = $remote_address['Direccion'];
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
              if($remote_address['COD_ID'] == get_user_meta($local_address->userid,'remote-db-user-primary-key',true)&&$remote_address['Direccion'] == $parsed_data['billing_address_1']){
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
  }catch(\Error $e){
        myErrorHandler($e);
  }catch(\Exception $e){
        myErrorHandler($e);
  }  

}


