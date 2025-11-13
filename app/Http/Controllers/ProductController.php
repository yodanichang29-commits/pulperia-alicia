<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


    class ProductController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required','string','max:150'],
            'barcode'        => ['nullable','string','max:100'],
            'price'          => ['required','numeric','min:0'],
            'cost'           => ['nullable','numeric','min:0'],
            'category'       => ['nullable','string','max:100'],
            'stock'          => ['required','integer','min:0'],
            'min_stock'      => ['required','integer','min:0'],
            'active'         => ['sometimes','boolean'],
            'image'          => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $data['active'] = $request->boolean('active');

        // 📸 guardar imagen si viene
        if ($request->hasFile('image')) {
            // guarda en storage/app/public/products
            $path = $request->file('image')->store('products', 'public');
            $data['photo'] = $path;
        }

        $product = Product::create($data);

        return redirect()->route('inventario.index')
            ->with('ok','Producto creado correctamente.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'           => ['required','string','max:150'],
            'barcode'        => ['nullable','string','max:100'],
            'price'          => ['required','numeric','min:0'],
            'cost'           => ['nullable','numeric','min:0'],
            'category'       => ['nullable','string','max:100'],
            'stock'          => ['required','integer','min:0'],
            'min_stock'      => ['required','integer','min:0'],
            'active'         => ['sometimes','boolean'],
            'image'          => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'remove_image'   => ['sometimes','boolean'],
        ]);

        $data['active'] = $request->boolean('active');

        // 🗑️ quitar imagen
        if ($request->boolean('remove_image') && $product->photo) {
            Storage::disk('public')->delete($product->photo);
            $data['photo'] = null;
        }

        // 📸 nueva imagen
        if ($request->hasFile('image')) {
            if ($product->photo) {
                Storage::disk('public')->delete($product->photo);
            }
            $path = $request->file('image')->store('products', 'public');
            $data['photo'] = $path;
        }

        $product->update($data);

        return redirect()->route('inventario.index')
            ->with('ok','Producto actualizado.');
    }





public function create()
{
    $product = new Product(['active' => true, 'stock' => 0, 'min_stock' => 0]);
    return view('inventario.productos.create', compact('product'));
}

public function edit(Product $product)
{
    return view('inventario.productos.edit', compact('product'));
}



/**
 * Buscar productos por nombre (autocomplete)
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function buscar(Request $request)
{
    $term = $request->get('q');
    $results = \App\Models\Product::where('name', 'like', "%{$term}%")
        ->select('id', 'name', 'barcode', 'cost', 'price', 'stock', 'category')
        ->orderBy('name')
        ->take(10)
        ->get();

    return response()->json($results);
}



}
