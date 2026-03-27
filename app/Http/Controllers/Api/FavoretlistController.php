<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FavoriteService;

class FavoretlistController  extends Controller
{
    protected $FavoretlistService;

    public function __construct(FavoriteService $FavoretlistService)
    {
        $this->FavoretlistService = $FavoretlistService;
    }

    // ajouter favori
    public function store($courseId)
    {
        $this->FavoretlistService->add($courseId,auth('api')->user());

        return response()->json([
            'message' => 'Cours ajouté aux favoris'
        ]);
    }

    // voir favoris
    public function index()
    {
        return response()->json(
            $this->FavoretlistService->myFavoretlist(auth('api')->user())
        );
    }

    // supprimer favori
    public function destroy($courseId)
    {
        $this->FavoretlistService->remove(
            $courseId,
            auth('api')->user()
        );

        return response()->json([
            'message' => 'Cours retiré des favoris'
        ]);
    }
}