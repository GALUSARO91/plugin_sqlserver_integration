<?php

/*
    Enlace a la tabla clientes
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class ClientModel extends Model{
    
    protected $table = 'Facturacion Clientes';

    protected $primaryKey = 'COD_ID';

    const CREATED_AT = 'FEC_ING';

}