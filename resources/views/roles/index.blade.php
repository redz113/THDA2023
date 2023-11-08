@extends('layouts.app')


@section('content')
<div class="card">
    <div class="card-header boder-0">
        <div class="float-left">
            <h2>Quản lý quyền</h2>
        </div>
        <div class="d-flex justify-content-end">
            @can('role-create')
            <a class="btn btn-success" href="{{ route('roles.create') }}">Tạo quyền</a>
            @endcan
        
            <a class="btn btn-primary" href="{{ url('/') }}">Quay lại</a>
        </div>
    </div>
</div>


@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif

<div class="card-body boder-0">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <tr>
                <th>STT</th>
                <th>Quyền</th>
                <th class="w-25">Thao tác</th>
            </tr>
            @foreach ($roles as $key => $role)
            <tr>
                <td class="align-middle">{{ ++$i }}</td>
                <td class="align-middle">{{ $role->name }}</td>
                <td>
                    <a class="btn btn-info" href="{{ route('roles.show',$role->id) }}">Xem</a>
                    @can('role-edit')
                    <a class="btn btn-primary" href="{{ route('roles.edit',$role->id) }}">Sửa</a>
                    @endcan
                    @can('role-delete')
                    {!! Form::open(['method' => 'DELETE','route' => ['roles.destroy', $role->id],'style'=>'display:inline'])
                    !!}
                    {!! Form::submit('Xóa', ['class' => 'btn btn-danger']) !!}
                    {!! Form::close() !!}
                    @endcan
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    
    {!! $roles->render() !!}
</div>


@endsection