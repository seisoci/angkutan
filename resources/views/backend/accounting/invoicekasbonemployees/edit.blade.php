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
    </div>
    <form id="formUpdate" action="{{ route('backend.invoicekasbonemployees.update', Request::segment(3)) }}">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      @method('PUT')
      <div id="TampungId">
      </div>
      <div class="card-body">
        <div class="mb-10">
          <div class="row align-items-center border border-dark py-10 px-4">
            <div class="col-12">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">Prefix:</label>
                    <div class="col-lg-6">
                      <input type="text" class="form-control rounded-0" value="{{ $data->prefix_invoice }}" disabled>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">No. Kasbon:</label>
                    <div class="col-lg-6">
                      <input name="num_bill" type="hidden" value="{{ Carbon\Carbon::now()->timestamp }}">
                      <input class="form-control rounded-0" value="{{ Carbon\Carbon::now()->timestamp }}" disabled>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">Karyawaan:</label>
                    <div class="col-lg-9">
                      <input type="text" class="form-control rounded-0" value="{{ $data->employee->name }}" disabled>
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
              <table id="table_invoice" class="table table-striped">
                <thead>
                <tr>
                  <th scope="col" class="text-center">#</th>
                  <th scope="col">Tanggal</th>
                  <th scope="col">Nama Karyawaan</th>
                  <th scope="col">Keterangan</th>
                  <th scope="col" class="text-right">Nominal</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($data->kasbonemployees as $item)
                  <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->employee->name }}</td>
                    <td>{{ $item->memo }}</td>
                    <td class="text-right currency">{{ $item->amount }}</td>
                  </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                  <td colspan="4" class="text-right">Total</td>
                  <td class="text-right currency">{{ $data->total_kasbon }}</td>
                </tr>
                </tfoot>
              </table>
              <table class="table table-bordered mt-20">
                <thead>
                <tr>
                  <th class="text-center" scope="col" width="5%">
                    <button type="button"
                            class="addPayment btn btn-sm btn-primary rounded-0">+
                    </button>
                  </th>
                  <th class="text-left" scope="col">Tanggal Pembayaran</th>
                  <th class="text-left" scope="col">Master Akun</th>
                  <th class="text-right" scope="col">Nominal Cicilan</th>
                  <th class="text-right" scope="col">Total Cicilan</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($data->paymentkasbonemployes as $item)
                  <tr>
                    <td></td>
                    <td><input type="text" class="rounded-0 form-control" value="{{ $item->date_payment }}" disabled/>
                    </td>
                    <td><input type="text" class="rounded-0 form-control"
                               value="{{ $item->coa->code ." - ".$item->coa->name }}"
                               disabled/>
                    </td>
                    <td><input type="text" class="currency rounded-0 form-control" value="{{ $item->payment }}"
                               disabled/>
                    </td>
                    <td><input type="text" class="currency rounded-0 form-control" value="{{ $item->payment }}"
                               disabled/></td>
                  </tr>
                @endforeach
                <tr class="payment" id="payment_1">
                  <td></td>
                  <td><input type="text" name="payment[date][]" class="form-control rounded-0 datepicker"
                             style="max-width:150px !important" readonly/>
                  </td>
                  <td><select name="payment[coa_id][]" class="form-control rounded-0" style="min-width: 250px">
                      @foreach($selectCoa->coa as $item)
                        <option value="{{ $item->id }}">{{ $item->code .' - '. $item->name }}</option>
                      @endforeach
                    </select></td>
                  <td><input type="text" name="payment[payment][]" class="currency rounded-0 form-control"
                             style="min-width: 150px"/></td>
                  <td><input type="text" name="payment[total_payment][]" class="currency rounded-0 form-control"
                             disabled style="min-width: 150px"/>
                  </td>
                </tr>
                </tbody>
              </table>
              <table class="table table-borderless ">
                <thead>
                <tr>
                  <th></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td>
                    <div class="form-group row">
                      <label for="totalTagihan" class="col-lg-3 offset-6 col-form-label text-right">Total
                        Kasbon:</label>
                      <div class="col-lg-3">
                        <input id="totalTagihan" type="text" class="currency form-control rounded-0 float-right"
                               disabled style="width: 150px" value="{{ $data->total_kasbon }}"/>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div class="form-group row">
                      <label for="totalPembayaran" class="col-lg-3 offset-6 col-form-label text-right">Total
                        Pembayaran:</label>
                      <div class="col-lg-3">
                        <input type="hidden" value="{{ $data->total_payment }}" id="hiddenTotalPembayaran"/>
                        <input id="totalPembayaran" type="text" class="currency form-control rounded-0 float-right"
                               style="width: 150px"  value="{{ $data->total_payment }}" disabled/>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div class="form-group row">
                      <label for="sisaPembayaran" class="col-lg-3 offset-6 col-form-label text-right">Sisa
                        Pembayaran:</label>
                      <div class="col-lg-3">
                        <input type="hidden" value="{{ $data->rest_payment }}" id="hiddenSisaPembayaran"/>
                        <input id="sisaPembayaran" type="text" class="currency form-control rounded-0 float-right"
                                style="width: 150px" disabled/>
                      </div>
                    </div>
                  </td>
                </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary mr-2">Buat Invoice</button>
      </div>
    </form>
  </div>


@endsection

{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
  <link href="{{ asset('css/backend/datatables/dataTables.checkboxes.css') }}" rel="stylesheet" type="text/css"/>
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
  <script src="{{ asset('js/backend/datatables/dataTables.checkboxes.js') }}" type="text/javascript"></script>
  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {
      initCalculation();
      initCurrency();
      initDate();


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
          digits: 0,
          rightAlign: true,
          removeMaskOnSubmit: true,
          autoUnmask: true,
        });
      }

      function initCalculation() {
        let grandTotalPayment = parseInt($('#hiddenTotalPembayaran').val()) || 0;
        let grandTotal = parseInt($('#totalTagihan').val()) || 0;
        let $row = $(this).closest("tr");
        let total = parseInt($row.find('input[name="payment[payment][]"]').val()) || 0;
        $row.find('input[name="payment[total_payment][]"]').val(total);
        $('input[name^="payment[total_payment]"]').each(function () {
          grandTotalPayment += parseInt($(this).val()) || 0;
        });
        $('#grandTotal').val(grandTotal);
        $('#totalPembayaran').val(grandTotalPayment);
        $('#sisaPembayaran').val(grandTotal - grandTotalPayment);

        $('input[name^="payment[payment]"]').on('keyup', function () {
          let grandTotalPayment = parseInt($('#hiddenTotalPembayaran').val()) || 0;
          let grandTotal = parseInt($('#totalTagihan').val()) || 0;
          let $row = $(this).closest("tr");
          let total = parseInt($row.find('input[name="payment[payment][]"]').val()) || 0;
          $row.find('input[name="payment[total_payment][]"]').val(total);
          $('input[name^="payment[total_payment]"]').each(function () {
            grandTotalPayment += parseInt($(this).val()) || 0;
          });
          $('#grandTotal').val(grandTotal);
          $('#totalPembayaran').val(grandTotalPayment);
          $('#sisaPembayaran').val(grandTotal - grandTotalPayment);
        });
      }

      $(".addPayment").on('click', function () {
        let total_items = $(".payment").length;
        let lastid = $(".payment:last").attr("id");
        let split_id = lastid.split("_");
        let nextindex = Number(split_id[1]) + 1;
        let max = 100;
        if (total_items < max) {
          $(".payment:last").after("<tr class='payment' id='payment_" + nextindex + "'></tr>");
          $("#payment_" + nextindex).append(raw_payment(nextindex));
          initCalculation();
          initCurrency();
          initDate();
        }
      });

      $('tbody').on('click', '.rmPayment', function () {
        let id = this.id;
        let split_id = id.split("_");
        let deleteindex = split_id[1];
        $("#payment_" + deleteindex).remove();

        let grandTotalPayment = parseInt($('#hiddenTotalPembayaran').val()) || 0;
        let grandTotal = parseInt($('#totalTagihan').val()) || 0;
        let $row = $(this).closest("tr");
        let total = parseInt($row.find('input[name="payment[payment][]"]').val()) || 0;
        $row.find('input[name="payment[total_payment][]"]').val(total);
        $('input[name^="payment[total_payment]"]').each(function () {
          grandTotalPayment += parseInt($(this).val()) || 0;
        });
        $('#grandTotal').val(grandTotal);
        $('#totalPembayaran').val(grandTotalPayment);
        $('#sisaPembayaran').val(grandTotal - grandTotalPayment);
      });

      function raw_payment(nextindex) {
        return "<td><button id='payment_" + nextindex + "' class='btn btn-block btn-danger rmPayment rounded-0'>-</button></td>" +
          '<td><input type="text" name="payment[date][]" class="form-control rounded-0 datepicker"' +
          ' style="width:100% !important" readonly />' +
          '</td>' +
          '<td>' +
          '   <select  name="payment[coa_id][]" class="form-control rounded-0" style="min-width: 250px">' +
          '      @foreach($selectCoa->coa as $item)' +
          '      <option value="{{ $item->id }}">{{ $item->code ." - ". $item->name }}</option>' +
          '      @endforeach' +
          '   </select>' +
          '</td>' +
          '<td><input type="text" name="payment[payment][]" class="currency rounded-0 form-control" /></td>' +
          '<td><input type="text" name="payment[total_payment][]" class="currency rounded-0 form-control" disabled />' +
          '</td>';
      }

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
