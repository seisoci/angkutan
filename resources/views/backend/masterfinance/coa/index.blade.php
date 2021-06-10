{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

  <!--begin::Card-->
  <div class="card card-custom">
    <div class="card-header flex-wrap py-3">
      <div class="card-title">
        <h3 class="card-label">{{ $config['page_title'] }}
          <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
      </div>
      <div class="card-toolbar">
        <!--begin::Button-->
        <a href="{{ route('backend.mastercoa.create') }}" class="btn btn-primary font-weight-bolder">
        <span class="svg-icon svg-icon-md">
          <!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
          <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
               viewBox="0 0 24 24" version="1.1">
            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
              <rect x="0" y="0" width="24" height="24"></rect>
              <circle fill="#000000" cx="9" cy="15" r="6"></circle>
              <path
                d="M8.8012943,7.00241953 C9.83837775,5.20768121 11.7781543,4 14,4 C17.3137085,4 20,6.6862915 20,10 C20,12.2218457 18.7923188,14.1616223 16.9975805,15.1987057 C16.9991904,15.1326658 17,15.0664274 17,15 C17,10.581722 13.418278,7 9,7 C8.93357256,7 8.86733422,7.00080962 8.8012943,7.00241953 Z"
                fill="#000000" opacity="0.3"></path>
            </g>
          </svg>
          <!--end::Svg Icon-->
        </span>New Record</a>
        <!--end::Button-->
      </div>
    </div>

    <div class="card-body">
      <!--begin: Datatable-->
      <table class="table table-borderless">
        <thead>
        <tr>
          <th scope="col" width="100px">Kode Akun</th>
          <th scope="col">Nama Akun</th>
          <th scope="col">Saldo Normal</th>
          <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($collection as $keyCollection => $itemCollection)
          <tr class="table-dark font-weight-bolder text-dark">
            <td colspan="4">{{  "Kelompok ".ucwords($keyCollection) }}</td>
          </tr>
          @foreach ($itemCollection as $itemParent)
            <tr class="table-dark font-weight-bold text-dark">
              <td>{{ $itemParent->code }}</td>
              <td>{{ $itemParent->name }}</td>
              <td>{{ $itemParent->normal_balance }}</td>
              <td></td>
            </tr>
            @foreach($itemParent->children as $itemChildren)
              <tr class="font-weight-bold">
                <td>{{ $itemChildren->code }}</td>
                <td>{{ $itemChildren->name }}</td>
                <td>{{ $itemChildren->normal_balance }}</td>
                <td></td>
              </tr>
            @endforeach
          @endforeach
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {
    });
  </script>
@endsection
