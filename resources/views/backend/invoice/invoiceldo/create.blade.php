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
    <form id="formStore" action="{{ route('backend.invoiceldo.store') }}">
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
                    <label class="col-lg-3 col-form-label">Tanggal Invoice:</label>
                    <div class="col-lg-9">
                      <input type="text" class="form-control rounded-0 datepicker w-100" name="invoice_date"
                             placeholder="Tanggal Invoice" readonly>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">No. Invoice Costumer:</label>
                    <div class="col-lg-9">
                      <input name="num_bill" type="hidden" value="{{ Carbon\Carbon::now()->timestamp }}">
                      <input class="form-control rounded-0" value="{{ Carbon\Carbon::now()->timestamp }}" disabled>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">Tgl Jatuh Tempo:</label>
                    <div class="col-lg-9">
                      <input type="text" class="form-control rounded-0 datepicker w-100" name="due_date"
                             placeholder="Tgl Jatuh Tempo" readonly="">
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">LDO:</label>
                    <div class="col-lg-9">
                      <select name="another_expedition_id" class="form-control" id="select2AnotherExpedition">
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">Prefix:</label>
                    <div class="col-lg-9">
                      <select name="prefix" class="form-control" id="select2Prefix">
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
                <table id="table_invoice" class="table table-responsive table-striped">
                  <thead>
                  <tr>
                    <th scope="col" class="text-center">#</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">S. Jalan</th>
                    <th scope="col">No. Polisi</th>
                    <th scope="col">Pelanggan</th>
                    <th scope="col">Rute Dari</th>
                    <th scope="col">Rute Ke</th>
                    <th scope="col">Jenis Barang</th>
                    <th scope="col">Tarif LDO (Rp.)</th>
                    <th scope="col">Qty (Unit)</th>
                    <th scope="col">Total Harga Dasar</th>
                    <th scope="col">Total Operasional</th>
                    <th scope="col">Tagihan Bersih</th>
                  </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
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
                  <tr>
                    <td><input type="text" class="form-control rounded-0 datepicker w-100" name="payment[date_payment]"
                               placeholder="Tanggal Invoice" readonly></td>
                    <td><input name="payment[description]" class="form-control rounded-0"/></td>
                    <td><select name="coa_id" class="form-control rounded-0" style="min-width: 250px">
                        @foreach($selectCoa->coa as $item)
                          <option value="{{ $item->id }}">{{ $item->code .' - '. $item->name }}</option>
                        @endforeach
                      </select></td>
                    <td><input type="text" name="payment[payment]" class="currency rounded-0 form-control"></td>
                    <td><input type="text" class="currency rounded-0 form-control total_payment" disabled></td>
                  </tr>
                  </tbody>
                  <tfoot>
                  <tr>
                    <td colspan="4" class="text-right">Total Tagihan</td>
                    <td class="text-right"><input type="text" name="total_bill" class="currency rounded-0 form-control"
                                                  disabled></td>
                  </tr>
                  <tr>
                    <td colspan="4" class="text-right">Total Pemotongan</td>
                    <td class="text-right"><input type="text" name="total_cut" class="currency rounded-0 form-control">
                    </td>
                  </tr>
                  <tr>
                    <td colspan="4" class="text-right">Total Pembayaran</td>
                    <td class="text-right"><input type="text" class="currency rounded-0 form-control total_payment"
                                                  disabled>
                    </td>
                  </tr>
                  <tr>
                    <input type="hidden" name="rest_payment" class="currency rounded-0 form-control rest_payment">
                    <td colspan="4" class="text-right">Sisa Pembayaran</td>
                    <td class="text-right"><input type="text" class="currency rounded-0 form-control rest_payment"
                                                  disabled>
                    </td>
                  </tr>
                  </tfoot>
                </table>
              </div>
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
        <button id="submitAppend" class="btn btn-primary">Masukan Ke Form Invoice</button>
      </div>
    </div>
    <div class="card-body">
      <!--begin: Datatable-->
      <table class="table table-bordered table-hover" id="Datatable">
        <thead>
        <tr>
          <th></th>
          <th>Tanggal Mulai</th>
          <th>Prefix</th>
          <th>No. Job Order</th>
          <th>No. Polisi</th>
          <th>LDO</th>
          <th>Pelanggan</th>
          <th>Rute Dari</th>
          <th>Rute Ke</th>
          <th>Jenis Barang</th>
          <th>Tarif (Rp.)</th>
          <th>Tarif LDO (Rp.)</th>
          <th>Qty (Unit)</th>
          <th>Total Harga Dasar</th>
          <th>Total Operasional</th>
          <th>Tagihan Bersih</th>
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {
      initDate();
      initCurrency();
      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[14, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.invoiceldo.create') }}",
          data: function (d) {
            d.costumer_id = $('#select2Costumer').find(':selected').val();
            d.another_expedition_id = $('#select2AnotherExpedition').find(':selected').val();
          }
        },
        columns: [
          {data: 'id', name: 'id'},
          {data: 'date_begin', name: 'date_begin'},
          {data: 'prefix', name: 'prefix'},
          {data: 'num_bill', name: 'num_bill'},
          {data: 'transport.num_pol', name: 'transport.num_pol'},
          {data: 'anotherexpedition.name', name: 'anotherexpedition.name'},
          {data: 'costumer.name', name: 'costumer.name'},
          {data: 'routefrom.name', name: 'routefrom.name'},
          {data: 'routeto.name', name: 'routeto.name'},
          {data: 'cargo.name', name: 'cargo.name'},
          {
            data: 'basic_price',
            name: 'basic_price',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'basic_price_ldo',
            name: 'basic_price_ldo',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {data: 'payload', name: 'payload', className: 'dt-center'},
          {
            data: 'total_basic_price_ldo',
            name: 'total_basic_price_ldo',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right',
            orderable: false,
            searchable: false
          },
          {
            data: 'roadmoneydetail_sum_amount',
            name: 'roadmoneydetail_sum_amount',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right',
            orderable: false,
            searchable: false
          },
          {
            data: 'total_netto_ldo',
            name: 'total_netto_ldo',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right',
            orderable: false,
            searchable: false
          },
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
          url: "{{ route('backend.invoiceldo.findbypk') }}",
          data: {data: JSON.stringify(dataSelected)},
          success: function (response) {
            if (response.data) {
              $('#table_invoice tbody').empty();
              $('#table_invoice tfoot').empty();
              $('#TampungId').empty();
              let total = 0;
              $.each(response.data, function (index, data) {
                total += parseFloat(data.total_netto_ldo);
                $('#TampungId').append('<input type="hidden" name="job_order_id[]" value="' + data.id + '">');
                $('#table_invoice tbody').append('<tr>' +
                  ' <td class="text-center">' + (index + 1) + '</td>' +
                  ' <td>' + data.date_begin + '</td>' +
                  ' <td>' + data.prefix + '-' + data.num_bill + '</td>' +
                  ' <td>' + data.transport.num_pol + '</td>' +
                  ' <td>' + data.costumer.name + '</td>' +
                  ' <td>' + data.routefrom.name + '</td>' +
                  ' <td>' + data.routeto.name + '</td>' +
                  ' <td>' + data.cargo.name + '</td>' +
                  ' <td class="text-right money">' + data.basic_price_ldo + '</td>' +
                  ' <td>' + data.payload + '</td>' +
                  ' <td class="text-right money">' + data.total_basic_price_ldo + '</td>' +
                  ' <td class="text-right money">' + data.roadmoneydetail_sum_amount + '</td>' +
                  ' <td class="text-right money">' + data.total_netto_ldo + '</td>' +
                  '</tr>');
              });
              $('#TampungId').append('<input type="hidden" name="total_bill" value="' + total + '">');

              $('#table_invoice tfoot').append('<tr>' +
                '<td colspan="12" class="text-right">Total</td>' +
                '<td class="text-right money">' + total + '</td>' +
                '</tr>');

              $(".money").inputmask({
                'alias': 'decimal',
                'groupSeparator': ',',
                'autoGroup': true,
                'digits': 2,
                'digitsOptional': false,
              });
              initCalculate();
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

      $("#select2Costumer").select2({
        placeholder: "Search Pelanggan",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.costumers.select2') }}",
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

      $("#select2AnotherExpedition").select2({
        placeholder: "Search LDO",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.anotherexpedition.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function (e) {
            let query = {
              q: e.term || '',
              page: e.page || 1
            }
            return query
          },
        },
      }).on('change', function () {
        dataTable.draw();
        $('#table_invoice tbody').empty();
        $('#table_invoice tfoot').empty();
        $('#TampungId').empty();
      });

      function initDate() {
        // $('.datepicker').datepicker({
        //   format: 'yyyy-mm-dd',
        //   todayBtn: "linked",
        //   clearBtn: true,
        //   todayHighlight: true,
        // });

        $(".datepicker").flatpickr();

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
        let total_cut = parseFloat($('input[name="total_cut"]').val()) || 0;
        let total_payment = parseFloat($('input[name="payment[payment]"]').val()) || 0;
        let rest_payment = total_bill - total_cut - total_payment;
        $('.total_payment').val(total_payment);
        $('.rest_payment').val(rest_payment);
        $('input[name=total_bill]').val(total_bill);
      }

      $('input[name="payment[payment]"],input[name="total_cut"],#diskon').on('keyup', function () {
        initCalculate();
      });

      $("#formStore").submit(function (e) {
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
          },
          error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
          }
        });
      });
    });
  </script>
@endsection
