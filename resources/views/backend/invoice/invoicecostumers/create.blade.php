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
    <form id="formStore" action="{{ route('backend.invoicecostumers.store') }}">
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
                    <div class="col-md-6">
                      <input type="text" class="form-control rounded-0 datepicker w-100" name="invoice_date"
                             placeholder="Tanggal Invoice" readonly>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">Prefix:</label>
                    <div class="col-lg-6">
                      <select name="prefix" class="form-control" id="select2Prefix">
                      </select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">No. Invoice Costumer:</label>
                    <div class="col-lg-6">
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
                  <div class="form-group row">
                    <label class="col-lg-3 col-form-label">Pelanggan:</label>
                    <div class="col-lg-9">
                      <select name="costumer_id" class="form-control" id="select2Costumer">
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
                    <th>#</th>
                    <th scope="col" class="text-center">No.</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">No. JobOrder</th>
                    <th scope="col">No. Polisi</th>
                    <th scope="col">No. SJ</th>
                    <th scope="col">No. Shipment</th>
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
                  </tbody>
                  <tfoot>
                  </tfoot>
                </table>
              </div>
              <h3 class="pt-10"><u>Pembayaran</u></h3>
              <div class="table-responsive">
                <table class="table table-borderless">
                  <thead>
                  <tr>
                    <th scope="col" style="min-width: 150px">Tanggal Pembayaran</th>
                    <th scope="col" style="min-width: 200px">Keterangan</th>
                    <th scope="col" style="min-width: 150px">Master Akun</th>
                    <th scope="col" style="min-width: 150px">Nominal</th>
                    <th scope="col" style="width: 160px; min-width: 160px">Total Dibayar</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <td><input type="text" class="form-control rounded-0 datepicker w-100" name="payment[date_payment]"
                               placeholder="Tanggal Invoice" readonly></td>
                    <td><input name="payment[description]" class="form-control rounded-0"/></td>
                    <td><select name="coa_id" class="form-control rounded-0" style="width: 250px">
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
                    <td class="text-right"><input type="text" name="total_tagihan"
                                                  class="currency rounded-0 form-control"
                                                  disabled></td>
                  </tr>
                  <tr>
                    <td colspan="4" class="text-right">Total Piutang</td>
                    <td class="text-right"><input type="text" class="currency rounded-0 form-control total_piutang"
                                                  disabled>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="4" class="text-right">Total Klaim</td>
                    <td class="text-right"><input type="text" class="currency rounded-0 form-control total_klaim"
                                                  disabled>
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
      <table class="table table-hover" id="Datatable">
        <thead>
        <tr>
          <th></th>
          <th>Tanggal Mulai</th>
          <th>Prefix</th>
          <th>No. Job Order</th>
          <th>No. Polisi</th>
          <th>No. SJ</th>
          <th>No. Shipment</th>
          <th>Pelanggan</th>
          <th>Rute Dari</th>
          <th>Rute Ke</th>
          <th>Jenis Barang</th>
          <th>Tarif (Rp.)</th>
          <th>Qty (Unit)</th>
          <th>Tagihan</th>
          <th>Pajak (%)</th>
          <th>Pajak (Rp.)</th>
          <th>Fee Thanks</th>
          <th>Tagihan (Inc. Tax)</th>
          <th>Tagihan (Inc. Tax & Fee)</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  {{--  Modal--}}
  <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
       aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Tambah</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <div class="modal-body">
          <form>
            <input type="hidden" name="tb_job_order_id">
            <div class="form-group">
              <label for="selectType" class="col-form-label">Tipe:</label>
              <select class="form-control" name="type" id="selectType">
                <option value="tambah">Tambah</option>
                <option value="kurang">Kurang</option>
              </select>
            </div>
            <div class="form-group">
              <label for="message-text" class="col-form-label">Nominal:</label>
              <input class="form-control currency" name="nominal">
            </div>
            <div class="form-group">
              <label for="message-text" class="col-form-label">Keterangan:</label>
              <textarea class="form-control" name="keterangan" rows="4"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button id="addRow" type="button" class="btn btn-primary">Submit</button>
        </div>
      </div>
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
      initDate();
      initCurrency();
      initCalculate();
      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[1, 'asc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.invoicecostumers.create') }}",
          data: function (d) {
            d.costumer_id = $('#select2Costumer').find(':selected').val();
          }
        },
        columns: [
          {data: 'id', name: 'id'},
          {data: 'date_begin', name: 'date_begin'},
          {data: 'prefix', name: 'prefix'},
          {data: 'num_bill', name: 'num_bill'},
          {data: 'transport.num_pol', name: 'transport.num_pol'},
          {data: 'no_sj', name: 'no_sj'},
          {data: 'no_shipment', name: 'no_shipment'},
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
          {data: 'payload', name: 'payload', className: 'dt-center'},
          {
            data: 'invoice_bill',
            name: 'invoice_bill',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {data: 'tax_percent', name: 'tax_percent', className: 'dt-center'},
          {
            data: 'fee_thanks',
            name: 'fee_thanks',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'tax_amount',
            name: 'tax_amount',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right',
            orderable: false,
            searchable: false
          },
          {
            data: 'total_basic_price_after_tax',
            name: 'total_basic_price_after_tax',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right',
            orderable: false,
            searchable: false
          },
          {
            data: 'total_basic_price_after_thanks',
            name: 'total_basic_price_after_thanks',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right',
            orderable: false,
            searchable: false
          },
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
        let totalKlaim = 0;
        let totalPiutang = 0;
        $("input[name*='[kurang][nominal]']").each(function () {
          totalKlaim += parseInt($(this).val()) || 0;
        });

        $("input[name*='[tambah][nominal]']").each(function () {
          totalPiutang += parseInt($(this).val()) || 0;
        });

        let total_bill = parseFloat($('input[name="total_bill"]').val()) || 0;
        let total_payment = parseFloat($('input[name="payment[payment]"]').val()) || 0;
        let totalTagihan = total_bill;
        let rest_payment = total_bill - total_payment + totalPiutang - totalKlaim;
        $('#totalTagihan').val(totalTagihan);
        $('input[name="total_tagihan"]').val(totalTagihan);
        $('.total_payment').val(total_payment);
        $('.rest_payment').val(rest_payment);
        $('.total_klaim').val(totalKlaim);
        $('.total_piutang').val(totalPiutang);
      }

      $('input[name="payment[payment]"],input[name="total_cut"],input[name="total_piutang"],#diskon').on('keyup', function () {
        initCalculate();
      });

      $('#addModal').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('input[name="tb_job_order_id"]').val(id);

      });
      $('#addModal').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('input[name="tb_job_order_id"]').val('');
        $(this).find('.modal-body').find('input[name="nominal"]').val('');
        $(this).find('.modal-body').find('textarea[name="keterangan"]').val('');
      });

      $('#addRow').on('click', function () {
        let jobOrderId = $(this).parent().parent().find('input[name="tb_job_order_id"]').val();
        let keterangan = $(this).parent().parent().find('textarea[name="keterangan"]').val();
        let nominal = $(this).parent().parent().find('input[name="nominal"]').val();
        let select = $(this).parent().parent().find('select[name="type"]').val();
        let typeVar = '';
        if (select == 'tambah' && !$.trim($('#jo_' + jobOrderId + '_tambahan').html())) {
          typeVar = 'jo_' + jobOrderId + '_tambahan';
          $("#jo_" + jobOrderId).after('<tr id="' + typeVar + '">' +
            '<td><button type="button" class="btn btn-sm btn-danger deleteItem">-</button></td>' +
            '<td><input type="hidden" name="job_orderid['+jobOrderId+'][tambah][nominal]" value="'+nominal+'"></td>' +
            '<td><span class="badge badge-success">Penambahan</span></td>' +
            '<td colspan="13">' + keterangan + '<input type="hidden" name="job_orderid['+jobOrderId+'][tambah][keterangan]" value="'+keterangan+'"></td>' +
            '<td class="text-right money">' + nominal + '</td>' +
            '</tr>');
        } else if (select == 'kurang' && !$.trim($('#jo_' + jobOrderId + '_pengurangan').html())) {
          typeVar = 'jo_' + jobOrderId + '_pengurangan';
          console.log(nominal);
          $("#jo_" + jobOrderId).after('<tr id="' + typeVar + '">' +
            '<td><button type="button" class="btn btn-sm btn-danger deleteItem">-</button></td>' +
            '<td><input type="hidden" name="job_orderid['+jobOrderId+'][kurang][nominal]" value="'+nominal+'"></td>' +
            '<td><span class="badge badge-danger">Pengurangan</span></td>' +
            '<td colspan="13">' + keterangan + '<input type="hidden" name="job_orderid['+jobOrderId+'][kurang][keterangan]" value="'+keterangan+'"></td>' +
          '<td class="text-right money">' + nominal + '</td>' +
          '</tr>'
        );
        }

        $(".money").inputmask({
          'alias': 'decimal',
          'groupSeparator': ',',
          'autoGroup': true,
          'digits': 2,
          'digitsOptional': false,
        });

        initCalculate();
        $('#addModal').modal('hide');

        $('.deleteItem').on('click', function (){
          $(this).parent().parent().empty();
          initCalculate();
        });
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
          url: "{{ route('backend.invoicecostumers.findbypk') }}",
          data: {data: JSON.stringify(dataSelected)},
          success: function (response) {
            if (response.data) {
              $('#table_invoice tbody').empty();
              $('#table_invoice tfoot').empty();
              $('#TampungId').empty();
              let total = 0;
              let totalBasicPriceAfterThanks = 0;
              let totalTax = 0;
              let totalFee = 0;
              let totalTagihan = 0;
              $.each(response.data, function (index, data) {
                total += parseFloat(data.total_basic_price) || 0;
                totalTax += parseFloat(data.tax_amount) || 0;
                totalFee += parseFloat(data.fee_thanks) || 0;
                totalBasicPriceAfterThanks += parseFloat(data.total_basic_price_after_thanks) || 0;
                $('#TampungId').append('<input type="hidden" name="job_order_id[]" value="' + data.id + '">');
                $('#table_invoice tbody').append('<tr id="jo_' + data.id + '">' +
                  '<td><button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addModal" data-id="' + data.id + '">+</button></td>' +
                  ' <td class="text-center">' + (index + 1) + '</td>' +
                  ' <td>' + data.date_begin + '</td>' +
                  ' <td>' + data.prefix + '-' + data.num_bill + '</td>' +
                  ' <td>' + data.transport.num_pol + '</td>' +
                  ' <td>' + (data.no_sj ?? '') + '</td>' +
                  ' <td>' + (data.no_shipment ?? '') + '</td>' +
                  ' <td>' + data.costumer.name + '</td>' +
                  ' <td>' + data.routefrom.name + '</td>' +
                  ' <td>' + data.routeto.name + '</td>' +
                  ' <td>' + data.cargo.name + '</td>' +
                  ' <td class="text-right money">' + data.basic_price + '</td>' +
                  ' <td class="text-center">' + data.payload + '</td>' +
                  ' <td class="text-center">' + (data.tax_percent ? data.tax_percent : 0) + '</td>' +
                  ' <td class="text-right money">' + data.tax_amount + '</td>' +
                  ' <td class="text-right money">' + (data.fee_thanks ? data.fee_thanks : 0) + '</td>' +
                  ' <td class="text-right money">' + data.total_basic_price + '</td>' +
                  '</tr>');

              });
              totalTagihan = total;
              $('input[name=total_tagihan]').val(totalTagihan);
              $('#TampungId').append('<input type="hidden" name="total_bill" value="' + total + '">' +
                '<input type="hidden" name="total_tax" value="' + totalTax + '">' +
                '<input type="hidden" name="total_fee" value="' + totalFee + '">' +
                '<input type="hidden" name="total_basic_price_after_thanks" value="' + totalBasicPriceAfterThanks + '">');

              $('#table_invoice tfoot').append('<tr>' +
                '<td colspan="14" class="text-right">Total</td>' +
                '<td class="text-right money">' + totalTax + '</td>' +
                '<td class="text-right money">' + totalFee + '</td>' +
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

      $('#statusCargoModal').on('change', function () {
        if (this.value == 'selesai') {
          $("#dateEndModal").parent().css("display", "block");
          $("#dateEndModal").parent().find('label').css("display", "block");
        } else {
          $("#dateEndModal").parent().css("display", "none");
          $("#dateEndModal").parent().find('label').css("display", "none");
        }
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
