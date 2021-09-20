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
      <!--begin: Datatable-->
      <table class="table table-bordered table-hover" id="Datatable">
        <thead>
        <tr>
          <th></th>
          <th>Nama</th>
          <th>Kerjasama</th>
          <th>No. Telp</th>
          <th>Nama Darurat</th>
          <th>No. Telp Darurat</th>
          <th>Alamat</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
  <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detail Uang Jalan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <div class="modal-body">
          <table id="tableDetail" class="table table-bordered">
            <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Uang Jalan Engkel</th>
              <th scope="col">Uang Jalan Tronton</th>
              <th scope="col">Ongkosan</th>
              <th scope="col">Jenis Muatan</th>
              <th scope="col">Tipe</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
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
    <table class="table table-bordered DatatableDetail" id="posts-{{id}}">
      <thead>
      <tr>
        <th>Rute Dari</th>
        <th>Rute Ke</th>
        <th>Muatan</th>
        <th>Fee Thanks</th>
        <th>Tax PPH (%)</th>
        <th>Detail</th>
      </tr>
      </thead>
    </table>
    @endverbatim
  </script>
  <script type="text/javascript">
    $(function () {
      $('#btn_excel').on('click', function (e) {
        e.preventDefault();
        window.location.href = '{{ $config['excel_url'] }}';
      });

      $('#btn_pdf').on('click', function (e) {
        e.preventDefault();
        location.href = '{{ $config['pdf_url'] }}';
      });

      $('#btn_print').on('click', function (e) {
        e.preventDefault();
        window.open('{{ $config['print_url'] }}');
      });

      let template = Handlebars.compile($("#details-template").html());

      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        searching: false,
        bSort: false,
        orderable: false,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.reportcustomerroadmoney.index') }}",
          data: function (d) {
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
          {data: 'name', name: 'name', orderable: false},
          {data: 'cooperation.nickname', name: 'cooperation.nickname'},
          {data: 'phone', name: 'phone'},
          {data: 'emergency_name', name: 'emergency_name'},
          {data: 'emergency_phone', name: 'emergency_phone'},
          {data: 'address', name: 'address'},
        ],
      });

      $('#Datatable tbody').on('click', 'td.details-control', function () {
        let tr = $(this).closest('tr');
        let row = dataTable.row(tr);
        let tableId = 'posts-' + row.data().id;

        if (row.child.isShown()) {
          row.child.hide();
          tr.removeClass('shown');
        } else {
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
          searching: false,
          bSort: false,
          orderable: false,
          ajax: data.details_url,
          columns: [
            {data: 'routefrom', name: 'routefrom'},
            {data: 'routeto', name: 'routeto'},
            {data: 'cargo', name: 'cargo'},
            {
              data: 'fee_thanks',
              name: 'fee_thanks',
              render: $.fn.dataTable.render.number(',', '.', 2),
              className: 'dt-right'
            },
            {data: 'tax_pph', name: 'tax_pph'},
            {data: 'action', name: 'action'}
          ]
        })
      }

      $('#modalDetail').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
          }
        });
        let formData = {
          title: jQuery('#title').val(),
        };
        $.ajax({
          type: 'GET',
          data: formData,
          url: "{{ route('backend.reportcustomerroadmoney.index') }}/findbypk/"+id,
          dataType: 'json',
          success: function (response) {
            response.message.forEach((element, index) => {
              let tipe = '';
              if(element.type == 'Fix'){
                tipe = "Fix";
              }else{
                tipe = "Kalkulasi";
              }
              $('#tableDetail tbody').empty();
              $('#tableDetail tbody').append('<tr>' +
                '<td class="text-center">' +(index+1)+ '</td>'+
                '<td class="text-right">' +element.road_engkel+ '</td>'+
                '<td class="text-right">' +element.road_tronton+ '</td>'+
                '<td class="text-right">' +element.expense+ '</td>'+
                '<td>' +element.type_capacity+ '</td>'+
                '<td>' + tipe + '</td>'+
                '</tr>')
            })
          },
          error: function (data) {
            console.log(data);
          }
        });
      });

      $('#modalDetail').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('input[name="name"]').val('');
      });
    });
  </script>
@endsection
