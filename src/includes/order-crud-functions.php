<?php

/* *
    *this file contains all the logic to handle the data of order's crud
*/

if ( ! defined( 'ABSPATH' ) ) exit;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ROOT\controllers\ordersrecordcontroller;
use ROOT\controllers\orderstarjetadesrecordcontroller;
use ROOT\models\factventasmodel;
use ROOT\models\cotizacionmodel;
use ROOT\models\cotizaciontarjetadesmodel;
use ROOT\controllers\orderskardexrecordcontroller;
use ROOT\models\cotizacionkardexmodel;
use ROOT\models\productmodel;
use ROOT\controllers\productsrecordcontroller;
use ROOT\controllers\clientsrecordcontroller;
use ROOT\models\clientsdestinymodel;
use ROOT\controllers\clientsdestinycontroller;
use ROOT\models\exchangeratemodel;

include_once __DIR__.'/error-handler.php';

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG));

function remote_order_creator($id){ 
    try{
        $post_data =get_post($id);
        if($post_data->post_type == 'shop_order'){
        $order =wc_get_order($id);
        $user_order = $order->get_user_id();
        if($user_order){
            $ssh = start_ssh();
            start_remote_db();
            $order_num_meta = get_post_meta($post_data->ID,'order_num',true)??'';
            $client_id = $order->get_user()->get('remote-db-user-primary-key');
            $name = $order->get_user()->get('user_login');
            $myorder = new orderstarjetadesrecordcontroller(new cotizaciontarjetadesmodel());
            $order_num = $myorder->calculateNumReg($order_num_meta);
            $order_date_created = $order->get_date_created();
            $camdollar_table = new exchangeratemodel();
            $exchange_rate = $camdollar_table->whereDate('FECHA',$order_date_created)->firstOr(function(){
                return "35.5";
            });
            $args =[
                "Cod_Emp"=>"01",
                "COD_SUC"=>"01",
                "COD_DIA"=>"ORD-WEB",
                "TIP_DOC"=>36,
                "COD_ID" => $client_id,
                "FECHA"=>date_format($order_date_created,"Y-m-d")."T".date_format($order_date_created,"H:i:s"),
                "HORA"=>date_format($order_date_created,"H:i:s"),
                "DESC_DOC" => $name,
                "COD_US"=>"SCM",
                "NUM_DOC"=>$myorder->set_num_doc(),
                "TIPO_CAMB"=>$exchange_rate
            ];
            $prod_items = [];
            $myOrder_items_controller = new orderskardexrecordcontroller(new cotizacionkardexmodel());
            $wc_order_items = $order->get_items();
            foreach ($wc_order_items as $item_id => $item) {
                $remote_product_controller = new productsrecordcontroller(new productmodel());
                $item_prod = $item->get_product();
                $COD_PROD = $item_prod->get_attribute('ID_GCM');
                $remote_product_info = $remote_product_controller->retrieveRecord($COD_PROD);
                $destiny_address_source = new clientsdestinycontroller(new clientsdestinymodel());
                $destiny_address_info = $destiny_address_source->retrieveRecord($client_id)->toArray();
                $destiny_values = array_values(array_filter($destiny_address_info,function($destiny)use($order){
                    $string1 = trim($destiny['Direccion']); 
                    $string2 = $order->get_billing_address_1();
                        if(strcasecmp($string1,$string2)==0){
                            return true;
                        } else {
                            return false;
                        }
                }));
                $valor = (float)$remote_product_info->P_COS_C??0;
                $valor += (float)$destiny_values[0]['FLETE']??0;
                $valor += (float)$destiny_values[0][$COD_PROD.'_COM_EMP']??0;
                $valor += (float)$destiny_values[0][$COD_PROD.'_COM_VEND']??0;
                $item_args = [
                    'NUM_REG'=> $order_num_meta!=''?$order_num_meta:$order_num,
                    'COD_SUC' => 0,
                    'NUM_LIN' => $destiny_values[0]['NUM_LIN_DIR'],
                    'COD_PROD' => $COD_PROD,
                    'CANTIDAD' => $item->get_quantity()*-1,
                    'VALOR' => $valor,
                    'COSTO' => $remote_product_info->P_COS_C,
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
                create_remote_order_items($prod_items,$myOrder_items_controller);
                } else {
                    $myorder->updateRecord($order_num_meta,$args);
                    $myOrder_items_controller->deleteRecod($order_num_meta);
                    create_remote_order_items($prod_items,$myOrder_items_controller);
                }
                    $ssh->ssh_bridge_close();
                    
            }
        }
        }catch(\Error $e){
            myErrorHandler($e);
        }catch(\Exception $e){
            myErrorHandler($e);
        }    
}

 function retrieve_order_info ($post_query = null){
    try{
        $type = isset($post_query->query['post_type'])?$post_query->query['post_type']:null;
        if($type =="shop_order_refund"){
            $post = $post_query->query['post_parent'];
            $order_num_meta = get_post_meta($post,'order_num',true)??'';
            $ssh = start_ssh();
            start_remote_db();
            $myorder = new orderstarjetadesrecordcontroller(new cotizaciontarjetadesmodel());
            $myorder_items = new orderskardexrecordcontroller(new cotizacionkardexmodel());
            if($order_num_meta!==''){
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
                    $products_found = $product_query->get_products();
                    $product = find_product_from_remote_info($products_found,$remote_prod_id);
                    $order_item_args = [
                        'order_item_name' => $remote_item['DESC_TARJ'],
                        'order_item_type' => 'line_item',
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
    }catch(\Error $e){
        myErrorHandler($e);
    }catch(\Exception $e){
        myErrorHandler($e);
    } 
}

function delete_order($order =null){
    try{
        if(isset($order)){
            $order_num = get_post_meta($order,'order_num',true)??'';
            $ssh = start_ssh();
            start_remote_db();
            $myorder = new orderstarjetadesrecordcontroller(new cotizaciontarjetadesmodel());
            $myOrder_items_controller = new orderskardexrecordcontroller(new cotizacionkardexmodel());
            if($order_num!=''){
                $myorder->deleteRecord($order_num);
                $myOrder_items_controller->deleteRecord($order_num);
            }
            $ssh->ssh_bridge_close();
        }
    }catch(\Error $e){
        myErrorHandler($e);
    }catch(\Exception $e){
      myErrorHandler($e);
    } 
}
 
/* *
    function to create a new record on remote db
    *@param args contains the data to be passed to the remote db
    *@param controller is the object makes the action
*/
function create_remote_order_items($args,$controller){
        foreach ($args as $arg){
            $controller->createRecord($arg);
        }
        
}

/* *
    *function that filters the gcm ids
    *@param query is the info coming from the remote db
    *@param filter is the value we are looking for
*/

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