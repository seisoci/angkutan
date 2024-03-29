@extends('layout.default')

@section('content')
  <div class="card card-custom overflow-hidden">
    <div class="card-header d-flex justify-content-end align-items-center">
      <div class="">
        <div class="btn-group btn-group-md" role="group" aria-label="Large button group">
          <button onclick="window.history.back();" type="button" class="btn btn-outline-secondary"><i
              class="fa fa-arrow-left"></i> Back
          </button>


          <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
              <i class="fa fa-print"></i>Print
            </button>
            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
              <a href="{{ $config['print_url'] }}" target="_blank" class="dropdown-item">Biasa</a>
              <a href="#" id="btn_print" class="dropdown-item" target="_blank">
                Dot Matrix
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="row justify-content-center py-8 px-8 px-md-0">
        <div class="col-md-11">
          <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>SLIP GAJI</u></h2>
          <table class="table table-borderless table-title">
            <tbody>
            <tr>
              <td class="font-weight-bolder text-uppercase"
                  style="width:50%">{{ $cooperationDefault['nickname'] ?? '' }}
              </td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left pl-20" style="width:20%">Tanggal</td>
              <td class="text-left" style="width:2%">: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->invoice_date }}</td>
            </tr>
            <tr>
              <td style="width:50%">{{ $cooperationDefault['address'] ?? '' }}</td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left pl-20" style="width:20%">No. Referensi</td>
              <td class="text-left" style="width:2%">: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->num_invoice }}</td>
            </tr>
            <tr>
              <td>{{ $cooperationDefault['phone'] ?? ''}}</td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left pl-20" style="width:20%">Nama Supir</td>
              <td class="text-left" style="width:2%">: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->driver->name }}</td>
            </tr>
            <tr>
              <td>FAX {{ $cooperationDefault['fax'] ?? ''}}</td>
              <td class="text-left" style="width:10%"></td>
            </tr>
            </tbody>
          </table>
          <div class="separator separator-solid separator-border-1"></div>
          <table class="table">
            <thead>
            <tr>
              <th style="width:5%">#</th>
              <th style="width:10%">Tgl Muat</th>
              <th style="width:15%">No. Job Order</th>
              <th style="width:10%">No. Pol</th>
              <th style="width:30%">PELANGGAN</th>
              <th style="width:30%">RUTE</th>
              <th class="text-right" style="width:15%">JUMLAH</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($data['joborders'] ?? [] as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->date_begin }}</td>
                <td>{{ $item->num_bill }}</td>
                <td>{{ $item->transport->num_pol }}</td>
                <td>{{ $item->costumer->name }}</td>
                <td>{{ $item->routefrom->name }} -> {{ $item->routeto->name }}</td>
                <td class="text-right">{{ number_format($item->total_salary ?? 0, 2, ',', '.') }}</td>
              </tr>
            @endforeach
            <tr>
              <td colspan="5" class="text-left font-weight-bolder">
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

@section('styles')
  <style>
    .table-title td,
    th {
      padding: 0;
    }
  </style>
@endsection

@section('scripts')
  <script>
    $(document).ready(function () {
      $('#btn_print').on('click', function (e) {
        e.preventDefault();
        $.ajax({
          url: "{{ $config['print_dotMatrix_url'] }}",
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
@endsection
