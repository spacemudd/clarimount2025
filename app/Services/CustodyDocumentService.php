<?php

namespace App\Services;

use App\Models\CustodyChange;

class CustodyDocumentService
{
    /**
     * Generate a printable custody document.
     */
    public function generateCustodyDocument(CustodyChange $custodyChange): string
    {
        $employee = $custodyChange->employee;
        $previousAssets = $custodyChange->previous_state['assets'] ?? [];
        $newAssets = $custodyChange->new_state['assets'] ?? [];
        
        // Generate HTML document for printing
        $html = view('documents.custody-change', compact(
            'employee', 
            'previousAssets', 
            'newAssets', 
            'custodyChange'
        ))->render();
        
        return $html;
    }

    /**
     * Generate a comparison of assets between previous and new state.
     */
    public function generateAssetComparison(CustodyChange $custodyChange): array
    {
        $previousAssets = collect($custodyChange->previous_state['assets'] ?? []);
        $newAssets = collect($custodyChange->new_state['assets'] ?? []);

        $previousIds = $previousAssets->pluck('id')->toArray();
        $newIds = $newAssets->pluck('id')->toArray();

        return [
            'removed_assets' => $previousAssets->whereNotIn('id', $newIds)->values()->toArray(),
            'added_assets' => $newAssets->whereNotIn('id', $previousIds)->values()->toArray(),
            'unchanged_assets' => $previousAssets->whereIn('id', $newIds)->values()->toArray(),
            'summary' => [
                'total_previous' => count($previousAssets),
                'total_new' => count($newAssets),
                'removed_count' => count($previousAssets->whereNotIn('id', $newIds)),
                'added_count' => count($newAssets->whereNotIn('id', $previousIds)),
                'unchanged_count' => count($previousAssets->whereIn('id', $newIds)),
            ]
        ];
    }

    /**
     * Get a human-readable summary of the custody change.
     */
    public function getChangesSummary(CustodyChange $custodyChange): string
    {
        if (!empty($custodyChange->changes_summary)) {
            return $custodyChange->changes_summary;
        }

        $comparison = $this->generateAssetComparison($custodyChange);
        $summary = $comparison['summary'];

        $parts = [];

        if ($summary['added_count'] > 0) {
            $parts[] = "Added {$summary['added_count']} asset(s)";
        }

        if ($summary['removed_count'] > 0) {
            $parts[] = "Removed {$summary['removed_count']} asset(s)";
        }

        if (empty($parts)) {
            return "No asset changes made";
        }

        return implode(', ', $parts);
    }

    /**
     * Validate that the custody change is ready for document generation.
     */
    public function validateCustodyChange(CustodyChange $custodyChange): array
    {
        $errors = [];

        if (!$custodyChange->employee) {
            $errors[] = 'Employee not found';
        }

        if (empty($custodyChange->previous_state) || empty($custodyChange->new_state)) {
            $errors[] = 'Incomplete custody state data';
        }

        return $errors;
    }
} 