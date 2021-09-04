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
                <a href="#" id="btn_excel" class="navi-link">
                  <span class="navi-icon">
                    <i class="la la-file-excel-o"></i>
                  </span>
                  <span class="navi-text">Excel</span>
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
      <div class="mb-10">
        <form action="{{ route('backend.ledgeroperational.index') }}" method="GET">
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
                            <input type="text" class="form-control" name="date_begin" id="dateBegin" value="{{ $date }}"
                                   readonly/>
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
      @foreach($data as $keyCollection => $itemCollection)
        @foreach ($itemCollection->children as $itemParent)
          <div class="table-responsive mb-10">
            <span
              class="d-bloc pt-2 font-size-lg font-weight-bolder">Kode Akun: {{ $itemParent->code }}</span>
            <span
              class="d-block pb-2 font-size-lg font-weight-bolder">Nama Akun: {{ $itemParent->name }}</span>
            <table class="table table-bordered">
              <thead>
              <tr>
                <th scope="row" rowspan="2" class="text-center align-middle">Tanggal</th>
                <th scope="row" rowspan="2" class="text-center align-middle">Keterangan</th>
                <th scope="row" rowspan="2" class="text-center align-middle">Debit</th>
                <th scope="row" rowspan="2" class="text-center align-middle">Kredit</th>
                <th colspan="2" class="text-center">Saldo</th>
              </tr>
              <tr>
                <th class="text-center" style="min-width: 100px">Debit</th>
                <th class="text-center" style="min-width: 100px">Kredit</th>
              </tr>
              </thead>
              <tbody>
              @php
                $saldo = 0;
                $saldo += $itemParent->rest_balance ?? 0;
              @endphp
              @if(isset($itemParent->rest_balance))
                <tr>
                  <td>-</td>
                  <td>Sisa saldo bulan sebelumnya</td>
                  <td
                    class="text-right">{{ $itemParent->rest_balance >= 0 ? number_format(abs($itemParent->rest_balance), 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right">{{ $itemParent->rest_balance < 0 ? number_format(abs($itemParent->rest_balance), 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right">{{ ($itemParent->rest_balance >= 0) ? number_format(abs($saldo), 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right">{{ ($itemParent->rest_balance < 0) ? number_format(abs($saldo), 2,'.',',') : NULL }}</td>
                </tr>
              @endif
              @foreach($itemParent->journal as $itemChildren)
                @php
                  if($itemParent->normal_balance == 'Db'){
                   if($itemChildren->debit != 0){
                       $saldo += $itemChildren->debit;
                   }else{
                       $saldo -= $itemChildren->kredit;
                   }
                  }else{
                    if($itemChildren->debit != 0){
                       $saldo -= $itemChildren->debit;
                   }else{
                       $saldo += $itemChildren->kredit;
                   }
                  }
                @endphp
                <tr>
                  <td style="width: 75px">{{ $itemChildren->date }}</td>
                  <td style="min-width: 300px">{{ $itemChildren->description }}</td>
                  <td
                    class="text-right"
                    style="min-width: 100px">{{ $itemChildren->debit != 0 ? number_format($itemChildren->debit, 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right"
                    style="min-width: 100px">{{ $itemChildren->kredit != 0 ? number_format($itemChildren->kredit, 2,'.',',') : NULL }}</td>
                  <td
                    class="text-right"
                    style="min-width: 100px">
                    @if($itemParent->normal_balance == 'Db' && $saldo >= 0)
                      {{ number_format($saldo, 2,'.',',') }}
                    @elseif($itemParent->normal_balance == 'Kr' && $saldo < 0)
                      {{ number_format(abs($saldo), 2,'.',',') }}
                    @endif
                  </td>
                  <td
                    class="text-right"
                    style="min-width: 100px">
                    @if($itemParent->normal_balance == 'Kr' && $saldo >= 0)
                      {{ number_format($saldo, 2,'.',',') }}
                    @elseif($itemParent->normal_balance == 'Db' && $saldo < 0)
                      {{ number_format(abs($saldo), 2,'.',',') }}
                    @endif
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        @endforeach
      @endforeach
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
      $('#btn_excel').on('click', function (e) {
        e.preventDefault();
        let params = new URLSearchParams({
          date_begin: $("input[name=date_begin]").val(),
        });
        window.location.href = '{{ $config['excel_url'] }}&' + params.toString();
      });

      $('#dateBegin').datepicker({
        format: 'M yyyy',
        viewMode: 'months',
        minViewMode: 'months'
      });
    });
  </script>
@endsection
