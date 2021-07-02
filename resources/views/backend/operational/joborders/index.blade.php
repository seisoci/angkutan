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
        <a href="{{ route('backend.joborders.create') }}" class="btn btn-primary font-weight-bolder">
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
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>LDO:</label>
                  <select class="form-control" id="select2AnotherExpedition">
                  </select>
                </div>
              </div>
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Supir:</label>
                  <select class="form-control" id="select2Driver">
                  </select>
                </div>
              </div>
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>No. Pol:</label>
                  <select class="form-control" id="select2Transport">
                  </select>
                </div>
              </div>
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Costumer:</label>
                  <select class="form-control" id="select2Costumer">
                  </select>
                </div>
              </div>
            </div>
            <div class="row align-items-center">
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Rute Dari:</label>
                  <select class="form-control" id="select2RouteFrom">
                  </select>
                </div>
              </div>
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Rute Ke:</label>
                  <select class="form-control" id="select2RouteTo">
                  </select>
                </div>
              </div>
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Muatan:</label>
                  <select class="form-control" id="select2Cargo">
                  </select>
                </div>
              </div>
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Tanggal Mulai:</label>
                  <input id="dateBegin" readonly type="text" class="form-control datepicker" placeholder="Cari Tanggal"
                         style="width:100% !important">
                </div>
              </div>
            </div>
            <div class="row align-items-center">
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Tanggal Selesai:</label>
                  <input id="dateEnd" type="text" class="form-control datepicker" placeholder="Cari Tanggal"
                         style="width:100% !important" readonl>
                </div>
              </div>
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Status Job Order:</label>
                  <select class="form-control" id="selectStatus">
                    <option value="">Pilih Status</option>
                    <option value="mulai">Mulai</option>
                    <option value="selesai">Selesai</option>
                    <option value="batal">Batal</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Status Dokumen:</label>
                  <select class="form-control" id="selectDocument">
                    <option value="">Pilih Status</option>
                    <option value="zero">Belum</option>
                    <option value="1">Selesai</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
{{--      <div class="mb-10">--}}
{{--        <div class="dropdown d-flex justify-content-end">--}}
{{--          <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown"--}}
{{--                  aria-haspopup="true" aria-expanded="false">--}}
{{--            Hidden Column--}}
{{--          </button>--}}
{{--          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">--}}
{{--            <a class="dropdown-item" data-column="0">Prefix</a>--}}
{{--            <a class="dropdown-item" data-column="1">No. Job Order</a>--}}
{{--            <a class="dropdown-item" data-column="2">LDO</a>--}}
{{--            <a class="dropdown-item" data-column="3">Supir</a>--}}
{{--            <a class="dropdown-item" data-column="4">No. Pol</a>--}}
{{--            <a class="dropdown-item" data-column="6">Pelanggan</a>--}}
{{--            <a class="dropdown-item" data-column="7">Rute Dari</a>--}}
{{--            <a class="dropdown-item" data-column="8">Rute Ke</a>--}}
{{--            <a class="dropdown-item" data-column="9">Muatan</a>--}}
{{--            <a class="dropdown-item" data-column="10">Tanggal Mulai</a>--}}
{{--            <a class="dropdown-item" data-column="11">Tanggal Selesai</a>--}}
{{--            <a class="dropdown-item" data-column="12">Status JO</a>--}}
{{--            <a class="dropdown-item" data-column="13">Status Dokumen</a>--}}
{{--            <a class="dropdown-item" data-column="14">Created At</a>--}}
{{--          </div>--}}
{{--        </div>--}}
{{--      </div>--}}
      <!--begin: Datatable-->
      <table class="table table-hover" id="Datatable">
        <thead>
        <tr>
          <th>Prefix</th>
          <th>No. Job Order</th>
          <th>LDO</th>
          <th>Supir</th>
          <th>No. Pol</th>
          <th>Pelanggan</th>
          <th>Rute Dari</th>
          <th>Rute Ke</th>
          <th>Muatan</th>
          <th>Tanggal Mulai</th>
          <th>Tanggal Selesai</th>
          <th>Status JO</th>
          <th>Status Dokumen</th>
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
  <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit {{ $config['page_title'] }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <form class="formUpdate" action="#">
          @method('PUT')
          <meta name="csrf-token" content="{{ csrf_token() }}">
          <div class="modal-body">
            <div class="form-group" style="display:none;">
              <div class="alert alert-custom alert-light-danger" role="alert">
                <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
                <div class="alert-text">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Status Job Order:</label>
              <select id="statusCargoModal" class="form-control" name="status_cargo">
                <option value="selesai">Selesai</option>
                <option value="batal">Batal</option>
              </select>
            </div>
            <div class="form-group" style="display: none">
              <label>Tanggal Selesai:</label>
              <input id="dateEndModal" type="text" class="form-control" name="date_end" readonly
                     data-toggle="datetimepicker"
                     value="{{ Carbon\Carbon::parse()->timezone('Asia/Jakarta')->format('Y-m-d') }}">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" type="button" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modalEditDocument" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Status Surat Jalan {{ $config['page_title'] }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <form class="formUpdate" action="#">
          @method('PUT')
          <meta name="csrf-token" content="{{ csrf_token() }}">
          <div class="modal-body">
            <p>Surat jalan sudah di serahkan ?</p>
            <input type="hidden" value="1" name="status_document">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Belum</button>
            <button type="submit" type="button" class="btn btn-primary">Sudah</button>
          </div>
        </form>
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
        responsive: true,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[13, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.joborders.index') }}",
          data: function (d) {
            d.another_expedition_id = $('#select2AnotherExpedition').find(':selected').val();
            d.driver_id = $('#select2Driver').find(':selected').val();
            d.transport_id = $('#select2Transport').find(':selected').val();
            d.costumer_id = $('#select2Costumer').find(':selected').val();
            d.cargo_id = $('#select2Cargo').find(':selected').val();
            d.route_from = $('#select2RouteFrom').find(':selected').val();
            d.route_to = $('#select2RouteTo').find(':selected').val();
            d.date_begin = $('#dateBegin').val();
            d.date_end = $('#dateEnd').val();
            d.status_cargo = $('#selectStatus').find(':selected').val();
            d.status_document = $('#selectDocument').find(':selected').val();
          }
        },
        columns: [
          {data: 'prefix', name: 'prefix'},
          {data: 'num_bill', name: 'num_bill'},
          {data: 'anotherexpedition.name', name: 'anotherexpedition.name', defaultContent: ''},
          {data: 'driver.name', name: 'driver.name'},
          {data: 'transport.num_pol', name: 'transport.num_pol'},
          {data: 'costumer.name', name: 'costumer.name'},
          {data: 'routefrom.name', name: 'routefrom.name'},
          {data: 'routeto.name', name: 'routeto.name'},
          {data: 'cargo.name', name: 'cargo.name'},
          {data: 'date_begin', name: 'date_begin'},
          {data: 'date_end', name: 'date_end', defaultContent: ''},
          {data: 'status_cargo', name: 'status_cargo'},
          {data: 'status_document', name: 'status_document'},
          {data: 'created_at', name: 'created_at'},
          {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        columnDefs: [
          {
            className: 'dt-center',
            targets: 11,
            width: '75px',
            render: function (data, type, full, meta) {
              let status = {
                'mulai': {'title': 'Mulai', 'class': ' label-light-info'},
                'selesai': {'title': 'Selesai', 'class': ' label-light-success'},
                'batal': {'title': 'Batal', 'class': ' label-light-danger'},
              };
              if (typeof status[data] === 'undefined') {
                return data;
              }
              return '<span class="label label-lg font-weight-bold' + status[data].class + ' label-inline">' + status[data].title +
                '</span>';
            },
          },
          {
            className: 'dt-center',
            targets: 12,
            width: '75px',
            render: function (data, type, full, meta) {
              let status = {
                0: {'title': 'Belum', 'class': ' label-light-danger'},
                1: {'title': 'Selesai', 'class': ' label-light-success'},
              };
              if (typeof status[data] === 'undefined') {
                return data;
              }
              return '<span class="label label-lg font-weight-bold' + status[data].class + ' label-inline">' + status[data].title +
                '</span>';
            },
          },
        ],
      });

      $('a.dropdown-item').on('click', function (e) {
        e.preventDefault();
        let column = dataTable.column($(this).attr('data-column'));
        column.visible(!column.visible());
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


      $('input[name="date_end"]').datetimepicker({
        format: 'YYYY-MM-DD'
      });

      $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        todayHighlight: !0,
        todayBtn: "linked",
        clearBtn: !0,
      }).on('change', function () {
        dataTable.draw();
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
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      }).on('change', function (e) {
        dataTable.draw();
      });
      $("#select2Driver").select2({
        placeholder: "Search LDO",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.drivers.select2') }}",
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
      $("#select2Transport").select2({
        placeholder: "Search Kendaraan",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.transports.select2') }}",
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
      });
      $("#select2Cargo").select2({
        placeholder: "Search Muatan",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.cargos.select2') }}",
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
      $("#select2RouteFrom").select2({
        placeholder: "Search Rute Dari",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.routes.select2') }}",
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
      $("#select2RouteTo").select2({
        placeholder: "Search Rute Ke",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.routes.select2') }}",
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

      $('#selectStatus').on('change', function () {
        dataTable.draw();
      });

      $('#selectDocument').on('change', function () {
        dataTable.draw();
      });

      $('#modalDelete').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.brands.index") }}/' + id);
      });
      $('#modalDelete').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '');
      });
      $('#modalEdit').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        let status_cargo = $(event.relatedTarget).data('status_cargo');
        let date_end = $(event.relatedTarget).data('date_end');
        $(this).find('.formUpdate').attr('action', '{{ route("backend.joborders.index") }}/' + id);
        $(this).find('.modal-body').find('select[name="status_cargo"]').val(status_cargo);
        $("#dateEndModal").parent().css("display", "none");
        $("#dateEndModal").parent().find('label').css("display", "none");
      });
      $('#modalEdit').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('select[name="status_cargo"]').val('');
      });
      $('#modalEditDocument').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.formUpdate').attr('action', '{{ route("backend.joborders.index") }}/' + id)
      });
      $('#modalEditDocument').on('hidden.bs.modal', function (event) {
      });
      $(".formUpdate").submit(function (e) {
        e.preventDefault();
        let form = $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        let spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
        let url = form.attr("action");
        let data = new FormData(this);
        $.ajax({
          beforeSend: function () {
            btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading...").prop("disabled", "disabled");
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
              $('#modalEdit').modal('hide');
              $('#modalEditDocument').modal('hide');
              dataTable.draw();
              $("[role='alert']").parent().css("display", "none");
            } else {
              $("[role='alert']").parent().removeAttr("style");
              $(".alert-text").html('');
              $.each(response.error, function (key, value) {
                $(".alert-text").append('<span style="display: block">' + value + '</span>');
              });
              toastr.error("Please complete your form", 'Failed !');
            }
          }, error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
            $('#modalEdit').modal('hide');
            $('#modalEdit').find('a[name="id"]').attr('href', '');
            $('#modalEditDocument').modal('hide');
            $('#modalEditDocument').find('a[name="id"]').attr('href', '');
          }
        });
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
