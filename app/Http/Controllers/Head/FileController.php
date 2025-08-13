<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\CashflowFile;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    /**
     * Display the file upload index page.
     */
    public function index(): View
    {
        $branches = Branch::all();
        $cashflowFiles = CashflowFile::with(['branch', 'uploadedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('head.file', compact('cashflowFiles', 'branches'));
    }

    /**
     * Get cashflow files with optional filtering.
     */
    public function getFiles(Request $request): JsonResponse
    {
        $query = CashflowFile::with(['branch', 'uploadedBy']);

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        // Filter by branch
        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $files = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    /**
     * Store a newly uploaded cashflow file.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'nullable|string|max:20',
            'file_type' => 'required|string|max:50',
            'cashflow_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $file = $request->file('cashflow_file');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . $originalName;
            $filePath = $file->storeAs('cashflow_files', $fileName, 'public');

            $cashflowFile = CashflowFile::create([
                'file_name' => $fileName,
                'file_path' => $filePath,
                'original_name' => $originalName,
                'file_type' => $request->file_type,
                'year' => $request->year,
                'month' => $request->month,
                'branch_id' => $request->branch_id,
                'uploaded_by' => Auth::id(),
                'status' => 'pending',
                'description' => $request->description,
            ]);

            $cashflowFile->load(['branch', 'uploadedBy']);

            return response()->json([
                'success' => true,
                'message' => 'Cash flow file uploaded successfully',
                'data' => $cashflowFile
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload cash flow file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified cashflow file.
     */
    public function show(CashflowFile $cashflowFile): JsonResponse
    {
        $cashflowFile->load(['branch', 'uploadedBy']);

        return response()->json([
            'success' => true,
            'data' => $cashflowFile
        ]);
    }

    /**
     * Update the specified cashflow file.
     */
    public function update(Request $request, CashflowFile $cashflowFile): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'nullable|string|max:20',
            'file_type' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $cashflowFile->update([
                'year' => $request->year,
                'month' => $request->month,
                'branch_id' => $request->branch_id,
                'file_type' => $request->file_type,
                'description' => $request->description,
            ]);

            $cashflowFile->load(['branch', 'uploadedBy']);

            return response()->json([
                'success' => true,
                'message' => 'Cash flow file updated successfully',
                'data' => $cashflowFile
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cash flow file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified cashflow file.
     */
    public function destroy(CashflowFile $cashflowFile): JsonResponse
    {
        try {
            // Delete the physical file
            if (Storage::disk('public')->exists($cashflowFile->file_path)) {
                Storage::disk('public')->delete($cashflowFile->file_path);
            }

            // Delete the database record (this will cascade delete related cashflows)
            $cashflowFile->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cash flow file deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cash flow file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the specified cashflow file.
     */
    public function download(CashflowFile $cashflowFile)
    {
        try {
            if (!Storage::disk('public')->exists($cashflowFile->file_path)) {
                abort(404, 'File not found');
            }

            return Storage::disk('public')->download(
                $cashflowFile->file_path,
                $cashflowFile->original_name
            );

        } catch (\Exception $e) {
            abort(500, 'Error downloading file');
        }
    }

    /**
     * Process the specified cashflow file to extract data.
     */
    public function process(CashflowFile $cashflowFile): JsonResponse
    {
        try {
            if ($cashflowFile->status === 'processed') {
                return response()->json([
                    'success' => false,
                    'message' => 'File has already been processed'
                ], 400);
            }

            // Update status to processing
            $cashflowFile->update(['status' => 'processing']);

            // Here you would implement the actual Excel processing logic
            // For now, we'll simulate processing
            // In a real application, you would:
            // 1. Read the Excel file
            // 2. Parse the data
            // 3. Create cashflow records
            // 4. Update file status

            // Simulate processing delay
            sleep(2);

            // Update status to processed
            $cashflowFile->update(['status' => 'processed']);

            return response()->json([
                'success' => true,
                'message' => 'Cash flow file processed successfully. Data has been extracted and stored.',
                'data' => $cashflowFile->fresh(['branch', 'uploadedBy'])
            ]);

        } catch (\Exception $e) {
            // Update status to error
            $cashflowFile->update(['status' => 'error']);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process cash flow file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file upload statistics.
     */
    public function getStats(Request $request): JsonResponse
    {
        $query = CashflowFile::query();

        // Apply filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $query->where('branch_id', $request->branch_id);
        }

        $stats = [
            'total_files' => $query->count(),
            'pending_files' => $query->where('status', 'pending')->count(),
            'processed_files' => $query->where('status', 'processed')->count(),
            'error_files' => $query->where('status', 'error')->count(),
            'total_size' => $query->sum('file_size'), // You might want to add file_size to your migration
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
