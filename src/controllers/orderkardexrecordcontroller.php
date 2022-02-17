<?php

namespace ROOT\controllers;

class OrdersKardexRecordController extends BaseRecordsController{

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    function createRecord($id, $args = null){
        $remoteId = $this->calculateId($id);
        $recordFound = $this->BaseModel::where('NUM_REG',$remoteId)->first()??"";
            if($recordFound == ""){
                $this->BaseModel->timestamps = false;
                $this->BaseModel->NUM_REG = $remoteId;
                $this->BaseModel->PLAZO = 0;
                $this->BaseModel->COD_VEND = "00001";
                $this->BaseModel->MONEDA = 0;
                $this->BaseModel->VALOR = $total;
                $this->BaseModel->save();
            } else {
                $recordFound->timestamps = false;
                $recordFound->NUM_REG = $remoteId;
                $recordFound->PLAZO = 7;
                $recordFound->COD_VEND = "00001";
                $recordFound->MONEDA = 0;
                $recordFound->VALOR = $total;
                $recordFound->save();
            }
           
    }

    function retrieveRecord($id){
        $remoteId = $this->calculateId($id);
        if(isset($remoteId)&& $remoteId!=""){
            $return = $this->BaseModel::where('NUM_REG',$remoteId)->first();
            return $return;
            }
    }

    function updateRecord($id)
    {
        
    }

    function deleteRecord($id)
    {
        $remoteId = $this->calculateId($id);
        $this->BaseModel::where('NUM_REG',$remoteId)->first()->delete();   
    }

    function calculateId($id)
    {
        settype($id,"string");
        $return_id = "0101A";
        for($i = 1; $i < 10-strlen($id);$i++){
            $return_id .= "0";
        }
        $return_id .= $id;
        return $return_id;

    }

    function inverseCalculateId($id)
    {

    }
}