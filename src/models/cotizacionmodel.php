<?php

/*
    Enlace a la tabla clientes
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class CotizacionModel extends Model{
    
    protected $table = 'Cotizacion Ventas';

    protected $primaryKey = 'NUM_REG';


}