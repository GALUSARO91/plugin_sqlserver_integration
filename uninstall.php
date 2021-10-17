<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

function delete_remote_db_options($option){
    delete_option($option);

}

array_walk($GLOBALS['rm_db_option_names'],'delete_remote_db_options');