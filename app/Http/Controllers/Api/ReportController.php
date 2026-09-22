<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function filteredQuery(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        return Transaction::with(['product:id,name', 'batch:id,number'])
            ->whereHas('product', fn ($q) => $q->where('owner_id', $request->user()->workspaceOwnerId()))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at');
    }

    public function index(Request $request)
    {
        $rows = $this->filteredQuery($request)->get();

        $totalIn = (int) $rows->where('type', 'in')->sum('quantity');
        $totalOut = (int) $rows->where('type', 'out')->sum('quantity');

        return response()->json([
            'summary' => [
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'net' => $totalIn - $totalOut,
                'count' => $rows->count(),
            ],
            'rows' => $rows->map->toPublicArray(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->filteredQuery($request)->get();
        $from = $request->query('from', 'awal');
        $to = $request->query('to', 'sekarang');

        $filename = "laporan-storify-farm_{$from}_sampai_{$to}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Jenis', 'Produk', 'Batch', 'Jumlah (kg)', 'Lokasi', 'Catatan']);

            foreach ($rows as $t) {
                fputcsv($out, [
                    optional($t->created_at)->format('Y-m-d'),
                    $t->type === 'in' ? 'Masuk' : 'Keluar',
                    $t->product?->name ?? '-',
                    $t->batch?->number ?? '-',
                    $t->quantity,
                    $t->location ?? '-',
                    $t->notes ?? '-',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
