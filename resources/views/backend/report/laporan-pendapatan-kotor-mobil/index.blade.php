@extends('layout.default')

@section('content')
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
              <div class="col-md-4 my-md-0">
                <div class="form-group">
                  <label>No. Polisi:</label>
                  <select class="form-control" id="select2Transport">
                  </select>
                </div>
              </div>
              <div class="col-md-3 my-md-0">
                <div class="form-group">
                  <label>Status Job Order:</label>
                  <select class="form-control" id="selectStatus">
                    <option value="">Pilih Status</option>
                    <option value="mulai">Mulai</option>
                    <option value="transfer">Transfer</option>
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
                    <option value="0">Belum</option>
                    <option value="1">Selesai</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4 my-md-0">
                <div class="form-group">
                  <label>Tanggal Mulai JO:</label>
                  <div class="input-group">
                    <input type="text" class="form-control datePicker" name="date_begin_start" placeholder="Choose Date" readonly>
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="la la-calendar-check-o"></i></span>
                    </div>
                    <input type="text" class="form-control datePicker" name="date_begin_end" placeholder="Choose Date" readonly>
                  </div>
                </div>
              </div>
              <div class="col-md-4 my-md-0">
                <div class="form-group">
                  <label>Tanggal Selesai JO:</label>
                  <div class="input-group">
                    <input type="text" class="form-control datePicker" name="date_end_start" placeholder="Choose Date" readonly>
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="la la-calendar-check-o"></i></span>
                    </div>
                    <input type="text" class="form-control datePicker" name="date_end_end" placeholder="Choose Date" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover" id="Datatable">
          <thead>
          <tr>
            <th>No. Polisi</th>
            <th>Total Pendapatan Kotor</th>
          </tr>
          </thead>
          <tfoot>
          <th></th>
          <th></th>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
@endsection
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  <script type="text/javascript">
    $(document).ready(function () {
      let dataTable = $('#Datatable').DataTable({
        responsive: false,
        scrollX: true,
        processing: true,
        serverSide: true,
        bSort: false,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 25,
        lengthChange: false,
        buttons: ['pageLength', {
          extend: 'copy',
          footer: true
        }, {
          extend: 'csv',
          footer: true
        }, {
          extend: 'excel',
          footer: true
        }, {
          extend: 'print',
          footer: true
        }, {
          extend: 'pdf',
          footer: true,
          orientation: 'landscape',
          pageSize: 'LEGAL'
        },],
        ajax: {
          url: "{{ url()->current() }}",
          data: function (d) {
            d.status_cargo = $('#selectStatus').find(':selected').val();
            d.status_document = $('#selectDocument').find(':selected').val();
            d.transport_id = $('#select2Transport').find(':selected').val();
            d.date_begin_start = $("input[name=date_begin_start]").val();
            d.date_begin_end = $("input[name=date_begin_end]").val();
            d.date_end_start = $("input[name=date_end_start]").val();
            d.date_end_end = $("input[name=date_end_end]").val();
          }
        },
        columns: [
          {data: 'transport_name', name: 'transports.num_pol'},
          {
            data: 'total_kotor',
            name: 'job_orders.total_kotor',
            render: $.fn.dataTable.render.number(',', '.', 2),
            className: 'dt-right',
            searchable: false
          },
        ],
        initComplete: function (settings, json) {
          dataTable.buttons().container().appendTo('#Datatable_wrapper .col-md-6:eq(0)')
        },
        footerCallback: function (row, data, start, end, display) {
          let api = this.api();
          let intVal = function (i) {
            return typeof i === 'string' ?
              i.replace(/[\$,]/g, '') * 1 :
              typeof i === 'number' ?
                i : 0;
          };

          let totalBasic = api
            .column(1)
            .data()
            .reduce(function (a, b) {
              return intVal(a) + intVal(b);
            }, 0);

          $(api.column(0).footer()).html('Total');
          $(api.column(1).footer()).html($.fn.dataTable.render.number(',', '.', 0).display(totalBasic));

        },
      });

      $(".datePicker").flatpickr({
        disableMobile: true,
        dateFormat: 'Y-m-d',
        onChange: function (selectedDates, date_str, instance) {
          dataTable.draw();
        },
        onReady: function (dateObj, dateStr, instance) {
          const $clear = $('<button class="btn btn-danger btn-sm flatpickr-clear mb-2">Clear</button>')
            .on('click', () => {
              instance.clear();
              instance.close();
            })
            .appendTo($(instance.calendarContainer));
        }
      });

      $("#select2Transport").select2({
        placeholder: "Cari No Polisi",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.transports.select2self') }}",
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

    });
  </script>
@endsection
