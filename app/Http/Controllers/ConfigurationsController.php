<?php

namespace App\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConfigurationsController extends Controller
{
    private $data = [];

    public function __construct(){
        $this->data['nien_khoa'] = DB::table("configurations")
        ->select('is_value')
        ->where('name', 'academic_year')
        ->get() [0]->is_value;


        $topic_register_time = DB::table('time_configs')->where('name', 'topic_register')->first();
        $start = $topic_register_time->start_date;
        $this->data['start_date'] = Carbon::parse($start)->format('Y-m-d\TH:i');
        
        $end = $topic_register_time->end_date;
        $this->data['end_date'] = Carbon::parse($end)->format('Y-m-d\TH:i');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("configurations.index", $this->data);
    }


    public function edit()
    {
        return view("configurations.edit", $this->data);

    }

    public function add(Request $request){
        $this->data['start_date'] = $request->input("start_date");
        $this->data['end_date'] = $request->input("end_date");

        $start_date = new \DateTime($this->data['start_date']);
        $end_date = new \DateTime($this->data['end_date']);

        $current_date = Carbon::now();

        if($end_date < $start_date){
            return view('configurations.edit', $this->data)
                ->with('error', 'Ngày mở cổng đăng ký phải trước ngày đóng!');
        }

        if($current_date > $end_date){
            return view('configurations.edit', $this->data)
                ->with('error', 'Ngày đóng cổng đăng ký phải trước ngày hiện tại!');
        }

        // cập nhập niên khóa
        DB::table("configurations")
        ->where('name', 'academic_year')
        ->update([
            'is_value' => $request->academic_year
        ]);

        //cập nhập ngày đăng ký
        DB::table('time_configs')
        ->where('name', 'topic_register')
        ->update([
            'start_date'=> $this->data['start_date'], 
            'end_date'=> $this->data['end_date']
        ]);

        Toastr::success("Cập nhập thành công!");
        return redirect()->route('configurations.index');
    }
}
