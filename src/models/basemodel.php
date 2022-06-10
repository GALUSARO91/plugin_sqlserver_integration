<?php

/*
    Enlace a la tabla clientes
*/

namespace ROOT\models;
use Illuminate\Database\Eloquent\Model;

class basemodel extends Model{
    protected $table;

    function set_table(string $table)
    {   
        // parent::__construct();
        $this->table = $table;
    }

    function get_table(){
        return $this->table;
    }

}

