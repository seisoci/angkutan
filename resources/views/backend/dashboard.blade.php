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
        <div class="row row-eq-height">
          @hasanyrole('admin|super-admin')
          <div class="col-md-3">
            <div class="bg-light-warning px-6 py-8 rounded-xl mb-7">
              <div class="d-flex justify-content-between px-5">
                <div>
                <span class="svg-icon svg-icon-3x svg-icon-warning d-block my-2">
                  <i class="fas fa-user-friends fa-3x" style="color:#ffa800"></i>
                </span>
                  <span class="text-warning font-weight-bold font-size-h6">Supir Aktif</span>
                </div>
                <div class="align-self-center">
                  <span class="text-warning font-weight-bold mt-2 font-size-h1">{{ $driver_count ?? NULL }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-light-primary px-6 py-8 rounded-xl mb-7">
              <div class="d-flex justify-content-between px-5">
                <div>
                <span class="svg-icon svg-icon-3x svg-icon-primary d-block my-2">
                  <i class="fas fa-truck fa-3x" style="color:#3699ff"></i>
                </span>
                  <span class="text-primary font-weight-bold font-size-h6 mt-2">Total Kendaraan</span>
                </div>
                <div class="align-self-center">
                  <span class="text-primary font-weight-bold mt-2 font-size-h1">{{ $transport_count ?? NULL }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-light-danger px-6 py-8 rounded-xl mb-7">
              <div class="d-flex justify-content-between px-5">
                <div>
                <span class="svg-icon svg-icon-3x svg-icon-danger d-block my-2">
                  <i class="fas fa-briefcase fa-3x" style="color:#f64e60"></i>
                </span>
                  <span class="text-danger font-weight-bold font-size-h6 mt-2">Total Job Orders</span>
                </div>
                <div class="align-self-center">
                  <span class="text-danger font-weight-bold mt-2 font-size-h1">{{ $joborder_count ?? NULL }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="bg-light-success px-6 py-8 rounded-xl mb-7">
              <div class="d-flex justify-content-between px-5">
                <div>
                <span class="svg-icon svg-icon-3x svg-icon-success d-block my-2">
                  <i class="fas fa-wrench fa-3x" style="color:#1bc5bd"></i>
                </span>
                  <span class="text-success font-weight-bold font-size-h6 mt-2">Total Sparepart</span>
                </div>
                <div class="align-self-center">
                  <span class="text-success font-weight-bold mt-2 font-size-h1">{{ $sparepart_count ?? NULL }}</span>
                </div>
              </div>
            </div>
          </div>
          @else
          <div class="d-flex justify-content-center align-items-center w-100">
            <h2>Selamat Datang</h2>
          </div>
          @endhasanyrole
        </div>
      </div>
    </div>
  </div>
  @hasanyrole('admin|super-admin')
  <div class="card card-custom mt-10">
    <div class="card-header flex-wrap py-3">
      <div class="card-title w-100">
        <h3 class="btn btn-primary card-label w-100 text-center text-white">Informasi Kendaraan & Supir</h3>
      </div>
    </div>

    <div class="card-body">
      <div class="mb-10">
        <div class="row row-eq-height">
          <div class="col-md-6">
            <!--begin::Advance Table Widget 4-->
            <div class="card card-custom card-stretch gutter-b">
              <!--begin::Header-->
              <div class="card-header border-0 py-5">
                <h3 class="card-title align-items-start flex-column">
                  <span class="card-label font-weight-bolder text-dark">Masa Berlaku STNK</span>
                  {{-- <span class="text-muted mt-3 font-weight-bold font-size-sm"></span> --}}
                </h3>
              </div>
              <!--end::Header-->
              <!--begin::Body-->
              <div class="card-body pt-0 pb-3">
                <div class="tab-content">
                  <!--begin::Table-->
                  <div class="table-responsive">
                    <table class="table table-head-custom table-head-bg table-vertical-center">
                      <thead>
                      <tr class="text-left text-uppercase">
                        <th style="width: 200px">No. Polisi</th>
                        <th style="width: 100px">Masa Berlaku</th>
                      </tr>
                      </thead>
                      <tbody>
                      @foreach ($expired_stnk as $item)
                        <tr>
                          <td>
                            <span
                              class="text-dark-75 font-weight-bolder d-block font-size-lg">{{ $item->num_pol }}</span>
                          </td>
                          <td>
                            @if($item->diff_days >= 0)
                              <span class="btn btn-block btn-light-warning font-weight-bolder font-size-sm text-left"><i
                                  class="fas fa-info-circle"></i> {{ $item->diff_days }} Hari Lagi</span>
                            @else
                              <span class="btn btn-block btn-light-danger font-weight-bolder font-size-sm text-left"><i
                                  class="fas fa-info-circle"></i>{{ $item->diff_days }} Hari Telat</span>
                            @endif

                          </td>
                        </tr>
                      @endforeach
                      </tbody>
                    </table>
                  </div>
                  <!--end::Table-->
                </div>
              </div>
              <!--end::Body-->
            </div>
            <!--end::Advance Table Widget 4-->
          </div>
          <div class="col-md-6">
            <div class="card card-custom card-stretch gutter-b">
              <!--begin::Header-->
              <div class="card-header border-0 py-5">
                <h3 class="card-title align-items-start flex-column">
                  <span class="card-label font-weight-bolder text-dark">Masa Berlaku KIR</span>
                  {{-- <span class="text-muted mt-3 font-weight-bold font-size-sm"></span> --}}
                </h3>
              </div>
              <!--end::Header-->
              <!--begin::Body-->
              <div class="card-body pt-0 pb-3">
                <div class="tab-content">
                  <!--begin::Table-->
                  <div class="table-responsive">
                    <table class="table table-head-custom table-head-bg table-vertical-center">
                      <thead>
                      <tr class="text-left text-uppercase">
                        <th style="width: 200px">No. Polisi</th>
                        <th style="width: 100px">Masa Berlaku</th>
                      </tr>
                      </thead>
                      <tbody>
                      @foreach ($expired_kir as $item)
                        <tr>
                          <td>
                            <span
                              class="text-dark-75 font-weight-bolder d-block font-size-lg">{{ $item->num_pol }}</span>
                          </td>
                          <td>
                            @if($item->diff_days >= 0)
                              <span class="btn btn-block btn-light-warning font-weight-bolder font-size-sm text-left"><i
                                  class="fas fa-info-circle"></i> {{ $item->diff_days }} Hari Lagi</span>
                            @else
                              <span class="btn btn-block btn-light-danger font-weight-bolder font-size-sm text-left"><i
                                  class="fas fa-info-circle"></i>{{ $item->diff_days }} Hari Telat</span>
                            @endif

                          </td>
                        </tr>
                      @endforeach
                      </tbody>
                    </table>
                  </div>
                  <!--end::Table-->
                </div>
              </div>
              <!--end::Body-->
            </div>
          </div>
          <div class="col-md-6">
            <div class="card card-custom card-stretch gutter-b">
              <!--begin::Header-->
              <div class="card-header border-0 py-5">
                <h3 class="card-title align-items-start flex-column">
                  <span class="card-label font-weight-bolder text-dark">Masa Berlaku SIM</span>
                  {{-- <span class="text-muted mt-3 font-weight-bold font-size-sm"></span> --}}
                </h3>
              </div>
              <!--end::Header-->
              <!--begin::Body-->
              <div class="card-body pt-0 pb-3">
                <div class="tab-content">
                  <!--begin::Table-->
                  <div class="table-responsive">
                    <table class="table table-head-custom table-head-bg table-vertical-center">
                      <thead>
                      <tr class="text-left text-uppercase">
                        <th style="width: 200px">Nama</th>
                        <th style="width: 100px">Masa Berlaku</th>
                      </tr>
                      </thead>
                      <tbody>
                      @foreach ($expired_sim as $item)
                        <tr>
                          <td>
                            <span class="text-dark-75 font-weight-bolder d-block font-size-lg">{{ $item->name }}</span>
                          </td>
                          <td>
                            @if($item->diff_days >= 0)
                              <span class="btn btn-block btn-light-warning font-weight-bolder font-size-sm text-left"><i
                                  class="fas fa-info-circle"></i> {{ $item->diff_days }} Hari Lagi</span>
                            @else
                              <span class="btn btn-block btn-light-danger font-weight-bolder font-size-sm text-left"><i
                                  class="fas fa-info-circle"></i>{{ $item->diff_days }} Hari Telat</span>
                            @endif

                          </td>
                        </tr>
                      @endforeach
                      </tbody>
                    </table>
                  </div>
                  <!--end::Table-->
                </div>
              </div>
              <!--end::Body-->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card card-custom mt-10">
    <div class="card-header flex-wrap py-3">
      <div class="card-title w-100">
        <h3 class="btn btn-success card-label w-100 text-center text-white">Informasi Job Order</h3>
      </div>
    </div>

    <div class="card-body">
      <div class="mb-10">
        <div class="row row-eq-height">
          <div class="col-md-12">
            <!--begin::Advance Table Widget 4-->
            <div class="card card-custom card-stretch gutter-b">
              <!--begin::Header-->
              <div class="card-header border-0 py-5">
                <h3 class="card-title align-items-start flex-column">
                  <span class="card-label font-weight-bolder text-dark">Surat Dokumen Belum Kembali</span>
                  {{-- <span class="text-muted mt-3 font-weight-bold font-size-sm"></span> --}}
                </h3>
              </div>
              <!--end::Header-->
              <!--begin::Body-->
              <div class="card-body pt-0 pb-3">
                <div class="tab-content">
                  <!--begin::Table-->
                  <div class="table-responsive">
                    <table class="table table-head-custom table-head-bg table-vertical-center">
                      <thead>
                      <tr class="text-left text-uppercase">
                        <th>Prefix</th>
                        <th>No. Job Order</th>
                        <th>LDO</th>
                        <th>Supir</th>
                        <th>No. Polisi</th>
                        <th>Pelanggan</th>
                        <th>Rute Dari</th>
                        <th>Rute Ke</th>
                        <th>Muatan</th>
                      </tr>
                      </thead>
                      <tbody>
                      @foreach ($job_order_document as $item)
                        <tr>
                          <td>
                            <span class="text-dark-75 d-block font-size-xs">{{ $item->prefix }}</span>
                          </td>
                          <td>
                            <span class="text-dark-75 d-block font-size-xs">{{ $item->num_bill }}</span>
                          </td>
                          <td>
                          <span
                            class="text-dark-75 d-block font-size-xs">{{ $item->anotherexpedition->name ?? NULL }}</span>
                          </td>
                          <td>
                            <span class="text-dark-75 d-block font-size-xs">{{ $item->driver->name }}</span>
                          </td>
                          <td>
                            <span class="text-dark-75 d-block font-size-xs">{{ $item->transport->num_pol }}</span>
                          </td>
                          <td>
                            <span class="text-dark-75 d-block font-size-xs">{{ $item->costumer->name }}</span>
                          </td>
                          <td>
                            <span class="text-dark-75 d-block font-size-xs">{{ $item->routefrom->name }}</span>
                          </td>
                          <td>
                            <span class="text-dark-75 d-block font-size-xs">{{ $item->routeto->name }}</span>
                          </td>
                          <td>
                            <span class="text-dark-75 d-block font-size-xs">{{ $item->cargo->name }}</span>
                          </td>
                        </tr>
                      @endforeach
                      </tbody>
                    </table>
                  </div>
                  <!--end::Table-->
                </div>
              </div>
              <!--end::Body-->
            </div>
            <!--end::Advance Table Widget 4-->
          </div>
        </div>
      </div>
    </div>
  </div>
  @endhasanyrole
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
      var dataTable = $('#Datatable').DataTable({
        autoWidth: false,
        responsive: false,
        fixedHeader: true,
        scrollX: true,
        processing: true,
        serverSide: true,
        order: [[7, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: 10,
        ajax: {
          url: "{{ route('backend.reportcostumersldo.index') }}",
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
            render: function (data, type, full, meta) {
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
      })
    });
  </script>
@endsection
