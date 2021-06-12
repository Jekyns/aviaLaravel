<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\Flight;

class FlightsController extends Controller
{

    public function __construct()
    {
    }

    public function index()
    {
        $flights = Flight::where()->get();

        return $flights;
    }


    public function getOne(Request $request, $id)
    {
        $flight = Flight::where(['id' => $id])->first();
        if (!$flight) {
            return json_encode(['error' => true, 'message' => 'Flight does not exist']);
        }
        return json_encode(['Number' => $flight->Number, 'DepartureDate' => $flight->DepartureDate, 'DepartureTime' => $flight->DepartureTime, 'ArrivalDate' => $flight->ArrivalDate, 'ArrivalTime' => $flight->ArrivalTime]);
    }

    /**
     * Upload csv function.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function upload(Request $request)
    {
        $flightCsv = $request->file('flight_csv');
        // if($flightCsv->extension() !== 'csv'){
        //     return 'Wrong file extension';
        // }
        Storage::putFileAs('flights', new File($flightCsv), 'flight.csv');
        Artisan::call('parse:flights');
        return json_encode(['error' => false, 'message' => 'Flights loaded']);
    }
}
