<?php

namespace App\Livewire;

use App\Models\Leave;
use Livewire\Component;
use App\Models\Schedule;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Presensi extends Component
{
    public $latitude;
    public $longitude;
    public $insideRadius = false;
    public function render()
    {
        $schedule = Schedule::where('user_id', Auth::user()->id)->first();
        $attendance = Attendance::where('user_id', Auth::user()->id)
                    ->whereDate('created_at', date('Y-m-d'))->first();


        return view('livewire.presensi',[
            'schedule' => $schedule,
            'insideRadius' => $this->insideRadius,
            'attendance' => $attendance
        ]);
    }

    public function store()
    {
        $this->validate([
            'latitude' => 'required',
            'longitude' => 'required'
        ]);

        $schedule = Schedule::where('user_id', Auth::user()->id)->first();

        $today = Carbon::today()->format('Y-m-d');
        $approvedLeave = Leave::where('user_id', Auth::user()->id)
                        -> where('status', 'approved')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->exists();

        if($approvedLeave) {
            session()->flash('eror', 'Anda Tidak Dapat Melakukan Presens, Karena Sedang Cuti');
            return;
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
                    'start_latitude'=> $this->latitude,
                    'start_longitude'=> $this->longitude,
                    'start_time' => Carbon::now()->toTimeString(),
                    'end_time' => Carbon::now()->toTimeString(),
                ]);
            } else {
                $attendance->update([
                    'end_latitude' =>$this->latitude,
                    'end_longitude' => $this->longitude,
                    'end_time' => Carbon::now()->toTimeString(),
                ]);
            }

            return redirect ('admin/attendances');

            // return redirect()->route('presensi', [
            //     'schedule' => $schedule,
            //     'insideRadius' => false
            // ]);
        }
    }
}
