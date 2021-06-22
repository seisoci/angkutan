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
        </div>
      </div>
    </div>
    {{-- Body --}}
    <div class="card-body p-0">
      <!-- begin: Invoice header-->
      <div class="row justify-content-center py-8 px-8 px-md-0">
        <div class="col-md-11">
          <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Invoice Pelanggan</u></h2>
          <table class="table table-borderless table-title">
            <tbody>
            <tr>
              <td scope="col" class="font-weight-bolder text-uppercase" style="width:50%">{{ $profile['name'] ?? '' }}
              </td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" class="text-left" style="padding-left:4rem;width:20%">No. Invoice</td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:18%"> {{ $data->num_invoice }}</td>
            </tr>
            <tr>
              <td scope="col" style="width:50%">{{ $profile['address'] ?? '' }}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" class="text-left" style="padding-left:4rem;width:20%">Supplier</td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:18%"> {{ $data->costumer->name }}</td>
            </tr>
            <tr>
              <td scope="col">{{ $profile['telp'] ?? ''}}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" class="text-left" style="padding-left:4rem;width:20%">Tanggal</td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:18%"> {{ $data->created_at }}</td>
            </tr>
            <tr>
              <td scope="col">FAX {{ $profile['fax'] ?? ''}}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" class="text-left" style="padding-left:4rem;width:20%">Tanggal Jth Tempo</td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:18%"> {{ $data->due_date }}</td>
            </tr>
            <tr>
              <td scope="col">Memo : {{ $data->memo ?? ''}}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
            </tr>
            </tbody>
          </table>
          <div class="separator separator-solid separator-border-1"></div>
          <div class="table-responsive">
            <table class="table">
              <thead>
              <tr>
                <th scope="col" class="text-center">#</th>
                <th scope="col">Tanggal</th>
                <th scope="col">S. Jalan</th>
                <th scope="col">Pelanggan</th>
                <th scope="col">Rute Dari</th>
                <th scope="col">Rute Ke</th>
                <th scope="col">Jenis Barang</th>
                <th scope="col">Qty (Unit)</th>
                <th scope="col">Harga Dasar</th>
                <th scope="col">Pajak (%)</th>
                <th scope="col">Pajak (Rp.)</th>
                <th scope="col">Fee</th>
                <th scope="col" class="text-right">Total Tagihan (Rp.)</th>
              </tr>
              </thead>
              <tbody>
              @foreach($data->joborders as $item)
                <tr>
                  <td class="text-center">{{ $loop->iteration }}</td>
                  <td>{{  $item->date_begin }}</td>
                  <td>{{ $item->prefix . '-' . $item->num_bill  }}</td>
                  <td>{{ $item->costumer->name }}</td>
                  <td>{{ $item->routefrom->name }}</td>
                  <td>{{ $item->routeto->name }}</td>
                  <td>{{ $item->cargo->name }}</td>
                  <td class="text-right currency">{{ $item->basic_price }}</td>
                  <td class="text-center">{{ $item->payload }}</td>
                  <td class="text-center">{{ $item->tax_percent ?? 0 }}</td>
                  <td class="text-right currency">{{ $item->tax_amount }}</td>
                  <td class="text-right currency">{{ $item->fee_thanks }}</td>
                  <td class="text-right currency">{{ $item->total_basic_price }}</td>
                </tr>
              @endforeach
              <tr>
                <td colspan="10" class="text-right font-weight-bolder">Total</td>
                <td class="text-right font-weight-bolder currency">{{ $data->total_tax }}</td>
                <td class="text-right font-weight-bolder currency">{{ $data->total_fee_thanks }}</td>
                <td class="text-right font-weight-bolder currency">{{ $data->total_bill  }}</td>
              </tr>
              </tbody>
            </table>
          </div>
          <h4><u>Pembayaran</u></h4>
          <table class="table">
            <thead>
            <tr>
              <th scope="col" width="20%">Tanggal Pembayaran</th>
              <th scope="col" width="30%">Keterangan</th>
              <th scope="col" width="25%" class="text-right">Nominal</th>
              <th scope="col" width="25%" class="text-right">Total Dibayar</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data->paymentcostumers as $item)
              <tr>
                <td>{{ $item->date_payment }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ number_format($item->payment ?? 0,2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->payment ?? 0,2, ',', '.') }}</td>
              </tr>
            @endforeach
            <tr>
              <td colspan="3" class="text-right font-weight-bolder">Total Tagihan</td>
              <td class="text-right font-weight-bolder">{{ number_format($data->total_bill ?? 0,2, ',', '.') }}</td>
            </tr>
            <tr>
              <td colspan="3" class="text-right font-weight-bolder">Total Pemotongan Klaim</td>
              <td class="text-right font-weight-bolder">{{ number_format($data->total_cut ?? 0,2, ',', '.') }}</td>
            </tr>
            <tr>
              <td colspan="3" class="text-right font-weight-bolder">Total Pembayaran</td>
              <td class="text-right font-weight-bolder">{{ number_format($data->total_payment ?? 0,2, ',', '.') }}</td>
            </tr>
            <tr>
              <td colspan="3" class="text-right font-weight-bolder">Sisa Pembayaran</td>
              <td class="text-right font-weight-bolder">{{ number_format($data->rest_payment ?? 0,2, ',', '.') }}</td>
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
  <script type="text/javascript">
    $(document).ready(function () {
      $(".currency").inputmask('decimal', {
        groupSeparator: '.',
        digits: 2,
        rightAlign: true,
        removeMaskOnSubmit: true,
        autoUnmask: true,
      });
    });
  </script>
  {{-- page scripts --}}
@endsection
