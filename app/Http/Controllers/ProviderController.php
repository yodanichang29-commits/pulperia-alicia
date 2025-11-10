<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $providers = Provider::query()
        
            ->when($q, fn($query) =>
                $query->where('name','like',"%{$q}%")
                      ->orWhere('contact_name','like',"%{$q}%")
                      ->orWhere('phone','like',"%{$q}%")
                      ->orWhere('email','like',"%{$q}%")
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();


            if ($request->ajax()) {
    return view('proveedores.partials.tabla', compact('providers'))->render();
}


        return view('proveedores.index', compact('providers','q'));
    }

    public function create()
    {
        $provider = new Provider(['active' => true]);
        return view('proveedores.create', compact('provider'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255',
            'notes'        => 'nullable|string',
            'active'       => 'sometimes|boolean',
        ]);

        $data['active'] = $request->boolean('active', true);

        Provider::create($data);

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor creado.');
    }

    public function edit(Provider $proveedore) // nombre del parámetro coincide con la ruta resource
    {
        $provider = $proveedore;
        return view('proveedores.edit', compact('provider'));
    }

    public function update(Request $request, Provider $proveedore)
    {
        $provider = $proveedore;

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255',
            'notes'        => 'nullable|string',
            'active'       => 'sometimes|boolean',
        ]);

        $data['active'] = $request->boolean('active', true);

        $provider->update($data);

        return redirect()->route('proveedores.index')->with('ok', 'Proveedor actualizado.');
    }

    public function destroy(Provider $proveedore)
    {
        $proveedore->delete();
        return redirect()->route('proveedores.index')->with('ok', 'Proveedor eliminado.');
    }


public function buscar(\Illuminate\Http\Request $request)
{
    $q = $request->get('q', '');
    $items = \App\Models\Provider::query()
        ->when($q, fn($w) => $w->where('name', 'like', "%{$q}%"))
        ->orderBy('name')
        ->limit(10)
        ->get(['id','name']);

    return response()->json($items);
}


}
