<?php

/*
    Enlace a la tabla clientes
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class clientsdestinymodel extends Model{
    
    protected $table = 'Tabla_Direccion_Clientes_Destino';
    
    public $timestamps = false;
}