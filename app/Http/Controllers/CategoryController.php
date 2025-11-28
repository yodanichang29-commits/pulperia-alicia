<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::ordered()->paginate(20);
        return view('categorias.index', compact('categories'));
    }

    public function create()
    {
        $category = new Category(['active' => true, 'order' => 0]);
        return view('categorias.create', compact('category'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'active' => 'sometimes|boolean'
        ]);

        $data['active'] = $request->boolean('active', true);

        Category::create($data);

        return redirect()->route('categorias.index')
            ->with('ok', 'Categoría creada correctamente.');
    }

    public function edit(Category $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Category $categoria)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $categoria->id,
            'description' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'active' => 'sometimes|boolean'
        ]);

        $data['active'] = $request->boolean('active');

        $categoria->update($data);

        return redirect()->route('categorias.index')
            ->with('ok', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $categoria)
    {
        // Verificar si tiene productos asignados
        if ($categoria->products()->count() > 0) {
            return back()->with('error', 'No se puede eliminar esta categoría porque tiene productos asignados.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('ok', 'Categoría eliminada correctamente.');
    }
}