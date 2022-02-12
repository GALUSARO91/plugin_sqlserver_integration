<?php
namespace ROOT\controllers;

class ClientsRecordController extends BaseRecordsController{

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    public function createRecord($id){

        $already_set = $this->BaseModel::where('COD_ID',$_POST['remote-db-user-primary-key'])->first();
        if($_POST['role'] == 'customer'){
            $client_id = $this->set_random_id($_POST['remote-db-user-primary-key'],!is_null($already_set)?$already_set->COD_ID:'');
            $client_name = $_POST['first_name']." ".$_POST['last_name'];
            $this->BaseModel->timestamps = false;
            $this->BaseModel->Cod_Emp = "01";
            $this->BaseModel->COD_SUC = "01";
            $this->BaseModel->COD_ZON = "01";
            $this->BaseModel->COD_ID = $client_id;
            $this->BaseModel->NOMBRE = $client_name;
            $this->BaseModel->EMAIL = $_POST['email'];
            $this->BaseModel->CUENTA = '1103-01-1';
            $this->BaseModel->save();
            return $client_id;
        }
    }

    public function retrieveRecord($id)
    {
        if(isset($id)&& $id!=""){
        $return = $this->BaseModel::where('COD_ID',$id)->first();
        return $return;
        }
    }
    
    public function updateRecord($id)
    {
        // TODO: create function updateRecord
        if(isset($id)&& $id!=""){
        $client_name = $_POST['first_name']." ".$_POST['last_name'];
        $remote_user = $this->BaseModel::where('COD_ID',$_POST['remote-db-user-primary-key'])->first();
        $remote_user->timestamps = false;
        $remote_user->NOMBRE = $client_name;
        $remote_user->DIRECCION = $_POST['billing_address_1'];
        $remote_user->CIUDAD = $_POST['billing_city'];
        $remote_user->TELEFONO_1 = $_POST['billing_phone'];
        $remote_user->EMAIL = $_POST['email'];
        $remote_user->save();
        }        

    }

    public function deleteRecord($id)
    {
        if(isset($id)&& $id!=""){
        $this->BaseModel::where('COD_ID',$id)->first()->delete();
        }
    }
    function set_random_id(string $id_given, string $id_in_remote_db){
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