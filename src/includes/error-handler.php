<?php
/* *
    function to handle errors
    *@param e is the error message that comes by default
    *@param customMessage is a string with data we would like to show to the user
 */
function myErrorHandler($e = null,$customMessage = null){
    if(isset($e)){
        $wp_error = new WP_Error();
        $wp_error->add($e->getCode(),$e->getMessage());
        error_log('Error No: '.$e->getCode().' Error Message: '.$e->getMessage());
        add_action('wp_footer',function() use($customMessage){
            error_message($customMessage);
        },10,1);
        add_action('admin_footer',function() use($customMessage){
            error_message($customMessage);
        },10,1);
    }  
}

function error_message($message = null){
    $defaultMessage = $message??'No pudimos conectarnos con el sistema principal, por favor intente de nuevo. Si el problema persiste comuniquese con GCM Transportes directamente';
    echo('<script type="text/javascript" id="error-alert">alert("'.$defaultMessage.'")</script>');
}