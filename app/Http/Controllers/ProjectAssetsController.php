<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Gallery;
use App\Models\Image;

class ProjectAssetsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'project_token' => 'required|string',
            'external_reference' => 'required|string',
        ]);

        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        $externalReference = $request->input('external_reference');

        // Busca a galeria correspondente (se existir)
        $gallery = Gallery::where('project_id', $project->id)
                          ->where('external_reference', $externalReference)
                          ->with('images')
                          ->first();

        // Busca imagens soltas (sem galeria) com o mesmo external_reference
        $imagesSoltas = Image::where('project_id', $project->id)
                             ->whereNull('gallery_id')
                             ->where('external_reference', $externalReference)
                             ->get();

        return response()->json([
            'gallery' => $gallery ? [
                'id' => $gallery->id,
                'title' => $gallery->title,
                'external_reference' => $gallery->external_reference,
                'images' => $gallery->images->map(function ($img) {
                    return [
                        'id' => $img->id,
                        'title' => $img->title,
                        'path' => asset('storage/' . $img->path),
                    ];
                }),
            ] : null,

            'images_soltas' => $imagesSoltas->map(function ($img) {
                return [
                    'id' => $img->id,
                    'title' => $img->title,
                    'path' => asset('storage/' . $img->path),
                ];
            }),
        ]);
    }
}
