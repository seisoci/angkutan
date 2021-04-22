{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<!-- begin::Card-->
<div class="card card-custom overflow-hidden">
  <div class="card-body p-0">
    <!-- begin: Invoice-->
    <!-- begin: Invoice header-->
    <div class="row justify-content-center py-8 px-8 py-md-27 px-md-0">
      <div class="col-md-9">
        <div class="d-flex justify-content-between pb-10 pb-md-20 flex-column flex-md-row">
          <h1 class="display-4 font-weight-boldest mb-10">INVOICE <br>PURCHASE ORDER</h1>
          <div class="d-flex flex-column align-items-md-end px-0">
            <!--begin::Logo-->
            <a href="#" class="mb-5">
              <img
                src="{{ $profile['logo_url'] != NULL ? asset("/images/thumbnail/".$profile['logo_url']) : asset('media/bg/no-content.svg') }}"
                width="75px" height="75px" />
            </a>
            <!--end::Logo-->
            <span class="d-flex flex-column align-items-md-end opacity-70">
              <span>{{ $profile['name'] ?? '' }}</span>
              <span>{{ $profile['telp'] ?? ''}}</span>
              <span>{{ $profile['email'] ?? '' }}</span>
              <span>{{ $profile['address'] ?? '' }}</span>
            </span>
          </div>
        </div>
        <div class="border-bottom w-100"></div>
        <div class="d-flex justify-content-between pt-6">
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">Tanggal Note</span>
            <span class="opacity-70">{{ $data->invoice_date ?? '' }}</span>
          </div>
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">Tanggal Jatuh Tempo</span>
            <span class="opacity-70">{{ $data->due_date ?? '' }}</span>
          </div>
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">NO. INVOICE</span>
            <span class="opacity-70">{{ $data->prefix_invoice ?? '' }}</span>
          </div>
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">METODE</span>
            <span class="opacity-70">{{ $data->method_payment ?? '' }}</span>
          </div>
          <div class="d-flex flex-column flex-root">
            <span class="font-weight-bolder mb-2">INVOICE PEMBELIAN KE.</span>
            <span class="opacity-70">{{ $data->supplier->name ?? '' }}
              <br />{{ $data->supplier->phone }} <br />{{ $data->supplier->address }}</span>
          </div>
        </div>
      </div>
    </div>
    <!-- end: Invoice header-->
    <!-- begin: Invoice body-->
    <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0">
      <div class="col-md-9">
        <div class="table-responsive">
          <h4 class="font-weight-bold text-uppercase">PEMBELIAN BARANG</h4>
          <table class="table">
            <thead>
              <tr>
                <th class="pl-0 font-weight-bold text-muted text-uppercase">Barang</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">Unit</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">Harga</th>
                <th class="text-right pr-0 font-weight-bold text-muted text-uppercase">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data->purchases as $item)
              <tr class="font-weight-boldest">
                <td class="pl-0 pt-7">{{ $item->sparepart->name }}</td>
                <td class="text-right pt-7">{{ $item->qty }}</td>
                <td class="text-right pt-7">{{ number_format($item->price, 2, ',', '.') }}</td>
                <td class="text-danger pr-0 pt-7 text-right">{{ number_format($item->qty * $item->price, 2, ',', '.') }}
                </td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td></td>
                <td></td>
                <td class="text-right pr-0 font-weight-bold text-uppercase">Diskon</td>
                <td class="text-success text-right pr-0 font-weight-bold text-uppercase">
                  {{ number_format($data->discount ?? 0,2, ',', '.') }}</td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td class="text-right pr-0 font-weight-bold text-muted text-uppercase">Total Tagihan</td>
                <td class="text-danger text-right pr-0 font-weight-bold text-uppercase">
                  {{ number_format($data->total_bill ?? 0,2, ',', '.') }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div class="table-responsive my-10">
          <h4 class="font-weight-bold text-uppercase">Pembayaran</h4>
          <table class="table">
            <thead>
              <tr>
                <th class="pl-0 font-weight-bold text-muted text-uppercase">Tanggal</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">Nominal</th>
                <th class="text-right pr-0 font-weight-bold text-muted text-uppercase">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data->purchasepayments as $item)
              <tr class="font-weight-boldest">
                <td class="text-right pt-7">{{ $item->date_payment }}</td>
                <td class="text-right pt-7">{{ number_format($item->payment, 2, ',', '.') }}</td>
                <td class="pr-0 pt-7 text-right">
                  {{ number_format($item->payment, 2, ',', '.') }}
                </td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td></td>
                <td class="text-right pr-0 font-weight-bold text-muted text-uppercase">Total Tagihan</td>
                <td class="text-right pr-0 font-weight-bold text-muted text-uppercase">
                  {{ number_format($data->total_bill ?? 0,2, ',', '.') }}</td>
              </tr>
              <tr>
                <td></td>
                <td class="text-right pr-0 font-weight-bold text-muted text-uppercase">Total Pembayaran</td>
                <td class="text-right pr-0 font-weight-bold text-muted text-uppercase">
                  {{ number_format($data->total_payment ?? 0,2, ',', '.') }}</td>
              </tr>
              <tr>
                <td></td>
                <td class="text-right pr-0 font-weight-bold text-muted text-uppercase">Sisa Tagihan</td>
                <td class="text-right pr-0 font-weight-bold text-muted text-uppercase">
                  {{ number_format($data->rest_payment ?? 0,2, ',', '.') }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
    <!-- end: Invoice body-->
    <!-- begin: Invoice action-->
    <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0 d-print-none">
      <div class="col-md-9">
        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-primary font-weight-bold" onclick="window.print();">Print
            Invoice</button>
        </div>
      </div>
    </div>
    <!-- end: Invoice action-->
    <!-- end: Invoice-->
  </div>
</div>
@endsection

{{-- Styles Section --}}
@section('styles')
@endsection

{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script>
  $(document).ready(function(){
    $('body').addClass('print-content-only');
  });
</script>
{{-- page scripts --}}
@endsection
