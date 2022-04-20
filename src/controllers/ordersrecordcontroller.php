<?php

namespace ROOT\controllers;

class OrdersRecordController extends BaseRecordsController{

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    function createRecord($id,$args = null){
        try{
        // $post_data =get_post($id);
        $remoteId = $this->calculateId($id);
        $recordFound = $this->BaseModel::where('NUM_REG',$remoteId)->first()??"";
        // $order = wc_get_orders($id);
        // $total = $order[0]->data["total"];
            if(isset($args)){
                if($recordFound == ""){
                    $this->BaseModel->timestamps = false;
                    $this->BaseModel->NUM_REG = $remoteId;
                    foreach($args as $key=>$value){
                        $this->BaseModel->$key = $value;
                    }
                    $this->BaseModel->save();
                } else {
                    $recordFound->timestamps = false;
                    $recordFound->NUM_REG = $remoteId;
                    foreach($args as $key=>$value){
                        $recordFound->$key = $value;
                    }
                    $recordFound->save();
                }
            }
            return true;
        } catch(\Exception $e){
            return $e;
        }
           
    }

    function retrieveRecord($id){
        try{
        $remoteId = $this->calculateId($id);
        if(isset($remoteId)&& $remoteId!=""){
            $return = $this->BaseModel::where('NUM_REG',$remoteId)->first();
            return $return;
            }
        } catch(\Exception $e){
            return $e;
        }
    }

    function updateRecord($id)
    {
        
    }

    function deleteRecord($id)
    {
        try{
            $remoteId = $this->calculateId($id);
            $this->BaseModel::where('NUM_REG',$remoteId)->first()->delete();   
            return true;
        } catch(\Exception $e){
            return $e;
        }
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