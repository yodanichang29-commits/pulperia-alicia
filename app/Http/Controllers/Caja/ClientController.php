<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Autocomplete / búsqueda rápida
     * GET /caja/clients?q=texto
     * Devuelve JSON con los primeros 10 resultados por nombre o teléfono
     */
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $clients = Client::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($xx) use ($q) {
                    $xx->where('name', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone']);

        return response()->json($clients);
    }

    /**
     * Crear cliente
     * POST /caja/clients
     * Body (JSON): { "name": "...", "phone": "..." }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $client = Client::create($data);

        return response()->json([
            'ok'     => true,
            'client' => $client,
        ], 201);
    }
}
