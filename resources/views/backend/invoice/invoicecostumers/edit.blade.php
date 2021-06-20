{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
  {{-- Dashboard 1 --}}
  <!--begin::Card-->
  <div class="card card-custom mt-6">
    <div class="card-header flex-wrap py-3">
      <div class="card-title">
        <h3 class="card-label">{{ $config['page_title'] }}
        </h3>
      </div>
      <div class="card-toolbar">
      </div>
    </div>
    <div class="card-body">
      <form id="formUpdate" action="{{ route('backend.invoicecostumers.update', Request::segment(3)) }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @method('PUT')
        <div class="row align-items-center border border-dark py-10 px-4">
          <div class="col-12">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-4 col-form-label">Tanggal Invoice:</label>
                  <div class="col-md-6">
                    <input type="text" class="form-control rounded-0 w-100" name="invoice_date"
                           value="{{ $data->invoice_date }}" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-4 col-form-label">Prefix:</label>
                  <div class="col-lg-6">
                    <input type="text" class="form-control rounded-0 w-100" name="invoice_date"
                           value="{{ $data->prefix }}" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-4 col-form-label">No. Invoice Tagihan:</label>
                  <div class="col-lg-6">
                    <input class="form-control rounded-0" value="{{ $data->num_bill }}" disabled>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-3  col-form-label">Tgl Jatuh Tempo:</label>
                  <div class="col-lg-9">
                    <input class="form-control rounded-0" value="{{ $data->due_date }}" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Pelanggan:</label>
                  <div class="col-lg-9">
                    <input class="form-control rounded-0" value="{{ $data->costumer->name }}" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Memo:</label>
                  <div class="col-lg-9">
                    <textarea name="memo" class="form-control rounded-0" disabled>{{ $data->memo }}</textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table id="table_invoice" class="table table-striped">
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
              </tbody>
              <tfoot>
              <tr>
                <td colspan="10" class="text-right">Total</td>
                <td class="text-right currency">{{ $data->joborders->sum('tax_amount') }}</td>
                <td class="text-right currency">{{ $data->joborders->sum('fee_thanks') }}</td>
                <td class="text-right currency">{{ $data->joborders->sum('total_basic_price') }}</td>
              </tr>
              </tfoot>
            </table>
          </div>
          <h2 class="pt-10"><u>Pembayaran</u></h2>
          <div class="table-responsive">
            <table class="table table-borderless">
              <thead>
              <tr>
                <th scope="col" style="min-width: 150px">Tanggal Pembayaran</th>
                <th scope="col" style="min-width: 200px">Keterangan</th>
                <th scope="col" style="min-width: 150px">Master Akun</th>
                <th scope="col" style="min-width: 150px">Nominal</th>
                <th scope="col" style="min-width: 150px">Total Dibayar</th>
              </tr>
              </thead>
              <tbody>
              @foreach($data->paymentcostumers as $item)
                <tr>
                  <td><input type="text" class="form-control rounded-0 datepicker w-100" placeholder="Tanggal Invoice"
                             disabled value="{{ $item->date_payment }}"></td>
                  <td><input class="form-control rounded-0" value="{{ $item->description }}" disabled></td>
                  <td><input type="text" class="form-control rounded-0" value="{{ $item->coa->code." - ".$item->coa->name }}" disabled></td>
                  <td><input type="text" class="currency rounded-0 form-control" value="{{ $item->payment }}" disabled>
                  </td>
                  <td><input type="text" class="currency rounded-0 form-control"
                             value="{{ $item->payment }}" disabled>
                  </td>
                </tr>
              @endforeach
              <tr>
                <td><input type="text" class="form-control rounded-0 datepicker w-100" name="payment[date_payment]"
                           placeholder="Tanggal Invoice" readonly></td>
                <td><input name="payment[description]" class="form-control rounded-0"></td>
                <td><select name="coa_id" class="form-control rounded-0" style="min-width: 250px">
                    @foreach($selectCoa->coa as $item)
                      <option value="{{ $item->id }}">{{ $item->code .' - '. $item->name }}</option>
                    @endforeach
                  </select></td>
                <td><input type="text" name="payment[payment]" class="currency rounded-0 form-control"></td>
                <td><input type="text" name="payment[total_payment]" class="currency rounded-0 form-control totalPayment" disabled>
                </td>
              </tr>
              </tbody>
              <tfoot>
              <tr>
                <td colspan="4" class="text-right">Total Tagihan</td>
                <td class="text-right"><input type="text" name="total_bill" class="currency rounded-0 form-control"
                                              value="{{ $data->total_bill }}"
                                              disabled></td>
              </tr>
              <tr>
                <td colspan="4" class="text-right">Total Pemotongan Klaim</td>
                <td class="text-right"><input type="text" name="total_cut" class="currency rounded-0 form-control"
                                              value="{{ $data->total_cut }}" disabled>
                </td>
              </tr>
              <tr>
                <td colspan="4" class="text-right">Total Pembayaran</td>
                <td class="text-right"><input type="text" class="currency rounded-0 form-control total_payment"
                                              value="{{ $data->total_payment }}" disabled>
                </td>
              </tr>
              <tr>
                <input type="hidden" name="rest_payment" class="currency rounded-0 form-control rest_payment"
                       value="{{ $data->rest_payment }}">
                <td colspan="4" class="text-right">Sisa Pembayaran</td>
                <td class="text-right"><input type="text" class="currency rounded-0 form-control rest_payment" disabled
                                              value="{{ $data->rest_payment }}">
                </td>
              </tr>
              <tr>
                <td colspan="5" class="text-right">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </td>
              </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
  <style>
    .select2-container--default .select2-selection--single {
      border-radius: 0 !important;
    }
  </style>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>

  {{-- page scripts --}}
  <script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
  <script type="text/javascript">
    $(document).ready(function () {
      initCurrency();
      initDate();
      initCalculate();

      function initDate() {
        $('.datepicker').datepicker({
          format: 'yyyy-mm-dd',
          todayBtn: "linked",
          clearBtn: true,
          todayHighlight: true,
        });
      }

      function initCurrency() {
        $(".currency").inputmask('decimal', {
          groupSeparator: '.',
          digits: 2,
          rightAlign: true,
          removeMaskOnSubmit: true,
          autoUnmask: true,
        });
      }

      function initCalculate() {
        let total_bill = parseFloat($('input[name="total_bill"]').val()) || 0;
        let total_fee = parseFloat($('input[name="total_fee"]').val()) || 0;
        let total_cut =  parseFloat('{{ $data->total_cut }}');
        let totalPayment = parseFloat('{{ $data->total_payment }}');
        let totalTax = parseFloat('{{ $data->total_tax }}');
        let payment = parseFloat($('input[name="payment[payment]"]').val()) || 0;
        let grandTotal = totalPayment + payment;
        let rest_payment = total_bill - total_cut - totalPayment - payment;
        $('.totalPayment').val(payment);
        $('.total_payment').val(grandTotal);
        $('.rest_payment').val(rest_payment);
        $('input[name=total_bill]').val(total_bill);
      }

      $('input[name="payment[payment]"],input[name="total_cut"],#diskon').on('keyup', function () {
        initCalculate();
      });

      $("#formUpdate").submit(function (e) {
        $('.currency').inputmask('remove');
        e.preventDefault();
        let form = $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        let spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
        let url = form.attr("action");
        let data = new FormData(this);
        $.ajax({
          beforeSend: function () {
            btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading...").prop("disabled", "disabled");
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          cache: false,
          processData: false,
          contentType: false,
          type: "POST",
          url: url,
          data: data,
          success: function (response) {
            initCurrency();
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            if (response.status === "success") {
              toastr.success(response.message, 'Success !');
              setTimeout(function () {
                if (response.redirect === "" || response.redirect === "reload") {
                  location.reload();
                } else {
                  location.href = response.redirect;
                }
              }, 1000);
            } else {
              $("[role='alert']").parent().removeAttr("style");
              $(".alert-text").html('');
              $.each(response.error, function (key, value) {
                $(".alert-text").append('<span style="display: block">' + value + '</span>');
              });
              toastr.error((response.message || "Please complete your form"), 'Failed !');
            }
          }, error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
            initCurrency();
          }
        });
      });

    });
  </script>
@endsection
