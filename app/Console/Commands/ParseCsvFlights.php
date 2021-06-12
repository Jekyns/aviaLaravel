<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            $filght = explode(',', $row);

            $DepartureTime = $filght[4] . '00';
            $ArrivalTime = $filght[6] . '00';

            $flights[] = [
                'id' => $filght[0],
                'Origin' => $filght[1],
                'Destination' => $filght[2],
                'DepartureDate' => $filght[3],
                'DepartureTime' => $DepartureTime,
                'ArrivalDate' => $filght[5],
                'ArrivalTime' => $ArrivalTime,
                'Number' => $filght[7],
            ];
        }
        DB::table('flights')->upsert($flights, 'id');
        return true;
    }
}
