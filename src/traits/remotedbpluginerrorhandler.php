<?php
namespace ROOT\traits;

trait remotedbpluginerrorhandler{
   public function remoteDBPluginErrorHandler($code,$message = null){
        $wp_error = new WP_Error($code, $message = null);
        
    }
}
