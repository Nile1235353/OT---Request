<?php

namespace App\Exports;

// ... existing imports ...
use App\Models\AssignTeam;
use App\Models\OtAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeOtExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    // ... existing constructor & collection method ...
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $currentUser = Auth::user();
        // Query
        $query = AssignTeam::with(['user', 'otRequest', 'otRequest.supervisor'])
            ->join('ot_requests', 'assign_teams.ot_requests_id', '=', 'ot_requests.id')
            ->join('users', 'assign_teams.user_id', '=', 'users.id')
            ->where('ot_requests.status', 'Approved');

        // Access Control
        $isSuperUser = in_array($currentUser->role, ['Admin', 'HR']) || $currentUser->position === 'General Manager';
        if (!$isSuperUser) {
            $query->where('users.location', $currentUser->location)
                  ->where('users.department', $currentUser->department);
        }

        // Date Filter
        if ($this->startDate) {
            $query->where('ot_requests.ot_date', '>=', Carbon::parse($this->startDate)->startOfDay());
        }
        if ($this->endDate) {
            $query->where('ot_requests.ot_date', '<=', Carbon::parse($this->endDate)->endOfDay());
        }

        $assignedOts = $query->orderBy('ot_requests.ot_date', 'desc')->select('assign_teams.*')->get();

        // Map Actual Data (Similar to Controller logic)
        $fingerPrintIds = $assignedOts->map(fn($item) => $item->user->finger_print_id)->filter()->unique();
        $dates = $assignedOts->map(fn($item) => $item->otRequest->ot_date)->unique();

        $attendanceRecords = [];
        if ($fingerPrintIds->isNotEmpty()) {
            $attendanceRecords = OtAttendance::whereIn('employee_id', $fingerPrintIds)
                ->whereIn('date', $dates)
                ->get()
                ->keyBy(fn($item) => $item->employee_id . '_' . $item->date);
        }

        foreach ($assignedOts as $assignment) {
            $fpId = $assignment->user->finger_print_id;
            $otDate = $assignment->otRequest->ot_date;
            $key = $fpId . '_' . $otDate;

            if (isset($attendanceRecords[$key])) {
                $attendance = $attendanceRecords[$key];
                $assignment->actual_hours = $attendance->actual_ot_hours;
                $assignment->actual_in = $attendance->check_in_time;
                $assignment->actual_out = $attendance->check_out_time;
            } else {
                $assignment->actual_hours = 0;
                $assignment->actual_in = null;
                $assignment->actual_out = null;
            }
        }

        return $assignedOts;
    }

    public function headings(): array
    {
        return [
            'OT Date',
            'Job Code', // [NEW] Added Column Header
            'Employee',
            'FP ID',
            'Supervisor',
            'Reason',
            'Task',
            'Actual In',
            'Actual Out',
            'Actual Hours',
            'Status',
        ];
    }

    public function map($item): array
    {
        return [
            Carbon::parse($item->otRequest->ot_date)->format('d-M-Y'),
            
            // [NEW] Job Code Mapping
            $item->otRequest->job_code ?? '-',

            $item->user->name ?? 'N/A',
            $item->user->finger_print_id ?? '-',
            $item->otRequest->supervisor->name ?? 'N/A',
            $item->otRequest->reason,
            $item->task_description,
            $item->actual_in ? Carbon::parse($item->actual_in)->format('H:i') : '-',
            $item->actual_out ? Carbon::parse($item->actual_out)->format('H:i') : '-',
            $item->actual_hours > 0 ? $item->actual_hours : '0',
            $item->otRequest->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [ 1 => ['font' => ['bold' => true]] ];
    }
}