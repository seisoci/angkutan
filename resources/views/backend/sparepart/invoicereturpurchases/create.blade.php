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
      <form id="formStore" action="{{ route('backend.invoicereturpurchases.store') }}">
        @csrf
        <div class="row align-items-center border border-dark py-10 px-4">
          <div class="col-12">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-5 col-form-label">Tanggal Retur:</label>
                  <div class="col-md-6">
                    <input type="text" class="form-control rounded-0 datepicker w-100" name="invoice_date"
                           placeholder="Tanggal Invoice" readonly>
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
                  <label class="col-lg-5 col-form-label">No. Retur Pembelian:</label>
                  <div class="col-lg-6">
                    <input name="num_bill" type="hidden" value="{{ Carbon\Carbon::now()->timestamp }}">
                    <input class="form-control rounded-0" value="{{ Carbon\Carbon::now()->timestamp }}" disabled>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-5 col-form-label">Supplier:</label>
                  <div class="col-lg-6">
                    <select class="form-control rounded-0" name="supplier_sparepart_id" id="select2Suppliers">
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-5 col-form-label">No. Invoice Pembelian:</label>
                  <div class="col-lg-6">
                    <select class="form-control rounded-0" name="invoice_purchase_id" id="select2Invoice">
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-5 col-form-label">Phone:</label>
                  <div class="col-lg-6">
                    <input type="text" class="form-control rounded-0" id="phone" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-5 col-form-label">Alamat:</label>
                  <div class="col-lg-6">
                    <input type="text" class="form-control rounded-0" id="address" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-5 col-form-label">Master Akun:</label>
                  <div class="col-lg-6">
                    <select name="coa_id" class="form-control rounded-0" required>
                      @foreach($selectCoa->coa as $item)
                        <option value="{{ $item->id }}">{{ $item->code ." - ". $item->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered" id="tableRetur">
              <thead>
              <tr>
                <th class="text-center" scope="col" style="width: 50px">
                  <button type="button"
                          class="add btn btn-sm btn-primary rounded-0">+
                  </button>
                </th>
                <th class="text-left" scope="col">Produk</th>
                <th class="text-right" scope="col">Harga</th>
                <th class="text-center" scope="col">Stok Tersedia</th>
                <th class="text-right" scope="col">Jml Retur</th>
                <th class="text-right" scope="col">Total</th>
              </tr>
              </thead>
              <tbody>
              <tr class="items" id="items_1">
                <td></td>
                <td style="width: 300px">
                  <select class="form-control select2Sparepart" name="items[sparepart_id][]"
                          style="width: 300px"></select>
                </td>
                <td style="width: 150px"><input type="hidden" name="items[price][]"><input type="text" name="items[pricedis][]"
                                                class="form-control rounded-0 unit" style="width: 150px" disabled/>
                </td>
                <td style="width: 75px"><input type="text" name="items[qty_system][]"
                                               class="form-control rounded-0 unit" style="width: 75px" disabled/>
                </td>
                </td>
                <td style="width: 75px"><input name="items[qty][]" type="number" min="1" class="form-control rounded-0"
                                               style="width: 75px"></td>
                <td style="width: 150px">
                  <input name="items[total][]" class="currency unit rounded-0 form-control" style="min-width: 150px"
                         disabled/>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
          <table class="table table-borderless ">
            <thead>
            <tr>
              <th class="text-right" scope="col"></th>
              <th class="text-right" scope="col" style="width: 150px"></th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td class="text-right">Diskon Pembelian</td>
              <td class="float-right"><input id="discountPO" type="hidden" name="discountPO"><input id="discount" type="text" class="currency form-control rounded-0"
                                             disabled style="width: 150px"/></td>
            </tr>
            <tr>
              <td class="text-right">Potong Dari Diskon</td>
              <td class="float-right"><input id="discountCut" type="text" class="currency form-control rounded-0" name="discount"
                                             style="width: 150px"/></td>
            </tr>
            <tr>
              <td class="text-right">Total Kembali ke Kas</td>
              <td class="float-right"><input id="grandTotal" type="text" class="currency form-control rounded-0"
                                             disabled style="width: 150px"/></td>
            </tr>
            <tr>
              <td></td>
              <td class="text-right">
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
      }).on('change', function () {
        $('#select2Invoice').val("");
        $('#select2Invoice').trigger("change");
        $('#discount').val('');
        $('#discountCut').val('');
        $('#grandTotal').val('');
        $('#discountPO').val('');
        let $row = $('#tableRetur').find('tbody tr');
        $row.each(function () {
          let $this = $(this);
          $this.find('select[name="items[sparepart_id][]"]').val("");
          $this.find('select[name="items[sparepart_id][]"]').trigger("change");
          $this.find('input[name="items[qty_system][]"]').val('');
          $this.find('input[name="items[price][]"]').val('');
          $this.find('input[name="items[pricedis][]"]').val('');
          $this.find('input[name="items[qty][]"]').val('');
          $this.find('input[name="items[total][]"]').val('');
          $this.find('input[name="items[qty][]"]').attr('max', 1);
        });
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

      $("#select2Invoice").select2({
        placeholder: "Search Invoice",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.invoicereturpurchases.select2Invoice') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function (e) {
            return {
              supplier_id: $('#select2Suppliers').find(":selected").val() || 'NULL',
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      }).on('change', function () {
        $('#discount').val('');
        $('#discountCut').val('');
        $('#grandTotal').val('');
        $('#discountPO').val('');
        let $row = $('#tableRetur').find('tbody tr');
        $row.each(function () {
          let $this = $(this);
          $this.find('select[name="items[sparepart_id][]"]').val("");
          $this.find('select[name="items[sparepart_id][]"]').trigger("change");
          $this.find('input[name="items[qty_system][]"]').val('');
          $this.find('input[name="items[price][]"]').val('');
          $this.find('input[name="items[pricedis][]"]').val('');
          $this.find('input[name="items[qty][]"]').val('');
          $this.find('input[name="items[total][]"]').val('');
          $this.find('input[name="items[qty][]"]').attr('max', 1);
        });
      }).on('select2:select', function (evt) {
        $('#discount').val(evt.params.data.discount);
        $('#discountPO').val(evt.params.data.discount);
      })

      function initSelect2() {
        $(".select2Sparepart").select2({
          placeholder: "Search SparePart",
          allowClear: true,
          ajax: {
            url: "{{ route('backend.invoicereturpurchases.select2SparePart') }}",
            dataType: "json",
            delay: 250,
            cache: true,
            data: function (e) {
              let arrayUsed = [];
              $('select[name^="items[sparepart_id]"]').each(function () {
                if ($(this).val()) {
                  arrayUsed.push($(this).val());
                }
              });
              return {
                invoice_purchase_id: $('#select2Invoice').find(":selected").val() || 'NULL',
                used: arrayUsed,
                q: e.term || '',
                page: e.page || 1
              }
            },
          },
        }).on('change', function () {
          let $row = $(this).closest("tr");
          $row.find('input[name="items[qty_system][]"]').val('');
          $row.find('input[name="items[price][]"]').val('');
        }).on('select2:select', function (evt) {
          let $row = $(this).closest("tr");
          $row.find('input[name="items[qty_system][]"]').val(evt.params.data.qty);
          $row.find('input[name="items[qty][]"]').attr('max', evt.params.data.qty);
          $row.find('input[name="items[invoice_purchase_id][]"]').val(evt.params.data.invoice_purchase_id);
          $row.find('input[name="items[pricedis][]"]').val(evt.params.data.price);
          $row.find('input[name="items[price][]"]').val(evt.params.data.price);
        });
      }

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
        $('input[name^="items[qty]"],input[name^="items[price]"],#discountCut').on('keyup', function () {
          let grandtotal = 0;
          let $row = $(this).closest("tr"); //<table> class
          let qty = parseInt($row.find('input[name="items[qty][]"]').val());
          let discount = parseInt($('#discountCut').val()) || 0;
          let price = parseFloat($row.find('input[name="items[price][]"]').val());
          let total = (price * qty) || 0;
          $row.find('input[name="items[total][]"]').val(total);
          $('input[name^="items[total]"]').each(function () {
            grandtotal += parseInt($(this).val());
          });
          let grandTotalNetto = grandtotal - discount;
          $('#grandTotal').val(grandTotalNetto);
          $('#totalTagihan').val(grandTotalNetto);
        });

        $('input[name^="items[qty]"]').on('change', function () {
          let grandtotal = 0;
          let $row = $(this).closest("tr"); //<table> class
          let qty = parseInt($row.find('input[name="items[qty][]"]').val());
          let discount = parseInt($('#discountCut').val()) || 0;
          let price = parseFloat($row.find('input[name="items[price][]"]').val());
          let total = (price * qty) || 0;
          $row.find('input[name="items[total][]"]').val(total);
          $('input[name^="items[total]"]').each(function () {
            grandtotal += parseInt($(this).val());
          });
          let grandTotalNetto = grandtotal - discount;
          $('#grandTotal').val(grandTotalNetto);
          $('#totalTagihan').val(grandTotalNetto);
        });
      }

      $('tbody').on('click', '.rmItems', function () {
        let id = this.id;
        let split_id = id.split("_");
        let deleteindex = split_id[1];
        $("#items_" + deleteindex).remove();

        let grandtotal = 0;
        let $row = $(this).closest("tr"); //<table> class
        let qty = parseInt($row.find('input[name="items[qty][]"]').val());
        let discount = parseInt($('#discountCut').val()) || 0;
        let price = parseFloat($row.find('input[name="items[price][]"]').val());
        let total = (price * qty) || 0;
        $row.find('input[name="items[total][]"]').val(total);
        $('input[name^="items[total]"]').each(function () {
          grandtotal += parseInt($(this).val());
        });
        let grandTotalNetto = grandtotal - discount;
        $('#grandTotal').val(grandTotalNetto);
        $('#totalTagihan').val(grandTotalNetto);
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

      function raw_items(nextindex) {
        return "<td><button type='button' id='items_" + nextindex + "' class='btn btn-block btn-danger rmItems rounded-0'>-</button></td>" +
          '<td style="width: 300px">' +
          '   <select class="form-control select2Sparepart" name="items[sparepart_id][]"' +
          '      style="width: 300px"></select>' +
          '</td>' +
          '<td><input type="hidden" name="items[price][]"><input type="text" name="items[pricedis][]"' +
          '   class="form-control rounded-0 unit" style="min-width: 75px" disabled/></td>' +
          '</td>' +
          '<td style="width: 75px"><input type="text" name="items[qty_system][]"' +
          '   class="form-control rounded-0 unit" style="min-width: 75px" disabled/>' +
          '</td>' +
          '<td><input name="items[qty][]" type="number" min="1" class="form-control rounded-0"></td>' +
          '<td>' +
          '   <input name="items[total][]" class="currency unit rounded-0 form-control" style="min-width: 100px"' +
          '      disabled/>' +
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
              initCurrency();
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
