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
        <div class="form-group row">
          <label class="col-form-label text-left col-lg-3 col-sm-12">No Pol</label>
          <div class=" col-lg-4 col-md-9 col-sm-12">
            <select id="select2Transport" class="form-control" name="transport_id">
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label text-left col-lg-3 col-sm-12">Supir</label>
          <div class=" col-lg-4 col-md-9 col-sm-12">
            <select id="select2Driver" class="form-control" name="driver_id">
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label text-left col-lg-3 col-sm-12">Status Gaji Supir</label>
          <div class=" col-lg-4 col-md-9 col-sm-12">
            <select id="selectSalary" class="form-control">
              <option value="">Semua</option>
              <option value="dibayar">Dibayar</option>
              <option value="belum_dibayar">Belum Dibayar</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label text-left col-lg-3 col-sm-12">Tanggal Mulai (Dari)</label>
          <div class="col-lg-4 col-md-9 col-sm-12">
            <div class="input-group date">
              <input type="text" class="form-control datePicker" readonly name="date_begin"
                     value="{{ $date_begin ?? '' }}"/>
              <div class="input-group-append">
              <span class="input-group-text">
                <i class="la la-calendar"></i>
              </span>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label text-left col-lg-3 col-sm-12">Tanggal Mulai (Sampai)</label>
          <div class="col-lg-4 col-md-9 col-sm-12">
            <div class="input-group date">
              <input type="text" class="form-control datePicker" readonly name="date_end"
                     value="{{ $date_end ?? '' }}"/>
              <div class="input-group-append">
              <span class="input-group-text">
                <i class="la la-calendar"></i>
              </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-end">
        <button id="findData" type="button" class="btn btn-primary font-weight-bold"><i class="fas fa-search"></i> Cari Data</button>
        <button id="exportExcel" class="btn btn-success ml-4"><i class="far fa-file-excel"></i>Export Excel</button>
      </div>
    <div class="card-body">
      <div class="mb-10">
        <div class="row align-items-center">
          <div class="col-12">
            <div class="row align-items-center">
            </div>
          </div>
        </div>
      </div>
      <div id="table">
      </div>
    </div>
  </div>
@endsection
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('scripts')
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  <script src="{{ asset('plugins/custom/autonumeric/autoNumeric.min.js') }}" type="text/javascript"></script>
  <script type="text/javascript">
    $(document).ready(function () {
      const callAutoNumeric = () => {
        document.querySelectorAll('.autoNumeric').forEach(element => {
          if (AutoNumeric.getAutoNumericElement(element) === null) {
            new AutoNumeric(element, {
              unformatOnSubmit: true,
              decimalCharacterAlternative: ".",
              decimalPlaces: 0,
              overrideMinMaxLimits: "ignore"
            });
          }
        });
      };
      callAutoNumeric();

      $(".datePicker").flatpickr({
        disableMobile: true,
        dateFormat: 'Y-m-d',
        onReady: function (dateObj, dateStr, instance) {
          const $clear = $('<button class="btn btn-danger btn-sm flatpickr-clear mb-2">Clear</button>')
            .on('click', () => {
              instance.clear();
              instance.close();
            })
            .appendTo($(instance.calendarContainer));
        }
      });

      $("#select2Driver").select2({
        placeholder: "Search No Polisi",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.drivers.select2self') }}",
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
      });

      $("#select2Transport").select2({
        placeholder: "Search Driver",
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
      });

      document.getElementById('findData').addEventListener('click', () => {
        const transportId = $('select[name="transport_id"]').find(':selected').val();
        const driverId = $('select[name="driver_id"]').find(':selected').val();
        const dateBegin = $('input[name="date_begin"]').val();
        const dateEnd = $('input[name="date_end"]').val();
        const salary = $('#selectSalary').val();

        $.ajax({
          url: '{{ url()->current() }}', // Replace with your API endpoint
          method: 'GET',
          data: {
            transport_id: transportId,
            driver_id: driverId,
            date_begin: dateBegin,
            date_end: dateEnd,
            salary: salary
          },
          success: (response) => {
            toastr.success('Data berhasil diambil','Success !');
            $('#table').empty();
            $('#table').append(response);
            callAutoNumeric();
          },
          error: (error) => {
          }
        });
      });

      document.getElementById('exportExcel').addEventListener('click', (e) => {
        e.preventDefault();
        const params = new URLSearchParams({
          transport_id: $('#select2Transport').find(':selected').val() || '',
          driver_id: $('#select2Driver').find(':selected').val() || '',
          date_begin: $('input[name="date_begin"]').val(),
          date_end: $('input[name="date_end"]').val(),
          salary: $('#selectSalary').val()

        });
        location.href = `{{ route('backend.laporan-rekap-operasional.index') }}/export?${params.toString()}`;
      });



    });
  </script>
@endsection
