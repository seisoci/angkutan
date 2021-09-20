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
      </div>
    </div>

    <div class="card-body">
      <div class="row">
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
        <div class="col-12 mb-10">
          <div class="alert alert-custom alert-outline-primary fade show mb-5" role="alert">
            <div class="alert-icon"><i class="flaticon-warning"></i></div>
            <div class="d-flex flex-column">
              <h4>Status Uang Jalan Lebih/Kurang Expedisi Sendiri</h4>
              @foreach($restRoadMoney as $item)
                @if($item->road_money_extra != 0)
                  @if($item->type == 'self')
                    <span>{{ $item->driver->name }} - {{ $item->transport->num_pol }} # {{ number_format($item->road_money_extra, 2, '.', ',') }}</span>
                  @else
                  @endif
                @endif
              @endforeach
            </div>
          </div>
        </div>
        <div class="col-md-3 my-md-0">
          <div class="form-group">
            <label>Status Transfer:</label>
            <select class="form-control" id="selectStatus">
              <option value="all">All</option>
              <option value="pending">Pending</option>
              <option value="0">Di Tolak</option>
              <option value="1">Di Setujui</option>
            </select>
          </div>
        </div>
        <div class="col-md-3 my-md-0">
          <div class="form-group">
            <label>Tipe:</label>
            <select class="form-control" id="selectType">
              <option value="null">All</option>
              <option value="roadmoney">Uang Jalan</option>
              <option value="operational">Operasional</option>
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
      </div>
      <!--begin: Datatable-->
      <table class="table table-bordered table-hover" id="Datatable">
        <thead>
        <tr>
          <th>No Job Order</th>
          <th>Tanggal Pengajuan</th>
          <th>Pelanggan</th>
          <th>Supir</th>
          <th>No. Pol</th>
          <th>Dari</th>
          <th>Tujuan</th>
          <th>Nominal</th>
          <th>Deskripsi</th>
          <th>Status</th>
          <th>Tipe</th>
          <th>Status JO</th>
          <th>Actions</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
  {{-- Modal --}}
  <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit {{ $config['page_title'] }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <form id="formUpdate" action="#">
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
            <input type="hidden" name="approved_by" value="{{ Auth::id() }}">
            <div class="form-group">
              <span id="roadMoney"></span>
            </div>
            <div class="form-group">
              <label>Status Pengajuan</label>
              <select class="form-control form-control-solid" name="approved">
                <option>Pilih Status</option>
                <option value="0">Di Tolak</option>
                <option value="1">Setuju</option>
              </select>
            </div>
            <div class="form-group">
              <label>Akun COA</label>
              <select class="form-control form-control-solid" name="coa_id">
                @foreach($selectCoa->coa as $item)
                  <option value="{{ $item->id }}">{{ $item->code .' - '. $item->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label>Deskripsi</label>
              <textarea type="text" name="description" class="form-control form-control-solid"
                        placeholder="Keterangan" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Submit</button>
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
      $(".currency").inputmask('decimal', {
        groupSeparator: '.',
        digits: 0,
        rightAlign: true,
        removeMaskOnSubmit: true
      });
      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[1, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        dom: 'Bfrtip',
        stateSave: true,
        buttons: [
          'colvis'
        ],
        ajax: {
          url: "{{ route('backend.submission.index') }}",
          data: function (d) {
            d.status = $('#selectStatus').find(':selected').val();
            d.type = $('#selectType').find(':selected').val();
            d.driver_id = $('#select2Driver').find(':selected').val();
            d.transport_id = $('#select2Transport').find(':selected').val();
          }
        },
        columns: [
          {data: 'joborder.num_prefix', name: 'joborder.num_bill', width: '140px'},
          {data: 'created_at', name: 'created_at'},
          {data: 'joborder.costumer.name', name: 'joborder.costumer.name'},
          {data: 'joborder.driver.name', name: 'joborder.driver.name'},
          {data: 'joborder.transport.num_pol', name: 'joborder.transport.num_pol'},
          {data: 'joborder.routefrom.name', name: 'joborder.routefrom.name'},
          {data: 'joborder.routeto.name', name: 'joborder.routeto.name'},
          {
            data: 'amount', name: 'amount',
            render: $.fn.dataTable.render.number(',', '.', 2),
            orderable: false,
            searchable: false,
            className: 'dt-right',
          },
          {data: 'description', name: 'description'},
          {data: 'approved', name: 'approved'},
          {data: 'type', name: 'type'},
          {data: 'joborder.status_cargo', name: 'joborder.status_cargo'},
          {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        columnDefs: [
          {
            className: 'dt-center',
            targets: 9,
            width: '75px',
            render: function (data, type, full, meta) {
              let status = {
                null: {'title': 'Pending', 'class': 'badge badge-secondary'},
                0: {'title': 'Di Tolak', 'class': 'badge badge-danger'},
                1: {'title': 'Di Setujui', 'class': 'badge badge-success'},
              };
              if (typeof status[data] === 'undefined') {
                return data;
              }
              return '<span class="' + status[data].class + '">' + status[data].title +
                '</span>';
            },
          },
          {
            className: 'dt-center',
            targets: 10,
            width: '75px',
            render: function (data, type, full, meta) {
              let status = {
                'roadmoney': {'title': 'Uang Jalan', 'class': 'badge badge-primary'},
                'operational': {'title': 'Uang Jalan Operasional', 'class': 'badge badge-warning'},
              };
              if (typeof status[data] === 'undefined') {
                return data;
              }
              return '<span class="' + status[data].class + '">' + status[data].title +
                '</span>';
            },
          },
          {
            className: 'dt-center',
            targets: 11,
            width: '75px',
            render: function (data, type, full, meta) {
              let status = {
                'mulai': {'title': 'Mulai', 'class': ' label-light-info'},
                'transfer': {'title': 'Transfer', 'class': ' label-light-dark'},
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
        ]
      });

      $('#modalEdit').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        let description = $(event.relatedTarget).data('description');
        $(this).find('#formUpdate').attr('action', '{{ route("backend.submission.index") }}/' + id)
        $(this).find('.modal-body').find('textarea[name="description"]').text(description);
        let url = '{{ route("backend.submission.index") }}/findbypk/' + id;
        $.ajax({
          type: 'GET',
          url: url,
          dataType: 'json',
          success: function (response) {
            $('#roadMoney').empty();
            if (response.type == "self") {
              if (response.roadMoney > 0) {
                $('#roadMoney').text('Sisa uang jalan: ' + response.roadMoneyFormat)
              } else if (response.roadMoney < 0) {
                $('#roadMoney').text('Uang jalan sudah melewati sistem: ' + response.roadMoneyFormat)
              } else {
                $('#roadMoney').text('Uang jalan KLOP')
              }
            } else {
              $('#roadMoney').text('Total Uang jalan LDO telah diambil: ' + response.roadMoneyFormat)
            }
          },
          error: function (response) {
            console.log(data);
          }
        });
      });
      $('#modalEdit').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('textarea[name="description"]').text('');
      });

      $('#selectType, #selectStatus').on('change', function () {
        dataTable.draw();
      });

      $("#select2Driver").select2({
        placeholder: "Search Supir",
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
            if (response.status == "success") {
              toastr.success(response.message, 'Success !');
              $('#modalCreate').modal('hide');
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
          },
          error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
            $('#modalCreate').modal('hide');
            $('#modalCreate').find('a[name="id"]').attr('href', '');
          }
        });
      });

      $("#formUpdate").submit(function (e) {
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
            if (response.status == "success") {
              toastr.success(response.message, 'Success !');
              $('#modalEdit').modal('hide');
              dataTable.draw();
            } else {
              toastr.error("Data Cannot Update", 'Failed !');
            }
          }, error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
            $('#modalEdit').modal('hide');
            $('#modalEdit').find('a[name="id"]').attr('href', '');
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
