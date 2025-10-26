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
            ->where("status", "=", "APPROVED")
            ->with("user:id,first_name,last_name,patronymic,avatar")
            ->with("location:id,name")
            ->when($location_id, fn ($q) => $q->where("location_id", $location_id))
            ->paginate(8);

        return response()->json([
            "data" => [
                "items" => [
                    $reviews->getCollection()->map(function($review) {
                        return [
                            "id" => $review->id,
                            "user" => [
                                "id" => $review->user->id,
                                "first_name" => $review->user->first_name,
                                "last_name" => $review->user->last_name,
                                "patronymic" => $review->user->patronymic,
                                "avatar" => $review->user->avatar
                            ],
                            "location" => $review->location->name,
                            "emotion" => $review->emotion
                        ];
                    })
                ],
                "current_page" => $reviews->currentPage(),
                "total_pages" => $reviews->total(),
                "items_per_page" => $reviews->perPage()
            ]
        ]);
    }

    public function my(Request $request): JsonResponse
    {
        $user = $request->user("sanctum");

        $reviews = Review::query()
            ->where("user_id", $user->id)
            ->with("user:id,first_name,last_name,patronymic,avatar")
            ->with("location:id,name");

        return response()->json([
            "data" => [
                $reviews->get()->map(function ($review) {
                    return [
                        "user" => [
                            $review->user->id,
                            $review->user->first_name,
                            $review->user->last_name,
                            $review->user->patronymic,
                            $review->user->avatar
                        ],
                        "location" => $review->location->name,
                        "emotion" => $review->emotion
                    ];
                })
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
                "message" => "Validation failed",
                "errors" => $e->errors()
            ], 422);
        }

    }

    public function getFeedback(Request $request, string $id): JsonResponse
    {
        $user = $request->user("sanctum");

        $review = Review::where("id", $id)->first();

        if (!$review) {
            return response()->json([
                "message" => "Resource not found"
            ], 404);
        }

        if ($review->user_id !== $user->id && $review->status !== "APPROVED") {
            return response()->json([
                "message" => "No rights? :("
            ], 403);
        }

        $review = Review::query()
            ->where("id", $id)
            ->with("user:id,first_name,last_name,patronymic,avatar")
            ->with("location:id,name,longitude,latitude")
            ->first();

        return response()->json([
            "data" => [
                "id" => $review->id,
                "user" => [
                    "id" => $review->user->id,
                    "first_name" => $review->user->first_name,
                    "last_name" => $review->user->last_name,
                    "patronymic" => $review->user->patronymic,
                    "avatar" => $review->user->avatar
                ],
                "location" => [
                    "id" => $review->location->id,
                    "name" => $review->location->name,
                    "longitude" => $review->location->longitude,
                    "latitude" => $review->location->latitude
                ],
                "emotion" => $review->emotion,
                "comment" => $review->comment,
                "created_at" => $review->created_at,
                "updated_at" => $review->updated_at

            ]
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user("sanctum");

        $review = Review::where("id", $id)->first();

        if (!$review) {
            return response()->json([
                "message" => "Resource not found"
            ], 404);
        }

        if ($review->user_id !== $user->id) {
            return response()->json([
                "message" => "No rights? :("
            ], 403);
        }

        try {
            $validated = $request->validate([
                "emotion" => [
                    Rule::in([
                        "HAPPY",
                        "SAD",
                        "ANGRY"
                    ]),
                    "nullable",
                    "string"
                ],
                "comment" => "nullable|string"
            ]);

            $review->update($validated);

            $review = Review::query()
                ->where("id", $id)
                ->with("user:id,first_name,last_name,patronymic,avatar")
                ->with("location:id,name,longitude,latitude")
                ->first();

            return response()->json([
                "message" => "Successfully updated feedback",
                "data" => [
                    "id" => $review->id,
                    "user" => [
                        "id" => $review->user->id,
                        "first_name" => $review->user->first_name,
                        "last_name" => $review->user->last_name,
                        "patronymic" => $review->user->patronymic,
                        "avatar" => $review->user->avatar
                    ],
                    "location" => [
                        "id" => $review->location->id,
                        "name" => $review->location->name,
                        "longitude" => $review->location->longitude,
                        "latitude" => $review->location->latitude
                    ],
                    "emotion" => $review->emotion,
                    "comment" => $review->comment,
                    "created_at" => $review->created_at,
                    "updated_at" => $review->updated_at

                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                "message" => "Validation failed",
                "errors" => $e->errors()
            ], 422);
        }
    }

    public function created(Request $request): JsonResponse
    {

        $reviews = Review::query()
            ->where("status", "CREATED")
            ->with("user:id,first_name,last_name,patronymic,avatar")
            ->with("location:id,name")
            ->get();

        return response()->json([
            "data" => [
                $reviews->map(function ($review) {
                    return [
                        "id" => $review->id,
                        "user" => [
                            "id" => $review->user->id,
                            "first_name" => $review->first_name,
                            "patronymic" => $review->user->patronymic,
                            "avatar" => $review->user->patronymic
                        ],
                        "location" => $review->location->name,
                        "comment" => $review->comment,
                        "emotion" => $review->emotion,
                        "status" => $review->status
                    ];
                })
            ]
        ]);
    }

    public function status(Request $request, string $id): JsonResponse
    {
        try {

            $review = Review::where("id", $id)->first();

            if (!$review) {
                return response()->json([
                    "message" => "Resource not found"
                ], 404);
            }

            $validated = $request->validate([
                "status" => [
                    "required",
                    Rule::in([
                        "APPROVED",
                        "DECLINED"
                    ])
                ]
            ]);

            $review->update($validated);

            $review = Review::query()
                ->where("id", $id)
                ->with("user:id,first_name,last_name,patronymic,avatar")
                ->with("location:id,name,longitude,latitude")
                ->first();

            return response()->json([
                "message" => "Successfully updated feedback",
                "data" => [
                    "id" => $review->id,
                    "user" => [
                        "id" => $review->user->id,
                        "first_name" => $review->user->first_name,
                        "last_name" => $review->user->last_name,
                        "patronymic" => $review->user->patronymic,
                        "avatar" => $review->user->avatar
                    ],
                    "location" => [
                        "id" => $review->location->id,
                        "name" => $review->location->name,
                        "longitude" => $review->location->longitude,
                        "latitude" => $review->location->latitude
                    ],
                    "emotion" => $review->emotion,
                    "comment" => $review->comment,
                    "created_at" => $review->created_at,
                    "updated_at" => $review->updated_at

                ]
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
        //
    }
}
