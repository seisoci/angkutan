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
          <select class="form-control" id="select2Bank" style="width: 200px">
          </select>
          <button id="btn_print" class="btn btn-outline-secondary"><i
              class="fa fa-print"></i> Print
          </button>
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
              <td class="font-weight-bolder text-uppercase"
                  style="width:50%">{{ $data->costumer->cooperation->nickname ?? '' }}
              </td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%">No. Invoice</td>
              <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->num_invoice }}</td>
            </tr>
            <tr>
              <td style="width:50%">{{ $data->costumer->cooperation->address ?? '' }}</td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%">Supplier</td>
              <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->costumer->name }}</td>
            </tr>
            <tr>
              <td>{{ $data->costumer->cooperation->phone ?? ''}}</td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%">Tanggal</td>
              <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->created_at }}</td>
            </tr>
            <tr>
              <td>FAX {{ $data->costumer->cooperation->fax ?? ''}}</td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%">Tanggal Jth Tempo</td>
              <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->due_date }}</td>
            </tr>
            <tr>
              <td>Memo : {{ $data->memo ?? ''}}</td>
              <td class="text-left" style="width:10%"></td>
            </tr>
            </tbody>
          </table>
          <div class="separator separator-solid separator-border-1"></div>
          <div class="table-responsive">
            <table class="table">
              <thead>
              <tr>
                <th class="text-center">#</th>
                <th>Tanggal</th>
                <th>No. Job Order</th>
                <th>No. SJ</th>
                <th>No. Shipment</th>
                <th>Pelanggan</th>
                <th>Rute Dari</th>
                <th>Rute Ke</th>
                <th>Jenis Barang</th>
                <th>Qty (Unit)</th>
                <th>Harga Dasar</th>
                <th>Pajak (%)</th>
                <th>Pajak (Rp.)</th>
                <th>Fee</th>
                <th class="text-right">Total Tagihan (Rp.)</th>
              </tr>
              </thead>
              <tbody>
              @foreach($data->joborders as $item)
                <tr>
                  <td class="text-center">{{ $loop->iteration }}</td>
                  <td>{{  $item->date_begin }}</td>
                  <td>{{ $item->prefix . '-' . $item->num_bill  }}</td>
                  <td>{{ $item->no_sj  }}</td>
                  <td>{{ $item->no_shipment  }}</td>
                  <td>{{ $item->costumer->name }}</td>
                  <td>{{ $item->routefrom->name }}</td>
                  <td>{{ $item->routeto->name }}</td>
                  <td>{{ $item->cargo->name }}</td>
                  <td class="text-center">{{ $item->payload }}</td>
                  <td class="text-right currency">{{ $item->basic_price }}</td>
                  <td class="text-center">{{ $item->tax_percent ?? 0 }}</td>
                  <td class="text-right currency">{{ $item->tax_amount }}</td>
                  <td class="text-right currency">{{ $item->fee_thanks }}</td>
                  <td class="text-right currency">{{ $item->total_basic_price }}</td>
                </tr>
                @foreach($item->piutangklaimcustomer as $piutangklaim)
                  <tr>
                    <td></td>
                    <td>
                      <span class="badge {{ $piutangklaim->type == 'tambah' ? 'badge-success' : 'badge-danger' }}">{{ $piutangklaim->type == 'tambah' ? 'Penambahan' : 'Pengurangan' }}</span>
                    </td>
                    <td colspan="12">{{ $piutangklaim->description }}<input type="hidden" name="job_orderid['+jobOrderId+'][tambah][keterangan]" value="{{ $piutangklaim->description }}"></td>
                    <td class="text-right currency">{{ $piutangklaim->amount }}</td>
                  </tr>
                @endforeach
              @endforeach
              <tr>
                <td colspan="12" class="text-right font-weight-bolder">Total</td>
                <td class="text-right font-weight-bolder currency">{{ $data->total_tax }}</td>
                <td class="text-right font-weight-bolder currency">{{ $data->total_fee_thanks }}</td>
                <td
                  class="text-right font-weight-bolder currency">{{ ($data->total_bill + $data->total_piutang)  }}</td>
              </tr>
              </tbody>
            </table>
          </div>
          <h4><u>Pembayaran</u></h4>
          <table class="table">
            <thead>
            <tr>
              <th style="width: 20%">Tanggal Pembayaran</th>
              <th style="width: 30%">Keterangan</th>
              <th style="width: 25%" class="text-right">Nominal</th>
              <th style="width: 25%" class="text-right">Total Dibayar</th>
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
              <td colspan="3" class="text-right font-weight-bolder">Total Piutang Klaim</td>
              <td class="text-right font-weight-bolder text-success">{{ number_format($data->total_piutang ?? 0,2, ',', '.') }}</td>
            </tr>
            <tr>
              <td colspan="3" class="text-right font-weight-bolder">Total Pemotongan Klaim</td>
              <td
                class="text-right font-weight-bolder text-danger">{{ number_format($data->total_cut ?? 0,2, ',', '.') }}</td>
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

    .select2-container--default .select2-selection--single {
      border-radius: 0 !important;
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

      $('#btn_print').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          bank_id: $('#select2Bank').find(':selected').val() || '',
        });
{{--        window.location.href = '{{ $config['print_url'] }}?' + params.toString();--}}
        window.open('{{ $config['print_url'] }}?' + params.toString(), '_blank');

      });

      $("#select2Bank").select2({
        placeholder: "Search Bank",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.banks.select2') }}",
          dataType: "json",
          cache: true,
          data: function (e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      });
    });
  </script>
  {{-- page scripts --}}
@endsection
