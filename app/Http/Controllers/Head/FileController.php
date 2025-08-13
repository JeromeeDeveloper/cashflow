<?php

namespace App\Http\Controllers\Head;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashflowFile;
use App\Imports\CashflowImport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display the file upload page.
     */
    public function index(): View
    {
        $branches = Branch::orderBy('name')->get();
        $cashflowFiles = CashflowFile::with(['branch'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('head.file', compact('branches', 'cashflowFiles'));
    }

    /**
     * Get cashflow files with filtering.
     */
    public function getFiles(Request $request): JsonResponse
    {
        $query = CashflowFile::with(['branch']);

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        $files = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    /**
     * Store a newly uploaded file.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
            'branch_id' => 'required|exists:branches,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileName = 'cashflow_' . Str::slug($request->month) . '_' . $request->year . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Store file in branch-specific directory
            $filePath = $file->storeAs('cashflow_files/' . $request->branch_id, $fileName, 'public');

            // Create cashflow file record
            $cashflowFile = CashflowFile::create([
                'file_name' => $fileName,
                'file_path' => $filePath,
                'original_name' => $originalName,
                'file_type' => 'cashflow',
                'year' => $request->year,
                'month' => $request->month,
                'branch_id' => $request->branch_id,
                'uploaded_by' => Auth::user()->name,
                'status' => 'pending',
                'description' => $request->description ?? "Cashflow file for {$request->month} {$request->year}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $cashflowFile->load('branch')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified cashflow file.
     */
    public function show(CashflowFile $cashflowFile): JsonResponse
    {
        $cashflowFile->load(['branch', 'cashflows']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cashflowFile->id,
                'file_name' => $cashflowFile->file_name,
                'original_name' => $cashflowFile->original_name,
                'file_path' => $cashflowFile->file_path,
                'file_type' => $cashflowFile->file_type,
                'year' => $cashflowFile->year,
                'month' => $cashflowFile->month,
                'branch' => $cashflowFile->branch ? $cashflowFile->branch->name : 'All Branches',
                'uploaded_by' => $cashflowFile->uploaded_by,
                'status' => $cashflowFile->status,
                'description' => $cashflowFile->description,
                'created_at' => $cashflowFile->created_at->format('M d, Y H:i'),
                'updated_at' => $cashflowFile->updated_at->format('M d, Y H:i'),
                'cashflows_count' => $cashflowFile->cashflows->count(),
            ]
        ]);
    }

    /**
     * Update the specified file.
     */
    public function update(Request $request, CashflowFile $cashflowFile): JsonResponse
    {
        $request->validate([
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:pending,processed,error',
        ]);

        try {
            $cashflowFile->update([
                'description' => $request->description ?? $cashflowFile->description,
                'status' => $request->status ?? $cashflowFile->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File updated successfully',
                'data' => $cashflowFile->load('branch')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified file.
     */
    public function destroy(CashflowFile $cashflowFile): JsonResponse
    {
        try {
            // Delete physical file
            if (Storage::disk('public')->exists($cashflowFile->file_path)) {
                Storage::disk('public')->delete($cashflowFile->file_path);
            }

            // Delete associated cashflow data
            $cashflowFile->cashflows()->delete();

            // Delete file record
            $cashflowFile->delete();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the specified file.
     */
    public function download(CashflowFile $cashflowFile): JsonResponse
    {
        try {
            if (!Storage::disk('public')->exists($cashflowFile->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // For local storage, return the file path
            $filePath = Storage::disk('public')->path($cashflowFile->file_path);

            return response()->json([
                'success' => true,
                'message' => 'File ready for download',
                'data' => [
                    'file_path' => $filePath,
                    'file_name' => $cashflowFile->original_name,
                    'download_url' => asset('storage/' . $cashflowFile->file_path)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating download URL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process the Excel file and import cashflow data.
     */
    public function process(CashflowFile $cashflowFile): JsonResponse
    {
        try {
            // Check if file exists
            if (!Storage::disk('public')->exists($cashflowFile->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on server'
                ], 404);
            }

            // Check if already processed
            if ($cashflowFile->status === 'processed') {
                return response()->json([
                    'success' => false,
                    'message' => 'File has already been processed'
                ], 422);
            }

            // Update status to processing
            $cashflowFile->update(['status' => 'processing']);

            // Get file path
            $filePath = Storage::disk('public')->path($cashflowFile->file_path);

            // Import Excel data for the specific branch
            Excel::import(
                new CashflowImport($cashflowFile, $cashflowFile->branch_id, $cashflowFile->year, $cashflowFile->month),
                $filePath
            );

            return response()->json([
                'success' => true,
                'message' => 'File processed successfully. Cashflow data has been imported.',
                'data' => $cashflowFile->load(['branch', 'cashflows'])
            ]);

        } catch (\Exception $e) {
            // Update status to error
            $cashflowFile->update([
                'status' => 'error',
                'description' => 'Processing failed: ' . $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upload statistics.
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_files' => CashflowFile::count(),
            'pending_files' => CashflowFile::where('status', 'pending')->count(),
            'processed_files' => CashflowFile::where('status', 'processed')->count(),
            'error_files' => CashflowFile::where('status', 'error')->count(),
            'total_cashflows' => \App\Models\Cashflow::count(),
            'recent_uploads' => CashflowFile::with('branch')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
