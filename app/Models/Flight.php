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

    public static function uploadCsv()
    {
        $flightCsv = explode(PHP_EOL, Storage::get('flights/flight.csv'));
        $csvHead = explode(',', array_shift($flightCsv));

        $flights = [];
        $wrongRows = [];
        $wrongDetails = [];
        foreach ($flightCsv as $i => $row) {
            if (!$row) {
                continue;
            }

            $flightColumns = explode(',', $row);
            $flight = array_combine(
                array_slice($csvHead, 0, count($flightColumns)),
                $flightColumns
            );

            $validator = Validator::make($flight, [
                'Id' => 'required|integer',
                'Origin' => 'required|string',
                'Destination' => 'required|string',
                'DepartureDate' => 'required|date',
                'DepartureTime' => 'required|date_format:Hi',
                'ArrivalDate' => 'required|date',
                'ArrivalTime' => 'required|date_format:Hi',
                'Number' => 'required|string',
            ]);

            if ($validator->fails()) {
                $validateErrors = $validator->errors();
                $wrongRows[] = $i + 1;
                $wrongDetails[] = [$i + 1 => array_values($validateErrors->messages())];
                Log::info('ERROR at ' . $i . ' row');
                Log::info($validateErrors);
                continue;
            }

            $DepartureTime = $flight['DepartureTime'] . '00';
            $ArrivalTime = $flight['ArrivalTime'] . '00';

            $flights[] = [
                'id' => $flight['Id'],
                'Origin' => $flight['Origin'],
                'Destination' => $flight['Destination'],
                'DepartureDate' => $flight['DepartureDate'] . $DepartureTime,
                'ArrivalDate' => $flight['ArrivalDate'] . $ArrivalTime,
                'Number' => $flight['Number'],
            ];
        }
        $res = DB::table('flights')->upsert($flights, 'id');

        return ['result' => !!$res, 'wrongRows' => $wrongRows, 'wrongDetails' => $wrongDetails];
    }
}
