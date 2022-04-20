<?php

namespace ROOT\controllers;

class OrdersKardexRecordController extends BaseRecordsController{

    public $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    function createRecord($id){
        try{
            if(isset($id)){
                // if($recordFound == ""){
                    $this->BaseModel->timestamps = false;
                    // $this->BaseModel->NUM_REG = $id;
                    foreach($id as $key=>$value){
                        $this->BaseModel->$key = $value;
                    }
                    $this->BaseModel->save();
                    // return $remoteId;
                // } 
                return true;
            }
        } catch (\Exception $e){
            return $e;
        }
    }

    function retrieveRecord($id){
        // $remoteId = $this->calculateId($id);
        try{
            if(isset($id)&& $id!=""){
                $return = $this->BaseModel::where('NUM_REG',$id)->get();
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
        }catch(\Exception $e){
            return $e;
        }
    }

    function calculateNumReg(string $id=null,string $remote_id=null)
    {
        if($remote_id!=null){
            $latest_num = trim($this->inverseCalculateNumReg($remote_id));
        }else{
            $latest = $this->BaseModel->latest('FECHA')->first(); //Probar este codigo
            $latest_id = trim($latest->NUM_REG);
            $latest_num = $this->inverseCalculateNumReg($latest_id);
        }
        $id_num = $this->inverseCalculateNumReg(trim($id))??0;
        do{
            $id_num++;
        }while($latest_num >= $id_num);
        settype($id_num,'string');
        $return_id = "0101A";
        for($i = 1; $i < 11-strlen($id_num);$i++){
            $return_id .= "0";
        }
        $return_id .= $id_num;
        $remote_record = $this->BaseModel::where('NUM_REG',$return_id)->first();
        $remote_record_id = isset($remote_record)?trim($remote_record->NUM_REG):"";
        if($remote_record_id == $return_id){
           return $this->calculateNumReg($remote_record_id,$return_id);

        } else{
            return $return_id;
        }
        

    }

    function inverseCalculateNumReg($id=null)
    {
        if(isset($id)){
            $num_string = substr($id,-9);
            $return_id = intval($num_string);
            return $return_id;
        }
        
    }


}