@extends('layouts.app')

@section('content')

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif

<div class="card">
    <div class="card-header border-0">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="mb-0">Đăng ký đề tài khóa luận tốt nghiệp</h3>
            </div>
            <!-- <div class="col text-right">
                <a href="#!" class="btn btn-sm btn-primary">See all</a>
            </div> -->
        </div>
    </div>
    <div class="card-body">
        <div class="row">            
            <div class="col-lg-12">
                <p>
                    <span class="blockquote" style="color: red;">
                    <b>Chú ý:</b> Chưa đến thời gian đăng kí khóa luận tốt nghiệp. Xin vui lòng quay lại sau!
                    </span>
                </p>
            </div>
        </div>
        <div class="row">            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <tr class="badge-default">
                        <th class="text-align-center">STT</th>
                        <th style="width: 30%;" class="text-align-center">
                            <a class="thead" href="#">    
                                <div class="col-xl-12">
                                    Tên đề tài  <i class="fas fa-arrows-alt-v"></i>
                                </div>
                            </a>
                        </th>
                        <th style="width: 30%;" class="text-align-center">
                            <a class="thead" href="#">    
                                <div class="col-xl-12">
                                    Yêu cầu  <i class="fas fa-arrows-alt-v"></i>
                                </div>
                            </a>
                        </th>
                        <th class="text-align-center">
                            <a class="thead" href="#">    
                                <div class="col-xl-12">
                                    Bộ môn  <i class="fas fa-arrows-alt-v"></i>
                                </div>
                            </a>
                        </th>
                        <!-- <th>Số lượng SV</th>
                        <th>Ghi chú</th> -->
                        <th class="text-align-center">
                            <a class="thead" href="#">    
                                <div class="col-xl-12">
                                    Giảng viên hướng dẫn  <i class="fas fa-arrows-alt-v"></i>
                                </div>
                            </a>
                        </th>
                        <th class="text-align-center">
                            <a class="thead" href="#">    
                                <div class="col-xl-12">
                                    Trạng thái  <i class="fas fa-arrows-alt-v"></i>
                                </div>
                            </a>
                        </th>
                    </tr>
                    <?php
                        $i = 0;
                    ?>
                    @foreach ($topics2 as $key => $topic)
                    <tr>
                        <td class="text-align-center">{{ ++$i }}</td>
                        @if($topic->status == '1')
                            <td class="align-middle">
                                <a class="text-primary" rel="noopener noreferrer">{{ $topic->name }}</a>
                                <br><a> {{ $topic->description }} </a>
                            </td>
                        @else
                            <td class="align-middle">
                                <a class="text-danger" rel="noopener noreferrer">{{ $topic->name }}</a>
                                <br><a> {{ $topic->description }} </a>
                            </td>   
                        @endif 
                        <td class="text-align-center">{{ $topic->required }}</td>
                        <td class="text-align-center">{{ $topic->department }}</td>
                        <td class="text-align-center">{{ $topic->instructor_name }}</td>
                        <td class="text-align-center">
                            @if($topic->status == '1')
                            <div class="badge badge-success">Có thể đăng ký</div>   
                            @else
                            <div class="badge badge-danger">Đã đủ SV</div>   
                            @endif             
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>

@endsection