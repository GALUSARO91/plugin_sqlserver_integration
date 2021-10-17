<?php
namespace ROOT\controllers;

use ROOT\models\Clients;

// TODO: Abstract class for record controllers

class ClientsController{

    private $clients;

    function __construct(Clients $clients){

        $this->clients = $clients;
    }


    function create_client($id){
       
        // $already_set = is_null($this->clients::where('COD_ID',$_POST['remote-db-user-primary-key'])->get())?$_POST['remote-db-user-primary-key']:;
        $already_set = $this->clients::where('COD_ID',$_POST['remote-db-user-primary-key'])->get();
        if(isset($_POST['remote-db-user-primary-key']) && $_POST['role'] == 'customer'){

            $client_id = $this->set_random_id($_POST['remote-db-user-primary-key'],$already_set);
            $client_name = $_POST['first_name']." ".$_POST['last_name'];
            $client_balance = 0;    

            $this->clients->timestamps = false;
            $this->clients->COD_ID = $client_id;
            $this->clients->NOMBRE = $client_name;
            $this->clients->SALDO = $client_balance;
            $this->clients->save();
        }


    }
// TODO: Test below function
    function set_random_id(string $id_given, string $id_in_remote_db){
        $new_id=null;
        if($id_given == $id_in_remote_db){
            $new_id = mt_rand(1,99999999);
            $this->set_random_id($new_id,$this->clients::where('COD_ID',$new_id)->get());

        } else {
            $new_id = $id_given;
            return $new_id;
        }

    }

}