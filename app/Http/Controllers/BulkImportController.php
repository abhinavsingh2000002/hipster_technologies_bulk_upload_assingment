<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessProductCsv;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\DataTables;

class BulkImportController extends Controller
{

    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:102400',
        ]);

        $file = $request->file('csv_file');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('uploads'), $filename);
        $fullPath = public_path('uploads/'.$filename);

        $cacheKey = 'csv_progress_'.time();

        // Dispatch job to queue
        ProcessProductCsv::dispatch($fullPath, $cacheKey);
        exec('php '.base_path('artisan').' queue:work --tries=3 --timeout=0 > /dev/null 2>&1 &');

        return response()->json(['cacheKey' => $cacheKey]);
    }

    public function csvProgress($key)
    {
        $summary = Cache::get($key.'_summary', null);
        $processed = Cache::get($key.'_processed', 0);
        $totalRows = Cache::get($key.'_total', 1);

        $percent = min(100, ($processed / $totalRows) * 100);

        return response()->json([
            'percent' => round($percent),
            'summary' => $summary,
            'processed' => $processed,
            'total' => $totalRows,
        ]);
    }

    public function getProductsData()
    {
        $query = Product::query();

        return DataTables::of($query)
            ->editColumn('created_at', function ($p) {
                return $p->created_at->format('Y-m-d H:i');
            })
            ->make(true);
        dd($data
        );
    }
}
