@extends('layouts.app')


@section('content')

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif

<div class="card">
    <div class="card-header boder-0">
        <div class="float-left">
            <h2>Quản lý tài khoản</h2>
        </div>
        @can('user-create')
        <div class="float-right">
            <a class="btn btn-success" href="{{ route('users.create') }}">Tạo tài khoản</a>
        </div>
        @endcan

        <!-- <div class="col text-right">
            <a class="btn btn-primary" href="javascript:history.back()"> Quay lại</a>
        </div> -->
    </div>

    <div class="card-body">
        <table class="table table-bordered table-hover">
            <tr class="badge-default">
                <th>STT</th>
                <th>Họ và tên</th>
                <th>Tên đăng nhập</th>
                <th>Vai trò</th>
                <th>SL đề tài</th>
                <th width="270px">Thao tác</th>
            </tr>
            
            @foreach ($users as $key => $user)
            <tr>
                <td class="align-middle">{{ ++$i }}</td>
                <td class="align-middle">{{ $user->name }}</td>
                <td class="align-middle">{{ $user->username }}</td>
                <td class="align-middle">
                    @if(!empty($user->getRoleNames()))
                    @foreach($user->getRoleNames() as $v)
                    <div class="badge badge-success">{{ $v }}</div>
                    @endforeach
                    @endif
                </td>
                <td class="text-center align-middle">{{$user->topics_count}}</td>
                <td class="align-middle">
                    <a class="btn btn-info" href="{{ route('users.show', $user) }}">Xem</a>
                    @can('user-edit')
                    <a class="btn btn-primary" href="{{ route('users.edit', $user->id) }}">Sửa</a>
                    @endcan

                    @can('user-delete')
                    {!! Form::open(['method' => 'DELETE',
                                    'route' => ['users.destroy', $user->id],
                                    'style'=>'display:inline', 
                                    'onsubmit' => "return confirmDelete('Bạn có chắc muốn tài khoản có tên \'$user->name\' không?')", 
                                   ])!!}
                    {!! Form::submit('Xóa', ['class' => 'btn btn-danger confirm']) !!}
                    {!! Form::close() !!}
                    @endcan
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>

{!! $users->render() !!}
@endsection




