<?php



function add_transactions_endpoint(){
    add_rewrite_endpoint( 'transaction-history', EP_ROOT | EP_PAGES );
}

function transactions_query_vars($vars){
    $vars[] = 'transaction-history';
    return $vars;
}
function add_transactions_endpoint_link_my_account($items){
    $items['transaction-history'] = 'Historial de transacciones';
    return $items;
}
 

function get_transaction_history_content(){

    echo'<section id="trasaction-history"><ul id="transactions-navbar"></ul><lable>Elija la cantidad de registros por pagina</lable>
    <input type="number" id="amountOfRecords" name="amountOfRecords" value=50 min=1 max=100></input>
    <input type="button" id="setRecordsPerPage" value="Seleccionar cantidad de registros por pagina"></input><div id="transaction-history-content"></div></section>';

}

function transaction_history_records($user = null){
    wp_register_script('transaction-history-script',plugins_url('/js/transaction-history.js',__FILE__ ),array('jquery'),null, true);
        // wp_enqueue_script('transaction-history-script',plugins_url('/js/transaction-history.js',__FILE__ ),array('jquery'),null, true);
    $link = $_SERVER['REQUEST_URI'];
    if($link=="/mi-cuenta/transaction-history/"){
        $columnsToPrint =array('PLAZO','COSTO','DESCUENTO','SUBTOTAL','IGV','IEC','VALOR');
        $found_id = $user==null?get_current_user_id():$user->ID;
        $records = get_user_meta($found_id,'all_transactions',true); 
        // wp_enqueue_script('transaction-history-script',plugins_url('/js/transaction-history.js',__FILE__ ),array('jquery'),null, true);
        if(is_array($records)){
            wp_localize_script('transaction-history-script', 'allrecords', array('records' =>$records));
        }
        wp_localize_script('transaction-history-script', 'columnsToPrint',array('columns'=>$columnsToPrint));
        wp_enqueue_script('transaction-history-script');
    }

}
