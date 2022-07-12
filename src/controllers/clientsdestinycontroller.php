<?php
/** 
 * CRUD for client´s destiny records on remote db
 * */ 
 

namespace ROOT\controllers;

use ROOT\controllers\baserecordscontroller;

class clientsdestinycontroller extends baserecordscontroller{

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
            return $e;
        }

    }

    public function deleteRecord($id,$args = null)
    {
        if(isset($args)){
            // array_push($args,['Desactivar' => true]);
            $args['Desactivar'] = true;
            $this->BaseModel->upsert($args,['COD_ID','Direccion'],['Desactivar']);
        }
            
    }
    

}