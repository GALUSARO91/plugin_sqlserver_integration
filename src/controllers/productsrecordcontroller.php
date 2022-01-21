<?php
namespace ROOT\controllers;


class ProductsRecordController extends BaseRecordsController{

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    function createRecord($id){
        $post_data = get_post($id);
        $prod_attributes = get_post_meta($id,'_product_attributes',true);
        $remoteId = ($prod_attributes != "" and $prod_attributes['gcm_id']['value'] != false) ? $prod_attributes['gcm_id']['value']:"";
        $recordFound = $this->BaseModel::where('COD_PROD',$remoteId)->first();
        if($remoteId !="" and $post_data->post_status =="publish"){
            if($remoteId != $recordFound->COD_PROD){
                $this->BaseModel->timestamps = false;
                $this->BaseModel->Cod_emp = "01";
                $this->BaseModel->COD_GRUP = "01";
                $this->BaseModel->COD_LIN = "02";
                $this->BaseModel->U_MEDIDA = 2;
                $this->BaseModel->COD_IMP = 2;
                $this->BaseModel->COD_PROD = $remoteId;
                $this->BaseModel->NOM_PROD = $post_data->post_title;
                $this->BaseModel->PRECIO_VTA = get_post_meta($id,'_price',true);
                $this->BaseModel->save();
            } else {
                $recordFound->timestamps = false;
                $recordFound->Cod_emp = "01";
                $recordFound->COD_GRUP = "01";
                $recordFound->COD_LIN = "02";
                $recordFound->U_MEDIDA = 2;
                $recordFound->COD_IMP = 2;
                $recordFound->NOM_PROD = $post_data->post_title;
                $recordFound->PRECIO_VTA = get_post_meta($id,'_price',true);
                $recordFound->save();
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