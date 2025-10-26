<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function register(Request $request): JsonResponse
    {

        try {

            $validated = $request->validate([
                "first_name" => [
                    "required",
                    "string",
                    "regex:/^[А-Я][а-я]*$/u"
                ],
                "last_name" => [
                    "required",
                    "string",
                    "regex:/^[А-Я][а-я]*$/u"
                ],
                "patronymic" => [
                    "required",
                    "string",
                    "regex:/^[А-Я][а-я]*$/u"
                ],
                "avatar" => "required|image|mimes:png,jpg,jpeg|max:4096",
                "email" => "required|email",
                "password" => [
                    "string",
                    "required",
                    "regex:/[A-Z]/",
                    "regex:/[a-z]/",
                    "regex:/[0-9]/"
                ]
            ]);

            if ($request->hasFile("avatar")) {
                $path = $request->file("avatar")->store("avatars", "public");
                $validated["avatar"] = $path;
            }

            $validated["password"] = Hash::make($validated["password"]);

            $user = User::create($validated);

            return response()->json([
                "message" => "Successfully registered!",
                "data" => $user->only([
                    "id",
                    "first_name",
                    "last_name",
                    "patronymic",
                    "avatar"
                ])
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                "message" => "Validation failed",
                "errors" => $e->errors()
            ], 422);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                "email" => "required|email",
                "password" => [
                    "string",
                    "required",
                ]
            ]);

            if (!Auth::attempt($validated)) {
                return response()->json([
                    "message" => "Login failed"
                ], 401);
            }

            $user = Auth::user($validated);
            $token = $user->createToken("auth-token")->plainTextToken;

            return response()->json([
                "message" => "Successfully logged in!",
                "data" => [
                    "profile" => $user->only([
                        "id",
                        "first_name",
                        "last_name",
                        "patronymic",
                        "avatar"
                    ]),
                    "credentials" => $token
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                "message" => "Validation failed",
                "errors" => $e->errors()
            ], 422);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user("sanctum");

        $user->tokens()->delete();

        return response()->json([
            "message" => "Successfully logged out!"
        ]);

    }

    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
