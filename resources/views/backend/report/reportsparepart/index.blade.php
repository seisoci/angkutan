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
            <div class="col-md-4 my-md-0">
              <div class="form-group">
                <label>Range Picker</label>
                <div class="input-daterange input-group">
                  <input type="text" class="form-control text-center datepicker" id="dateStart" readonly />
                  <div class="input-group-append">
                    <span class="input-group-text">sd</span>
                  </div>
                  <input type="text" class="form-control text-center datepicker" id="dateEnd" readonly />
                </div>
              </div>
            </div>
          </div>
          <div class="row my-md-0">
            <div class="col-md-12 text-right mr-10">
              <button id="Excel" class="btn btn-secondary"><i class="fas fa-file-excel"></i> Excel</button>
              <button id="Print" target="_blank" class="btn btn-secondary"><i class="fa fa-print"></i> Print</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--begin: Datatable-->
    <table class="table border-top-1 table-hover" id="Datatable">
      <thead>
        <tr>
          <th>No. Pol</th>
          <th>Nama Supir</th>
          <th>Produk</th>
          <th>Unit</th>
          <th>No. Ref</th>
          <th>Tanggal Pemakaian</th>
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
  let print = document.getElementById("Print");
  let excel = document.getElementById("Excel");
  let driver_id = document.getElementById('select2Driver').value;
  let transport_id = document.getElementById('select2Transport').value;
  let date_start = document.getElementById('dateStart').value;
  let date_end = document.getElementById('dateEnd').value;

  print.addEventListener('click', function(e){
    e.preventDefault();
    window.open(`/backend/reportsparepart/print?driver_id=${driver_id}&transport_id=${transport_id}&date_start=${date_start}&date_end=${date_end}`);
  });
  excel.addEventListener('click', function(e){
      e.preventDefault();
      window.open(`/backend/reportsparepart/document?driver_id=${driver_id}&transport_id=${transport_id}&date_start=${date_start}&date_end=${date_end}&type_document='excel'`);
    });
</script>
<script type="text/javascript">
  $(document).ready(function(){
    var dataTable = $('#Datatable').DataTable({
        autoWidth : false,
        responsive: false,
        fixedHeader: true,
        scrollX: true,
        processing: true,
        serverSide: true,
        searching: false,
        // bSort: false,
        order: [[5, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.reportsparepart.index') }}",
          data: function(d){
            d.driver_id     = $('#select2Driver').find(':selected').val();
            d.transport_id  = $('#select2Transport').find(':selected').val();
            d.date_start    = $('#dateStart').val();
            d.date_end      = $('#dateEnd').val();
          }
      },
        columns: [
            {data: 'invoiceusage.transport.num_pol', name: 'invoiceusage.transport.num_pol'},
            {data: 'invoiceusage.driver.name', name: 'invoiceusage.driver.name'},
            {data: 'sparepart.name', name: 'sparepart.name', orderable: false, searchable: true},
            {data: 'qty', name: 'qty', className: 'dt-center'},
            {data: 'invoiceusage.num_invoice', name: 'invoiceusage.num_invoice', searchable: false, orderable: false},
            {data: 'invoiceusage.created_at', name: 'invoiceusage.created_at'},
        ],
        columnDefs: [
        {
          "targets": 2,
          "searchable": false,
          "render": function(data, type, row, meta){
            if(!data){
              data = row.name ;
            }
            return data;
          }
        }
        ]
    });

    $('.datepicker').datepicker({
      format: 'yyyy-mm-dd',
      todayHighlight: !0,
      todayBtn: "linked",
      clearBtn: !0,
    }).on('change', function(){
      dataTable.draw();
    });

    $("#select2Driver").select2({
      placeholder: "Search LDO",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.drivers.select2self') }}",
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
          url: "{{ route('backend.transports.select2self') }}",
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
  });
</script>
@endsection
