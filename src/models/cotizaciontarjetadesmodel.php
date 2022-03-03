<?php

/*
    Enlace a la tabla clientes
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class CotizacionTarjetaDesModel extends Model{
    
    protected $table = 'Cotizacion Tarj Desc Doc';

    protected $primaryKey = 'NUM_REG';

    public $incrementing = false;

    protected $keyType = 'string';


}