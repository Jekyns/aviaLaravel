<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
ini_set('max_execution_time', 300);

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
        $headStr = strtolower(array_shift($flightCsv)); // Приводим весь заголовок к строке без заглавных букв 
        $headStr = str_replace(" ", '', $headStr); // Удаляем все пробелы
        $csvHead = explode(',', $headStr); // Разбиваем заголовок таблицы

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
            ); // Формируем обьект где ключи это имена колонок а значение берем из этой строки

            $validator = Validator::make($flight, [
                'id' => 'required|integer',
                'origin' => 'required|string',
                'destination' => 'required|string',
                'departuredate' => 'required|date',
                'departuretime' => 'required|date_format:Hi',
                'arrivaldate' => 'required|date',
                'arrivaltime' => 'required|date_format:Hi',
                'number' => 'required|string',
            ]);

            if ($validator->fails()) {
                $validateErrors = $validator->errors();
                $wrongRows[] = $i + 1;
                $wrongDetails[] = [$i + 1 => array_values($validateErrors->messages())];
                Log::info('ERROR at ' . $i . ' row');
                Log::info($validateErrors);
                continue;
            }

            $DepartureTime = $flight['departuretime'] . '00'; // Добавляем секунды
            $ArrivalTime = $flight['arrivaltime'] . '00';

            $flights[] = [
                'id' => $flight['id'],
                'Origin' => $flight['origin'],
                'Destination' => $flight['destination'],
                'DepartureDate' => $flight['departuredate'] . $DepartureTime,
                'ArrivalDate' => $flight['arrivaldate'] . $ArrivalTime,
                'Number' => $flight['number'],
            ];
        }
        $chunkedFlights = array_chunk($flights, 1000);
        $res = 0;
        foreach($chunkedFlights as $flightsChunk){
            $res += DB::table('flights')->upsert($flightsChunk, 'id');
        };

        return ['result' => !!$res, 'wrongRows' => $wrongRows, 'wrongDetails' => $wrongDetails];
    }
}
