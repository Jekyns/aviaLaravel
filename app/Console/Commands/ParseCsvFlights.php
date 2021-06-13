<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ParseCsvFlights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:flights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload flights from csv';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $flightCsv = explode(PHP_EOL, Storage::get('flights/flight.csv'));
        array_shift($flightCsv);

        $flights = [];
        foreach ($flightCsv as $row) {
            if (!$row) {
                break;
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
                Log::info('ERROR with flight id: ' . $flight[0]);
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

        return !!$res;
    }
}
