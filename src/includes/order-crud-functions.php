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
    $post_data =get_post($id);
    if($post_data->post_type == 'shop_order'){
    $order =wc_get_order($id);
    $user_order = $order->get_user_id();
    if($user_order){
        $ssh = start_ssh();
        start_remote_db();
        $order_num_meta = get_post_meta($post_data->ID,'order_num',true)??'';
        $client_id = $order->get_user()->get('remote-db-user-primary-key');
        $first_name = $order->get_user()->get('user_firstname');
        $last_name =($order->get_user()->get('user_lastname')!==false && $order->get_user()->get('user_lastname')!=="")?" ".$order->get_user()->get('user_lastname'):'';
        $name = $first_name.$last_name;
        $myorder = new OrdersTarjetaDesRecordController(new CotizacionTarjetaDesModel());
        $order_num = $myorder->calculateNumReg($order_num_meta);
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
        $prod_items = [];
        $myOrder_items_model = new OrdersKardexRecordController(new CotizacionKardexModel());
        foreach ($order->get_items() as $item_id => $item) {
            $item_prod = $item->get_product();
            $COD_PROD = $item_prod->get_attribute('ID_GCM');
            $item_args = [
                'NUM_REG'=> $order_num_meta!=''?$order_num_meta:$order_num,
                'COD_SUC' => 0,
                'NUM_LIN' => 1,
                'COD_PROD' => $COD_PROD,
                'CANTIDAD' => $item->get_quantity()*-1,
                'VALOR' => $item->get_subtotal(),
                'COSTO' => $item->get_subtotal(),
                'DESC_TARJ' => $item->get_name(),
                'Cod_Prec' => 1,
                'Unidades'=> 1,
                'TIPO_ITEM' => "P",
            ];
            array_push($prod_items,$item_args);
        }
         if($order_num_meta == ''){
            $myorder->createRecord($order_num,$args);
            update_post_meta($post_data->ID,'order_num', $order_num);
            // $myOrder_items_model->BaseModel->upsert($prod_items,['NUM_REG','COD_PROD'],['CANTIDAD','VALOR','COSTO','DESC_TARJ','COD_SUC','NUM_LIN','Cod_Prec','Unidades','TIPO_ITEM']);       
            create_remote_order_items($prod_items,$myOrder_items_model);
            // $myOrder_items_model->
            } else {
                $myorder->updateRecord($order_num_meta,$args);
                $myOrder_items_model->BaseModel::where('NUM_REG',$order_num_meta)->delete();
                // $myOrder_items_model->BaseModel->upsert($prod_items,['NUM_REG','COD_PROD'],['CANTIDAD','VALOR','COSTO','DESC_TARJ','COD_SUC','NUM_LIN','Cod_Prec','Unidades','TIPO_ITEM']);
                create_remote_order_items($prod_items,$myOrder_items_model);
            }
                $ssh->ssh_bridge_close();
        }
    }

}

 function retrieve_order_info($post_query =null){
    $type = isset($post_query->query['post_type'])?$post_query->query['post_type']:null;
    if($type =="shop_order_refund"){
        $post = $post_query->query['post_parent'];
        $order_num_meta = get_post_meta($post,'order_num',true)??'';
        $ssh = start_ssh();
        start_remote_db();
        $myorder = new OrdersTarjetaDesRecordController(new CotizacionTarjetaDesModel());
        $myorder_items = new OrdersKardexRecordController(new CotizacionKardexModel());
        if($order_num_meta!==''){
            // $orderinfo = $myorder->retrieveRecord($order_num_meta)??'';
            $wc_order = new  WC_Order($post);
            $order_items = $wc_order->get_items( array('line_item', 'fee', 'shipping') );
            $remote_order_items = $myorder_items->retrieveRecord($order_num_meta)->toArray()??'';
            foreach ($order_items as $item) {
                wc_delete_order_item($item->get_id());
            }
            foreach ($remote_order_items as $remote_item){
                $remote_prod_id = $remote_item['COD_PROD'];
                $product_query = new WC_Product_Query(array(
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'return' => 'objects',
                    'limit' => -1,
                ));
                // $products_found = wc_get_products();
                $products_found = $product_query->get_products();
                $product = find_product_from_remote_info($products_found,$remote_prod_id);
                $order_item_args = [
                    'order_item_name' => $remote_item['DESC_TARJ'],
                    'order_item_type' => 'line_item',
                    // '_qty' => $remote_item['CANTIDAD']*-1,
                    // '_product_id' => $product->get_id(),
                    // '_line_subtotal' => $remote_item['VALOR'],
                    // '_line_total' => ($remote_item['VALOR']*$remote_item['CANTIDAD']*-1)
                ];
                $order_item_id = wc_add_order_item($post,$order_item_args); 
                wc_add_order_item_meta($order_item_id,'_qty',$remote_item['CANTIDAD']*-1,true);
                wc_add_order_item_meta($order_item_id,'_product_id',$product->get_id(),true);
                wc_add_order_item_meta($order_item_id,'_line_subtotal',$remote_item['VALOR'],true);
                wc_add_order_item_meta($order_item_id,'_line_total',$remote_item['VALOR']*$remote_item['CANTIDAD']*-1,true);
            }
            $wc_order->calculate_totals();
        }
        $ssh->ssh_bridge_close();

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
        $myOrder_items_model = new OrdersKardexRecordController(new CotizacionKardexModel());
        if($order_num!=''){
            $myorder->deleteRecord($order_num);
            $myOrder_items_model->BaseModel::where('NUM_REG',$order_num_meta)->delete();
        }
        $ssh->ssh_bridge_close();
    }
}
 
function create_remote_order_items($args,$model){
        foreach ($args as $arg){
            $model->BaseModel::create($arg);
        }
        
}

function find_product_from_remote_info($query,$filter){
    $return;
    foreach($query as $product){
       $remote_id = $product->get_attribute('ID_GCM');
       if($remote_id == $filter){
           $return = $product;
           break;
       }
    }
    return $return;
}