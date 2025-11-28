<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Provider;
use Illuminate\Support\Facades\Storage;


    class ProductController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required','string','max:150'],
            'barcode'        => ['nullable','string','max:100'],
            'price'          => ['required','numeric','min:0'],
            'purchase_price' => ['nullable','numeric','min:0'],
           
            'provider_id'    => ['nullable','integer','exists:providers,id'],
                    'category_id'    => ['nullable','integer','exists:categories,id'], // ← AGREGAR ESTO
            'stock'          => ['required','integer','min:0'],
            'min_stock'      => ['required','integer','min:0'],
            'expires_at'     => ['nullable','date'],
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
            'purchase_price' => ['nullable','numeric','min:0'],
            'provider_id'    => ['nullable','integer','exists:providers,id'],
               'category_id'    => ['nullable','integer','exists:categories,id'], // ← AGREGAR ESTO
            'stock'          => ['required','integer','min:0'],
            'min_stock'      => ['required','integer','min:0'],
            'expires_at'     => ['nullable','date'],
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
    $product   = new Product(['active' => true, 'stock' => 0, 'min_stock' => 0]);
    $providers = Provider::orderBy('name')->get(['id','name']);
    $categories = \App\Models\Category::active()->ordered()->get(['id','name']); // ← AGREGAR ESTO
    return view('inventario.productos.create', compact('product','providers','categories')); // ← MODIFICAR
}

public function edit(Product $product)
{
    $providers = Provider::orderBy('name')->get(['id','name']);
    $categories = \App\Models\Category::active()->ordered()->get(['id','name']); // ← AGREGAR ESTO
    return view('inventario.productos.edit', compact('product','providers','categories')); // ← MODIFICAR
}



/**
 * Buscar productos por nombre o código de barras (autocomplete)
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function buscar(Request $request)
{
    $term = $request->get('q');

    if (!$term || strlen($term) < 1) {
        return response()->json([]);
    }

    $results = \App\Models\Product::where(function($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('barcode', 'like', "%{$term}%");
        })
        ->select('id', 'name', 'barcode as codigo', 'price', 'purchase_price', 'stock')
        ->orderBy('name')
        ->take(10)
        ->get();

    return response()->json($results);
}



}
