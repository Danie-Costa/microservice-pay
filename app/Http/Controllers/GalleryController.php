<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Models\Project;

class GalleryController extends Controller
{
    /**
     * Criar nova galeria.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_token' => 'required|string',
            'title' => 'nullable|string',
            'external_reference' => 'nullable|string',
        ]);

        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        $gallery = new Gallery();
        $gallery->title = $request->input('title');
        $gallery->external_reference = $request->input('external_reference');
        $gallery->project_id = $project->id;
        $gallery->save();

        return response()->json($gallery, 201);
    }

    /**
     * Listar galerias por projeto + filtros opcionais.
     */
    public function index(Request $request)
    {
        $request->validate([
            'project_token' => 'required|string',
        ]);

        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        $query = Gallery::where('project_id', $project->id)->with('images');

        if ($request->has('external_reference')) {
            $query->where('external_reference', $request->input('external_reference'));
        }

        return response()->json($query->get());
    }

    /**
     * Deletar galeria por ID + token.
     */
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'project_token' => 'required|string',
        ]);

        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        $gallery = Gallery::where('id', $id)
                          ->where('project_id', $project->id)
                          ->first();

        if (!$gallery) {
            return response()->json(['error' => 'Galeria não encontrada'], 404);
        }

        $gallery->delete();

        return response()->json(['message' => 'Galeria deletada com sucesso']);
    }
}
