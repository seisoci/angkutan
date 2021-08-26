{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
  {{-- Dashboard 1 --}}
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
      <table class="table table-bordered table-hover" id="Datatable">
        <thead>
        <tr>
          <th>No. Job Order</th>
          <th>Tgl Mulai</th>
          <th>Tgl Selesai</th>
          <th>LDO</th>
          <th>Supir LDO</th>
          <th>Pelanggan</th>
          <th>Rute Dari</th>
          <th>Rute Ke</th>
          <th>Muatan</th>
          <th>Total Harga Dasar</th>
          <th>Total Harga Dasar(Inc. Tax & FEE)</th>
          <th>Total Harga Dasar LDO</th>
          <th>Created At</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('css/backend/datatables/dataTables.control.css') }}" rel="stylesheet" type="text/css"/>
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  <script type="text/javascript">
    $(function () {
      $('#btn_excel').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          date: $("input[name=date]").val(),
          status_payment: $('#statusPayment').find(':selected').val() || '',
        });
        window.location.href = '{{ $config['excel_url'] }}&' + params.toString();
      });

      $('#btn_pdf').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          date: $("input[name=date]").val(),
          status_payment: $('#statusPayment').find(':selected').val() || '',
        });
        location.href = '{{ $config['pdf_url'] }}&' + params.toString();
      });

      $('#btn_print').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          date: $("input[name=date]").val(),
          status_payment: $('#statusPayment').find(':selected').val() || '',
        });
        window.open('{{ $config['print_url'] }}?' + params.toString());
      });

      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        order: [12, 'desc'],
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: [
          'colvis'
        ],
        ajax: {
          url: "{{ route('backend.reportldonetprofit.index') }}",
          data: function (d) {
            d.status_payment = $('#statusPayment').find(':selected').val();
            d.date = $("input[name=date]").val();
          }
        },
        columns: [
          {data: 'num_prefix', name: 'num_bill', orderable: false},
          {data: 'date_begin', name: 'date_begin'},
          {data: 'date_end', name: 'date_end'},
          {data: 'anotherexpedition.name', name: 'anotherexpedition.name'},
          {data: 'driver.name', name: 'driver.name'},
          {data: 'costumer.name', name: 'costumer.name'},
          {data: 'routefrom.name', name: 'routefrom.name'},
          {data: 'routeto.name', name: 'routeto.name'},
          {data: 'cargo.name', name: 'cargo.name'},
          {
            data: 'total_basic_price',
            name: 'total_basic_price',
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
          {
            data: 'total_basic_price_ldo',
            name: 'total_basic_price_ldo',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right',
            orderable: false,
            searchable: false
          },
          {data: 'created_at', name: 'created_at'},
        ],
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

    });
  </script>
@endsection
