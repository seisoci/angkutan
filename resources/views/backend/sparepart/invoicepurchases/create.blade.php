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
      <form id="formStore" action="{{ route('backend.invoicepurchases.store') }}">
        @csrf
        <div class="row align-items-center border border-dark py-10 px-4">
          <div class="col-12">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-5 col-form-label">Tanggal Invoice:</label>
                  <div class="col-md-6">
                    <input type="text" class="form-control rounded-0 datepicker w-100" name="invoice_date"
                           placeholder="Tanggal Invoice" readonly>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-5 col-form-label">Tgl Jatuh Tempo:</label>
                  <div class="col-lg-6">
                    <input type="text" class="form-control rounded-0 datepicker w-100" name="due_date"
                           placeholder="Tgl Jatuh Tempo" readonly>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-5 col-form-label">Prefix:</label>
                  <div class="col-lg-6">
                    <select name="prefix" class="form-control" id="select2Prefix">
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-5 col-form-label">No. Invoice Pembelian:</label>
                  <div class="col-lg-6">
                    <input name="num_bill" type="hidden" value="{{ Carbon\Carbon::now()->timestamp }}">
                    <input class="form-control rounded-0" value="{{ Carbon\Carbon::now()->timestamp }}" disabled>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-4 offset-md-2 col-form-label">Supplier:</label>
                  <div class="col-lg-6">
                    <select class="form-control rounded-0" name="supplier_sparepart_id" id="select2Suppliers">
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-4 offset-md-2 col-form-label">Phone:</label>
                  <div class="col-lg-6">
                    <input type="text" class="form-control rounded-0" id="phone" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-4 offset-md-2 col-form-label">Alamat:</label>
                  <div class="col-lg-6">
                    <input type="text" class="form-control rounded-0" id="address" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-4 offset-2 col-form-label">Metode Pembayaran</label>
                  <div class="col-lg-6">
                    <select id="method_payment" name="method_payment" class="form-control rounded-0">
                      <option value="cash">Tunai</option>
                      <option value="credit">Kredit</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
              <tr>
                <th class="text-center" scope="col" style="width: 50px">
                  <button type="button"
                          class="add btn btn-sm btn-primary rounded-0">+
                  </button>
                </th>
                <th class="text-left" scope="col">Produk</th>
                <th class="text-right" scope="col" style="width: 50px">Unit</th>
                <th class="text-right" scope="col">Harga</th>
                <th class="text-right" scope="col">Total</th>
              </tr>
              </thead>
              <tbody>
              <tr class="items" id="items_1">
                <td></td>
                <td style="width: 350px"><select class="form-control select2SparePart" name="items[sparepart_id][]"
                                                 style="width: 350px"></select></td>
                <td><input type="text" name="items[qty][]" class="form-control rounded-0 unit" style="width: 50px"/>
                </td>
                <td><input type="text" name="items[price][]" class="currency rounded-0 form-control"
                           style="min-width: 175px"/></td>
                <td><input type="text" name="items[total][]" class="currency rounded-0 form-control" disabled
                           style="min-width: 175px"/></td>
              </tr>
              </tbody>
            </table>
          </div>
          <table class="table table-borderless ">
            <thead>
            <tr>
              <th class="text-right" scope="col"></th>
              <th class="text-right" scope="col"></th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td class="pt-6 text-right">Diskon</td>
              <td style="width: 150px;"><input id="diskon" name="discount" type="text"
                                               class="currency form-control rounded-0"
                                               value="0" style="width: 175px"/>
              </td>
            </tr>
            <tr>
              <td class="pt-6 text-right">Grand Total</td>
              <td style="width: 150px;"><input id="grandTotal" type="text" class="currency form-control rounded-0"
                                               disabled style="width: 175px;"/>
              </td>
            </tr>
            </tbody>
          </table>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
              <tr>
                <th class="text-center" scope="col">
                  <button type="button"
                          class="addPayment btn btn-sm btn-primary rounded-0">+
                  </button>
                </th>
                <th class="text-left" scope="col" style="width: 100px">Tanggal Pembayaran</th>
                <th class="text-left" scope="col">Akun</th>
                <th class="text-right" scope="col">Nominal</th>
                <th class="text-right" scope="col">Total Pembayaran</th>
              </tr>
              </thead>
              <tbody>
              <tr class="payment" id="payment_1">
                <td style="width: 50px;"></td>
                <td style="width: 100px">
                  <input type="text" name="payment[date][]"
                         class="form-control rounded-0 datepicker"
                         style="width:100px" readonly/>
                </td>
                <td><select name="payment[coa][]" class="form-control rounded-0" style="min-width: 300px;" required>
                    @foreach($selectCoa->coa as $item)
                      <option value="{{ $item->id }}">{{ $item->code ." - ". $item->name }}</option>
                    @endforeach
                  </select></td>
                <td><input type="text" name="payment[payment][]" class="currency rounded-0 form-control"
                           style="min-width: 175px"/></td>
                <td><input type="text" name="payment[total_payment][]" class="currency rounded-0 form-control" disabled
                           style="min-width: 175px"/>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
          <table class="table table-borderless">
            <thead>
            <tr>
              <th class="text-right" scope="col"></th>
              <th class="text-right" scope="col"></th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td class="pt-6 text-right">Total Tagihan</td>
              <td style="width: 175px"><input id="totalTagihan" type="text" class="currency form-control rounded-0"
                                               disabled style="width: 175px;"/>
              </td>
            </tr>
            <tr>
              <td class="pt-6 text-right">Total Pembayaran</td>
              <td class="text-right" style="width: 175px"><input id="totalPembayaran" type="text"
                                                                 class="currency form-control rounded-0 text-right"
                                                                 disabled style="width: 175px;"/>
              </td>
            </tr>
            <tr>
              <td class="pt-6 text-right">Sisa Tagihan</td>
              <td style="width: 150px"><input id="sisaPembayaran" type="text" class="currency form-control rounded-0"
                                              disabled style="width: 175px;"/>
              </td>
            </tr>
            <tr>
              <td></td>
              <td class="text-right" style="width: 150px">
                <button type="submit" class="btn btn-primary rounded-0">Submit</button>
              </td>
            </tr>
            </tbody>
          </table>
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
      initCalculation();
      initSelect2();
      initCurrency();
      initDate();

      $("#select2Suppliers").select2({
        placeholder: "Search Suppliers",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.supplierspareparts.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function (e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      }).on('select2:select', function (evt) {
        $('#phone').val(evt.params.data.phone);
        $('#address').val(evt.params.data.address);
      });

      $("#select2Prefix").select2({
        placeholder: "Choose Prefix",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.prefixes.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function (e) {
            return {
              type: 'sparepart',
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      });

      function initSelect2() {
        $(".select2SparePart").select2({
          placeholder: "Search SparePart",
          allowClear: true,
          ajax: {
            url: "{{ route('backend.spareparts.select2') }}",
            dataType: "json",
            cache: true,
            data: function (e) {
              let arrayUsed = [];
              $('select[name^="items[sparepart_id]"]').each(function () {
                if ($(this).val()) {
                  arrayUsed.push($(this).val());
                }
              });
              return {
                used: arrayUsed,
                q: e.term || '',
                page: e.page || 1
              }
            },
          },
        })
      }

      function initDate() {
        $('.datepicker').datepicker({
          format: 'yyyy-mm-dd',
          todayBtn: "linked",
          clearBtn: true,
          todayHighlight: true
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

        $(".unit").inputmask('numeric', {
          groupSeparator: '.',
          digits: 0,
          rightAlign: true,
          removeMaskOnSubmit: true,
          autoUnmask: true,
          allowMinus: false
        });
      }

      function initCalculation() {
        $('input[name^="items[qty]"],input[name^="items[price]"],#diskon,input[name^="payment[payment]"]').on('keyup', function () {
          let totalPriceItem = 0;
          let totalPrice = 0;
          let grandTotalNetto = 0;
          let $row = $(this).closest("tr"); //<table> class
          let qty = parseInt($row.find('input[name="items[qty][]"]').val()) || 0;
          let diskon = parseInt($('#diskon').val()) || 0;
          let price = parseFloat($row.find('input[name="items[price][]"]').val()) || 0;
          totalPrice = (price * qty) || 0;
          $row.find('input[name="items[total][]"]').val(totalPrice);
          $('input[name^="items[total]"]').each(function () {
            totalPriceItem += parseInt($(this).val()) || 0;
          });
          grandTotalNetto = totalPriceItem - diskon;
          $('#grandTotal').val(grandTotalNetto);
          $('#totalTagihan').val(grandTotalNetto);

          let grandTotalPayment = 0;
          let grandTotal = parseInt($('#grandTotal').val()) || 0;
          let totalPayment = parseInt($row.find('input[name="payment[payment][]"]').val()) || 0;
          $row.find('input[name="payment[total_payment][]"]').val(totalPayment);
          $('input[name^="payment[total_payment]"]').each(function () {
            grandTotalPayment += parseInt($(this).val()) || 0;
          });
          $('#grandTotal').val(grandTotal);
          $('#totalPembayaran').val(grandTotalPayment);
          $('#sisaPembayaran').val(grandTotal - grandTotalPayment);
        });

      }

      $('tbody').on('click', '.rmItems', function () {
        let id = this.id;
        let split_id = id.split("_");
        let deleteindex = split_id[1];
        $("#items_" + deleteindex).remove();

        let totalPriceItem = 0;
        let totalPrice = 0;
        let grandTotalNetto = 0;
        let $row = $(this).closest("tr"); //<table> class
        let qty = parseInt($row.find('input[name="items[qty][]"]').val()) || 0;
        let diskon = parseInt($('#diskon').val()) || 0;
        let price = parseFloat($row.find('input[name="items[price][]"]').val()) || 0;
        totalPrice = (price * qty) || 0;
        $row.find('input[name="items[total][]"]').val(totalPrice);
        $('input[name^="items[total]"]').each(function () {
          totalPriceItem += parseInt($(this).val()) || 0;
        });
        grandTotalNetto = totalPriceItem - diskon;
        $('#grandTotal').val(grandTotalNetto);
        $('#totalTagihan').val(grandTotalNetto);

        let grandTotalPayment = 0;
        let grandTotal = parseInt($('#grandTotal').val()) || 0;
        let totalPayment = parseInt($row.find('input[name="payment[payment][]"]').val()) || 0;
        $row.find('input[name="payment[total_payment][]"]').val(totalPayment);
        $('input[name^="payment[total_payment]"]').each(function () {
          grandTotalPayment += parseInt($(this).val()) || 0;
        });
        $('#grandTotal').val(grandTotal);
        $('#totalPembayaran').val(grandTotalPayment);
        $('#sisaPembayaran').val(grandTotal - grandTotalPayment);
      });

      $(".add").on('click', function () {
        let total_items = $(".items").length;
        let lastid = $(".items:last").attr("id");
        let split_id = lastid.split("_");
        let nextindex = Number(split_id[1]) + 1;
        let max = 100;
        if (total_items < max) {
          $(".items:last").after("<tr class='items' id='items_" + nextindex + "'></tr>");
          $("#items_" + nextindex).append(raw_items(nextindex));
          initCalculation();
          initSelect2();
          initCurrency();
        }
      });

      $('tbody').on('click', '.rmPayment', function () {
        let id = this.id;
        let split_id = id.split("_");
        let deleteindex = split_id[1];
        $("#payment_" + deleteindex).remove();

        let totalPriceItem = 0;
        let totalPrice = 0;
        let grandTotalNetto = 0;
        let $row = $(this).closest("tr"); //<table> class
        let qty = parseInt($row.find('input[name="items[qty][]"]').val()) || 0;
        let diskon = parseInt($('#diskon').val()) || 0;
        let price = parseFloat($row.find('input[name="items[price][]"]').val()) || 0;
        totalPrice = (price * qty) || 0;
        $row.find('input[name="items[total][]"]').val(totalPrice);
        $('input[name^="items[total]"]').each(function () {
          totalPriceItem += parseInt($(this).val()) || 0;
        });
        grandTotalNetto = totalPriceItem - diskon;
        $('#grandTotal').val(grandTotalNetto);
        $('#totalTagihan').val(grandTotalNetto);

        let grandTotalPayment = 0;
        let grandTotal = parseInt($('#grandTotal').val()) || 0;
        let totalPayment = parseInt($row.find('input[name="payment[payment][]"]').val()) || 0;
        $row.find('input[name="payment[total_payment][]"]').val(totalPayment);
        $('input[name^="payment[total_payment]"]').each(function () {
          grandTotalPayment += parseInt($(this).val()) || 0;
        });
        $('#grandTotal').val(grandTotal);
        $('#totalPembayaran').val(grandTotalPayment);
        $('#sisaPembayaran').val(grandTotal - grandTotalPayment);
      });

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
          initSelect2();
          initCurrency();
          initDate();
        }
      });

      function raw_items(nextindex) {
        return "<td><button type='button' id='items_" + nextindex + "' class='btn btn-block btn-danger rmItems rounded-0'>-</button></td>" + '<td><select class="form-control select2SparePart" name="items[sparepart_id][]"></select></td>' +
          '<td><input type="text" name="items[qty][]" class="form-control unit rounded-0" /></td>' +
          '<td><input type="text" data-inputmask=""alias": "decimal"" name="items[price][]" class="currency form-control rounded-0" /></td>' +
          '<td><input type="text" name="items[total][]" class="currency form-control rounded-0" disabled /></td>';
      }

      function raw_payment(nextindex) {
        return "<td><button id='payment_" + nextindex + "' class='btn btn-block btn-danger rmPayment rounded-0'>-</button></td>" +
          '<td style="width: 100px"><input type="text" name="payment[date][]" class="form-control rounded-0 datepicker"' +
          '   style="width:100px !important" readonly/>' +
          '</td>' +
          '<td>' +
          '   <select name="payment[coa][]" class="form-control rounded-0" style="min-width: 300px;" required>' +
          '      @foreach($selectCoa->coa as $item)' +
          '      <option value="{{ $item->id }}">{{ $item->code ." - ". $item->name }}</option>' +
          '      @endforeach' +
          '   </select>' +
          '</td>' +
          '<td><input type="text" name="payment[payment][]" class="currency rounded-0 form-control" style="min-width: 175px"/>' +
          '</td>' +
          '<td><input type="text" name="payment[total_payment][]" class="currency rounded-0 form-control" disabled' +
          '   style="min-width: 175px"/>' +
          '</td>';
      }


      $("#formStore").submit(function (e) {
        $('.currency').inputmask('remove');
        $('.unit').inputmask('remove');
        e.preventDefault();
        let form = $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        let url = form.attr("action");
        let data = new FormData(this);
        $.ajax({
          beforeSend: function () {
            btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading ...").prop("disabled", "disabled");
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
          },
          error: function (response) {
            initCurrency();
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
          }
        });
      });

    });
  </script>
@endsection
