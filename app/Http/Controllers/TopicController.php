<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;

use Auth;
use App\Models\File;
use App\Models\User;
use App\Models\Topic;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use DateTime;
use Carbon\Carbon;
use Illuminate\Contracts\Session\Session;
use Spatie\Permission\Models\Role;

class TopicController extends Controller
{
    var $nien_khoa;
    var $departmentArr =  array('1' => 'CNPM', '2' => 'KHMT', '3' => 'HTTT', '4' => 'KTMT', '5' => 'PPGD');
    var $academic_year_arr = [];

    var $instructorArr = [];
    function __construct()
    {
        $this->nien_khoa = DB::table("configurations")
                        ->select('is_value')
                        ->where('name', 'academic_year')
                        ->get() [0]->is_value;
           
        //ds GVHD
        $instructors = DB::table('users')
                        ->select(['id', 'name'])
                        ->where('role', 4)
                        ->orWhere('role', 1)
                        ->get();

        foreach($instructors as $item){
            $this->instructorArr[$item->id] = $item->name;
        }

        $current_academic_year = idate('Y') - 1953;    //Niên khóa hiện tại
        for($i = $current_academic_year-4; $i <= $current_academic_year + 1 ; $i++){
            $this->academic_year_arr['K'.$i] = "K" .$i;
        }

        $this->middleware('permission:topic-list|topic-create|topic-edit|topic-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:topic-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:topic-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:topic-delete', ['only' => ['destroy']]);
        $this->middleware('permission:topic-report', ['only' => ['export']]);
        $this->middleware('permission:topic-register', ['only' => ['topicRegister', 'topicRegisterList']]);
        $this->middleware('permission:instructor-topic-list', ['only' => ['instructorTopicList', 'instructorShow']]);
    }

    public function index(Request $request)
    {
        $param = $request->all();
        // $topics = Topic::filter($param);

        $topics = DB::table('topics')
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select('topics.id', 'topics.name', 'topics.department', 'topics.number_student', 
            'topics.note', 'users.name as instructor_name', 'topics.status')
            // ->get();
            ->paginate(10);

        // $topics = $topics->paginate(10)->appends($param);
        return view('topics.index', compact('topics', 'param'))
            ->with('i', (request()->input('page', 1) - 1) * 10);


        // $param = $request->all();
        // if (Auth::user()->can('topic-list')) {
        //     $fields = Field::pluck('name', 'id')->all();
        //     $provinces = Province::pluck('name', 'id')->all();
        //     $users = User::whereIn('role', [3, 6])->pluck('name', 'id')->all();
        //     $groups = Group::pluck('name', 'id')->all();
        //     $medals = Medal::pluck('name', 'id')->all();
        // }
        // $researchs = Research::filter($param);

        // if (Auth::user()->role > 2 && Auth::user()->role != 8) {
        //     $researchs->where('user_id', Auth::user()->id);
        //     $param = ['user_id' => Auth::user()->id];
        // } else {
        //     $researchs->orderBy('id', 'ASC');
        // }
        // $researchs = $researchs->paginate(10)->appends($param);
        // return view('researchs.index', compact('researchs', 'param', 'fields', 'provinces', 'users', 'groups', 'medals'))
        //     ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function create() {
        $departmentArr = $this->departmentArr;
        $instructorArr = $this->instructorArr;
        $nien_khoa = $this->nien_khoa;
        $academic_year_arr = $this->academic_year_arr;

        $roles = Role::where('id', ">", Auth::user()->role)->pluck('name', 'id')->all();
        return view("topics.create", compact(
            'roles', 
            'instructorArr', 
            'departmentArr', 
            'nien_khoa',
            'academic_year_arr',
        ));
    }

    

    public function topicRegisterList(Request $request, $sort)
    {
        $param = $request->all();
        // $topics = Topic::filter($param);

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $topic_register_time = DB::table('time_configs')->where('name', 'topic_register')->first();
        $start = $topic_register_time->start_date;
        $end = $topic_register_time->end_date;
        $end_time = strtotime($end);
        $start_date = new DateTime($start);//start time
        $end_date = new DateTime($end);//end time
        $current_date_time = Carbon::now();

        // $topics = getTopics();    
        $topics2 = DB::table('topics')
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select('topics.id', 'topics.name', 'topics.department', 'topics.number_student', 
            'topics.note', 'users.name as instructor_name', 'topics.status', 'topics.description', 'topics.required')
            ->where('topics.academic_year', '=', $this->nien_khoa)
            ->get();

        if ($start_date >= $current_date_time) {
            return view('topics.register_timeout', compact('topics2'));
        }

        if ($current_date_time >= $end_date) {
             return view('topics.register_timeout', compact('topics2'));
        }

        $number_student = DB::table('user_register_topics')
                        ->select(DB::raw('count(user_id) as number_student'))
                        ->where('user_id', '=', Auth::User()->id)
                        ->value('number_student');
        //Da dang ky de tai
        if ((int)$number_student > 0){
            return redirect('topic-register-details');
        }

        $sort_param = [
            'topic_name' => 'topic_name_asc',
            'topic_department' => 'topic_department_asc',
            'instructor_name' => 'instructor_name_asc',
            'topic_status' => 'topic_status_asc'
        ];
        $orderBy = 'topics.created_at';
        $order_direct = 'desc';

        //Sort by topic name
        if ($sort == 'topic_name_asc') {
            $orderBy = 'topics.name';
            $order_direct = 'asc';
            $sort_param['topic_name'] = 'topic_name_desc';
        }
        if ($sort == 'topic_name_desc') {
            $orderBy = 'topics.name';
            $order_direct = 'desc';
            $sort_param['topic_name'] = 'topic_name_asc';
        }

        //Sort by topic deparment
        if ($sort == 'topic_department_asc') {
            $orderBy = 'topics.department';
            $order_direct = 'asc';
            $sort_param['topic_department'] = 'topic_department_desc';
        }
        if ($sort == 'topic_department_desc') {
            $orderBy = 'topics.department';
            $order_direct = 'desc';
            $sort_param['topic_department'] = 'topic_department_asc';
        }

        //Sort by topic instructor name
        if ($sort == 'instructor_name_asc') {
            $orderBy = 'instructor_name';
            $order_direct = 'asc';
            $sort_param['instructor_name'] = 'instructor_name_desc';
        }
        if ($sort == 'instructor_name_desc') {
            $orderBy = 'instructor_name';
            $order_direct = 'desc';
            $sort_param['instructor_name'] = 'instructor_name_asc';
        }

        //Sort by topic status
        if ($sort == 'topic_status_asc') {
            $orderBy = 'topics.status';
            $order_direct = 'asc';
            $sort_param['topic_status'] = 'topic_status_desc';
        }
        if ($sort == 'topic_status_desc') {
            $orderBy = 'topics.status';
            $order_direct = 'desc';
            $sort_param['topic_status'] = 'topic_status_asc';
        }

        // $topics = getTopics();    
        $topics = DB::table('topics')
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select('topics.id', 'topics.name', 'topics.department', 'topics.number_student', 
            'topics.note', 'users.name as instructor_name', 'topics.status', 'topics.description', 'topics.required')
            ->where('topics.academic_year', '=', $this->nien_khoa)
            ->orderBy($orderBy, $order_direct)
            ->orderBy('topics.department', 'asc')
            ->paginate(10);

        // $topics = $topics->paginate(10)->appends($param);

        return view('topics.register', compact('topics', 'param', 'sort_param'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    // public function getTopics() {
    //     $topics = DB::table('topics')
    //         ->join('users', 'topics.user_id', '=', 'users.id')
    //         ->select('topics.id', 'topics.name', 'topics.department', 'topics.number_student', 
    //         'topics.note', 'users.name as instructor_name', 'topics.status')
    //         ->orderBy($orderBy, $order_direct)
    //         ->orderBy('instructor_name', 'asc')
    //         // ->get();
    //         ->paginate(20);
    //     return $topics;
    // }

    public function topicRegister(Request $request)
    {
        $user_id = Auth::User()->id;
        $topic_id = $request->topic_id;
        echo $user_id.' Đăng ký đề tài '.$topic_id;

        //Tìm thông tin topic theo id
        $topic = DB::table('topics')->where(['id' => $topic_id]);
        $topic_status = $topic->value('status');
        $max_student = $topic->value('number_student');
        $number_student = DB::table('user_register_topics')
                        ->select(DB::raw('count(user_id) as number_student'))
                        ->where('topic_id', '=', $topic_id)
                        ->value('number_student');
        
        echo '<br>'.$topic_status.' - '.$max_student.' - '.$number_student;
        if ($topic_status = '1' && ((int)$number_student < (int)$max_student)) {
            echo '<br>Có thể đăng ký';
            //Lưu thông tin đăng ký đề tài
            DB::table('user_register_topics')->insert([
                'user_id' => $user_id,
                'topic_id' => $topic_id,
                'semester' => $this->nien_khoa
            ]);
            $topic = DB::table('topics')->where(['id' => $topic_id]);
            $max_student = $topic->value('number_student');
            $number_student = DB::table('user_register_topics')
                            ->select(DB::raw('count(user_id) as number_student'))
                            ->where('topic_id', '=', $topic_id)
                            ->value('number_student');
            if (((int)$number_student >= (int)$max_student)){
                DB::table('topics')
                ->where('id', $topic_id)
                ->update(['status' => 0]);
            }
            Toastr::success('Đăng ký đề tài thành công');
            return redirect('topic-register-details');
        }
        else {
            echo '<br>Đề tài đã đủ sinh viên';
            DB::table('topics')
                ->where('id', $topic_id)
                ->update(['status' => 0]);
            Toastr::error('Đề tài đã đủ sinh viên');
            return redirect('topics-register-list/all');
        }
        
    }

    public function topicRegisterDetails(Request $request)
    {
        $user_id = Auth::User()->id;
        $topic_id = DB::table('user_register_topics')->where('user_id', $user_id)->value('topic_id');
        if ($topic_id == ''){
            Toastr::warning('Bạn chưa đăng ký đề tài nào!');
            return redirect('topics-register-list/all');
        }
        else{
            $topic = DB::table('topics')
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select('topics.id', 'topics.name', 'topics.department', 'topics.number_student', 
            'topics.note', 'users.name as instructor_name', 'topics.status', 'topics.required', 'topics.description')
            ->where('topics.id', $topic_id)
            ->get()->first();
            
            return view('topics.register_details', ['topic' => $topic]);
        }
    }

    public function showTopicConfirm($topic_id)
    {

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $topic_register_time = DB::table('time_configs')->where('name', 'topic_register')->first();
        $start = $topic_register_time->start_date;
        $end = $topic_register_time->end_date;
        $end_time = strtotime($end);
        $start_date = new DateTime($start);//start time
        $end_date = new DateTime($end);//end time
        $current_date_time = Carbon::now();

        if ($start_date >= $current_date_time) {
            return view('topics.register_timeout');
        }

        if ($current_date_time >= $end_date) {
             return view('topics.register_timeout');
        }

        $user_id = Auth::User()->id;
        $registed_topic_id = DB::table('user_register_topics')->where('user_id', $user_id)->value('topic_id');
        if ($registed_topic_id != ''){

            $registed_topic = DB::table('topics')
            ->join('users', 'topics.user_id', '=', 'users.id')
            ->select('topics.id', 'topics.name', 'topics.department', 'topics.number_student', 
            'topics.note', 'users.name as instructor_name', 'topics.status', 'topics.required', 'topics.description')
            ->where('topics.id', $registed_topic_id)
            ->get()->first();

            Toastr::warning('Bạn đã có đề tài rồi!');
            return view('topics.register_details', ['topic' => $registed_topic]);
        }

        $topic = Topic::where('id', $topic_id)->get();
        $topic = $topic[0];
        $user_id = $topic->user_id;
        $user = User::where('id', '=', $user_id)->get();
        $user = $user[0];

        if ($topic->status == '1') {
            return view('topics.register_details_confirm', ['topic' => $topic, 'user' => $user]);
        } else {
            Toastr::warning('Bạn không được phép xem đề tài này');
            return redirect('topics-register-list/all');
        }

        
    }

    public function topicRegisterCancel(Request $request)
    {
        $user_id = Auth::User()->id;
        $topic_id = DB::table('user_register_topics')->where('user_id', $user_id)->value('topic_id');
        DB::table('user_register_topics')->where('user_id', $user_id)->delete();
        DB::table('topics')
        ->where('id', $topic_id)
        ->update(['status' => 1]);
        Toastr::error('Bạn đã hủy đăng ký đề tài!');

        return redirect('topics-register-list/all');
        
    }

    // public function create(){
    //     return view('topics.create');
    // }

    // public function create(Request $request)
    // {
    //     $fields = Field::pluck('name', 'id')->all();
    //     $fieldsEn = Field::pluck('nameEn', 'id')->all();
    //     $provinces = Province::pluck('name', 'id')->all();
    //     $users = User::pluck('name', 'id')->all();
    //     $user = Auth::user();
    //     return view('researchs.create', compact('user', 'fields', 'fieldsEn', 'provinces', 'users'));
    // }

    public function testTime() {
        $topic_register_time = DB::table('time_configs')->where('name', 'topic_register')->first();

        $start = $topic_register_time->start_date;
        $end = $topic_register_time->end_date;
        echo $start.' - '.$end.'<br>';

        $start_date = new DateTime($start);//start time
        $end_date = new DateTime($end);//end time
        $current_date_time = Carbon::now();
        if ($start_date <= $current_date_time && $current_date_time <= $end_date) {
            $interval = $end_date->diff($current_date_time);
        }
        
        // echo $interval->format('%Y years %m months %d days %H hours %i minutes %s seconds');
        echo $interval->format('%D Ngày %H Giờ %I Phút %S Giây');
            
        echo '<br>'.$current_date_time = Carbon::now()->toDateTimeString();

        // echo var_dump($end_date > $start_date);
        // echo var_dump($end_date == $start_date);
        // echo var_dump($end_date < $start_date);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        if (!$request->get('name')) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'name' => 'Tên đề tài không được bỏ trống!',
                ]);
        }

        if (!$request->get('department')) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'department' => 'Vui lòng chọn bộ môn!',
                ]);
        }

        if(isset($request['user_id'])){
            if (!$request->get('user_id')) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'department' => 'Vui lòng chọn giảng viên hướng dẫn!',
                    ]);
            }
        }

        if (!$request->get('number_student')) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'number_student' => 'Số lượng SV không được bỏ trống!',
                ]);
        }

        // request()->validate([
        //     'name' => 'required',
        //     'department' => 'required',
        //     'number_student' => 'required'
        // ]);
        
    $topic = new Topic();
    $topic->name = $request->name;
    $topic->department = $this->departmentArr[$request->department];
    $topic->number_student = $request->number_student;
    $topic->note = $request->note;
    $topic->required = $request->required;
    $topic->user_id = isset($request['user_id']) ? $request['user_id'] : auth()->user()->id;
    $topic->academic_year = $this->nien_khoa;
    $topic->save();
    Toastr::success('Thêm đề tài thành công');
    return redirect()->route($request->routeRedirect);
    }

    public function show(Topic $topic)
    {
        $user_id = $topic->user_id;
        $user = User::where('id', '=', $user_id)->get();

        $students = DB::table('user_register_topics')
        ->join('users', 'user_register_topics.user_id', '=', 'users.id')
        ->where('user_register_topics.topic_id', $topic->id)
        ->get();
        return view('topics.show', [
            'topic' => $topic, 
            'user' => $user[0],
            'students'=> $students,
            'i'=> 0,
        ]);
    }

    public function showRegisterResult () {
        $register_result = DB::table('user_register_topics')
        ->join('users as students', 'user_register_topics.user_id', '=', 'students.id')
        ->join('topics', 'user_register_topics.topic_id', '=', 'topics.id')
        ->join('users as instructors', 'topics.user_id', '=', 'instructors.id')
        ->select('topics.id as topic_id', 'topics.name as topic_name', 'topics.department', 
        'students.name as student_name', 'students.username as student_id', 'instructors.name as instructor_name')
        ->where('topics.academic_year', '=', $this->nien_khoa)
        ->orderBy('instructor_name', 'asc')
        ->paginate(30);
        return view('topics.show_register_result', ['register_result' => $register_result])
        ->with('i', (request()->input('page', 1) - 1) * 30); 
    }

    public function edit(Topic $topic)
    {
        $user_id = $topic->user_id;
        //thoong tin taif khoan
        // $user = User::where('id', '=', $user_id)->get();
       
        return view('topics.edit', [
            'topic' => $topic, 
            // 'user' => $user[0], 
            'departmentArr' => $this->departmentArr,
            'instructorArr' => $this->instructorArr,
            'nien_khoa' => $this->nien_khoa,
            'academic_year_arr' => $this->academic_year_arr,
        ]);
    }

    public function update(Request $request, Topic $topic)
    {
        if (!$request->get('name')) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'name' => 'Tên đề tài không được bỏ trống!',
                ]);
        }

        if(!$request['user_id']){
            return redirect()->back()
            ->withErrors([
                'department'=> "Vui lòng chọn giảng viên hướng dẫn",
            ]);
        }

        if (!$request->get('department')) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'department' => 'Vui lòng chọn bộ môn!',
                ]);
        }



        if (!$request->get('number_student')) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'number_student' => 'Số lượng SV không được bỏ trống!',
                ]);
        }

        // request()->validate([
        //     'name' => 'required',
        //     'department' => 'required',
        //     'number_student' => 'required'
        // ]);

        $students = DB::table('user_register_topics')
        ->join('users', 'user_register_topics.user_id', '=', 'users.id')
        ->where('user_register_topics.topic_id', $topic->id)
        ->get();

       
        $data = $request->only('_token', '_method', 'name', 'department', 'number_student', 'note', 'required', 'user_id');
        $data['department'] = $this->departmentArr[$data['department']];
        // isset($request['user_id']) ? ($data['user_id'] = $request['user_id']) : '';
        $data['academic_year'] = $request->academic_year;
        $data['status'] = (count($students) == $data['number_student']) ? 0 : 1;

        $topic->update($data);
        Toastr::success('Cập nhật đề tài thành công');

        return redirect()->route($request->routeRedirect, $topic);
    }

    //Instructuor
    public function instructorTopicList () {
        $user = Auth::User();
        $topics = Topic::where('user_id', $user->id)->paginate(10);
        $param = ['user_id' => $user->id];
        $instructorName = auth()->user()->name;
        return view('topics.instructor.show', compact('user', 'param', 'topics', 'instructorName'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function instructorShow (Topic $topic) {
        $user_id = $topic->user_id;
        $user = User::where('id', '=', $user_id)->get();
        $students = DB::table('user_register_topics')
        ->join('users', 'user_register_topics.user_id', '=', 'users.id')
        ->where('user_register_topics.topic_id', $topic->id)
        ->get();
        return view('topics.instructor.show_details', ['topic' => $topic, 'user' => $user[0], 'students' => $students, 'i' => 0]);
    }

    public function instructorEdit (Topic $topic) {
        $user_id = $topic->user_id;
        $user = User::where('id', '=', $user_id)->get();
        $departmentArr = ($this->departmentArr);
    
        return view('topics.instructor.edit', [
            'topic' => $topic, 
            'user' => $user[0], 
            'departmentArr' => $departmentArr,
            'academic_year_arr' => $this->academic_year_arr,
        ]);
    }

    public function instructorCreate (Topic $topic) {
        $departmentArr = ($this->departmentArr);
        $roles = Role::where('id', ">", Auth::user()->role)->pluck('name', 'id')->all();
        return view("topics.instructor.create", [
            'roles' => $roles,
            'departmentArr' => $this->departmentArr,
            'nien_khoa' => $this->nien_khoa,
        ]);
    }

    public function destroy($id)
    {
        Topic::where('id', '=', $id)->delete();
        return redirect()->back()->with('Success', 'Xóa đề tài thành công');
    }

    public function export(Request $request)
    {
        $param = $request->all();
        return Excel::download(new ResearchsExport($param), 'DS_de_tai.xlsx');
    }

    public function awardMedal(Request $request)
    {
        // dd($request->all());
        if ($request->has('medal_id')) {
            $rs = $request->get('medal_id');
            foreach ($rs as $k => $r) {
                $research = Research::find($k);
                $research->update(['medal_id' => $r ?? 0]);
            }
        }
        return back();
    }
    public function awardMedalGroup(Request $request)
    {
        if ($request->has('group_id')) {
            $group_id = $request->get('group_id');
            $medals = $request->get('medals');
            $sum = array_sum($medals);
            $rs = Research::where('group_id', $group_id)->orderBy('point', 'DESC')->get();
            $d = 0;
            foreach ($rs as $k => $r) {
                if ($k >= $sum)
                    $r->update(['medal_id' => 0]);
            }
            foreach ($medals as $k => $m) {
                for ($i = 0; $i < $m; $i++) {
                    $rs[$d]->update(['medal_id' => $k]);
                    $d++;
                }
            }
        }
        return back();
    }
}
