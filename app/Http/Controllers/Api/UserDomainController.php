<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserDomainController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'domains' => 'required|array'
        ]);

        $user = auth('api')->user();

        $user->domains()->sync($request->domains);

        return response()->json([
            'message' => 'Centres d’intérêt enregistrés'
        ]);
    }
}