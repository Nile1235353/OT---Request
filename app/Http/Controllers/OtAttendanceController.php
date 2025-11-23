<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OtAttendance;
use App\Imports\OtAttendanceImport;
use Maatwebsite\Excel\Facades\Excel;

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
}
