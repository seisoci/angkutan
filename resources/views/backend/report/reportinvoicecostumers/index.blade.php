@extends('layout.default')

@section('content')
  <div class="card card-custom">
    <div class="card-header flex-wrap py-3">
      <div class="card-title">
        <h3 class="card-label">{{ $config['page_title'] }}
          <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
      </div>
      <div class="card-toolbar">
        <div class="dropdown dropdown-inline mr-2">
          <button type="button" class="btn btn-light-primary font-weight-bolder dropdown-toggle" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
            <span class="svg-icon svg-icon-md">
              <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                   width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                  <rect x="0" y="0" width="24" height="24"></rect>
                  <path
                    d="M3,16 L5,16 C5.55228475,16 6,15.5522847 6,15 C6,14.4477153 5.55228475,14 5,14 L3,14 L3,12 L5,12 C5.55228475,12 6,11.5522847 6,11 C6,10.4477153 5.55228475,10 5,10 L3,10 L3,8 L5,8 C5.55228475,8 6,7.55228475 6,7 C6,6.44771525 5.55228475,6 5,6 L3,6 L3,4 C3,3.44771525 3.44771525,3 4,3 L10,3 C10.5522847,3 11,3.44771525 11,4 L11,19 C11,19.5522847 10.5522847,20 10,20 L4,20 C3.44771525,20 3,19.5522847 3,19 L3,16 Z"
                    fill="#000000" opacity="0.3"></path>
                  <path
                    d="M16,3 L19,3 C20.1045695,3 21,3.8954305 21,5 L21,15.2485298 C21,15.7329761 20.8241635,16.200956 20.5051534,16.565539 L17.8762883,19.5699562 C17.6944473,19.7777745 17.378566,19.7988332 17.1707477,19.6169922 C17.1540423,19.602375 17.1383289,19.5866616 17.1237117,19.5699562 L14.4948466,16.565539 C14.1758365,16.200956 14,15.7329761 14,15.2485298 L14,5 C14,3.8954305 14.8954305,3 16,3 Z"
                    fill="#000000"></path>
                </g>
              </svg>
            </span>Export
          </button>
          <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
            <ul class="navi flex-column navi-hover py-2">
              <li class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">Choose an
                option:
              </li>
              <li class="navi-item">
                <a href="#" id="btn_print" class="navi-link" target="_blank">
                  <span class="navi-icon">
                    <i class="la la-print"></i>
                  </span>
                  <span class="navi-text">Print</span>
                </a>
              </li>
              <li class="navi-item">
                <a href="#" id="btn_excel" class="navi-link">
                  <span class="navi-icon">
                    <i class="la la-file-excel-o"></i>
                  </span>
                  <span class="navi-text">Excel</span>
                </a>
              </li>
              <li class="navi-item">
                <a href="#" id="btn_pdf" class="navi-link">
                  <span class="navi-icon">
                    <i class="la la-file-pdf-o"></i>
                  </span>
                  <span class="navi-text">PDF</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="mb-10">
        <div class="row align-items-center">
          <div class="col-12">
            <div class="row align-items-center">
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Status Pembayaran:</label>
                  <select class="form-control" id="statusPayment">
                    <option value="">Pilih Status</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4 my-md-0">
                <div class="form-group">
                  <label>Nama Pelanggan:</label>
                  <select class="form-control" id="select2Costumer">
                  </select>
                </div>
              </div>
              <div class="col-md-4 my-md-0">
                <div class="form-group">
                  <label>Priode:</label>
                  <div class="input-group" id="dateRangePicker">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="la la-calendar-check-o"></i></span>
                    </div>
                    <input type="text" class="form-control" name="date" placeholder="Choose Date">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <table class="table table-bordered table-hover" id="Datatable">
        <thead>
        <tr>
          <th></th>
          <th>Invoice Number</th>
          <th>Tgl Invoice</th>
          <th>Tgl Jth. Tempo Invoice</th>
          <th>Nama Pelanggan</th>
          <th>Total Tagihan</th>
          <th>Total Pembayaran</th>
          <th>Potongan</th>
          <th>Pajak (Rp.)</th>
          <th>Fee</th>
          <th>Sisa Tagihan</th>
          <th>Created At</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
          <td colspan="5">Total</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        </tfoot>
      </table>
    </div>
  </div>
@endsection

@section('styles')
  <link href="{{ asset('css/backend/datatables/dataTables.control.css') }}" rel="stylesheet" type="text/css"/>
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  <script id="details-template" type="text/x-handlebars-template">
    @verbatim
    <table class="table table-bordered " id="posts-{{id}}">
      <thead>
      <tr>
        <th>No. Surat Jalan</th>
        <th>Tgl Muat</th>
        <th>No. Polisi</th>
        <th>Nama Supir</th>
        <th>Rute dari</th>
        <th>Rute Tujuan</th>
        <th>Muatan</th>
        <th>Fee Thanks</th>
        <th>Total Tagihan (Inc. Tax)</th>
      </tr>
      </thead>
    </table>
    @endverbatim
  </script>
  <script type="text/javascript">
    $(function () {
      $('#btn_excel').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          date: $("input[name=date]").val(),
          costumer_id: $('#select2Costumer').find(':selected').val() || '',
          status_payment: $('#statusPayment').find(':selected').val() || '',
        });
        window.location.href = '{{ $config['excel_url'] }}&' + params.toString();
      });

      $('#btn_pdf').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          date: $("input[name=date]").val(),
          costumer_id: $('#select2Costumer').find(':selected').val() || '',
          status_payment: $('#statusPayment').find(':selected').val() || '',
        });
        location.href = '{{ $config['pdf_url'] }}&' + params.toString();
      });

      $('#btn_print').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          date: $("input[name=date]").val(),
          costumer_id: $('#select2Costumer').find(':selected').val() || '',
          status_payment: $('#statusPayment').find(':selected').val() || '',
        });
        window.open('{{ $config['print_url'] }}?' + params.toString());
      });

      let template = Handlebars.compile($("#details-template").html());
      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        searching: false,
        bSort: false,
        orderable: false,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.reportinvoicecostumers.index') }}",
          data: function (d) {
            d.status_payment = $('#statusPayment').find(':selected').val();
            d.costumer_id = $('#select2Costumer').find(':selected').val();
            d.date = $("input[name=date]").val();
          }
        },
        columns: [
          {
            "className": 'details-control',
            "orderable": false,
            "searchable": false,
            "data": null,
            "defaultContent": ''
          },
          {data: 'num_invoice', name: 'num_invoice', orderable: false},
          {data: 'invoice_date', name: 'invoice_date'},
          {data: 'due_date', name: 'due_date'},
          {data: 'costumer.name', name: 'costumer.name'},
          {
            data: 'total_bill',
            name: 'total_bill',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_payment',
            name: 'total_payment',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_cut',
            name: 'total_cut',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_tax',
            name: 'total_tax',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_fee_thanks',
            name: 'total_fee_thanks',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'rest_payment',
            name: 'rest_payment',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {data: 'created_at', name: 'created_at'},

        ],
        footerCallback: function (row, data, start, end, display) {
          var api = this.api();

          var intVal = function (i) {
            return typeof i === 'string' ?
              i.replace(/[\$,]/g, '') * 1 :
              typeof i === 'number' ?
                i : 0;
          };

          var totalTagihan = api
            .column(5)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          var totalPembayaran = api
            .column(6)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          var totalPotongan = api
            .column(7)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          var totalPajak = api
            .column(8)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          var totalFee = api
            .column(9)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          var sisaTagihan = api
            .column(10)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          // Update footer
          $(api.column(5).footer()).html($.fn.dataTable.render.number(',', '.', 2, '').display(totalTagihan));
          $(api.column(6).footer()).html($.fn.dataTable.render.number(',', '.', 2, '').display(totalPembayaran));
          $(api.column(7).footer()).html($.fn.dataTable.render.number(',', '.', 2, '').display(totalPotongan));
          $(api.column(8).footer()).html($.fn.dataTable.render.number(',', '.', 2, '').display(totalPajak));
          $(api.column(9).footer()).html($.fn.dataTable.render.number(',', '.', 2, '').display(totalFee));
          $(api.column(10).footer()).html($.fn.dataTable.render.number(',', '.', 2, '').display(sisaTagihan));
        }
      });

      $('#dateRangePicker').daterangepicker({
        buttonClasses: ' btn',
        applyClass: 'btn-primary',
        cancelClass: 'btn-secondary'
      }, function (start, end, label) {
        $('#dateRangePicker .form-control').val(start.format('YYYY-MM-DD') + ' / ' + end.format('YYYY-MM-DD'));
        dataTable.draw();
      }).on('cancel.daterangepicker', function (ev, picker) {
        $('#dateRangePicker .form-control').val('');
        dataTable.draw();
      });

      $('#statusPayment').on('change', function () {
        dataTable.draw();
      });

      $("#select2Costumer").select2({
        placeholder: "Search Supir",
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
      });

      $('#Datatable tbody').on('click', 'td.details-control', function () {
        let tr = $(this).closest('tr');
        let row = dataTable.row(tr);
        let tableId = 'posts-' + row.data().id;

        if (row.child.isShown()) {
          row.child.hide();
          tr.removeClass('shown');
        } else {
          row.child(template(row.data())).show();
          initTable(tableId, row.data());
          tr.addClass('shown');
          tr.next().find('td').addClass('no-padding bg-gray');
        }
      });

      function initTable(tableId, data) {
        $('#' + tableId).DataTable({
          processing: true,
          serverSide: true,
          orderable: false,
          ajax: data.details_url,
          columns: [
            {data: 'num_bill', name: 'num_bill'},
            {data: 'date_begin', name: 'date_begin'},
            {data: 'transport.num_pol', name: 'transport.num_pol'},
            {data: 'driver.name', name: 'driver.name'},
            {data: 'routefrom.name', name: 'routefrom.name'},
            {data: 'routeto.name', name: 'routeto.name'},
            {data: 'cargo.name', name: 'cargo.name'},
            {
              data: 'fee_thanks',
              name: 'fee_thanks',
              render: $.fn.dataTable.render.number(',', '.', 2),
              orderable: false,
              searchable: false,
              className: 'dt-right',
              defaultContent: 0
            },
            {
              data: 'total_basic_price_after_tax',
              name: 'total_basic_price_after_tax',
              render: $.fn.dataTable.render.number(',', '.', 2),
              orderable: false,
              searchable: false,
              className: 'dt-right'
            }
          ]
        })
      }
    });
  </script>
@endsection
