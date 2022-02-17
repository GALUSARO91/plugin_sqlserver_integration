<?php

/*
    Enlace a la tabla clientes
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class CotizacionKardexModel extends Model{
    
    protected $table = 'Cotizacion Tarjeta de Kardex';

    protected $primaryKey = 'NUM_REG';


}