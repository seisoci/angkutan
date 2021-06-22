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
      <div class="mb-10">
        <form action="{{ route('backend.ledger.index') }}" method="GET">
        <div class="card">
          <h5 class="card-header bg-primary-o-60">Featured</h5>
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-12">
                <div class="row align-items-center">
                  <div class="col-md-6 my-md-0">
                    <div class="form-group row">
                      <label class="col-md-3 align-self-center">Priode:</label>
                      <div class=" col-md-9">
                        <div class="input-daterange input-group">
                          <div class="input-group-prepend">
                        <span class="input-group-text">
											    <i class="la la-calendar-check-o"></i>
                        </span>
                          </div>
                          <input type="text" class="form-control" name="date_begin" id="dateBegin" readonly/>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer  d-flex justify-content-end">
            <button type="submit" class="btn btn-sm btn-primary">Submit</button>
          </div>
        </div>
        </form>
      </div>

      <!--begin: Datatable-->
      @foreach($data as $keyCollection => $itemCollection)
        @foreach ($itemCollection->children as $itemParent)
          <div class="table-responsive mb-10">
            <span
              class="d-bloc pt-2 font-size-lg font-weight-bolder">Kode Akun: {{ $itemParent->code }}</span>
            <span
              class="d-block pb-2 font-size-lg font-weight-bolder">Nama Akun: {{ $itemParent->name }}</span>
            <table class="table table-bordered">
              <thead>
              <tr>
                <th scope="row" rowspan="2" class="text-center align-middle">Tanggal</th>
                <th scope="row" rowspan="2" class="text-center align-middle">Keterangan</th>
                <th scope="row" rowspan="2" class="text-center align-middle">Debit</th>
                <th scope="row" rowspan="2" class="text-center align-middle">Kredit</th>
                <th colspan="2" class="text-center">Saldo</th>
              </tr>
              <tr>
                <th class="text-center" style="min-width: 100px">Debit</th>
                <th class="text-center" style="min-width: 100px">Kredit</th>
              </tr>
              </thead>
              <tbody>
              @php
                $saldo = 0;
                $saldo += $itemParent->rest_balance ?? 0;
              @endphp
              @if(isset($itemParent->rest_balance))
                <tr>
                  <td>-</td>
                  <td>Sisa saldo bulan sebelumnya</td>
                  <td
                    class="text-right">{{ $itemParent->rest_balance >= 0 ? number_format($itemParent->rest_balance, 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right">{{ $itemParent->rest_balance < 0 ? number_format($itemParent->rest_balance, 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right">{{ ($itemParent->rest_balance >= 0) ? number_format($saldo, 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right">{{ ($itemParent->rest_balance < 0) ? number_format($saldo, 2,'.',',') : NULL }}</td>
                </tr>
              @endif
              @foreach($itemParent->journal as $itemChildren)
                @php
                  if($itemParent->normal_balance == 'Db'){
                   if($itemChildren->debit != 0){
                       $saldo += $itemChildren->debit;
                   }else{
                       $saldo -= $itemChildren->kredit;
                   }
                  }else{
                    if($itemChildren->debit != 0){
                       $saldo -= $itemChildren->debit;
                   }else{
                       $saldo += $itemChildren->kredit;
                   }
                  }
                @endphp
                <tr>
                  <td style="width: 75px">{{ $itemChildren->date }}</td>
                  <td style="min-width: 300px">{{ $itemChildren->description }}</td>
                  <td
                    class="text-right"
                    style="min-width: 100px">{{ $itemChildren->debit != 0 ? number_format($itemChildren->debit, 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right"
                    style="min-width: 100px">{{ $itemChildren->kredit != 0 ? number_format($itemChildren->kredit, 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right"
                    style="min-width: 100px">{{ ($itemParent->normal_balance == 'Db' && $saldo >= 0) ? number_format($saldo, 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right"
                    style="min-width: 100px">{{ ($itemParent->normal_balance == 'Kr' && $saldo >= 0) ? number_format($saldo, 2,'.',',') : NULL }}</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        @endforeach
      @endforeach
    </div>
  </div>
  {{-- Modal --}}
@endsection
{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
  <style>
    .table thead th {
      font-size: 0.80rem !important;
    }

    .table tbody td {
      font-size: 0.75rem !important;
    }
  </style>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {

      $('#dateBegin').datepicker({
        format: 'M yyyy',
        viewMode: 'months',
        minViewMode: 'months'
      });

    });
  </script>
@endsection
