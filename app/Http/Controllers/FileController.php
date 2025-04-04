<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Project;
use App\Models\File;

class FileController extends Controller
{
    /**
     * Upload simples de arquivo vinculado a um projeto.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_token' => 'required|string',
            'file' => 'required|file|max:3072', // 3MB
            'title' => 'nullable|string',
            'external_reference' => 'nullable|string',
        ]);

        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $title = $request->input('title') ?? 'arquivo';
        $slugTitle = Str::slug($title);

        $nextId = (File::max('id') ?? 0) + 1;
        $filename = "{$slugTitle}-{$nextId}.{$extension}";
        $path = "files/{$filename}";

        Storage::disk('public')->putFileAs('files', $file, $filename);

        $fileModel = new File();
        $fileModel->title = $title;
        $fileModel->path = $path;
        $fileModel->external_reference = $request->input('external_reference');
        $fileModel->project_id = $project->id;
        $fileModel->save();

        return response()->json($fileModel, 201);
    }

    /**
     * Deletar um arquivo.
     */
    public function destroy(Request $request, $id)
    {
        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        $file = File::where('id', $id)
                   ->where('project_id', $project->id)
                   ->first();

        if (!$file) {
            return response()->json(['error' => 'Arquivo não encontrado'], 404);
        }

        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        $file->delete();

        return response()->json(['message' => 'Arquivo deletado com sucesso']);
    }

    /**
     * Listar arquivos de um projeto, com filtro opcional.
     */
    public function index(Request $request)
    {
        $project = Project::where('token', $request->input('project_token'))->first();

        if (!$project) {
            return response()->json(['error' => 'Projeto não encontrado'], 404);
        }

        $query = File::where('project_id', $project->id);

        if ($request->has('external_reference')) {
            $query->where('external_reference', $request->input('external_reference'));
        }

        return response()->json($query->get());
    }
}

