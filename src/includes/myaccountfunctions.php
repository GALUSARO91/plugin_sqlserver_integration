<?php

/* *
    *functions to display transactions data
*/

/* *
    *function that adds a new endpoint to wordpress.
    *This is bound to the init hook
*/

function add_transactions_endpoint(){

    add_rewrite_endpoint( 'transaction-history', EP_ROOT | EP_PAGES );
}

/* *
    *function to add a new queryvar to wordpress
    *This is bound to a the query_vars filter
    *@param vars comes from the query_vars filter
*/

function transactions_query_vars($vars){
    $vars[] = 'transaction-history';
    return $vars;
}

/* *
    *function to add a new a link woocommerce my account page
    *This is bound to a the woocommerce_account_menu_items filter
    *@param items comes from the woocommerce filter
*/

function add_transactions_endpoint_link_my_account($items){
    $items['transaction-history'] = 'Historial de transacciones';
    return $items;
}
 
/* *
    *function that echoes first part of the transactions view
*/

function get_transaction_history_content(){

    echo'<section id="trasaction-history"><ul class="transactions-pagebar"></ul><div class="transaction-form"><lable>Elija la cantidad de registros por pagina</lable>
    <input type="number" id="amountOfRecords" name="amountOfRecords" value=50 min=1 max=100></input>
    <input type="button" id="setRecordsPerPage" value="Cambiar"></input></div><div id="transaction-history-content"></div><ul class="transactions-pagebar"></ul></section>';

}

/* *
    function that set all the logic to show transaction records
 */

function transaction_history_records($user = null){
    wp_register_script('transaction-history-script',plugins_url('/js/transaction-history.js',__FILE__ ),array('jquery'),null, true);        
    wp_register_style('transaction-history-style',plugins_url('/css/transaction-history.css',__FILE__ ),array(),null,'all');
    $link = $_SERVER['REQUEST_URI'];
    if($link=="/mi-cuenta/transaction-history/"){
        $GLOBALS['columnsToPrint'] = array('FECHA','NUM_DOC','SALDO','PLAZO','VENCE','LIMITE'); //FIXME: This is hardcoded
        $found_id = $user==null?get_current_user_id():$user->ID;

        $records = get_user_meta($found_id,'all_transactions',true);

        if(is_array($records)){
            $parsed_records = array_map("filter_columns_within_array",$records);
            wp_localize_script('transaction-history-script', 'allrecords', array('records' =>$records));
        };
        wp_localize_script('transaction-history-script', 'columnsToPrint',array('columns'=>$GLOBALS['columnsToPrint']));
        wp_enqueue_script('transaction-history-script');
        wp_enqueue_style('transaction-history-style');        
    }

}

/* *
    function to filter only necesary columns form the transaction
    * @param array_to_filter is where each transaction live

*/

function filter_columns_within_array($array_to_filter){

    $sought_array = $GLOBALS['columnsToPrint'];
    $filtered_array = array_filter($array_to_filter,function($key) use ($sought_array){
        return in_array($key,$sought_array);
    }
    ,ARRAY_FILTER_USE_KEY); 

    return $filtered_array;

}