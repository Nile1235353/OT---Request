<?php

namespace App\Http\Controllers;

use App\Models\OtAttendance;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OtAttendanceImport;
use Carbon\Carbon;

class OtAttendanceController extends Controller
{
    // 1. Page ပြသခြင်းနှင့် Data ရှာဖွေခြင်း Logic
    public function index(Request $request)
    {
        $query = OtAttendance::with('user'); // User relationship ပါ ချိတ်ယူမယ်

        // Date Filter ပါလာခဲ့ရင်
        if ($request->has('filter_date') && $request->filter_date) {
            $query->whereDate('date', $request->filter_date);
        }

        // နောက်ဆုံးထည့်ထားတဲ့ Data ကို အပေါ်ဆုံးမှာပြမယ်၊ ၂၀ ခုစီ ခွဲပြမယ်
        $attendanceData = $query->orderBy('date', 'desc')->paginate(20);

        return view('pages.fingerprint.fingerPrintImport', compact('attendanceData'));
    }

    // 2. Excel Import လုပ်မည့် Logic
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'office_start_time' => 'required',
            'office_end_time'   => 'required',
            'location' => 'required|string', // Location ကို validate လုပ်ရန်
        ]);

        try {
            // Import Class ကို ခေါ်တဲ့အချိန်မှာ Time တွေနဲ့ Location ကို Constructor ထဲ ထည့်ပေးလိုက်ပါတယ်
            Excel::import(new OtAttendanceImport(
                $request->office_start_time, 
                $request->office_end_time,
                $request->location 
            ), $request->file('file'));

            return redirect()->back()->with('success', 'Attendance data imported and calculated successfully.');
        } catch (\Exception $e) {
            // Error အသေးစိတ်ကို ပြန်ပြပေးခြင်း (Development အတွက်)
            return redirect()->back()->with('error', 'Import Failed: ' . $e->getMessage());
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

        // Request မှ ရုံးချိန်များကို ယူပါ
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
                $morningOtMins = $checkIn->diffInMinutes($officeStart);
                $debugMsg .= " Morning: {$morningOtMins} mins.";
            } else {
                $debugMsg .= " No Morning OT.";
            }
        }

        // (B) Evening OT Calculation (ရုံးဆင်းပြီးနောက် နောက်ကျပြန်ချိန်)
        if ($request->check_out_time) {
            $checkOut = Carbon::parse("$dateStr " . $request->check_out_time);
            
            // အကယ်၍ Check In ရှိပြီး Check Out က Check In ထက် စောနေလျှင် (ဥပမာ - ည ၁၂ ကျော်ပြန်လျှင်)
            // Check Out ကို နောက်တစ်ရက်သို့ တိုးပေးရမည်
            if ($request->check_in_time) {
                $checkInForCompare = Carbon::parse("$dateStr " . $request->check_in_time);
                if ($checkOut->lt($checkInForCompare)) {
                    $checkOut->addDay();
                    $debugMsg .= " (Next Day Checkout detected)";
                }
            }

            // CheckOut သည် OfficeEnd ထက် နောက်ကျနေလျှင် (Greater Than)
            if ($checkOut->gt($officeEnd)) {
                $eveningOtMins = $checkOut->diffInMinutes($officeEnd);
                $debugMsg .= " Evening: {$eveningOtMins} mins.";
            } else {
                $debugMsg .= " No Evening OT.";
            }
        }

        // မိနစ်မှ နာရီသို့ ပြောင်းလဲခြင်း (Round to 2 decimal places)
        $morningOtHours = round($morningOtMins / 60, 2);
        $eveningOtHours = round($eveningOtMins / 60, 2);
        
        // Total OT ပေါင်းထည့်ခြင်း
        $record->actual_ot_hours = $morningOtHours + $eveningOtHours;
        
        $record->save();

        return redirect()->back()->with('success', "Updated! Morning: {$morningOtHours} hrs, Evening: {$eveningOtHours} hrs. Total: {$record->actual_ot_hours} hrs.");
    }
}