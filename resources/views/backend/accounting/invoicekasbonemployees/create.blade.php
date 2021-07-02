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
    <form id="formStore" action="{{ route('backend.invoicekasbonemployees.store') }}">
      @csrf
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
                      <select name="prefix" class="form-control" id="select2Prefix">
                      </select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">No. Kasbon:</label>
                    <div class="col-lg-6">
                      <input name="num_bill" type="hidden" value="{{ Carbon\Carbon::now()->timestamp }}">
                      <input class="form-control rounded-0" value="{{ Carbon\Carbon::now()->timestamp }}" disabled>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">Supir:</label>
                    <div class="col-lg-9">
                      <select name="employee_id" class="form-control" id="select2Employee">
                      </select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">Memo:</label>
                    <div class="col-lg-9">
                      <textarea name="memo" class="form-control rounded-0"></textarea>
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
                    <th scope="col">Nama Supir</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col" class="text-right">Nominal</th>
                  </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>
              </div>
              <div class="table-responsive">
                <table class="table table-bordered mt-20">
                  <thead>
                  <tr>
                    <th class="text-center" scope="col" style="width: 50px">
                      <button type="button"
                              class="addPayment btn btn-sm btn-primary rounded-0" style="width: 50px">+
                      </button>
                    </th>
                    <th class="text-left" scope="col">Tanggal Pembayaran</th>
                    <th class="text-left" scope="col">Master Akun</th>
                    <th class="text-right" scope="col">Nominal Cicilan</th>
                    <th class="text-right" scope="col">Total Cicilan</th>
                  </tr>
                  </thead>
                  <tbody>
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
              </div>
              <table class="table table-borderless">
                <thead>
                <tr>
                  <th class="text-right" scope="col"></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td>
                    <div class="form-group row">
                      <label for="totalTagihan" class="col-lg-3 offset-6 col-form-label text-right">Total Kasbon:</label>
                      <div class="col-lg-3">
                        <input id="totalTagihan" type="text" class="currency form-control rounded-0 float-right"
                               disabled style="width: 150px"/>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div class="form-group row">
                      <label for="totalPembayaran" class="col-lg-3 offset-6 col-form-label text-right">Total Pembayaran:</label>
                      <div class="col-lg-3">
                        <input id="totalPembayaran" type="text" class="currency form-control rounded-0 float-right"
                               disabled style="width: 150px"/>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>
                    <div class="form-group row">
                      <label for="sisaPembayaran" class="col-lg-3 offset-6 col-form-label text-right">Sisa Pembayaran:</label>
                      <div class="col-lg-3">
                        <input id="sisaPembayaran" type="text" class="currency form-control rounded-0 float-right"
                               disabled style="width: 150px"/>
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

  {{-- DataTables --}}
  <div class="card card-custom mt-10">
    <div class="card-header flex-wrap py-3">
      <div class="card-title">
        <h3 class="card-label">{{ $config['page_title'] }}
          <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
      </div>
      <div class="card-toolbar">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <button id="submitAppend" class="btn btn-primary">Masukan Ke Form Kasbon</button>
      </div>
    </div>
    <div class="card-body">
      <!--begin: Datatable-->
      <table class="table table-bordered table-hover" id="Datatable">
        <thead>
        <tr>
          <th></th>
          <th>Nama Karyawaan</th>
          <th>Total Pinjaman</th>
          <th>Keterangan</th>
          <th>Created At</th>
        </tr>
        </thead>
      </table>
    </div>
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

      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[4, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.invoicekasbonemployees.create') }}",
          data: function (d) {
            d.employee_id = $('#select2Employee').find(':selected').val();
          }
        },
        columns: [
          {data: 'id', name: 'id'},
          {data: 'employee.name', name: 'employee.name'},
          {data: 'amount', name: 'amount', render: $.fn.dataTable.render.number(',', '.', 2), className: 'dt-right'},
          {data: 'memo', name: 'memo'},
          {data: 'created_at', name: 'created_at'},
        ],
        columnDefs: [
          {
            targets: 0,
            checkboxes: {
              selectRow: true
            }
          },
        ],
        select: {
          style: 'multi'
        },
      });

      $('#submitAppend').on('click', function (e) {
        e.preventDefault();
        let selected = dataTable.column(0).checkboxes.selected();
        let dataSelected = [];
        $.each(selected, function (index, data) {
          dataSelected.push(data);
        });
        $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          type: 'POST',
          url: "{{ route('backend.invoicekasbonemployees.findbypk') }}",
          data: {data: JSON.stringify(dataSelected)},
          success: function (response) {
            if (response.data) {
              $('#table_invoice tbody').empty();
              $('#table_invoice tfoot').empty();
              $('#TampungId').empty();
              let total = 0;
              $.each(response.data, function (index, data) {
                total += parseFloat(data.amount);
                $('#TampungId').append('<input type="hidden" name="kasbon_id[]" value="' + data.id + '">');
                $('#table_invoice tbody').append('<tr>' +
                  ' <td class="text-center">' + (index + 1) + '</td>' +
                  ' <td>' + data.created_at + '</td>' +
                  ' <td>' + data.employee.name + '</td>' +
                  ' <td>' + data.memo + '</td>' +
                  ' <td class="text-right currency">' + data.amount + '</td>' +
                  '</tr>');
              });
              $('#TampungId').append('<input type="hidden" name="total_kasbon" value="' + total + '">');
              $('#totalTagihan').val(total);
              $('#table_invoice tfoot').append('<tr>' +
                '<td colspan="4" class="text-right">Total</td>' +
                '<td class="text-right currency">' + total + '</td>' +
                '</tr>');
              initCurrency();
              initCalculation();
            }
          }
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
              type: 'operational',
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      });

      $("#select2Employee").select2({
        placeholder: "Search Karywaaan",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.employee.select2') }}",
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
      }).on('change', function (e) {
        dataTable.draw();
        $('#table_invoice tbody').empty();
        $('#table_invoice tfoot').empty();
        $('#TampungId').empty();
      });

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
        let grandTotalPayment = 0;
        let grandTotal = parseInt($('input[name=total_kasbon]').val());
        let $row = $(this).closest("tr");
        let total = parseInt($row.find('input[name="payment[payment][]"]').val()) || 0;
        $row.find('input[name="payment[total_payment][]"]').val(total);
        $('input[name^="payment[total_payment]"]').each(function () {
          grandTotalPayment += parseInt($(this).val());
        });
        $('#grandTotal').val(grandTotal);
        $('#totalPembayaran').val(grandTotalPayment);
        $('#sisaPembayaran').val(grandTotal - grandTotalPayment);

        $('input[name^="payment[payment]"]').on('keyup', function () {
          let grandTotalPayment = 0;
          let grandTotal = parseInt($('input[name=total_kasbon]').val()) || 0;
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

        let grandTotalPayment = 0;
        let grandTotal = parseInt($('input[name=total_kasbon]').val());
        let $row = $(this).closest("tr");
        let total = parseInt($row.find('input[name="payment[payment][]"]').val()) || 0;
        $row.find('input[name="payment[total_payment][]"]').val(total);
        $('input[name^="payment[total_payment]"]').each(function () {
          grandTotalPayment += parseInt($(this).val());
        });
        $('#grandTotal').val(grandTotal);
        $('#totalPembayaran').val(grandTotalPayment);
        $('#sisaPembayaran').val(grandTotal - grandTotalPayment);
      });


      function raw_payment(nextindex) {
        return "<td><button type='button' id='payment_" + nextindex + "' class='btn btn-block btn-danger rmPayment rounded-0'>-</button></td>" +
          '<td><input type="text" name="payment[date][]" class="form-control rounded-0 datepicker"' +
          ' style="width:100% !important" readonly />' +
          '</td>' +
          '<td>'+
          '   <select  name="payment[coa_id][]" class="form-control rounded-0" style="min-width: 250px">'+
          '      @foreach($selectCoa->coa as $item)'+
          '      <option value="{{ $item->id }}">{{ $item->code ." - ". $item->name }}</option>' +
          '      @endforeach'+
          '   </select>'+
          '</td>'+
          '<td><input type="text" name="payment[payment][]" class="currency rounded-0 form-control" /></td>' +
          '<td><input type="text" name="payment[total_payment][]" class="currency rounded-0 form-control" disabled />' +
          '</td>';
      }

      $("#formStore").submit(function (e) {
        $('.currency').inputmask('remove');
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
              $("[role='alert']").parent().removeAttr("style");
              $(".alert-text").html('');
              $.each(response.error, function (key, value) {
                $(".alert-text").append('<span style="display: block">' + value + '</span>');
              });
              toastr.error((response.message || "Please complete your form"), 'Failed !');
            }
            initCurrency();
          },
          error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
            initCurrency();
          }
        });
      });
    });
  </script>
@endsection
