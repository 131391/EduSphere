<?php

namespace App\Traits;

use Illuminate\Support\Facades\Response;

/**
 * Exportable Trait
 * 
 * Provides export functionality for models
 */
trait Exportable
{
    /**
     * Export to CSV
     * 
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @param array $headers Column headers
     * @param array $columns Columns to export
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToCsv($data, array $headers, array $columns, string $filename = null)
    {
        $filename = $filename ?? $this->getExportFilename('csv');

        $callback = function () use ($data, $headers, $columns) {
            $file = fopen('php://output', 'w');

            // Write headers
            fputcsv($file, $headers);

            // Write data
            foreach ($data as $row) {
                $rowData = [];
                foreach ($columns as $column) {
                    // Handle nested attributes (e.g., 'user.name')
                    if (str_contains($column, '.')) {
                        $value = data_get($row, $column);
                    } else {
                        $value = $row->{$column} ?? '';
                    }

                    // Format dates
                    if ($value instanceof \DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    }

                    $rowData[] = $value;
                }
                fputcsv($file, $rowData);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export to JSON
     * 
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToJson($data, string $filename = null)
    {
        $filename = $filename ?? $this->getExportFilename('json');

        return Response::stream(function () use ($data) {
            echo $data->toJson(JSON_PRETTY_PRINT);
        }, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Get export filename
     * 
     * @param string $extension
     * @return string
     */
    protected function getExportFilename(string $extension): string
    {
        $modelName = strtolower(class_basename($this));
        $timestamp = date('Y-m-d_His');
        return "{$modelName}_export_{$timestamp}.{$extension}";
    }

    /**
     * Get exportable columns
     * Override this in your model to define exportable columns
     * 
     * @return array
     */
    public function getExportableColumns(): array
    {
        return $this->exportable ?? [];
    }
}
