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
      <div class="card-toolbar">
        <div class="dropdown dropdown-inline mr-2">
          <button type="button" class="btn btn-light-primary font-weight-bolder dropdown-toggle" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
            <span class="svg-icon svg-icon-md">
              <!--begin::Svg Icon | path:/metronic/theme/html/demo1/dist/assets/media/svg/icons/Design/PenAndRuller.svg-->
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
              <!--end::Svg Icon-->
            </span>Export
          </button>
          <!--begin::Dropdown Menu-->
          <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
            <!--begin::Navigation-->
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
            <!--end::Navigation-->
          </div>
          <!--end::Dropdown Menu-->
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
                  <label>Nama Supir:</label>
                  <select class="form-control" id="select2Driver">
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

      <!--begin: Datatable-->
      <table class="table table-hover" id="Datatable">
        <thead>
        <tr>
          <th>Nama Pelanggan</th>
          <th>T. Muat</th>
          <th>Sub Total</th>
          <th>Biaya Operasional</th>
          <th>Spare Part</th>
          <th>Gaji Supir</th>
          <th>Sisa Bersih</th>
        </tr>
        </thead>
        <tfoot>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        </tfoot>
      </table>
    </div>
  </div>
  {{-- Modal --}}
@endsection
{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {
      $('#btn_excel').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          driver_id: $('#select2Driver').find(':selected').val() || '',
          date: $("input[name=date]").val(),
        });
        window.location.href = '{{ $config['excel_url'] }}&' + params.toString();
      });

      $('#btn_pdf').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          driver_id: $('#select2Driver').find(':selected').val() || '',
          date: $("input[name=date]").val(),
        });
        location.href = '{{ $config['pdf_url'] }}&' + params.toString();
      });

      $('#btn_print').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          driver_id: $('#select2Driver').find(':selected').val() || '',
          date: $("input[name=date]").val(),
        });
        window.open('{{ $config['print_url'] }}?' + params.toString(), '_blank');
      });

      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[0, 'asc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 25,
        ajax: {
          url: "{{ route('backend.reportsalaries.index') }}",
          data: function (d) {
            d.driver_id = $('#select2Driver').find(':selected').val();
            d.date = $("input[name=date]").val();
          }
        },
        columns: [
          {data: 'costumer.name', name: 'costumer.name'},
          {data: 'date_begin', name: 'date_begin'},
          {
            data: 'total_basic_price_after_thanks', name: 'total_basic_price_after_thanks',
            orderable: false,
            searchable: false,
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_operational', name: 'total_operational',
            orderable: false,
            searchable: false,
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_sparepart', name: 'total_sparepart',
            orderable: false,
            searchable: false,
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_salary', name: 'total_salary',
            orderable: false,
            searchable: false,
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'total_clean_summary', name: 'total_clean_summary',
            orderable: false,
            searchable: false,
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right'
          },
        ],
        footerCallback: function (row, data, start, end, display) {
          let api = this.api();
          let intVal = function (i) {
            return typeof i === 'string' ?
              i.replace(/[\$,]/g, '') * 1 :
              typeof i === 'number' ?
                i : 0;
          };

          let totalBasic = api
            .column(2)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          let totalOperatinal = api
            .column(3)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          let totalSparepart = api
            .column(4)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          let totalSalary = api
            .column(5)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          let totalClean = api
            .column(6)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          $(api.column(1).footer()).html('Total');
          $(api.column(2).footer()).html(format(totalBasic));
          $(api.column(3).footer()).html(format(totalOperatinal));
          $(api.column(4).footer()).html(format(totalSparepart));
          $(api.column(5).footer()).html(format(totalSalary));
          $(api.column(6).footer()).html(format(totalClean));

        },
      });

      let format = function (num) {
        let str = num.toString().replace("", ""), parts = false, output = [], i = 1, formatted = null;
        if (str.indexOf(".") > 0) {
          parts = str.split(".");
          str = parts[0];
        }
        str = str.split("").reverse();
        for (let j = 0, len = str.length; j < len; j++) {
          if (str[j] !== ",") {
            output.push(str[j]);
            if (i % 3 === 0 && j < (len - 1)) {
              output.push(",");
            }
            i++;
          }
        }
        formatted = output.reverse().join("");
        return ("" + formatted + ((parts) ? "." + parts[1].substr(0, 2) : ".00"));
      };

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
      }).on('change', function (e) {
        dataTable.draw();
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

    });
  </script>
@endsection
