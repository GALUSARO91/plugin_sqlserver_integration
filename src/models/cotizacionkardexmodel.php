<?php

/*
    Enlace a la tabla clientes
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class cotizacionkardexmodel extends Model{
    
    protected $table = 'Cotizacion Tarjeta de Kardex';

    protected $primaryKey = 'NUM_REG';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public $timestamps = false;
}