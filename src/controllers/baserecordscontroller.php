<?php
/**
 * Abstract class to create controllers
 * @Param $BaseModel of tipe Illuminate\Database\Model 
 * stores the functions for the crud in remote db
 */
namespace ROOT\controllers;
use ROOT\traits\remotedbpluginerrorhandler;

abstract class baserecordscontroller  {
    // use remotedbpluginerrorhandler;

    private $BaseModel;

    function __construct($BaseModel){

        $this->BaseModel = $BaseModel;
    }


    abstract public function createRecord($id);

    abstract public function retrieveRecord($id);
    
    abstract public function updateRecord($id);

    abstract public function deleteRecord($id);

    public function getModel(){
        return $this->BaseModel;
    }

}