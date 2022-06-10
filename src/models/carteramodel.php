<?php

/*
    Enlace a la tabla clientes
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class carteramodel extends Model{
    
    protected $table = 'CARTERA';
    
    public $timestamps = false;
}