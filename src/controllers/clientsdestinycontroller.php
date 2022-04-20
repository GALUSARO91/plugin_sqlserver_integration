<?php
namespace ROOT\controllers;

class ClientsDestinyController extends BaseRecordsController{

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    public function createRecord($id,$args = null){
        $this->updateRecord($id,$args);
    }

    public function retrieveRecord($id)
    {
        try{
            return $this->BaseModel::where('COD_ID',$id)->where('Desactivar',0)->get();
        } catch(\Exception $e){
            return $e;
        }
    }
    
    public function updateRecord($id,$args=null)
    {
        try{
            if(isset($args)){
                // $records_found=$this->retrieveRecord($id);
                $this->BaseModel->upsert($args,['COD_ID','Direccion'],['COD_ID','Direccion']);
                return true;
            }
        }
        catch(\Exception $e){
            // remoteDBPluginErrorHandler($e->code,$e->message);
            // $wp_error = new WP_Error($e->getCode(),$e->getMessage());
            return $e;
        }

    }

    public function deleteRecord($id)
    {

    }
    

}