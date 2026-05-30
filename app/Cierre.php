<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cierre extends Model
{
    protected $table = 'cierres';

    protected $fillable = ['user_id', 'fecha_cierre', 'observacion'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
