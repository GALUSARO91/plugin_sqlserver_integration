<?php
/* *
* Handles crud for clients in remote db

*/

namespace ROOT\controllers;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class clientsrecordcontroller extends baserecordscontroller{

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    public function createRecord($id, $args = null){
        try{
        $already_set = $this->BaseModel::where('COD_ID',$id)->first();
            if($_POST['role'] == 'customer'){
                $client_id = $this->set_random_id($id,!is_null($already_set)?$already_set->COD_ID:'');
                $this->BaseModel->timestamps = false;
                $this->BaseModel->COD_ID = $client_id;
                foreach($args as $key=>$value){
                    $this->BaseModel->$key = $value;
                }
                $this->BaseModel->save();
                return $client_id;
            }
        }catch(\Exception $e){

            return $e;
        }
    }

    public function retrieveRecord($id)
    {
        try{
            if(isset($id)&& $id!=""){
                $return = $this->BaseModel::where('COD_ID',$id)->first();
                return $return;
            }
        }catch(\Exception $e){

            return $e;
        }

    }
    
    public function updateRecord($id,$args=null)
    {
        try{
            if(isset($id)&& $id!=""){
            $remote_user = $this->BaseModel::where('COD_ID',$_POST['remote-db-user-primary-key'])->first();
            foreach($args as $key=>$value){
                $remote_user->$key = $value;
            }
                $remote_user->save();
            }        
        } catch(\Exception $e){
                return $e;
        }
    }

    public function deleteRecord($id)
    {
        try{
            if(isset($id)&& $id!=""){
                $this->BaseModel::where('COD_ID',$id)->first()->delete();
                return true;
            }
        }catch(\Exception $e){
            return $e;
        }    
    }
    function set_random_id(string $id_given, string $id_in_remote_db){
        /* *
            Sets a consecutve number for the orders
         */
        $new_id = null;

        if($id_given == $id_in_remote_db){
            $new_id = $this->BaseModel->max('COD_ID')+1;
            $new_id_in_remote_db = $this->BaseModel::where('COD_ID',$new_id)->first();
            return $this->set_random_id($new_id,!is_null($new_id_in_remote_db )?$new_id_in_remote_db->COD_ID:''); 
        } else {
            
            settype($id_given,'string');
            for($i = 0; $i < 5-strlen($id_given);$i++){
                $new_id .= '0';
            }
            $new_id.=$id_given;
            return $new_id;
        }

    }


}