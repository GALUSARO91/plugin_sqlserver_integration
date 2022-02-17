<?php

if ( ! defined( 'ABSPATH' ) ) exit;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ROOT\models\ProductModel;
use ROOT\controllers\ProductsRecordController;


$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

function remote_product_creator($product = null){
    $ssh = start_ssh();
    start_remote_db();
    $myproduct = new ProductsRecordController(new ProductModel());
    $myproduct->createRecord($product);
    $ssh->ssh_bridge_close();
};

function retrieve_product_info($post_query =null){
        // $test = $_REQUEST;
    $type = isset($post_query->query['post_type'])?$post_query->query['post_type']:null;
    if($type =="product"){
      $ssh = start_ssh();
      start_remote_db();
      $myproduct = new ProductsRecordController(new ProductModel());
      $name = isset($post_query->query['name'])?$post_query->query['name']:null;
      $product = get_page_by_title($name, OBJECT, $type);
      $prod_attributes = get_post_meta($product->ID,'_product_attributes',true)??"";
      $remoteId = ($prod_attributes != "" and $prod_attributes['gcm_id']['value'] != false) ? $prod_attributes['gcm_id']['value']:"";
      $remoteProductInfo = $myproduct->retrieveRecord($remoteId);
      $ssh->ssh_bridge_close();
      update_post_meta($product->ID,"_price",$remoteProductInfo->PRECIO_VTA);
      
    }

    
}

function delete_product($product=null){
  $ssh = start_ssh();
  start_remote_db();
  $prod_attributes = get_post_meta($product,'_product_attributes',true)??"";
  $remoteId = ($prod_attributes != "" and $prod_attributes['gcm_id']['value'] != false) ? $prod_attributes['gcm_id']['value']:"";
  $product = new ProductsRecordController(new ProductModel());
  if($remoteId !=""){
    $product->deleteRecord($remoteId);
  }
  $ssh->ssh_bridge_close();
}

