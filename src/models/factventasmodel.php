<?php

/*
    Enlace a la tabla clientes
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class FactVentasModel extends Model{
    
    protected $table = 'Facturacion Ventas';

    protected $primaryKey = 'NUM_REG';


}