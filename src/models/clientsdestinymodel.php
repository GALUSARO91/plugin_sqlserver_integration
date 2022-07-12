<?php

/*
    Link to "Tabla_Direccion_Clientes_Destino" table
    This table has the destinies to calculate price
    per galon
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class clientsdestinymodel extends Model{
    
    protected $table = 'Tabla_Direccion_Clientes_Destino';
    
    public $timestamps = false;
}