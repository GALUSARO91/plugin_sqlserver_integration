<?php

/* 
    Link to the "CAMDOLAR" table
    this has the info for the exchange rate

*/

namespace ROOT\models;

use Illuminate\Database\Eloquent\Model;

class exchangeratemodel extends Model{

    protected $table = 'CAMDOLAR';

    const CREATED_AT = 'FECHA';
    
}
