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
      <div class="mb-10">
        <form action="{{ route('backend.ledger.index') }}" method="GET">
          <div class="card">
            <h5 class="card-header bg-primary-o-60">Featured</h5>
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-12">
                  <div class="row align-items-center">
                    <div class="col-md-6 my-md-0">
                      <div class="form-group row">
                        <label class="col-md-3 align-self-center">Priode:</label>
                        <div class=" col-md-9">
                          <div class="input-daterange input-group">
                            <div class="input-group-prepend">
                        <span class="input-group-text">
											    <i class="la la-calendar-check-o"></i>
                        </span>
                            </div>
                            <input type="text" class="form-control" name="date_begin" id="dateBegin" readonly/>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer  d-flex justify-content-end">
              <button type="submit" class="btn btn-sm btn-primary">Submit</button>
            </div>
          </div>
        </form>
      </div>

      <!--begin: Datatable-->
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
          <tr>
            <th scope="col">Laporan Laba Rugi</th>
          </tr>
          <tr>
            <th scope="col">Untuk Bulan </th>
          </tr>
          <tbody>
          <tr>
            <th scope="row">1</th>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  {{-- Modal --}}
@endsection
{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
  <style>
    .table thead th {
      font-size: 0.80rem !important;
    }

    .table tbody td {
      font-size: 0.75rem !important;
    }
  </style>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
  {{-- page scripts --}}
  <script type="text/javascript">
    $(document).ready(function () {

      $('#dateBegin').datepicker({
        format: 'M yyyy',
        viewMode: 'months',
        minViewMode: 'months'
      });

    });
  </script>
@endsection
