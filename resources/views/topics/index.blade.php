 @extends('layouts.app')


@section('content')
<div class="card">
    <div class="card-header border-0">
        <div class="float-left">
            <h2>Quản lý đề tài</h2>
        </div>
        @can('topic-create')
        <div class="float-right">
            <a class="btn btn-success" href="{{ route('topics.create') }}">Tạo đề tài</a>
        </div>
        @endcan
    </div>

    @if(Session::has('Success'))
        <div class="alert alert-success" role="alert">
            {{ Session::get('Success') }}
        </div>
    @endif

    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover">
            <tr class="badge-default">
                <th>STT</th>
                <th>Tên đề tài</th>
                <th>Bộ môn</th>
                <th>Số lượng SV</th>
                <th>Ghi chú</th>
                <th>GVHD</th>
                <th>Trạng thái</th>
                <th width="270px">Thao tác</th>
            </tr>
            
            @foreach ($topics as $key => $topic)
            <tr>
                <td class="align-middle">{{ ++$i }}</td> 
                <td class="align-middle">{{ $topic->name }}</td>
                <td class="align-middle">{{ $topic->department }}</td>
                <td class="align-middle">{{ $topic->number_student }}</td>
                <td class="align-middle">{{ $topic->note }}</td>
                <td class="align-middle">{{ $topic->instructor_name }}</td>
                <td class="align-middle">
                    @if($topic->status == '0')                
                        <div class="badge badge-danger">Đã đủ SV</div>                
                    @else
                        <div class="badge badge-success">Có thể đăng ký</div>                
                    @endif
                </td>

                <td>
                    <a href="{{ route('topics.show', $topic->id) }}" class="btn btn-info mr-2">Xem</a>
                    <a href="{{ route('topics.edit', $topic->id ) }}" class="btn btn-primary mr-2">Sửa</a>
                    {{-- <a href="{{ route('topics.destroy', $topic->id) }}" class="btn btn-danger">Xóa</a> --}}

                    {!! Form::open(
                        ['method' => 'DELETE', 
                        'route' => ['topics.destroy', $topic->id], 
                        'onsubmit' => "return confirmDelete('Bạn có chắc muốn xóa đề tài \'$topic->name\' của giảng viên \'$topic->instructor_name\' không?')",
                        'style' => 'display:inline']) !!}
                    {!! Form::submit('Xóa', ['class' => 'btn btn-danger confirm']) !!}
                    {!! Form::close() !!}
                </td>
                
            </tr>
            @endforeach
        </table>
    </div>
</div>


@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif

{!! $topics->render() !!}

@endsection