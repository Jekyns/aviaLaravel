<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\Flight;
use Illuminate\Support\Facades\Validator;

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
        return json_encode([
            'Number' => $flight->Number,
            'DepartureDate' => $flight->DepartureDate,
            'ArrivalDate' => $flight->ArrivalDate,
        ]);
    }

    /**
     * Upload csv function.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function upload(Request $request)
    {
        $validator = Validator::make($request->file(), [
            'flight_csv' => 'required|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return json_encode([
                'error' => true,
                'message' => 'flight_csv does not exist or is not a file',
            ]);
        }

        $flightCsv = $request->file('flight_csv');

        Storage::putFileAs('flights', new File($flightCsv), 'flight.csv');
        $uploads = Flight::uploadCsv();
        return json_encode([
            'error' => false,
            'message' => $uploads['result'] ? 'Flights loaded' : 'There is nothing to add or update',
            'wrongRows' => $uploads['wrongRows'],
            'wrongDetails' => $uploads['wrongDetails']
        ]);
    }
}
