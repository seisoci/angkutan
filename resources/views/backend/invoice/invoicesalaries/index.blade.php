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
        <!--begin::Button-->
        <a href="{{ route('backend.invoicesalaries.create') }}" class="btn btn-primary font-weight-bolder">
        <span class="svg-icon svg-icon-md">
          <!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
          <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
               viewBox="0 0 24 24" version="1.1">
            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
              <rect x="0" y="0" width="24" height="24"></rect>
              <circle fill="#000000" cx="9" cy="15" r="6"></circle>
              <path
                d="M8.8012943,7.00241953 C9.83837775,5.20768121 11.7781543,4 14,4 C17.3137085,4 20,6.6862915 20,10 C20,12.2218457 18.7923188,14.1616223 16.9975805,15.1987057 C16.9991904,15.1326658 17,15.0664274 17,15 C17,10.581722 13.418278,7 9,7 C8.93357256,7 8.86733422,7.00080962 8.8012943,7.00241953 Z"
                fill="#000000" opacity="0.3"></path>
            </g>
          </svg>
          <!--end::Svg Icon-->
        </span>New Record</a>
        <!--end::Button-->
      </div>
    </div>

    <div class="card-body">
      <!--begin: Datatable-->
      <div class="row">
        <div class="col-md-3 my-md-0">
          <div class="form-group">
            <label>Supir:</label>
            <select class="form-control" id="select2Driver">
            </select>
          </div>
        </div>
        <div class="col-md-4 my-md-0">
          <div class="form-group">
            <label>Priode:</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="la la-calendar-check-o"></i></span>
              </div>
              <input id="datePicker" type="text" class="form-control" name="date" placeholder="Choose Date" readonly>
            </div>
          </div>
        </div>
      </div>
      <table class="table table-bordered table-hover" id="Datatable">
        <thead>
        <tr>
          <th></th>
          <th>No. Invoice</th>
          <th>Tgl. Invoice</th>
          <th>Nama Supir</th>
          <th>Grand Total</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
          <th></th>
          <th></th>
          <th></th>
          <th class="text-right"></th>
          <th></th>
          <th></th>
          <th></th>
        </tr>
        </tfoot>
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

  <script id="details-template" type="text/x-handlebars-template">
    @verbatim
    <table class="table table-hover" id="posts-{{id}}">
      <thead>
      <tr>
        <th>No. Surat Jalan</th>
        <th>Total Salary</th>
      </tr>
      </thead>
    </table>
    @endverbatim
  </script>
  <script type="text/javascript">
    $(function () {
      let template = Handlebars.compile($("#details-template").html());
      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[2, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.invoicesalaries.index') }}",
          data: function (d) {
            d.driver_id = $('#select2Driver').find(':selected').val();
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
          {data: 'num_invoice', name: 'num_bill', orderable: false},
          {data: 'invoice_date', name: 'invoice_date'},
          {data: 'driver.name', name: 'driver.name'},
          {
            data: 'grandtotal',
            name: 'grandtotal',
            render: $.fn.dataTable.render.number('.', '.', 2),
            className: 'dt-right'
          },
          {data: 'created_at', name: 'created_at'},
          {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        footerCallback: function (row, data, start, end, display) {
          let api = this.api();
          let intVal = function (i) {
            return typeof i === 'string' ?
              i.replace(/[\$,]/g, '') * 1 :
              typeof i === 'number' ?
                i : 0;
          };

          let totalSalary = api
            .column(4)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          $(api.column(3).footer()).html('Total');
          $(api.column(4).footer()).html(format(totalSalary));
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

      // $('#dateRangePicker').daterangepicker({
      //   buttonClasses: ' btn',
      //   applyClass: 'btn-primary',
      //   cancelClass: 'btn-secondary'
      // }, function (start, end, label) {
      //   $('#dateRangePicker .form-control').val(start.format('YYYY-MM-DD') + ' / ' + end.format('YYYY-MM-DD'));
      //   dataTable.draw();
      // }).on('cancel.daterangepicker', function (ev, picker) {
      //   $('#dateRangePicker .form-control').val('');
      //   dataTable.draw();
      // });

      $('#datePicker').datepicker({
        format: 'yyyy-mm',
        startView: "months",
        minViewMode: "months",
        orientation: "bottom",
        autoclose: true,
      }).on('change', function (e) {
        dataTable.draw();
      });

      $('#Datatable tbody').on('click', 'td.details-control', function () {
        let tr = $(this).closest('tr');
        let row = dataTable.row(tr);
        let tableId = 'posts-' + row.data().id;

        if (row.child.isShown()) {
          // This row is already open - close it
          row.child.hide();
          tr.removeClass('shown');
        } else {
          // Open this row
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
          ajax: data.details_url,
          columns: [
            {data: 'num_prefix', name: 'num_bill', orderable: false},
            {
              data: 'total_salary',
              name: 'total_salary',
              render: $.fn.dataTable.render.number('.', '.', 2),
              orderable: false,
              searchable: false,
              className: 'dt-right'
            }
          ]
        })
      }

      $('#modalDelete').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.drivers.index") }}/' + id);
      });

      $('#modalDelete').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
      });

      $("#formDelete").click(function (e) {
        e.preventDefault();
        let form = $(this);
        let url = $('#modalDelete').find('a[name="id"]').attr('href');
        let btnHtml = form.html();
        let spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
        $.ajax({
          beforeSend: function () {
            form.prop('disabled', true).html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading...");
          },
          type: 'DELETE',
          url: url,
          dataType: 'json',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          success: function (response) {
            if (response.status == "success") {
              form.prop('disabled', false).html(btnHtml);
              toastr.success(response.message, 'Success !');
              $('#modalDelete').modal('hide');
              dataTable.draw();
            } else {
              form.prop('disabled', false).html(btnHtml);
              toastr.error(response.message, 'Failed !');
              $('#modalDelete').modal('hide');
            }
          },
          error: function (response) {
            form.prop('disabled', false).text('Submit').find("[role='status']").removeClass("spinner-border spinner-border-sm").html(btnHtml);
            toastr.error(response.responseJSON.message, 'Failed !');
            $('#modalDelete').modal('hide');
            $('#modalDelete').find('a[name="id"]').attr('href', '');
          }
        });
      });
    });
  </script>
@endsection
