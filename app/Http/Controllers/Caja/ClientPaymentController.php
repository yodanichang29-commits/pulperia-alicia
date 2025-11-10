<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPayment;
use App\Models\CashShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;

class ClientPaymentController extends Controller
{
    // === Ajusta SOLO esta función si tu columna es distinta ===
protected function currentShift(): ?CashShift
{
    return CashShift::openForUser(Auth::id())->first();
}

    // POST /caja/clients/{client}/pay
    public function store(Request $request, Client $client)
    {
        $shift = $this->currentShift();
        if (!$shift) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay turno abierto. Abre un turno para cobrar abonos.'
            ], 422);
        }

        $data = $request->validate([
            'amount' => ['required','numeric','min:0.01'],
            'method' => ['required','in:efectivo,tarjeta,transferencia'],
            'notes'  => ['nullable','string','max:255'],
        ]);

        $payment = ClientPayment::create([
            'client_id'     => $client->id,
            'user_id'       => Auth::id(),
            'cash_shift_id' => $shift->id,
            'amount'        => round($data['amount'], 2),
            'method'        => $data['method'],
            'notes'         => $data['notes'] ?? null,
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Abono registrado',
            'payment' => $payment,
        ]);
    }


public function index(Request $request, Client $client)
{
    $limit = (int) $request->get('limit', 5);

    $rows = ClientPayment::where('client_id', $client->id)
        ->orderByDesc('id')
        ->limit($limit)
        ->get(['id','amount','method','notes','created_at','cash_shift_id','user_id']);

    return response()->json([
        'client'   => ['id' => $client->id, 'name' => $client->name],
        'payments' => $rows,
    ]);
}





public function balance(\App\Models\Client $client)
{
    $creditTotal = (float) \App\Models\Sale::where('client_id', $client->id)
        ->where('payment', 'credit')   // en tu BD el valor es 'credit'
        ->sum('total');

    $paymentsTotal = (float) \App\Models\ClientPayment::where('client_id', $client->id)
        ->sum('amount');

    $balance = round($creditTotal - $paymentsTotal, 2);

    return response()->json([
        'client'          => ['id' => $client->id, 'name' => $client->name],
        'credit_total'    => $creditTotal,
        'payments_total'  => $paymentsTotal,
        'balance'         => $balance,
    ]);
}



}

