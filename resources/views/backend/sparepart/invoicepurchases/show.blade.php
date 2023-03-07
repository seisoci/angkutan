@extends('layout.default')

@section('content')
<div class="card card-custom overflow-hidden">
  <div class="card-header d-flex justify-content-end align-items-center">
    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
      <button onclick="window.history.back();" type="button" class="btn btn-outline-secondary"><i
          class="fa fa-arrow-left"></i> Back
      </button>
      <div class="btn-group" role="group">
        <button id="btnGroupDrop1" type="button" class="btn btn-outline-secondary font-weight-bold dropdown-toggle"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Cetak
        </button>
        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
          <a id="btnPrint" href="#" class="dropdown-item">Print DotMatrix</a>
          <a target="_blank" href="{{ $config['print_url'] }}" class="dropdown-item">Print Biasa</a>
        </div>
      </div>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="row justify-content-center py-8 px-8 px-md-0">
      <div class="col-md-11">
        <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Purchase Order</u></h2>
        <table class="table table-borderless table-title">
          <tbody>
            <tr>
              <td class="font-weight-bolder text-uppercase" style="width:50%">{{ $cooperationDefault['nickname'] ?? '' }}
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
              <td class="text-left" style="padding-left:4rem;width:20%">Metode Pembayaran</td>
              <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->method_payment == 'cash' ? 'Tunai' : 'Kredit' }}</td>
            </tr>
            <tr>
              <td>Deskripsi : {{ $data->description ?? ''}}</td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%">Tanggal Jth Tempo</td>
              <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->due_date }}</td>
            </tr>
            <tr>
              <td></td>
              <td class="text-left" style="width:10%"></td>
              <td class="text-left" style="padding-left:4rem;width:20%">Memo</td>
              <td class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td class="text-left" style="width:18%"> {{ $data->memo }}</td>
            </tr>
          </tbody>
        </table>
        <div class="separator separator-solid separator-border-1"></div>
        <table class="table">
          <thead>
            <tr>
              <th style="width:5%">#</th>
              <th style="width:45%">Produk</th>
              <th class="text-right" style="width: 20%">Deskripsi</th>
              <th class="text-center" style="width:10%">Unit</th>
              <th class="text-right" style="width:10%">Harga</th>
              <th class="text-right" style="width:10%">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data->purchases as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $item->sparepart->name }}</td>
              <td class="text-right">{{ $item['description'] ?? '' }}</td>
              <td class="text-center">{{ $item->qty }}</td>
              <td class="text-right">{{ number_format($item->price,0, ',', '.') }}</td>
              <td class="text-right">{{ number_format($item->qty * $item->price,0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr>
              <td colspan="5" class="text-right font-weight-bold">Diskon</td>
              <td class="text-right">{{ number_format($data->discount ?? 0,2, ',', '.') }}</td>
            </tr>
            <tr>
              <td colspan="5" class="text-right font-weight-bold">Total Tagihan</td>
              <td class="text-right">{{ number_format($data->total_bill ?? 0,2, ',', '.') }}</td>
            </tr>
          </tbody>
        </table>
        <h4>Pembayaran</h4>
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
            @foreach ($data->purchasepayments as $item)
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
    $('#btnPrint').on('click', function (e) {
      e.preventDefault();
      $.ajax({
        url: "{{ $config['print_dotmatrix_url'] }}",
        success: function (text) {
          $.post('http://localhost/dotmatrix/', JSON.stringify({
            printer: 'DotMatrix',
            data: text,
            autocut: true
          }), function (response) {
          });
        }
      });
    });
  });
</script>
{{-- page scripts --}}
@endsection
