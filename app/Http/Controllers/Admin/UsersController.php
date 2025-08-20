<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    /**
     * Display the users management page.
     */
    public function index(): View
    {
        $users = User::with(['branch'])->orderBy('created_at', 'desc')->get();
        $branches = Branch::orderBy('name')->get();

        return view('admin.users', compact('users', 'branches'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,head,branch',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'nullable|in:active,inactive',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate branch_id is required for branch users
        if ($request->role === 'branch' && !$request->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Branch is required for branch users'
            ], 422);
        }

        try {
            // Debug: Log the request data
            Log::info('Creating user with data:', [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'branch_id' => $request->branch_id,
                'status' => $request->status ?? 'inactive',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'branch_id' => $request->branch_id,
                'status' => $request->status ?? 'inactive', // Default to inactive for new users
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user->load('branch')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        try {
            $user->load('branch');

            Log::info('Loading user for edit/view:', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_status' => $user->status,
                'has_branch' => $user->branch ? true : false
            ]);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading user:', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading user data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,head,branch',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'nullable|in:active,inactive', // Made nullable since form field is commented out
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate branch_id is required for branch users
        if ($request->role === 'branch' && !$request->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Branch is required for branch users'
            ], 422);
        }

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'branch_id' => $request->branch_id,
                // Don't update status if not provided (since form field is commented out)
                // Status will be managed automatically by login/logout events
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->load('branch')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            // Prevent deletion of the last admin user
            if ($user->role === 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete the last admin user'
                    ], 422);
                }
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users with filtering.
     */
    public function getUsers(Request $request): JsonResponse
    {
        $query = User::with(['branch']);

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get user statistics.
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'head' => User::where('role', 'head')->count(),
            'branch' => User::where('role', 'branch')->count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Export users data.
     */
    public function export(Request $request): JsonResponse
    {
        $users = User::with(['branch'])->get();

        $exportData = $users->map(function($user) {
            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Phone' => $user->phone ?? 'N/A',
                'Role' => ucfirst($user->role),
                'Branch' => $user->branch ? $user->branch->name : 'N/A',
                'Status' => ucfirst($user->status),
                'Created' => $user->created_at->format('Y-m-d H:i:s'),
                'Last Updated' => $user->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Export data prepared successfully',
            'data' => $exportData
        ]);
    }
}


