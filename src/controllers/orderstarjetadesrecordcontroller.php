<?php

namespace ROOT\controllers;

class OrdersTarjetaDesRecordController extends BaseRecordsController{

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    function createRecord($id,$args = null){
        // $remoteId = $this->calculateNumReg($id);
        // $recordFound = $this->BaseModel::where('NUM_REG',$remoteId)->first()??"";
        // try{
            if(isset($args)){
                // if($recordFound == ""){
                    $this->BaseModel->timestamps = false;
                    $this->BaseModel->NUM_REG = $id;
                    foreach($args as $key=>$value){
                        $this->BaseModel->$key = $value;
                    }
                    $this->BaseModel->save();
                    return true;
                // } 
            }
        // } catch(\Exceptions $e){
            // return $e;
        // }
           
    }

    function retrieveRecord($id){
        // $remoteId = $this->calculateNumReg($id);
            // try{
            if(isset($id)&& $id!=""){
                $return = $this->BaseModel::where('NUM_REG',$id)->first();
                return $return;
                }
            // } catch(\Exception $e){
                // return $e;
            // }
    }

    function updateRecord($id,$args = null)
    {
        // $remoteId = $this->calculateNumReg($id);
        // try{    
            $recordFound = $this->BaseModel::where('NUM_REG',$id)->first()??"";
            if(isset($args)){
                if($recordFound != "") {
                    $recordFound->timestamps = false;
                    // $recordFound->NUM_REG = $remoteId;
                    foreach($args as $key=>$value){
                        $recordFound->$key = $value;
                    }
                    $recordFound->save();
                }
            }
        // }catch(\Exception $e){
            // return $e;
        // }
           
        
    }

    function deleteRecord($id)
    {  // $remoteId = $this->calculateId($id);
        // try{
            if (isset($id)){
                $model_found =$this->BaseModel::where('NUM_REG',$id)->first();
                if(isset($model_found)){
                    $model_found->delete();
                    // return true;
                }
            }
      /* d */   
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

    function set_num_doc(string $id_given =null, string $id_in_remote_db =null){
        $new_id = null;

        if($id_given == $id_in_remote_db){
            $new_id = $this->BaseModel->max('NUM_DOC')+1;
            $new_id_in_remote_db = $this->BaseModel::where('NUM_DOC',$new_id)->first();
            return $this->set_num_doc($new_id,!is_null($new_id_in_remote_db )?$new_id_in_remote_db->COD_ID:''); 
        } else {
            
            settype($id_given,'string');
            for($i = 0; $i < 7-strlen($id_given);$i++){
                $new_id .= '0';
            }
            $new_id.=$id_given;
            return $new_id;
        }
    }
}