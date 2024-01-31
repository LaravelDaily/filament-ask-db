<?php

namespace App\Filament\Imports;

use App\Models\Lead;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class LeadImporter extends Importer
{
    protected static ?string $model = Lead::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('added_on')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('lead_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('sales_rep_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('is_closed')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): ?Lead
    {
        // return Lead::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Lead();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your lead import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
