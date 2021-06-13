<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

    public static function uploadCsv () {
        $flightCsv = explode(PHP_EOL, Storage::get('flights/flight.csv'));
        array_shift($flightCsv);

        $flights = [];
        $wrongRows = [];
        foreach ($flightCsv as $i => $row) {
            if (!$row) {
                continue;
            }
            $flight = explode(',', $row);

            $validator = Validator::make($flight, [
                '0' => 'required|integer',
                '1' => 'required|string',
                '2' => 'required|string',
                '3' => 'required|date',
                '4' => 'required|date_format:Hi',
                '5' => 'required|date',
                '6' => 'required|date_format:Hi',
                '7' => 'required|string',
            ]);
            if ($validator->fails()) {
                $wrongRows[] = $i;
                Log::info('ERROR at ' . $i . ' row with flight id: ' . $flight[0]);
                Log::info($validator->errors());
                continue;
            }

            $DepartureTime = $flight[4] . '00';
            $ArrivalTime = $flight[6] . '00';

            $flights[] = [
                'id' => $flight[0],
                'Origin' => $flight[1],
                'Destination' => $flight[2],
                'DepartureDate' => $flight[3] . $DepartureTime,
                'ArrivalDate' => $flight[5] . $ArrivalTime,
                'Number' => $flight[7],
            ];
        }
        $res = DB::table('flights')->upsert($flights, 'id');

        return ['result' => !!$res, 'wrongRows' => $wrongRows];
    }
}
