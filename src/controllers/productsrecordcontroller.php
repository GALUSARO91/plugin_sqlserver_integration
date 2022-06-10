<?php
namespace ROOT\controllers;


class productsrecordcontroller extends baserecordscontroller{

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    function createRecord($id,$args = null){
        $product = $this->BaseModel::where('COD_PROD',$id)->first();
        // $recordFound = $product->COD_PROD;
        try{
            if(isset($args)){
                if($id !="" and $args['status'] =="publish"){
                    if(!$product){
                        $this->BaseModel->timestamps = false;
                        $this->BaseModel->COD_PROD = $id;
                        foreach($args as $key=>$value){
                            if($key !='status'){
                                $this->BaseModel->$key = $value;
                            }
                        }
                        $this->BaseModel->save();
                    } else {
                        $product->timestamps = false;
                        foreach($args as $key=>$value){
                            if($key !='status'){
                                $product->$key = $value;
                            }
                        }
                        $product->save();
                    }
                }
                return true;  
            }
        } catch(\Exception $e){
            return $e;
        }
    }
    function retrieveRecord($id){
        try{
            if(isset($id)&& $id!=""){
                $return = $this->BaseModel::where('COD_PROD',$id)->first();
                return $return;
            }
        }catch(\Excetion $e){
            return $e;
        }
    }

    function updateRecord($id,$args = null)
    {
        $this->createRecord($id,$args = null);   
    }

    function deleteRecord($id)
    {
        try{
            $this->BaseModel::where('COD_PROD',$id)->first()->delete();   
            return true;
        }catch(\Exception $e){
            return $e;
        }
    }
}