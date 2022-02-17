<?php

if ( ! defined( 'ABSPATH' ) ) exit;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ROOT\controllers\OrdersRecordController;
use ROOT\controllers\OrdersTarjetaDesRecordController;
use ROOT\models\FactVentasModel;
use ROOT\models\CotizacionModel;
use ROOT\models\CotizacionTarjetaDesModel;

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

function remote_order_creator($id){ 
    $ssh = start_ssh();
    start_remote_db();
    // $myorder = new OrdersRecordController(new FactVentasModel());
    $post_data =get_post($id);
    $order = wc_get_order($id);
    $client_id = $order->get_user()->get('remote-db-user-primary-key');
    $name = $order->get_user()->get('user_firstname').' '.$order->get_user()->get('user_lastname') ;
    
/*     $args = [
        "PLAZO" => 7,
        "COD_VEND" => "00001",
        "DIRECCION" => $order->get_shipping_address_1(),
        "TELEFONO" => $order->get_billing_phone(),
        "SUBTOTAL" => $order->get_subtotal(),
        "VALOR" => $order->get_total(),
        "MONEDA"=> 1,
        "TIP_DESC"=> 0,
        "TIP_EXO"=> 0
    ]; */

    $args =[
        "Cod_Emp"=>"01",
        "COD_SUC"=>"01",
        "COD_DIA"=>"ORD-1",
        "TIP_DOC"=>36,
        "COD_ID" => $client_id,
        "FECHA"=>date('Y-m-d').'T00:00:00.000',
        "HORA"=>date("H:i:s").".000",
        "DESC_DOC" => $name,
        "COD_US"=>"SCM"
        // "COD_ID"=
    ];
    $myorder = new OrdersTarjetaDesRecordController(new CotizacionTarjetaDesModel());
    // $remoteId = $myorder->calculatedId($post_data-ID);
    $myorder->createRecord($post_data->ID,$args);

    foreach ( $order->get_items() as $item_id => $item ) {
        $args = [
            'COD_SUC' => 0,
            'NUM_LIN' => 1,
            'COD_PROD' => $item->get_product()->get_attribute('GCM_ID'),
        ];
       /*  $product_id = $item->get_product_id();
        $variation_id = $item->get_variation_id();
        $product = $item->get_product();
        $product_name = $item->get_name();
        $quantity = $item->get_quantity();
        $subtotal = $item->get_subtotal();
        $total = $item->get_total();
        $tax = $item->get_subtotal_tax();
        $taxclass = $item->get_tax_class();
        $taxstat = $item->get_tax_status();
        $allmeta = $item->get_meta_data();
        $somemeta = $item->get_meta( '_whatever', true );
        $product_type = $item->get_type(); */
     }

    $ssh->ssh_bridge_close();

};

 function retrieve_order_info($post_query =null){
    $type = isset($post_query->query['post_type'])?$post_query->query['post_type']:null;
    if($type =="shop_order_refund"){
        $post = $post_query->query['post_parent'];
        $ssh = start_ssh();
        start_remote_db();
        $myorder = new OrdersRecordController(new FactVentasModel());
        $orderinfo = $myorder->retrieveRecord($post);
        $ssh->ssh_bridge_close();
        $args=[
            'order_id'=>$post,
        ];
    }
}

/*
function update_user($order =null){

  
}
*/
function delete_order($order =null){
    $ssh = start_ssh();
    start_remote_db();
    $myorder = new OrdersRecordController(new FactVentasModel());
    $myorder->deleteRecord($order);
    $ssh->ssh_bridge_close();
}
 
