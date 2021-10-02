{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

  <div class="alert alert-custom alert-white alert-shadow gutter-b" role="alert">
    <div class="alert-icon">
  	  <span class="svg-icon svg-icon-primary svg-icon-xl">
  	  <!--begin::Svg Icon | path:/metronic/theme/html/demo1/dist/assets/media/svg/icons/Tools/Compass.svg-->
  		<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
           height="24px" viewBox="0 0 24 24" version="1.1">
  		<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
  		<rect x="0" y="0" width="24" height="24"></rect>
  		<path
        d="M7.07744993,12.3040451 C7.72444571,13.0716094 8.54044565,13.6920474 9.46808594,14.1079953 L5,23 L4.5,18 L7.07744993,12.3040451 Z M14.5865511,14.2597864 C15.5319561,13.9019016 16.375416,13.3366121 17.0614026,12.6194459 L19.5,18 L19,23 L14.5865511,14.2597864 Z M12,3.55271368e-14 C12.8284271,3.53749572e-14 13.5,0.671572875 13.5,1.5 L13.5,4 L10.5,4 L10.5,1.5 C10.5,0.671572875 11.1715729,3.56793164e-14 12,3.55271368e-14 Z"
        fill="#000000" opacity="0.3"></path>
  		<path
        d="M12,10 C13.1045695,10 14,9.1045695 14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 C10,9.1045695 10.8954305,10 12,10 Z M12,13 C9.23857625,13 7,10.7614237 7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 C17,10.7614237 14.7614237,13 12,13 Z"
        fill="#000000" fill-rule="nonzero"></path>
  		</g>
  		</svg>
        <!--end::Svg Icon-->
  		</span>
    </div>
    <div class="alert-text">
      <ul>
        <li>Menghapus 1 Jurnal Transaksi akan menghapus jurnal terkait.</li>
        <li>Jurnal Job Order, Invoice, Pembelian, Retur, tidak dapat dihapus.</li>
      </ul>
    </div>
  </div>

  <!--begin::Card-->
  <div class="card card-custom">
    <div class="card-header flex-wrap py-3">
      <div class="card-title">
        <h3 class="card-label">{{ $config['page_title'] }}
          <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
      </div>
      <div class="card-toolbar">
        <!--begin::Button-->
        <a href="{{ route('backend.journals.create') }}" class="btn btn-primary font-weight-bolder">
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
      <div class="mb-10">
        <div class="row align-items-center">
          <div class="col-12">
            <div class="row align-items-center">
              <div class="col-md-4 my-md-0">
                <div class="form-group">
                  <label>Tanggal Transaksi Jurnal:</label>
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
      <table class="table table-borderless table-hover" id="Datatable">
        <thead>
        <tr>
          <th>ID</th>
          <th>Tanggal</th>
          <th>Kode Akun</th>
          <th>Deskripsi</th>
          <th>Debit</th>
          <th>Kredit</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
        </tr>
        </tfoot>
      </table>
    </div>
  </div>
  <div class="modal fade text-left" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="modalDeleteLabel"
       aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDeleteLabel">Delete</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @method('DELETE')
        <div class="modal-body">
          <a href="" type="hidden" name="id" disabled></a>
          Are you sure you want to delete this item?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button id="formDelete" type="button" class="btn btn-danger">Accept</button>
        </div>
      </div>
    </div>
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
  <style>
    .dataTable td, .dataTable th {
      font-size: 0.8rem !important;
    }
  </style>
@endsection


{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {
      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[1, 'desc'], [0, 'desc']],
        lengthMenu: [[50, 100, -1], [50, 100, "All"]],
        pageLength: 50,
        ajax: {
          url: "{{ route('backend.journals.index') }}",
          data: function (d) {
            d.date = $('input[name=date]').val();
          }
        },
        columns: [
          {data: 'id', name: 'id'},
          {data: 'date_journal', name: 'date_journal'},
          {data: 'coa.kode_akun', name: 'coa.kode_akun', searchable: false},
          {data: 'description', name: 'description'},
          {data: 'debit', name: 'debit', render: $.fn.dataTable.render.number(',', '.', 2), className: 'dt-right'},
          {data: 'kredit', name: 'kredit', render: $.fn.dataTable.render.number(',', '.', 2), className: 'dt-right'},
          {data: 'action', name: 'action'},
        ],
        footerCallback: function (row, data, start, end, display) {
          let api = this.api();
          let intVal = function (i) {
            return typeof i === 'string' ?
              i.replace(/[\$,]/g, '') * 1 :
              typeof i === 'number' ?
                i : 0;
          };

          let debit = api
            .column(4)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          let kredit = api
            .column(5)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);


          $(api.column(4).footer()).html(format(debit));
          $(api.column(5).footer()).html(format(kredit));

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

      $('#modalDelete').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.journals.index") }}/' + id);
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
            if (response.status === "success") {
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
