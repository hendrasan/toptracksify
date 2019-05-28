<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chart extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function tracks()
    {
        return $this->hasMany('App\Models\Track');
    }
}
