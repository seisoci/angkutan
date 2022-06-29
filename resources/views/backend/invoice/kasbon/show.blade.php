{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

  <!--begin::Card-->
  <div class="card card-custom">
    <div class="card-header flex-wrap py-3">
      <div class="card-title">
        <h3 class="card-label">{{ $config['page_title'] }} {{ $data->driver->name }}
          <span class="d-block text-muted pt-2 font-size-sm">{{ $config['page_description'] }}</span></h3>
      </div>
    </div>
    <div class="card-body">
      <div class="mb-10">
        <div class="row d-flex justify-content-end">
          <meta name="csrf-token" content="{{ csrf_token() }}">
          <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-print"></i>Cetak
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a id="btnPrint" href="#" class="dropdown-item">Print DotMatrix</a>
              <a id="btnPrintBiasa" href="#" class="dropdown-item">Print Biasa</a>
            </div>
          </div>
        </div>
      </div>
      <!--begin: Datatable-->
      <table class="table table-hover" id="Datatable">
        <thead>
        <tr>
          <th></th>
          <th>Nama</th>
          <th>Tanggal Transaksi</th>
          <th>Deskripsi</th>
          <th>Nominal</th>
          <th>Tipe</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
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
@endsection

{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
  <link href="{{ asset('css/backend/datatables/dataTables.checkboxes.css') }}" rel="stylesheet" type="text/css"/>
@endsection


{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  <script src="{{ asset('js/backend/datatables/dataTables.checkboxes.js') }}" type="text/javascript"></script>
  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {
      $(".currency").inputmask('decimal', {
        groupSeparator: '.',
        digits: 0,
        rightAlign: true,
        removeMaskOnSubmit: true,
        autoUnmask: true,
        allowMinus: false,
      });

      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[2, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: "{{ route('backend.kasbon.datatableshow', $id) }}",
        columns: [
          {data: 'id', name: 'id'},
          {data: 'nama_supir', name: 'nama_supir'},
          {data: 'date_payment', name: 'date_payment'},
          {data: 'description', name: 'description'},
          {
            data: 'payment',
            name: 'payment',
            render: $.fn.dataTable.render.number('.', '.', 2),
            className: 'dt-right'
          },
          {
            data: 'type',
            name: 'type',
            width: '75px',
            render: function (data, type, full, meta) {
              let status = {
                'hutang': {'title': 'Hutang', 'class': ' label-light-danger'},
                'pembayaran': {'title': 'Hutang Dibayar', 'class': ' label-light-success'},
              };
              if (typeof status[data] === 'undefined') {
                return data;
              }
              return '<span class="label label-lg font-weight-bold' + status[data].class + ' label-inline">' + status[data].title +
                '</span>';
            },
          },
          {data: 'created_at', name: 'created_at'},
          {data: 'action', name: 'action', orderable: false},
        ],
        select: {
          style: 'multi'
        },
        columnDefs: [
          {
            targets: 0,
            checkboxes: {
              selectRow: true
            }
          },
        ],
      }).on('draw', function () {
        $('.btnPrint').on('click', function (e) {
          e.preventDefault();
          $.ajax({
            url: '/backend/kasbon/' + $(this).attr('data-id') + '/dotmatrix',
            success: function (text) {
              $.post('http://localhost/dotmatrix/', JSON.stringify({
                printer: 'DotMatrix',
                data: text,
                autocut: true
              }), function (response) {
              });
            }
          });
        });
      });

      $('#btnPrint').on('click', function (e) {
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
          url: "{{ route('backend.kasbon.print-dotMatrixMultiple') }}",
          data: {data: dataSelected},
          success: function (response) {
            if (response.status === "error") {
              toastr.error(response.message, 'Failed !');
            } else {
              toastr.success("Print berhasil dicetak", 'Success !');
            }
          }
        });
      });

      $('#btnPrintBiasa').on('click', function (e) {
        let selected = dataTable.column(0).checkboxes.selected();
        let dataSelected = [];
        $.each(selected, function (index, data) {
          dataSelected.push(data);
        });
        let url = '{{ route('backend.kasbon.printMultiple') }}'+'?payment_kasbon_id='+dataSelected
        window.open(url, '_blank');
      });

      $('#modalDelete').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('a[name="id"]').attr('href', '{{ route("backend.kasbon.index") }}/' + id);
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
