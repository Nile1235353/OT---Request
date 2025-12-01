<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtAttendance;
use App\Imports\OtAttendanceImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class OtAttendanceController extends Controller
{
    // 1. Page ပြသခြင်းနှင့် Data ရှာဖွေခြင်း Logic
    public function index(Request $request)
    {
        $query = OtAttendance::with('user'); // User relationship ပါ ချိတ်ယူမယ်

        // Date Filter ပါလာခဲ့ရင်
        if ($request->has('filter_date') && $request->filter_date != null) {
            $query->whereDate('date', $request->filter_date);
        }

        // နောက်ဆုံးထည့်ထားတဲ့ Data ကို အပေါ်ဆုံးမှာပြမယ်၊ ၁၀ ခုစီ ခွဲပြမယ်
        $attendanceData = $query->latest()->paginate(10);

        return view('pages.fingerprint.fingerPrintImport', compact('attendanceData'));
    }

    // 2. Excel Import လုပ်မည့် Logic
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'office_start_time' => 'required',
            'office_end_time'   => 'required',
        ]);

        try {
            // Import Class ကို ခေါ်တဲ့အချိန်မှာ Time တွေကို Constructor ထဲ ထည့်ပေးလိုက်ပါတယ်
            Excel::import(new OtAttendanceImport(
                $request->office_start_time, 
                $request->office_end_time
            ), $request->file('file'));

            return redirect()->back()->with('success', 'Data imported successfully with OT calculation!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // 3. OT Attendance Update Logic
   /**
     * Update the attendance record and recalculate OT hours.
     */
    public function update(Request $request, $id)
    {
        // 1. Validation
        $request->validate([
            'date' => 'required|date',
            'check_in_time' => 'nullable', 
            'check_out_time' => 'nullable',
            // [NEW] Modal မှ ပို့လိုက်သော ရုံးချိန်များကို စစ်ဆေးခြင်း
            'office_start_time' => 'required', 
            'office_end_time' => 'required',   
        ]);

        // User relationship ပါတပါတည်း ယူပါ (Permission စစ်ရန်)
        $record = OtAttendance::with('user')->findOrFail($id);

        // 2. User Permission (Morning OT ရမရ စစ်ဆေးခြင်း)
        $allowMorningOt = $record->user ? $record->user->morning_ot : false;

        // 3. Data Update (အချိန်များကို Database ထဲ အရင်သိမ်းပါမည်)
        $record->date = $request->date;
        $record->check_in_time = $request->check_in_time;
        $record->check_out_time = $request->check_out_time;

        // 4. OT Hours Recalculation (Modal မှ ရုံးချိန်များကို အသုံးပြု၍ ပြန်တွက်ခြင်း)
        
        // ရက်စွဲကို String အဖြစ်ယူပါ (Example: "2025-11-30")
        $dateStr = Carbon::parse($request->date)->format('Y-m-d');

        // [UPDATED] Request မှ ရုံးချိန်များကို ယူပါ
        $startTimeInput = $request->input('office_start_time'); // e.g., "09:00"
        $endTimeInput   = $request->input('office_end_time');   // e.g., "17:00"

        // Carbon Date Object များ တည်ဆောက်ခြင်း
        $officeStart = Carbon::parse("$dateStr $startTimeInput");
        $officeEnd   = Carbon::parse("$dateStr $endTimeInput");

        $morningOtMins = 0;
        $eveningOtMins = 0;
        $debugMsg = "";

        // (A) Morning OT Calculation (ရုံးမတက်ခင် စောလာချိန်)
        if ($request->check_in_time && $allowMorningOt) {
            $checkIn = Carbon::parse("$dateStr " . $request->check_in_time);
            
            // CheckIn သည် OfficeStart ထက် စောနေလျှင် (Less Than)
            if ($checkIn->lt($officeStart)) {
                // Timestamp နည်းလမ်းဖြင့် မိနစ်ကွာခြားချက် ရှာခြင်း
                $morningOtMins = ($officeStart->timestamp - $checkIn->timestamp) / 60;
                $debugMsg .= " Morning: {$morningOtMins} mins.";
            } else {
                // ရုံးချိန်မီ သို့မဟုတ် နောက်ကျမှ ရောက်လျှင် OT မရှိ
                $debugMsg .= " No Morning OT (Arrived at/after start time).";
            }
        } elseif ($request->check_in_time && !$allowMorningOt) {
            $debugMsg .= " Morning OT disabled for this user.";
        }

        // (B) Evening OT Calculation (ရုံးဆင်းပြီးနောက် နောက်ကျပြန်ချိန်)
        if ($request->check_out_time) {
            $checkOut = Carbon::parse("$dateStr " . $request->check_out_time);

            // CheckOut သည် OfficeEnd ထက် နောက်ကျနေလျှင် (Greater Than)
            if ($checkOut->gt($officeEnd)) {
                // Timestamp နည်းလမ်းဖြင့် မိနစ်ကွာခြားချက် ရှာခြင်း
                $eveningOtMins = ($checkOut->timestamp - $officeEnd->timestamp) / 60;
                $debugMsg .= " Evening: {$eveningOtMins} mins.";
            } else {
                // ရုံးချိန်မတိုင်မီ သို့မဟုတ် ရုံးဆင်းချိန်အတိ ပြန်လျှင် OT မရှိ
                $debugMsg .= " No Evening OT (Left at/before end time).";
            }
        }

        // မိနစ်မှ နာရီသို့ ပြောင်းလဲခြင်း (Round to 2 decimal places)
        $morningOtHours = round($morningOtMins / 60, 2);
        $eveningOtHours = round($eveningOtMins / 60, 2);
        
        // Total OT ပေါင်းထည့်ခြင်း
        $record->actual_ot_hours = $morningOtHours + $eveningOtHours;
        
        $record->save();

        return redirect()->back()->with('success', "Updated! Morning: {$morningOtHours} hrs, Evening: {$eveningOtHours} hrs. Total: {$record->actual_ot_hours} hrs. (Note: $debugMsg)");
    }

}
