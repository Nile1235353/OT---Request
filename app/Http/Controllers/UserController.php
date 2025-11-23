<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OtRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // 👈 DB Facade ကို သေချာ import လုပ်ပါ

class UserController extends Controller
{
    /**
     * User အတွက် လစဉ် OT Data များကို JSON ဖြင့် ပြန်ပေးရန်
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

            // --- DEBUGGING (Log 1) ---
            Log::info("--- NEW REQUEST (JOIN Version) ---");
            Log::info("User ID: {$user->id} ({$user->name}), Month: {$month}");
            Log::info("Date Range: {$startOfMonth} to {$endOfMonth}");
            // --- END DEBUGGING ---

            // --- !!! QUERY ကို JOIN သုံးပြီး ပြန်လည်ပြင်ဆင်ထားပါသည် !!! ---
            // 3. Database Query (သင်၏ manual SQL နှင့် တူညီစေရန်)
            $query = OtRequest::query()
                // T1 INNER JOIN T2 ON T1.id = T2.ot_requests_id
                ->join('assign_teams', 'ot_requests.id', '=', 'assign_teams.ot_requests_id')
                
                // WHERE T2.user_id = 1
                ->where('assign_teams.user_id', $user->id)
                // WHERE LOWER(TRIM(T1.status)) = 'approved'
                ->whereRaw('LOWER(TRIM(ot_requests.status)) = ?', ['approved'])
                // WHERE DATE(T1.ot_date) BETWEEN ...
                ->whereRaw('DATE(ot_requests.ot_date) >= ?', [$startOfMonth])
                ->whereRaw('DATE(ot_requests.ot_date) <= ?', [$endOfMonth])

                // SELECT T1.ot_date as date, T1.total_hours as hours, T1.reason
                // (Ambiguous column name မဖြစ်စေရန် table name များ ထည့်သွင်းပါ)
                ->select(
                    'ot_requests.ot_date as date',
                    'ot_requests.total_hours as hours',
                    'ot_requests.reason'
                )
                // ORDER BY T1.ot_date ASC
                ->orderBy('ot_requests.ot_date', 'asc')
                // (တစ်ခါတစ်ရံ JOIN ကြောင့် data trùng နိုင်ပါက distinct ဖြင့် ဖယ်ထုတ်ပါ)
                ->distinct(); 
            // --- !!! END QUERY MODIFICATION !!! ---


            // --- !!! NEW DEBUGGING (Log 2) !!! ---
            $rawSql = $query->toSql();
            $bindings = $query->getBindings();
            Log::info("Eloquent Query SQL: " . $rawSql);
            Log::info("Eloquent Query Bindings: " . implode(", ", $bindings));
            // --- !!! END NEW DEBUGGING !!! ---

            // Query ကို Run ပါ
            $otRecords = $query->get();

            // --- DEBUGGING (Log 3) ---
            Log::info("Eloquent Query Found: " . $otRecords->count() . " records.");
            // --- END DEBUGGING ---

            // 4. Calculate Total Hours
            $totalHours = $otRecords->sum('hours'); // 'hours' key အသစ်ကို သုံးပါ

            // 5. Return JSON
            return response()->json([
                'totalHours' => $totalHours,
                'otList' => $otRecords,
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching OT data for user {$user->id}, month {$month}: " . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve overtime data.'], 500);
        }
    }
}

