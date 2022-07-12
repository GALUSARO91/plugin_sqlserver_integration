<?php

/*
    Link to cartera view to retrieve transaction history
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class carteramodel extends Model{
    
    protected $table = 'CARTERA';
    
    public $timestamps = false;
}