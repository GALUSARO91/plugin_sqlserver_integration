<?php

/*
    Link to "Cotizacion Tarj Desc Doc"
    This table has the main info of the orders
    that are shipped
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class cotizaciontarjetadesmodel extends Model{
    
    protected $table = 'Cotizacion Tarj Desc Doc';

    protected $primaryKey = 'NUM_REG';

    public $incrementing = false;

    protected $keyType = 'string';


}