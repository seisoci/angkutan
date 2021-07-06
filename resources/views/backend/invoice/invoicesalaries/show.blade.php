{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
  <!-- begin::Card-->
  <div class="card card-custom overflow-hidden">
    {{-- Header --}}
    <div class="card-header d-flex justify-content-end align-items-center">
      <div class="">
        <div class="btn-group btn-group-md" role="group" aria-label="Large button group">
          <button onclick="window.history.back();" type="button" class="btn btn-outline-secondary"><i
              class="fa fa-arrow-left"></i> Back
          </button>
          <a href="{{ $config['print_url'] }}" target="_blank" class="btn btn-outline-secondary"><i
              class="fa fa-print"></i> Print</a>
          {{--          <a href="#" id="btn_print" class="btn btn-outline-secondary font-weight-bold" target="_blank">--}}
          {{--                  <span class="navi-icon">--}}
          {{--                    <i class="la la-print"></i>--}}
          {{--                  </span>--}}
          {{--            <span class="navi-text">Print</span>--}}
          {{--          </a>--}}
        </div>
      </div>
    </div>
    {{-- Body --}}
    <div class="card-body p-0">
      <div class="row justify-content-center py-8 px-8 px-md-0">
        <div class="col-md-11">
          <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>SLIP GAJI</u></h2>
          <table class="table table-borderless table-title">
            <tbody>
            <tr>
              <td scope="col" class="font-weight-bolder text-uppercase" style="width:50%">{{ $profile['name'] ?? '' }}
              </td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" class="text-left pl-20" style="width:20%">Tanggal</td>
              <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
              <td scope="col" class="text-left" style="width:18%"> {{ $data->created_at }}</td>
            </tr>
            <tr>
              <td scope="col" style="width:50%">{{ $profile['address'] ?? '' }}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" class="text-left pl-20" style="width:20%">No. Referensi</td>
              <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
              <td scope="col" class="text-left" style="width:18%"> {{ $data->num_invoice }}</td>
            </tr>
            <tr>
              <td scope="col">{{ $profile['telp'] ?? ''}}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" class="text-left pl-20" style="width:20%">Nama Supir</td>
              <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
              <td scope="col" class="text-left" style="width:18%"> {{ $data->driver->name }}</td>
            </tr>
            <tr>
              <td scope="col">FAX {{ $profile['fax'] ?? ''}}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" class="text-left pl-20" style="width:20%">No. Polisi</td>
              <td scope="col" class="text-left" style="width:2%">: &ensp;</td>
              <td scope="col" class="text-left" style="width:18%"> {{ $data->transport->num_pol }}</td>
            </tr>
            </tbody>
          </table>
          <div class="separator separator-solid separator-border-1"></div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col" style="width:5%">#</th>
              <th scope="col" style="width:15%">No. Job Order</th>
              <th scope="col" style="width:5%">KETERANGAN</th>
              <th scope="col" style="width:30%">PELANGGAN</th>
              <th scope="col" style="width:30%">RUTE</th>
              <th scope="col" class="text-right" style="width:15%">JUMLAH</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($data->joborders as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->num_prefix }}</td>
                <td>Gaji</td>
                <td>{{ $item->costumer->name }}</td>
                <td>{{ $item->routefrom->name }} -> {{ $item->routeto->name }}</td>
                <td class="text-right">{{ number_format($item->total_salary ?? 0, 2, ',', '.') }}</td>
              </tr>
            @endforeach
            <tr>
              <td colspan="4" class="text-left font-weight-bolder">
                {{ ucwords(Terbilang::terbilang($data->grandtotal)) }}
              </td>
              <td class="text-right font-weight-bolder text-uppercase">TOTAL DITERIMA:</td>
              <td class="text-right font-weight-bolder">{{ number_format($data->grandtotal ?? 0, 2, '.', ',') }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
  <style>
    .table-title td,
    th {
      padding: 0;
    }
  </style>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script>
    $(document).ready(function () {
      $('#btn_print').on('click', function (e) {
        e.preventDefault();
        $.ajax({
          url: "{{ $config['print_url'] }}",
          success: function (text) {
            console.log(text);
            $.post('http://localhost/dotmatrix/', JSON.stringify({
              printer: 'DotMatrix',
              data: text,
              autocut: true
            }), function (response) {
              console.log(response);
            });
          }
        });
      });
    });
  </script>
  {{-- page scripts --}}
@endsection
