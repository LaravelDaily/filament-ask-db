<?php

namespace App\Http\Controllers;

use App\Models\Lead;

class DownloadCSV extends Controller
{
    public function __invoke()
    {
        abort_unless(app()->environment('local'), 404);

        $leads = Lead::all();

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Added on', 'Lead Name', 'Sales Rep Name', 'Is Closed']);
            foreach ($leads as $lead) {
                fputcsv($file, [$lead->id, $lead->added_on, $lead->lead_name, $lead->sales_rep_name, $lead->is_closed]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads.csv"',
        ]);
    }
}
