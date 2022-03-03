<?php
namespace ROOT\controllers;


class ProductsRecordController extends BaseRecordsController{

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    function createRecord($id,$args = null){
        $product = $this->BaseModel::where('COD_PROD',$id)->first();
        // $recordFound = $product->COD_PROD;
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
           
    }
    }
    function retrieveRecord($id){
        if(isset($id)&& $id!=""){
            $return = $this->BaseModel::where('COD_PROD',$id)->first();
            return $return;
            }
    }

    function updateRecord($id)
    {
        
    }

    function deleteRecord($id)
    {
        $this->BaseModel::where('COD_PROD',$id)->first()->delete();   
    }
}