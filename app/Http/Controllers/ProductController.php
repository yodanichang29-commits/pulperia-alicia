<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\Category;
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
          'stock' => ['required_unless:is_package,1', 'integer', 'min:0'],

            'min_stock'      => ['required','integer','min:0'],
            'expires_at'     => ['nullable','date'],
            'active'         => ['sometimes','boolean'],
            'image'          => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'category_id'    => ['nullable','integer','exists:categories,id'],
            // 👈 NUEVOS CAMPOS PARA PAQUETES
            'is_package'         => ['sometimes','boolean'],
            'parent_product_id'  => ['nullable','integer','exists:products,id'],
            'units_per_package'  => ['nullable','integer','min:1'],
        ]);

        $data['active'] = $request->boolean('active');

        // 👈 NUEVA LÓGICA PARA PAQUETES
        $data['is_package'] = $request->boolean('is_package');
        
        if ($data['is_package']) {
            // Si es paquete, validar que tenga parent_product_id y units_per_package
            if (empty($data['parent_product_id']) || empty($data['units_per_package'])) {
                return back()->withErrors([
                    'is_package' => 'Si marcas como paquete, debes seleccionar el producto individual y la cantidad de unidades.'
                ])->withInput();
            }
            // Los paquetes no tienen stock propio, se calcula del producto padre
            $data['stock'] = 0;
        } else {
            // Si no es paquete, limpiar estos campos
            $data['parent_product_id'] = null;
            $data['units_per_package'] = null;
        }

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
         'stock' => ['required_unless:is_package,1', 'integer', 'min:0'],

            'min_stock'      => ['required','integer','min:0'],
            'expires_at'     => ['nullable','date'],
            'active'         => ['sometimes','boolean'],
            'image'          => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'remove_image'   => ['sometimes','boolean'],
            'category_id'    => ['nullable','integer','exists:categories,id'],
            // 👈 NUEVOS CAMPOS PARA PAQUETES
            'is_package'         => ['sometimes','boolean'],
            'parent_product_id'  => ['nullable','integer','exists:products,id'],
            'units_per_package'  => ['nullable','integer','min:1'],
        ]);

        $data['active'] = $request->boolean('active');

        // 👈 NUEVA LÓGICA PARA PAQUETES
        $data['is_package'] = $request->boolean('is_package');
        
        if ($data['is_package']) {
            // Si es paquete, validar que tenga parent_product_id y units_per_package
            if (empty($data['parent_product_id']) || empty($data['units_per_package'])) {
                return back()->withErrors([
                    'is_package' => 'Si marcas como paquete, debes seleccionar el producto individual y la cantidad de unidades.'
                ])->withInput();
            }
            // Los paquetes no tienen stock propio, se calcula del producto padre
            $data['stock'] = 0;
        } else {
            // Si no es paquete, limpiar estos campos
            $data['parent_product_id'] = null;
            $data['units_per_package'] = null;
        }

        // 🗑️ quitar imagen
        if ($request->boolean('remove_image') && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $data['photo'] = null;
        }

        // 📸 nueva imagen
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
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
    $categories = Category::orderBy('name')->get(['id','name']);
    
    // 👈 NUEVO: Traer todos los productos que NO son paquetes (para el selector)
    $products = Product::where('is_package', 0)
        ->where('active', 1)
        ->orderBy('name')
        ->get(['id', 'name']);
    
    return view('inventario.productos.create', compact('product','providers', 'categories', 'products'));
}

public function edit(Product $product)
{
    $providers = Provider::orderBy('name')->get(['id','name']);
    $categories = Category::orderBy('name')->get(['id','name']);
    
    // 👈 NUEVO: Traer todos los productos que NO son paquetes (para el selector)
    // Excluimos el producto actual para que no se pueda vincular a sí mismo
    $products = Product::where('is_package', 0)
        ->where('active', 1)
        ->where('id', '!=', $product->id)
        ->orderBy('name')
        ->get(['id', 'name']);
    
    return view('inventario.productos.edit', compact('product','providers', 'categories', 'products'));
}



public function buscar(Request $request)
{
    $term = $request->get('q');

    $results = Product::query()
        ->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%");
        })
        ->where('active', 1)
        ->select('id', 'name', 'barcode', 'is_package', 'price', 'stock')
        ->orderBy('name')
        ->take(10)
        ->get();

    return response()->json($results);
}




}