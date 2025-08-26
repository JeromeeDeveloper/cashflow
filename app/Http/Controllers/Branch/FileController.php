<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\CashflowFile;
use App\Models\Branch;
use App\Imports\CashflowImport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class FileController extends Controller
{
    public function index(): View
    {
        $branch = Auth::user()->branch; // eager loaded in relationships
        $cashflowFiles = CashflowFile::with('branch')
            ->where('branch_id', optional($branch)->id)
            ->orderByDesc('created_at')
            ->get();

        return view('branch.file', [
            'branch' => $branch,
            'cashflowFiles' => $cashflowFiles,
        ]);
    }

    public function getFiles(Request $request): JsonResponse
    {
        $branchId = optional(Auth::user()->branch)->id;

        $query = CashflowFile::with('branch')->where('branch_id', $branchId);

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'year' => 'required|integer|min:2020|max:2035',
            'month' => 'required|string|max:20',
            'period_type' => 'required|in:monthly,weekly',
            'week' => 'nullable|integer|min:1|max:4|required_if:period_type,weekly',
            'description' => 'nullable|string|max:1000',
        ]);

        $branchId = optional(Auth::user()->branch)->id;
        abort_if(!$branchId, 422, 'User is not assigned to any branch');

        // Prevent duplicate uploads for the same branch + year + month + period_type + week
        $query = CashflowFile::where('branch_id', $branchId)
            ->where('year', (int) $request->year)
            ->where('month', $request->month)
            ->where('period_type', $request->period_type);

        if ($request->period_type === 'weekly') {
            $query->where('week', $request->week);
        }

        $alreadyExists = $query->whereIn('status', ['pending', 'processing', 'processed'])->exists();

        if ($alreadyExists) {
            $periodText = $request->period_type === 'weekly' ? "Week {$request->week} of {$request->month} {$request->year}" : "{$request->month} {$request->year}";
            return response()->json([
                'success' => false,
                'message' => "A file has already been uploaded for {$periodText}.",
            ], 422);
        }

        try {
            $file = $request->file('file');
            $fileName = 'cashflow_' . Str::slug($request->month) . '_' . $request->year . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('cashflow_files/' . $branchId, $fileName, 'public');

            $cashflowFile = CashflowFile::create([
                'file_name' => $fileName,
                'file_path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'file_type' => 'cashflow',
                'year' => (int) $request->year,
                'month' => $request->month,
                'period_type' => $request->period_type,
                'week' => $request->period_type === 'weekly' ? $request->week : null,
                'branch_id' => $branchId,
                'uploaded_by' => Auth::user()->name,
                'status' => 'processing',
                'description' => $request->description,
            ]);

            // Auto-process import
            $cashflowFile->update(['status' => 'processing']);
            $absolutePath = Storage::disk('public')->path($filePath);

            Excel::import(
                new CashflowImport($cashflowFile, $cashflowFile->branch_id, $cashflowFile->year, $cashflowFile->month, $cashflowFile->period_type, $cashflowFile->week),
                $absolutePath
            );

            $cashflowFile->update(['status' => 'processed']);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded and processed successfully. Cashflow data has been imported.',
                'data' => $cashflowFile->load('branch'),
            ]);
        } catch (\Exception $e) {
            if (isset($cashflowFile)) {
                $cashflowFile->update([
                    'status' => 'error',
                    'description' => 'Processing failed: ' . $e->getMessage(),
                ]);
            }

            // Check if it's a validation error from CashflowImport
            if (str_contains($e->getMessage(), 'Validation failed!')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'type' => 'validation_error'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'File uploaded but processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(CashflowFile $cashflowFile): JsonResponse
    {
        $this->authorizeFile($cashflowFile);

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
                'branch' => $cashflowFile->branch ? $cashflowFile->branch->name : 'N/A',
                'uploaded_by' => $cashflowFile->uploaded_by,
                'status' => $cashflowFile->status,
                'description' => $cashflowFile->description,
                'created_at' => $cashflowFile->created_at->format('M d, Y H:i'),
                'updated_at' => $cashflowFile->updated_at->format('M d, Y H:i'),
                'cashflows_count' => $cashflowFile->cashflows->count(),
            ],
        ]);
    }

    public function update(Request $request, CashflowFile $cashflowFile): JsonResponse
    {
        $this->authorizeFile($cashflowFile);

        $cashflowFile->update($request->only(['description']));

        return response()->json([
            'success' => true,
            'message' => 'File updated successfully',
            'data' => $cashflowFile,
        ]);
    }

    public function destroy(CashflowFile $cashflowFile): JsonResponse
    {
        $this->authorizeFile($cashflowFile);

        if (Storage::disk('public')->exists($cashflowFile->file_path)) {
            Storage::disk('public')->delete($cashflowFile->file_path);
        }

        // Delete associated cashflow rows imported from this file
        $cashflowFile->cashflows()->delete();

        // Delete file record
        $cashflowFile->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }

    public function download(CashflowFile $cashflowFile): JsonResponse
    {
        $this->authorizeFile($cashflowFile);

        // Build public URL to the stored file (storage symlink assumed)
        $publicUrl = asset('storage/' . ltrim($cashflowFile->file_path, '/'));

        return response()->json([
            'success' => true,
            'url' => $publicUrl,
        ]);
    }

    public function process(CashflowFile $cashflowFile): JsonResponse
    {
        $this->authorizeFile($cashflowFile);

        try {
            $filePath = Storage::disk('public')->path($cashflowFile->file_path);

            Excel::import(
                new CashflowImport($cashflowFile, $cashflowFile->branch_id, $cashflowFile->year, $cashflowFile->month),
                $filePath
            );

            return response()->json([
                'success' => true,
                'message' => 'File processed successfully. Cashflow data has been imported.',
                'data' => $cashflowFile->load(['branch', 'cashflows']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function authorizeFile(CashflowFile $cashflowFile): void
    {
        $branchId = optional(Auth::user()->branch)->id;
        abort_if($cashflowFile->branch_id !== $branchId, 403);
    }
}


