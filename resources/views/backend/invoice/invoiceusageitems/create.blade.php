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
    </div>
    <div class="card-body">
      <form id="formStore" action="{{ route('backend.invoiceusageitems.store') }}">
        @csrf
        <input type="hidden" name="type" value="self">
        <div class="row align-items-center border border-dark py-10 px-4">
          <div class="col-12">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Tanggal Invoice:</label>
                  <div class="col-md-9">
                    <input type="text" class="form-control rounded-0 datepicker w-100" name="invoice_date"
                           placeholder="Tanggal Invoice" readonly>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">No. Invoice Pemakaian:</label>
                  <div class="col-lg-9">
                    <input name="num_bill" type="hidden" value="{{ Carbon\Carbon::now()->timestamp }}">
                    <input class="form-control rounded-0" value="{{ Carbon\Carbon::now()->timestamp }}" disabled>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Supir:</label>
                  <div class="col-lg-9">
                    <select name="driver_id" class="form-control" id="select2Driver">
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">No. Pol:</label>
                  <div class="col-lg-9">
                    <select name="transport_id" class="form-control" id="select2Transport">
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Memo:</label>
                  <div class="col-lg-9">
                    <textarea name="memo" class="form-control rounded-0"></textarea>
                  </div>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                <tr>
                  <th class="text-left" scope="col">Produk</th>
                  <th class="text-left" scope="col">No. Invoice</th>
                  <th class="text-center" scope="col">Stok Tersedia</th>
                  <th class="text-right" scope="col">Jumlah Pemakaian</th>
                  <th class="text-right" scope="col">Deskripsi</th>
                </tr>
                </thead>
                <tbody>
                <tr class="items" id="items_1">
                  <td>
                    <select class="form-control select2Stocks" name="items[sparepart_id][]"
                            style="width: 300px"></select></td>
                  <td>
                    <select class="form-control select2Invoice" name="items[stock_id][]" style="width: 225px"></select>
                  </td>
                  <td><input type="text" name="items[qty_system][]"
                             class="form-control rounded-0 unit" disabled style="min-width: 75px"/>
                  </td>
                  <td><input type="hidden" name="items[invoice_purchase_id][]">
                    <input type="hidden" name="items[price][]">
                    <input type="number" min="1" name="items[qty][]" class="unit rounded-0 form-control"
                           style="min-width: 100px"/>
                  </td>
                  <td><input type="text" name="items[description][]" class="rounded-0 form-control"
                             style="min-width: 250px"/></td>
                </tr>
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-end">
              <div class="btn-group" role="group">
                <button type="button" class='rmItems btn btn btn-danger'>&nbsp;-&nbsp;</button>
                <button type="button" class="add btn btn-sm btn-primary">&nbsp;+&nbsp;</button>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-end mt-5">
          <button type="submit" class="btn btn-primary rounded-0">Submit</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('styles')
  <style>
    .select2-container--default .select2-selection--single {
      border-radius: 0 !important;
    }
  </style>
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>

  <script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
  <script type="text/javascript">
    $(document).ready(function () {
      initCalculation();
      initSelect2();
      initCurrency();
      initDate();

      $("#select2Driver").select2({
        placeholder: "Search Supir",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.drivers.select2self') }}",
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
      });

      $("#select2Transport").select2({
        placeholder: "Search Kendaraan",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.transports.select2self') }}",
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
      });

      function initSelect2() {
        $(".select2Stocks").select2({
          placeholder: "Search SparePart",
          allowClear: true,
          ajax: {
            url: "{{ route('backend.stocks.select2') }}",
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
        }).on('change', function () {
          let $row = $(this).closest("tr");
          $row.find('select[name="items[stock_id][]"]').val("");
          $row.find('select[name="items[stock_id][]"]').trigger("change");
          $row.find('input[name="items[invoice_purchase_id][]"]').val('');
          $row.find('input[name="items[qty_system][]"]').val('');
          $row.find('input[name="items[price][]"]').val('');
        });

        $(".select2Invoice").select2({
          placeholder: "Search Invoice",
          allowClear: true,
          ajax: {
            url: "{{ route('backend.stocks.select2Invoice') }}",
            dataType: "json",
            delay: 250,
            cache: true,
            data: function (e) {
              let invoiceId = [];
              let sparePartId = [];
              $('select[name^="items[sparepart_id]"]').each(function () {
                if ($(this).val()) {
                  sparePartId.push($(this).val());
                }
              });
              $('input[name^="items[invoice_purchase_id]"]').each(function () {
                if ($(this).val()) {
                  invoiceId.push($(this).val());
                }
              });
              return {
                sparepart_id_current: $(this).closest("tr").find('select[name="items[sparepart_id][]"]').find(":selected").val() || 0,
                sparepart_id: sparePartId,
                invoice_id: invoiceId,
                q: e.term || '',
                page: e.page || 1
              }
            },
          },
        }).on('change', function (evt) {
          let $row = $(this).closest("tr");
          $row.find('input[name="items[qty_system][]"]').val('');
          $row.find('input[name="items[invoice_purchase_id][]"]').val('');
        }).on('select2:select', function (evt) {
          let $row = $(this).closest("tr");
          $row.find('input[name="items[qty_system][]"]').val(evt.params.data.qty);
          $row.find('input[name="items[invoice_purchase_id][]"]').val(evt.params.data.invoice_purchase_id);
          $row.find('input[name="items[price][]"]').val(evt.params.data.price);
          $row.find('input[name="items[qty][]"]').attr('max', evt.params.data.qty);

        });
      }

      function initCurrency() {
        $(".unit").inputmask('numeric', {
          groupSeparator: '.',
          digits: 0,
          rightAlign: true,
          removeMaskOnSubmit: true,
          autoUnmask: true,
          allowMinus: false,
        });
      }

      function initDate() {
        $('.datepicker').datepicker({
          format: 'yyyy-mm-dd',
          todayBtn: "linked",
          clearBtn: true,
          todayHighlight: true
        });
      }

      function initCalculation() {
        $('input[name^="items[qty_now]"]').on('keyup', function () {
          let $row = $(this).closest("tr");
          let qty_system = parseInt($row.find('input[name="items[qty_system][]"]').val());
          let qty_now = parseInt($row.find('input[name="items[qty_now][]"]').val());
          qty_difference = qty_now - qty_system;
          $row.find('input[name="items[qty_difference][]"]').val(qty_difference);
        });
      }

      $('.rmItems').on('click', function () {
        let lastid = $(".items:last").attr("id");
        let split_id = lastid.split("_");
        let deleteindex = split_id[1];
        if (deleteindex > 1) {
          $("#" + lastid).remove();
        }
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
        return '<td style="width: 300px">' +
          '   <select class="form-control select2Stocks" name="items[sparepart_id][]"' +
          '      style="width: 300px"></select>' +
          '</td>' +
          '<td style="width: 225px"><select class="form-control select2Invoice" name="items[stock_id][]"' +
          '   style="width: 225px"></select></td>' +
          '<td><input type="text" name="items[qty_system][]" class="form-control rounded-0 unit" disabled/></td>' +
          '<td><input type="hidden" name="items[invoice_purchase_id][]"><input type="hidden" name="items[price][]"><input type="number" min="1" name="items[qty][]" class="unit rounded-0 form-control"/>' +
          '<td><input type="text" name="items[description][]" class="rounded-0 form-control" style="min-width: 250px"/></td>' +
          '</td>';
      }

      $("#formStore").submit(function (e) {
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
              toastr.error("Please complete your form", 'Failed !');
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
