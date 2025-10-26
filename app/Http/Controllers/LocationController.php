<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = Location::all();

        return response()->json([
            "data" => $locations->all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "name" => "required|string|min:4",
                "longitude" => "required|string",
                "latitude" => "required|string"
            ]);

            $location = Location::create($validated);

            return response()->json([
                "message" => "Successfully created location",
                "data" => $location->all()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                "message" => "Validation failed",
                "errors" => $e->errors()
            ], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $validated = $request->validate([
                "name" => "nullable|string|min:4",
                "longitude" => "nullable|string",
                "latitude" => "nullable|string"
            ]);

            $location = Location::findOrFail($id);

            if (!$location) {
                return response()->json([
                    "message" => "Resource not found"
                ], 404);
            }

            $location_update = $location->update($validated); // нихуя он true выводит а не массив

            return response()->json([
                "message" => "Successfully updated location",
                "data" => $location
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                "message" => "Validation failed",
                "errors" => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $location = Location::findOrfail($id);

        $location_copy = array_slice($location->toArray(), 0);

        if (!$location) {
            return response()->json([
                "message" => "Resource not found"
            ], 404);
        }

        $location->delete();

        return response()->json([
            "message" => "Successfully deleted location",
            "data" => $location_copy
        ]);
    }
}
