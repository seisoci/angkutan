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
        <table class="table table-bordered">
          <thead>
          <tr>
            <th colspan="3" class="text-center font-weight-bolder">Neraca Saldo</th>
          </tr>
          <tr>
            <th class="font-weight-bolder">Nama Akun</th>
            <th class="font-weight-bolder">Debit</th>
            <th class="font-weight-bolder">Kredit</th>
          </tr>
          </thead>
          <tbody>
          @foreach($data as $item)
            <tr>
              <td class="font-weight-bold">{{ $item->name }}</td>
              <td class="text-right font-weight-bold">{{ number_format($item->debit, 2, '.', ',') }}</td>
              <td class="text-right font-weight-bold">{{ number_format($item->kredit, 2, '.', ',') }}</td>
            </tr>
          @endforeach
          </tbody>
          <tfoot class="border-bottom">
          <tr>
            <td class="text-right font-weight-bolder">Total</td>
            <td class="text-right font-weight-bolder">{{ number_format($data->sum('debit'), 2, '.', ',') }}</td>
            <td class="text-right font-weight-bolder">{{ number_format($data->sum('kredit'), 2, '.', ',') }}</td>
          </tr>
          </tfoot>
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
