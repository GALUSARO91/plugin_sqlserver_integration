<?php

/*
    Enlace a la tabla clientes
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class clientfactmodel extends Model{
    
    protected $table = 'Facturacion Tarj Desc Doc';

    protected $primaryKey = 'NUM_REG';


}