<?php

namespace App\Exports;

use App\Models\assignTeam;
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
    protected $startDate;
    protected $endDate;
    protected $department;
    protected $requirementType; // [NEW] Property

    // [UPDATED] Constructor
    public function __construct($startDate, $endDate, $department = null, $requirementType = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->department = $department;
        $this->requirementType = $requirementType;
    }

    public function collection()
    {
        $currentUser = Auth::user();
        
        $query = assignTeam::with(['user', 'otRequest', 'otRequest.supervisor'])
            ->join('ot_requests', 'assign_teams.ot_requests_id', '=', 'ot_requests.id')
            ->join('users', 'assign_teams.user_id', '=', 'users.id')
            ->where('ot_requests.status', 'Approved');

        // Access Control
        $isSuperUser = in_array($currentUser->role, ['Admin', 'HR']) || $currentUser->position === 'General Manager';
        if (!$isSuperUser) {
            $query->where('users.location', $currentUser->location)
                  ->where('users.department', $currentUser->department);
        }

        // Filters
        if ($this->startDate) {
            $query->where('ot_requests.ot_date', '>=', Carbon::parse($this->startDate)->startOfDay());
        }
        if ($this->endDate) {
            $query->where('ot_requests.ot_date', '<=', Carbon::parse($this->endDate)->endOfDay());
        }
        if ($this->department) {
            $query->where('users.department', $this->department);
        }
        
        // [NEW] Requirement Type Filter
        if ($this->requirementType) {
            $query->where('ot_requests.requirement_type', $this->requirementType);
        }

        $assignedOts = $query->orderBy('ot_requests.ot_date', 'desc')->select('assign_teams.*')->get();

        // Map Actual Data (Unchanged)
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
            'Job Code',
            'Requirement', // [NEW] Header
            'Department',
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
            $item->otRequest->job_code ?? '-',
            
            // [NEW] Map Requirement Type
            $item->otRequest->requirement_type ?? '-',

            $item->user->department ?? '-',
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