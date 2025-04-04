<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
class ImageController extends Controller
{
    /**
     * Listar imagens filtrando por project_token e parâmetros opcionais.
     */
    public function index(Request $request)
    {
        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        $query = Image::where('project_id', $project->id);

        if ($request->has('external_reference')) {
            $query->where('external_reference', $request->input('external_reference'));
        }

        if ($request->has('gallery_id')) {
            $query->where('gallery_id', $request->input('gallery_id'));
        }

        return response()->json($query->get());
    }

    /**
     * Criar uma nova imagem.
     */

    public function store(Request $request)
    {
        $request->validate([
            'project_token' => 'required|string',
            'file' => 'required|file|mimes:jpeg,png,jpg,webp',
            'title' => 'nullable|string',
            'gallery_id' => 'nullable|exists:galleries,id',
            'external_reference' => 'nullable|string',
        ]);

        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        // Pega próximo ID estimado
        $nextId = (Image::max('id') ?? 0) + 1;

        $title = $request->input('title') ?? 'imagem';
        $slugTitle = Str::slug($title);
        $filename = "{$slugTitle}-{$nextId}.webp";

        $file = $request->file('file');
        $mime = $file->getMimeType();

        $path = "images/{$filename}";
        $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

        if (in_array($mime, ['image/jpeg', 'image/png'])) {
            $imageIntervention = $manager->read($file)
                ->resize(1920, null, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                })
                ->toWebp(90);

                Storage::disk('public')->put($path, (string) $imageIntervention);
        } else {
            // Se já for webp, apenas move o arquivo
            Storage::disk('public')->putFileAs('images', $file, $filename);
        }

        $image = new \App\Models\Image();
        $image->title = $title;
        $image->path = $path;
        $image->gallery_id = $request->input('gallery_id');
        $image->external_reference = $request->input('external_reference');
        $image->project_id = $project->id;
        $image->save();

        return response()->json($image, 201);
    }


    /**
     * Deletar uma imagem por ID + token.
     */
    public function destroy(Request $request, $id)
    {
        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        $image = Image::where('id', $id)
                    ->where('project_id', $project->id)
                    ->first();

        if (!$image) {
            return response()->json(['error' => 'Imagem não encontrada'], 404);
        }

        // Remove o arquivo do disco
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        // Remove do banco de dados
        $image->delete();

        return response()->json(['message' => 'Imagem deletada com sucesso']);
    }

}
