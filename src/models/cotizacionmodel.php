<?php

/*
    Link to "Cotizacion Ventas" table
    This has part of the data for the
    orders being shipped.
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class cotizacionmodel extends Model{
    
    protected $table = 'Cotizacion Ventas';

    protected $primaryKey = 'NUM_REG';


}