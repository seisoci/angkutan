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
          </div>
        </div>
      </div>
    </div>
    <!--begin: Datatable-->
    <table class="table table-hover" id="Datatable">
      <thead>
        <tr>
          <th>Prefix</th>
          <th>No. Job Order</th>
          <th>No. Polisi</th>
          <th>Pelanggan</th>
          <th>Muatan</th>
          <th>Tanggal Mulai</th>
          <th>Status JO</th>
          <th>Created At</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

{{-- Styles Section --}}
@section('styles')
<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

@endsection

{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
{{-- page scripts --}}
<script type="text/javascript">
  $(document).ready(function(){
    var dataTable = $('#Datatable').DataTable({
        autoWidth : false,
        responsive: false,
        fixedHeader: true,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[7, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.reportcostumers.index') }}",
          data: function(d){
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
          }
      },
        columns: [
            {data: 'prefix', name: 'prefix'},
            {data: 'num_bill', name: 'num_bill'},
            {data: 'transport.num_pol', name: 'transport.num_pol'},
            {data: 'costumer.name', name: 'costumer.name'},
            {data: 'cargo.name', name: 'cargo.name'},
            {data: 'date_begin', name: 'date_begin'},
            {data: 'status_cargo', name: 'status_cargo'},
            {data: 'created_at', name: 'created_at', width: '120px'},
        ],
        columnDefs: [
        {
          className: 'dt-center',
          targets: 6,
          render: function(data, type, full, meta) {
            var status = {
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
        ],
    });
    dataTable.columns.adjust().draw();

    $('#statusCargoModal').on('change', function(){
      if(this.value == 'selesai'){
        $("#dateEndModal").parent().css("display", "block");
        $("#dateEndModal").parent().find('label').css("display", "block");
      }else{
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
    }).on('change', function(){
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
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    }).on('change', function (e){
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
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    }).on('change', function (e){
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
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    }).on('change', function (e){
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
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    }).on('change', function (e){
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
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    }).on('change', function (e){
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
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    }).on('change', function (e){
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
          data: function(e) {
            return {
              q: e.term || '',
              page: e.page || 1
            }
          },
      },
    }).on('change', function (e){
      dataTable.draw();
    });
    $('#selectStatus').on('change', function(){
      dataTable.draw();
    })
  });
</script>
@endsection
