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
        <!--begin::Button-->
        <a href="{{ route('backend.invoicepurchases.create') }}" class="btn btn-primary font-weight-bolder">
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
      <div class="mb-10">
        <div class="row align-items-center">
          <div class="col-12 mb-10">
            <div class="alert alert-custom alert-outline-primary fade show mb-5" role="alert">
              <div class="alert-icon"><i class="flaticon-warning"></i></div>
              <div class="d-flex flex-column">
                <h4>Sisa Saldo</h4>
                @foreach($saldoGroup as $item)
                  <div><b>{{ $item['name'] }} : <span
                        class="text-success">{{ number_format($item['balance'], 2,'.',',') }}</span></b></div>
                @endforeach
              </div>
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
          <div class="col-md-4 my-md-0">
            <div class="form-group">
              <label>Nama Supplier:</label>
              <select class="form-control" id="select2Supplier">
              </select>
            </div>
          </div>
          <div class="col-md-4 my-md-0">
            <div class="form-group">
              <label>Status:</label>
              <select class="form-control" id="select2Status">
                <option value="null">All</option>
                <option value="lunas">Lunas</option>
                <option value="belum_lunas">Belum Lunas</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <table class="table table-hover" id="Datatable">
        <thead>
        <tr>
          <th>Prefix</th>
          <th>No. Invoice</th>
          <th>Supplier</th>
          <th>Total Tagihan</th>
          <th>Total Pembayaran</th>
          <th>Diskon</th>
          <th>Sisa Pembayaran</th>
          <th>Metode</th>
          <th>Tgl Invoice Dibuat</th>
          <th>Tgl Jth Tempo</th>
          <th>Memo</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
  {{-- Modal --}}
  <div class="modal fade" id="modalDelete" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete</h5>
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
          <button id="formDelete" type="button" class="btn btn-danger">Submit</button>
        </div>
      </div>
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
        order: [[10, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.invoicepurchases.index') }}",
          data: function (d) {
            d.supplier_sparepart_id = $('#select2Supplier').find(':selected').val();
            d.status = $('#select2Status').val();
            d.invoice_date = $("input[name=date]").val();
          }
        },
        columns: [
          {data: 'prefix', name: 'prefix', className: 'dt-center'},
          {data: 'num_bill', name: 'num_bill',},
          {data: 'supplier.name', name: 'supplier.name',},
          {
            data: 'total_bill',
            name: 'total_bill',
            className: 'dt-right',
            render: $.fn.dataTable.render.number('.', ',', 2)
          },
          {
            data: 'total_payment',
            name: 'total_payment',
            className: 'dt-right',
            render: $.fn.dataTable.render.number('.', ',', 2)
          },
          {
            data: 'discount',
            name: 'discount',
            className: 'dt-right',
            render: $.fn.dataTable.render.number('.', ',', 2)
          },
          {
            data: 'rest_payment',
            name: 'rest_payment',
            className: 'dt-right',
            render: $.fn.dataTable.render.number('.', ',', 2)
          },
          {data: 'method_payment', name: 'method_payment'},
          {data: 'invoice_date', name: 'invoice_date'},
          {data: 'due_date', name: 'due_date'},
          {data: 'memo', name: 'memo'},
          {data: 'created_at', name: 'created_at'},
          {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        columnDefs: [
          {
            className: 'dt-center',
            targets: 7,
            width: '75px',
            render: function (data, type, full, meta) {
              let status;
              if (data === 'cash') {
                status = 'Tunai';
              } else {
                status = 'Kredit';
              }
              return status;
            },
          },
        ]
      });

      $('#modalDelete').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.invoicepurchases.index") }}/' + id);
      });

      $('#modalDelete').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
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

      $("#select2Supplier").select2({
        placeholder: "Search Supplier",
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
      }).on('change', function (e) {
        dataTable.draw();
      });

      $('#select2Status').on('change', function () {
        dataTable.draw();
      });

      $("#formDelete").click(function (e) {
        e.preventDefault();
        let form = $(this);
        let url = $('#modalDelete').find('a[name="id"]').attr('href');
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        $.ajax({
          beforeSend: function () {
            btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading ...").prop("disabled", "disabled");
          },
          type: 'DELETE',
          url: url,
          dataType: 'json',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          success: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.success(response.message, 'Success !');
            $('#modalDelete').modal('hide');
            dataTable.draw();
          },
          error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
            $('#modalDelete').modal('hide');
            $('#modalDelete').find('a[name="id"]').attr('href', '');
          }
        });
      });
    });
  </script>
@endsection
