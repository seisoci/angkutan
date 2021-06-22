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
      </div>
    </div>

    <div class="card-body">
      <!--begin: Datatable-->
      <div class="table-responsive  d-flex justify-content-center">
        <table class="table table-bordered" style="width: 50%">
          <thead>
          <tr>
            <th>Nama Akun</th>
            <th>Saldo Saat ini</th>
          </tr>
          </thead>
          <tbody>
          @foreach($data as $item)
            <tr>
              <td>{{ $item->name }}</td>
              <td class="text-right">{{ number_format($item->balance, 2, '.', ',') }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  {{-- Modal --}}
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
@endsection
