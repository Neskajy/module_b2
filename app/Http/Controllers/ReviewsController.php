<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
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

        $reviews = Review::query()
            ->with("user:id,first_name,last_name,patronymic,avatar")
            ->with("location:id,name")
            ->when($location_id, fn($query) => $query->where("location_id", $location_id))
            ->paginate(8);

        return response()->json([
            "data" => [
                "items" => $reviews->getCollection()->map(function ($review) {
                    return [
                        "id" => $review->id,
                        "user" => [
                            $review->user->id,
                            $review->user->first_name,
                            $review->user->last_name,
                            $review->user->patronymic,
                            $review->user->avatar,
                        ],
                        "location" => $review->location->name,
                        "emotion" => $review->emotion
                    ];
                }),
                "current_page" => $reviews->currentPage(),
                "total_pages" => $reviews->total(),
                "items_per_page" => $reviews->perPage()
            ]
        ]);
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

    public function my(Request $request): JsonResponse
    {
        $user = $request->user("sanctum");

        $reviews = $user->reviews();

        return response()->json([
            "data" => $reviews
        ]);
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
