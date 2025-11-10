<?php

namespace App\Exports\Reportes;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReporteVentasExport implements WithMultipleSheets
{
    protected Collection $porDia;
    protected Collection $porUsuario;
    protected Collection $porMetodo;
    protected string $start;
    protected string $end;

    public function __construct(Collection $porDia, Collection $porUsuario, Collection $porMetodo, string $start, string $end)
    {
        $this->porDia     = $porDia;
        $this->porUsuario = $porUsuario;
        $this->porMetodo  = $porMetodo;
        $this->start      = $start;
        $this->end        = $end;
    }

    public function sheets(): array
    {
        return [
            new PorDiaSheet($this->porDia, $this->start, $this->end),
            new PorUsuarioSheet($this->porUsuario),
            new PorMetodoSheet($this->porMetodo),
        ];
    }
}

class PorDiaSheet implements FromCollection, WithHeadings, WithTitle
{
    protected Collection $data;
    protected string $start;
    protected string $end;

    public function __construct(Collection $data, string $start, string $end)
    {
        $this->data  = $data;
        $this->start = $start;
        $this->end   = $end;
    }

    public function title(): string
    {
        return 'Por día';
    }

    public function headings(): array
    {
        return [
            ["REPORTE GENERAL DE VENTAS"],
            ["Rango", "{$this->start} a {$this->end}"],
            [],
            ["Fecha", "# Ventas", "Total", "Efectivo", "Tarjeta", "Transferencia", "Crédito"],
        ];
    }

    public function collection()
    {
        $rows = $this->data->map(function ($r) {
            return [
                $r->fecha,
                (int) ($r->ventas ?? 0),
                round($r->total ?? 0, 2),
                round($r->efectivo ?? 0, 2),
                round($r->tarjeta ?? 0, 2),
                round($r->transferencia ?? 0, 2),
                round($r->credito ?? 0, 2),
            ];
        });

        $rows->push([
            'TOTAL',
            (int) $this->data->sum('ventas'),
            round($this->data->sum('total'), 2),
            round($this->data->sum('efectivo'), 2),
            round($this->data->sum('tarjeta'), 2),
            round($this->data->sum('transferencia'), 2),
            round($this->data->sum('credito'), 2),
        ]);

        return $rows;
    }
}

class PorUsuarioSheet implements FromCollection, WithHeadings, WithTitle
{
    protected Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Por usuario';
    }

    public function headings(): array
    {
        return [
            ["Usuario", "# Ventas", "Total", "Efectivo", "Tarjeta", "Transferencia", "Crédito"],
        ];
    }

    public function collection()
    {
        $rows = $this->data->map(function ($r) {
            return [
                $r->usuario,
                (int) ($r->ventas ?? 0),
                round($r->total ?? 0, 2),
                round($r->efectivo ?? 0, 2),
                round($r->tarjeta ?? 0, 2),
                round($r->transferencia ?? 0, 2),
                round($r->credito ?? 0, 2),
            ];
        });

        $rows->push([
            'TOTAL',
            (int) $this->data->sum('ventas'),
            round($this->data->sum('total'), 2),
            round($this->data->sum('efectivo'), 2),
            round($this->data->sum('tarjeta'), 2),
            round($this->data->sum('transferencia'), 2),
            round($this->data->sum('credito'), 2),
        ]);

        return $rows;
    }
}

class PorMetodoSheet implements FromCollection, WithHeadings, WithTitle
{
    protected Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Por método';
    }

    public function headings(): array
    {
        return [
            ["Método", "# Ventas", "Total"],
        ];
    }

    public function collection()
    {
        $rows = $this->data->map(function ($r) {
            return [
                match ($r->metodo) {
                    'cash' => 'Efectivo',
                    'card' => 'Tarjeta',
                    'transfer' => 'Transferencia',
                    'credit' => 'Crédito',
                    default => (string) $r->metodo,
                },
                (int) ($r->ventas ?? 0),
                round($r->total ?? 0, 2),
            ];
        });

        $rows->push([
            'TOTAL',
            (int) $this->data->sum('ventas'),
            round($this->data->sum('total'), 2),
        ]);

        return $rows;
    }
}
