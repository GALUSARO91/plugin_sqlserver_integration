<?php
/**
 * Abstract class to create controllers
 * @Param $BaseModel of tipe Illuminate\Database\Model 
 */
namespace ROOT\controllers;
use ROOT\traits\remotedbpluginerrorhandler;

abstract class BaseRecordsController  {
    // use remotedbpluginerrorhandler;

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    abstract public function createRecord($id);

    abstract public function retrieveRecord($id);
    
    abstract public function updateRecord($id);

    abstract public function deleteRecord($id);

    function getModel(){
        return $this->BaseModel;
    }

}