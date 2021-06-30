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
      <!--begin: Datatable-->
      <table class="table table-hover" id="Datatable">
        <thead>
        <tr>
          <th>Nama Log</th>
          <th>Aksi</th>
          <th>User</th>
          <th>Perubahan</th>
          <th>Created At</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
  <div class="modal fade" id="modalShow" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detail Perubahan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <h4>Old</h4>
              <table class="table-old table table-bordered">
                <thead>
                <tr>
                  <th>Field</th>
                  <th>Value</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
              </table>
            </div>
            <div class="col-md-6">
              <h4>New</h4>
              <table class="table-new table table-bordered">
                <thead>
                <tr>
                  <th>Field</th>
                  <th>Value</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
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
        order: [[4, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 25,
        ajax: "{{ route('backend.activitylog.index') }}",
        columns: [
          {data: 'log_name', name: 'log_name'},
          {data: 'description', name: 'description'},
          {data: 'user.name', name: 'user.name', defaultContent: ''},
          {data: 'action', name: 'action'},
          {data: 'created_at', name: 'created_at'},
        ],
        columnDefs: [
          {
            className: 'dt-center',
            targets: 1,
            width: '75px',
            render: function (data, type, full, meta) {
              let status = {
                'created': {'title': 'Created', 'class': ' label-light-success'},
                'updated': {'title': 'Updated', 'class': ' label-light-warning'},
                'deleted': {'title': 'Deleted', 'class': ' label-light-danger'},
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

      $('#modalShow').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $.ajax({
          cache: false,
          processData: false,
          contentType: false,
          type: "GET",
          url: '{{ route('backend.activitylog.index') }}' + '/' + id,
          success: function (response) {
            let attributes = response.attributes;
            let old = response.old;
            for (let i in attributes) {
              $('.table-new').find("tbody").append('<tr><td>' + i + '</td><td>' + attributes[i] + '</td></tr>');
            }
            for (let a in old) {
              $('.table-old').find("tbody").append('<tr><td>' + a + '</td><td>' + old[a] + '</td></tr>');
            }
          },
          error: function (response) {

          }
        });
      });
      $('#modalShow').on('hidden.bs.modal', function (event) {
        $('.table-new, .table-old').find('tbody').empty();
      });
    });
  </script>
@endsection
