<?php
/* *
  *this file contains the logic to handle the products crud on remote db
*/
if ( ! defined( 'ABSPATH' ) ) exit;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ROOT\models\productmodel;
use ROOT\controllers\productsrecordcontroller;


$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

function remote_product_creator($product = null){
    try{
        $ssh = start_ssh();
        start_remote_db();
        $post_data = get_post($product);
        $prod_attributes = get_post_meta($product,'_product_attributes',true);
        $remoteId = ($prod_attributes != "" and $prod_attributes['id_gcm']['value'] != false) ? $prod_attributes['id_gcm']['value']:"";
        $args =[
          "status" =>$post_data->post_status,
          "Cod_emp" => "01",
          "COD_GRUP" => "01",
          "COD_LIN" => "02",
          "U_MEDIDA" => 2,
          "COD_IMP" => 2,
          "NOM_PROD" => $post_data->post_title,
          "LIQUIDADO" => "N"
        ];
        $myproduct = new productsrecordcontroller(new productmodel());
        $myproduct->createRecord($remoteId,$args);
        $ssh->ssh_bridge_close();
    }catch(\Error $e){
        myErrorHandler($e);
    }catch(\Exception $e){
        myErrorHandler($e);
  } 
};

function retrieve_product_info($post_query =null){
    try{
        global $wpdb;
        $type = isset($post_query->query['post_type'])?$post_query->query['post_type']:null;
        if($type =="product"){
          $ssh = start_ssh();
          start_remote_db();
          $myproduct = new productsrecordcontroller(new productmodel());
          $name = isset($post_query->query['name'])?$post_query->query['name']:null;
          $product =$wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='$type'",$name));
          $prod_attributes = get_post_meta($product,'_product_attributes',true)??"";
          $remoteId = ($prod_attributes != "" and $prod_attributes['id_gcm']['value'] != false) ? $prod_attributes['id_gcm']['value']:"";
          $remoteProductInfo = $myproduct->retrieveRecord($remoteId);
          $ssh->ssh_bridge_close();
          if(isset($remoteProductInfo)){
          wp_update_post([
            'ID' =>$product,
            'post_title' =>$remoteProductInfo->NOM_PROD,
            /* 'meta_input'=>[
              '_regular_price'=>$remoteProductInfo->PRECIO_BASE,
              '_price'=>$remoteProductInfo->PRECIO_BASE,

            ], */
          ]);
          // update_post_meta($product->ID,"_price",$remoteProductInfo->PRECIO_VTA);
        }
      }
    }catch(\Error $e){
        myErrorHandler($e);
    }catch(\Exception $e){
        myErrorHandler($e);
  } 
    
}

function delete_product($product=null){
  try{
      $ssh = start_ssh();
      start_remote_db();
      $prod_attributes = get_post_meta($product,'_product_attributes',true)??"";
      $remoteId = ($prod_attributes != "" and $prod_attributes['id_gcm']['value'] != false) ? $prod_attributes['id_gcm']['value']:"";
      $product = new productsrecordcontroller(new productmodel());
      if($remoteId !=""){
        $product->deleteRecord($remoteId);
      }
      $ssh->ssh_bridge_close();

    }catch(\Error $e){
        myErrorHandler($e);
    }catch(\Exception $e){
        myErrorHandler($e);
  } 
}

