<?php

/* 
    CRUD to handle tarjetadesdoc
*/

namespace ROOT\controllers;

class orderstarjetadesrecordcontroller extends baserecordscontroller{

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    function createRecord($id,$args = null){
       
            if(isset($args)){
           
                    $this->BaseModel->timestamps = false;
                    $this->BaseModel->NUM_REG = $id;
                    foreach($args as $key=>$value){
                        $this->BaseModel->$key = $value;
                    }
                    $this->BaseModel->save();
                    return true;
            }
   
           
    }

    function retrieveRecord($id){
 
            if(isset($id)&& $id!=""){
                $return = $this->BaseModel::where('NUM_REG',$id)->first();
                return $return;
                }
 
    }

    function updateRecord($id,$args = null)
    {
       
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
    }

    function deleteRecord($id)
    {  
            if (isset($id)){
                $model_found =$this->BaseModel::where('NUM_REG',$id)->first();
                if(isset($model_found)){
                    $model_found->delete();
                    // return true;
                }
            }
     
    }
/* 
    *Creates a new string ID for the records
    *@param id, is the one saved on wordpress db
    *@param remote_id is the one saved on remote db
*/
    function calculateNumReg(string $id=null,string $remote_id=null)
    {
        if($remote_id!=null){
            $latest_num = trim($this->inverseCalculateNumReg($remote_id));
        }else{
            $latest = $this->BaseModel->latest('FECHA')->first(); 
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
/* 
    *returns a integer from the calculateNumReg string
    *@param $id is the string id from remote db 
 */

    function inverseCalculateNumReg($id=null)
    {
        if(isset($id)){
            $num_string = substr($id,-9);
            $return_id = intval($num_string);
            return $return_id;
        }
        
    }

    /* *
    *returns a string with an number of the document
    *@param id_given is the one store on wordpress db
     */

    function set_num_doc(string $id_given =null){
        $new_id = null;
            $preRecordFound = isset($id_given) ? $this->BaseModel->where('COD_DIA','ORD-WEB')->where('NUM_DOC',$id_given)->get():$this->BaseModel->where('COD_DIA','ORD-WEB')->get();
            $recordFound = $preRecordFound->max('NUM_DOC');
            $calculatedId = !empty($recordFound)? $recordFound + 1 : 1;
            $recordFoundInRemoteDB = $this->BaseModel->where('COD_DIA','ORD-WEB')->where('NUM_DOC',$calculatedId)->first(); 
            if(!empty($recordFoundInRemoteDB->NUM_DOC)){  
                $new_id_in_remote_db = !empty($recordFoundInRemoteDB->NUM_DOC)?$recordFoundInRemoteDB->NUM_DOC+1:$calculatedId;
                return $this->set_num_doc($new_id_in_remote_db); 
            } else {
            
                settype($calculatedId,'string');
                for($i = 0; $i < 7-strlen($calculatedId);$i++){
                    $new_id .= '0';
                }
                $new_id.=$calculatedId;
                return trim($new_id);
        }
    }
}