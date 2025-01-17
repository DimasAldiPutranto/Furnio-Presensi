<?php

namespace App\Http\Controllers\API;

use App\Models\Leave;
use App\Models\Schedule;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function getAttendanceToday()
    {
        $userId = auth()->user()->id;
        $today = now()->toDateString();
        $currentMonth = now()-> month;

        $attendanceToday = Attendance::select('start_time', 'end_time')
                            ->where('user_id', $userId)
                            ->whereDate('created_at', $today)
                            ->first();

        $attendanceThisMonth = Attendance::select('start_time', 'end_time', 'created_at')
                            ->where('user_id', $userId)
                            ->whereMonth('created_at', $currentMonth)
                            ->get()
                            ->map(function ($attendance) {
                                return [
                                    'start_time' => $attendance->start_time,
                                    'end_time' => $attendance->end_time,
                                    'date' => $attendance->created_at->toDateString()
                                ];
                            });

        return response()->json([
            'success' => true,
            'data' => [
                'today' => $attendanceToday,
                'this_month' => $attendanceThisMonth 
            ],
            'message' => 'success get Attendance today'
        ]);
    }

    public function getSchedule()
    {
            $schedule = Schedule::with(['office', 'shift'])
                        ->where('user_id', auth()->user()->id)
                        ->first();
                        
        $today = Carbon::today()->format('Y-m-d');
        $approvedLeave = Leave::where('user_id', Auth::user()->id)
                        -> where('status', 'approved')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->exists();

        if($approvedLeave) {
            return response()->json([
                'success' => true,
                'message' => 'Anda Tidak Dapat Melakukan Presensi, Karena Sedang Cuti',
                'data' => null
            ]);
        }
            if($schedule->is_banned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Soryy, You Are Banned',
                    'data' => null
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Success get Schedule',
                    'data' => $schedule,
                ]);
            }
    }

        public function store (Request $request)
        {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()-> json([
                    'success' => false,
                    'message' => 'Validator Eror',
                    'data' => $validator->errors()
                ], 422);
            }

            $schedule = Schedule::where('user_id', Auth::user()->id)->first();

            $today = Carbon::today()->format('Y-m-d');
            $approvedLeave = Leave::where('user_id', Auth::user()->id)
                        -> where('status', 'approved')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->exists();

            if($approvedLeave) {
            return response()->json([
                'success' => true,
                'message' => 'Anda Tidak Dapat Melakukan Presensi, Karena Sedang Cuti',
                'data' => null
            ]);
            }

            if ($schedule) {
                $attendance = Attendance::where('user_id', Auth::user()->id)
                ->whereDate('created_at', date('Y-m-d'))->first();
                
                if(!$attendance) {
                    $attendance = Attendance::create([
                        'user_id' => Auth::user()->id,
                        'schedule_latitude' => $schedule->office->latitude,
                        'schedule_longitude' => $schedule->office->longitude,
                        'schedule_start_time' => $schedule->shift->start_time,
                        'schedule_end_time' => $schedule->shift->end_time,
                        'start_latitude'=> $request->latitude,
                        'start_longitude'=> $request->longitude,
                        'start_time' => Carbon::now()->toTimeString(),
                        'end_time' => Carbon::now()->toTimeString(),
                    ]);
            } else {
                $attendance->update([
                    'end_latitude' =>$request->latitude,
                    'end_longitude' => $request->longitude,
                    'end_time' => Carbon::now()->toTimeString(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Succes Store Attendance',
                'data' => $attendance
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No Schedule Found',
                'data' => null
            ]);
        }
    }

    public function getAttendanceByMonthYear($month, $year)
    {
        $validator = Validator::make(['month' => $month, 'year' => $year], [
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2023|max:'.date('Y')
        ]);

        if ($validator->fails()) {
            return response()-> json([
                'success' => false,
                'message' => 'Validator Eror',
                'data' => $validator->errors()
            ], 422);
        }

        $userId = auth()->user()->id;
        $attendanceList = Attendance::select('start_time', 'end_time', 'created_at')
                            ->where('user_id', $userId)
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year)
                            ->get()
                            ->map(function ($attendance) {
                                return [
                                    'start_time' => $attendance->start_time,
                                    'end_time' => $attendance->end_time,
                                    'date' => $attendance->created_at->toDateString()
                                ];
                            });
        return response()->json([
            'succes' => true,
            'data' => $attendanceList,
            'message' => 'success get Attendance by month and year'
        ]);

    }

    public function banned()
    {
        $schedule = Schedule::where('user_id', Auth::user()->id)->first();
        if($schedule) {
            $schedule->update([
                'is_banned' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'success banned schedule',
                'data' => $schedule
            ]);
        }
    }

    public function getImage()
    {
        $user = auth()->user();
        return response()->json([
            'success' => true,
            'message' => 'success get image',
            'data' => $user->image_url
        ]);
    }
}
