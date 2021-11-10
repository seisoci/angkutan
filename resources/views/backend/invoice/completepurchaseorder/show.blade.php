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
          <div class="btn-group">
            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-print"></i> Print</button>
            <ul class="dropdown-menu" role="menu">
              <li><a href="{{ $config['print_url'] }}" class="dropdown-item" target="_blank"> Print</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    {{-- Body --}}
    <div class="card-body p-0">
      <!-- begin: Invoice header-->
      <div class="row justify-content-center py-8 px-8 px-md-0">
        <div class="col-md-11">
          <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark">
            <u>{{ $config['page_title'] ?? '' }}</u></h2>
          <table class="table table-borderless table-title">
            <tbody>
            <tr>
              <td class="font-weight-bolder text-uppercase"
                  style="width:50%">{{ $cooperationDefault['nickname'] ?? '' }}
              </td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%">No. Invoice</td>
              <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->num_invoice }}</td>
            </tr>
            <tr>
              <td style="width:50%">{{ $cooperationDefault['address'] ?? '' }}</td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%">Supplier</td>
              <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->supplier->name }}</td>
            </tr>
            <tr>
              <td>{{ $cooperationDefault['phone'] ?? ''}}</td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%">Tanggal</td>
              <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->invoice_date }}</td>
            </tr>
            <tr>
              <td>FAX {{ $cooperationDefault['fax'] ?? ''}}</td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%"></td>
              <td class="text-left" style="width:2%">&ensp;&ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->due_date }}</td>
            </tr>
            <tr>
              <td>Memo : {{ $data->memo ?? ''}}</td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%"></td>
            </tr>
            </tbody>
          </table>
          <div class="separator separator-solid separator-border-1"></div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col" class="text-center">#</th>
              <th style="min-width: 150px">Tgl Buat Invoice</th>
              <th style="min-width: 150px">No. Invoice</th>
              <th style="width: 100%">Supplier</th>
              <th style="min-width:200px; text-align: right">Total Tagihan</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($data->invoice_purchase as $item)
              <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{  $item->invoice_date }}</td>
                <td>{{ $item->prefix . '-' . $item->num_bill  }}</td>
                <td>{{ $item->supplier->name }}</td>
                <td
                  class="text-right currency">{{ number_format(($item->total_bill - $item->diskon) ?? 0,2, ',', '.') }}</td>
              </tr>
            @endforeach
            <tr>
              <td colspan="4" class="text-right font-weight-bold">Total Tagihan</td>
              <td class="text-right">{{ number_format($data->total_bill ?? 0,2, ',', '.') }}</td>
            </tr>
            </tbody>
          </table>
          <h4 class="font-weight-boldest text-center mb-10 text-uppercase text-dark">Pembayaran</h4>
          <table class="table">
            <thead>
            <tr>
              <th style="width:5%">#</th>
              <th style="width:65%">Tanggal</th>
              <th class="text-right" style="width:10%">Nominal</th>
              <th class="text-right" style="width:10%">Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($data->payment_complete as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->date_payment }}</td>
                <td class="text-right">{{ number_format($item->payment, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->payment, 2, ',', '.') }}</td>
              </tr>
            @endforeach
            <tr>
              <td colspan="3" class="text-right font-weight-bold">Total Tagihan</td>
              <td class="text-right">{{ number_format($data->total_net ?? 0,2, ',', '.') }}</td>
            </tr>
            <tr>
              <td colspan="3" class="text-right font-weight-bold">Total Pembayaran</td>
              <td class="text-right">{{ number_format($data->total_payment ?? 0,2, ',', '.') }}</td>
            </tr>
            <tr>
              <td colspan="3" class="text-right font-weight-bold">Sisa Tagihan</td>
              <td class="text-right">{{ number_format($data->rest_payment ?? 0,2, ',', '.') }}</td>
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
