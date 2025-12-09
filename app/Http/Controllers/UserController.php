<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OtRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // 👈 DB Facade ကို သေချာ import လုပ်ပါ
use App\Imports\UsersImport; // Import Class ကိုခေါ်ပါ
use Maatwebsite\Excel\Facades\Excel; // Excel Facade ကိုခေါ်ပါ
use App\Exports\UsersExport; // Export Class ကိုခေါ်ပါ
use App\Models\OtAttendance;

class UserController extends Controller
{
    public function import(Request $request) 
    {
        // 1. Validation
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls|max:2048',
        ]);

        try {
            // 2. Import Process
            Excel::import(new UsersImport, $request->file('file'));
            
            return back()->with('success', 'Users imported successfully via Excel!');
        } catch (\Exception $e) {
            // Error ဖြစ်ခဲ့ရင်
            Log::error("Excel Import Error: " . $e->getMessage());
            return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * User အတွက် လစဉ် OT Data များကို Fingerprint Table (OtAttendance) မှ ရယူရန်
     */
    public function getOvertimeData(Request $request, User $user)
    {
        // 1. Validate Month Input
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);
        $month = $validated['month'];

        try {
            // 2. Calculate Date Range
            $date = Carbon::createFromFormat('Y-m', $month);
            $startOfMonth = $date->copy()->startOfMonth()->toDateString();
            $endOfMonth = $date->copy()->endOfMonth()->toDateString();

            // --- DEBUGGING ---
            Log::info("Fetching OT for User ID: {$user->id}, Fingerprint ID: {$user->finger_print_id}");

            // 3. Database Query (OtAttendance Model အသစ်ကို အသုံးပြုခြင်း)
            // OtAttendance table ရှိ 'employee_id' သည် User table ရှိ 'finger_print_id' နှင့် တူညီသည်ဟု ယူဆပါသည်
            
            $query = OtAttendance::query()
                ->where('employee_id', $user->finger_print_id) // User ၏ FP ID ဖြင့် ရှာပါ
                ->whereBetween('date', [$startOfMonth, $endOfMonth]) // Date range စစ်ပါ
                ->where('actual_ot_hours', '>', 0) // OT ရှိသော ရက်များကိုသာ ယူပါ
                ->orderBy('date', 'asc');

            $attendanceRecords = $query->get();

            // 4. Frontend Format သို့ ပြောင်းခြင်း
            // Frontend JS သည် { date, hours, reason } ပုံစံ လိုချင်သောကြောင့် Map လုပ်ပေးရပါမည်
            $formattedRecords = $attendanceRecords->map(function ($record) {
                return [
                    'date'   => $record->date,             // Model ရှိ 'date' column
                    'hours'  => $record->actual_ot_hours,  // Model ရှိ 'actual_ot_hours' column
                    'reason' => 'Fingerprint Record',      // Reason မရှိသဖြင့် Static စာသား ထည့်ပါသည်
                ];
            });

            // 5. Calculate Total Hours
            $totalHours = $formattedRecords->sum('hours');

            // 6. Return JSON
            return response()->json([
                'totalHours' => $totalHours,
                'otList'     => $formattedRecords,
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching Fingerprint OT data: " . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve overtime data.'], 500);
        }
    }

    public function export() 
    {
        return Excel::download(new UsersExport, 'users_list.xlsx');
    }

    public function downloadSample() 
    {
        $filePath = public_path('samples/users_import_sample.xlsx');
        return response()->download($filePath, 'users_import_sample.xlsx');
    }
}

