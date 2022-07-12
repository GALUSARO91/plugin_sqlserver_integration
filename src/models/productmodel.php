<?php

/* 
    Link to the "Facturacion Productos" table
    This stores the products info

*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class productmodel extends Model{
    
    protected $table = 'Facturacion Productos';

    protected $primaryKey = 'COD_PROD';


}