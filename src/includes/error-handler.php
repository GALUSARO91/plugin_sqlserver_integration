<?php

function myErrorHandler($e){
    // TODO: Write error code here
    $wp_error = new WP_Error();
    $wp_error->add($e->getCode(),$e->getMessage());
    add_action('add_meta_boxes','errorMessageBoxSetup',10,1);
}

function errorMessageBoxSetup($message_to_show){
    add_meta_box( 'remote-db-plugin-error-message', 
    'Error', 
    'errorMessageBox',
    null,
    'advanced',
    'high',
    [$message_to_show]);
}

function errorMessageBox($message_to_show){

    echo $message_to_show;
  
}