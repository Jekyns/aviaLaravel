<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $table = 'flights';
    public $timestamps = false;

    protected $fillable = [
        'Origin',
        'Destination',
        'DepartureDate',
        'ArrivalDate',
        'Number'
    ];
}
