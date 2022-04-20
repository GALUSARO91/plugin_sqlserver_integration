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
      // "PRECIO_BASE" => get_post_meta($product,'_regular_price',true),
      "LIQUIDADO" => "N"
    ];
    
    $myproduct = new ProductsRecordController(new ProductModel());
    $myproduct->createRecord($remoteId,$args);
    $ssh->ssh_bridge_close();
};

function retrieve_product_info($post_query =null){
    global $wpdb;
        // $test = $_REQUEST;
    $type = isset($post_query->query['post_type'])?$post_query->query['post_type']:null;
    if($type =="product"){
      $ssh = start_ssh();
      start_remote_db();
      $myproduct = new ProductsRecordController(new ProductModel());
      $name = isset($post_query->query['name'])?$post_query->query['name']:null;
      $product =$wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='$type'",$name));
      // $product = get_page_by_path("/tienda/$name/", OBJECT, $type);
/*       $product = new WP_Query([
        "post_type" => $type,
        "name" => $name
      ]); */
    
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

    
}

function delete_product($product=null){
  $ssh = start_ssh();
  start_remote_db();
  $prod_attributes = get_post_meta($product,'_product_attributes',true)??"";
  $remoteId = ($prod_attributes != "" and $prod_attributes['id_gcm']['value'] != false) ? $prod_attributes['id_gcm']['value']:"";
  $product = new ProductsRecordController(new ProductModel());
  if($remoteId !=""){
    $product->deleteRecord($remoteId);
  }
  $ssh->ssh_bridge_close();
}

