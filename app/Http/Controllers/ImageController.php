<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Image;

class ImageController extends Controller
{
    // Listar todas as imagens
    public function index()
    {
        return Image::all();
    }

    // Criar nova imagem
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'path' => 'required|string',
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        $image = Image::create($validated);

        return response()->json($image, 201);
    }

    // Mostrar imagem específica
    public function show($id)
    {
        $image = Image::findOrFail($id);
        return response()->json($image);
    }

    // Deletar imagem
    public function destroy($id)
    {
        $image = Image::findOrFail($id);
        $image->delete();

        return response()->json(['message' => 'Imagem deletada com sucesso.']);
    }
}
