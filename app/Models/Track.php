<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $guarded = [];

    public function chart()
    {
        return $this->belongsTo('App\Models\Chart');
    }
}
