<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UsersController extends Controller
{
    public function index()
    {
        try {
            return User::all();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }public function store(Request $request)
    {
        try {
            $email = $request->input("email");
    
            $existingUser = User::where('email', $email)->first();
    
            if ($existingUser) {
                return response()->json(['error' => 'Email already exists'], 400);
            }
    
            $user = User::create([
                "name" => $request->input("name"),
                "email" => $email,
                "password" => $request->input("password")
            ]);
    
            return response()->json(['user' => $user, 'message' => 'User created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, User $user)
    {
        try {
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = $request->input('password');

            $user->save();
            return $user;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function remove(User $user)
    {
        try {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}