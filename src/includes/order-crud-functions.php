<?php

if ( ! defined( 'ABSPATH' ) ) exit;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ROOT\controllers\OrdersRecordController;
use ROOT\controllers\OrdersTarjetaDesRecordController;
use ROOT\models\FactVentasModel;
use ROOT\models\CotizacionModel;
use ROOT\models\CotizacionTarjetaDesModel;
use ROOT\controllers\OrdersKardexRecordController;
use ROOT\models\CotizacionKardexModel;


$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

function remote_order_creator($id){ 
    // $myorder = new OrdersRecordController(new FactVentasModel());
    $post_data =get_post($id);
    if($post_data->post_type == 'shop_order'){
    $order =wc_get_order($id);
    $user_order = $order->get_user_id();
    if($user_order){
        $ssh = start_ssh();
        start_remote_db();
        // $post_data =get_post($id);
        $order_num_meta = get_post_meta($post_data->ID,'order_num',true)??'';
        $client_id = $order->get_user()->get('remote-db-user-primary-key');
        $first_name = $order->get_user()->get('user_firstname');
        $last_name =($order->get_user()->get('user_lastname')!==false && $order->get_user()->get('user_lastname')!=="")?" ".$order->get_user()->get('user_lastname'):'';
        // $last_name =$order->get_user()->get('user_lastname');
        $name = $first_name.$last_name;
        $status = $post_data->post_status;
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
        $myorder = new OrdersTarjetaDesRecordController(new CotizacionTarjetaDesModel());
        $args =[
            "Cod_Emp"=>"01",
            "COD_SUC"=>"01",
            "COD_DIA"=>"ORD-1",
            "TIP_DOC"=>36,
            "COD_ID" => $client_id,
            "FECHA"=>date('Y-m-d').'T00:00:00.000',
            "HORA"=>date("H:i:s").".000",
            "DESC_DOC" => $name,
            "COD_US"=>"SCM",
            "NUM_DOC"=>$myorder->set_num_doc(),
            "TIPO_CAMB"=>35.5
        ];
        
    // $remoteId = $myorder->calculatedId($post_data-ID);
    if($order_num_meta == ''){// Se puede arreglar validando si ya hay previamente un ord_num
        $order_num = $myorder->createRecord($order_num_meta,$args);
        update_post_meta($post_data->ID,'order_num', $order_num);
        $myOrder_items_model = new CotizacionKardexModel();
        foreach ($order->get_items() as $item_id => $item) {
            $item_prod = $item->get_product();
            $COD_PROD = $item_prod->get_attribute('ID_GCM');;
            $args = [
                'NUM_REG'=> $order_num,
                'COD_SUC' => 0,
                'NUM_LIN' => 1,
                'COD_PROD' => $COD_PROD,
                'CANTIDAD' => $item->get_quantity(),
                'VALOR' => $item->get_subtotal(),
                'COSTO' => $item->get_subtotal(),
                'DESC_TARJ' => $item->get_name(),
                'Cod_Prec' => 1,
                'Unidades'=> 1,
                'TIPO_ITEM' => "S",
            ];
            $myOrder_items_model->create($args);
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
        
            } else {
                $myorder->updateRecord($order_num_meta,$args);
            }
                $ssh->ssh_bridge_close();
        }
    }
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
    if(isset($order)){
        $order_num = get_post_meta($order,'order_num',true)??'';
        $ssh = start_ssh();
        start_remote_db();
        $myorder = new OrdersTarjetaDesRecordController(new CotizacionTarjetaDesModel());
        if($order_num!=''){
            $myorder->deleteRecord($order_num);
        }
        $ssh->ssh_bridge_close();
    }
}
 
