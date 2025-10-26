<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReviewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $location_id = $request->query("location_id");

        $query = Location::query();

        if ($location_id) {
            $query->where("locations.id", $location_id);
        }

        $locations = $query->paginate(8);

        return response()->json([
            $locations
        ]);

//        return response()->json([
//
//        ])
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user("sanctum");

            $validated = $request->validate([
                "location_id" => "required|exists:locations,id",
                "emotion" => [
                    Rule::in([
                        "HAPPY",
                        "SAD",
                        "ANGRY"
                    ]),
                    "required",
                    "string"
                ],
                "comment" => "required|string"
            ]);

            $location = Location::findOrFail($validated["location_id"]);

            $validated["user_id"] = $user->id;

            Review::create($validated);

            return response()->json([
                "message" => "Successfully created feedback",
                "data" => [
                    "user" => $user->only([
                        "id",
                        "first_name",
                        "last_name",
                        "patronymic",
                        "avatar"
                    ]),
                    "location" => $location->all(),
                    "emotion" => $validated["emotion"],
                    "comment" => $validated["comment"]
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                "message" => "Login failed"
            ], 401);
        }

    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
