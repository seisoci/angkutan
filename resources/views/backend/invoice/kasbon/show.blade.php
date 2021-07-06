{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
  <!-- begin::Card-->
  <div class="card card-custom overflow-hidden">
    {{-- Header --}}
    <div class="card-header d-flex justify-content-end align-items-center">
      <div class="">
        <div class="btn-group btn-group-md" role="group" aria-label="Large button group">
          <button onclick="window.history.back();" type="button" class="btn btn-outline-secondary"><i
              class="fa fa-arrow-left"></i> Back
          </button>
          <a href="#" id="btn_print" class="btn btn-outline-secondary font-weight-bold" target="_blank">
                  <span class="navi-icon">
                    <i class="la la-print"></i>
                  </span>
            <span class="navi-text">Print</span>
          </a>
        </div>
      </div>
    </div>
    {{-- Body --}}
    <div class="card-body p-0">
      <!-- begin: Invoice header-->
      <div class="row justify-content-center py-8 px-8 px-md-0">
        <div class="col-md-11">
          <h2 class="font-weight-boldest text-center mb-10 text-uppercase text-dark"><u>Kasbon</u></h2>
          <table class="table table-borderless table-title">
            <tbody>
            <tr>
              <td scope="col" class="font-weight-bolder text-uppercase" style="width:50%">{{ $profile['name'] ?? '' }}
              </td>
            </tr>
            <tr>
              <td scope="col" style="width:50%">{{ $profile['address'] ?? '' }}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" class="text-left" style="padding-left:4rem;width:20%">Supir</td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:18%"> {{ $data->driver->name }}</td>
            </tr>
            <tr>
              <td scope="col">{{ $profile['telp'] ?? ''}}</td>
              <td scope="col" class="text-left" style="width:10%"></td>
              <td scope="col" class="text-left" style="padding-left:4rem;width:20%">Tanggal</td>
              <td scope="col" class="text-left" style="width:2%">&ensp;: &ensp;</td>
              <td scope="col" class="text-left" style="width:18%"> {{ $data->created_at }}</td>
            </tr>
            <tr>
              <td scope="col">FAX {{ $profile['fax'] ?? ''}}</td>
            </tr>
            </tbody>
          </table>
          <div class="separator separator-solid separator-border-1"></div>
          <table class="table">
            <thead>
            <tr>
              <th scope="col" class="text-left">Keterangan</th>
              <th scope="col" class="text-right">Nominal</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td class="text-left">{{ $data->memo }}</td>
              <td class="text-right">{{ number_format($data->amount, 2, '.', ',') }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
  <style>
    .table-title td,
    th {
      padding: 0;
    }
  </style>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script>
    $(document).ready(function () {
      $('#btn_print').on('click', function (e) {
        e.preventDefault();
        $.ajax({
          url: "{{ $config['print_url'] }}",
          success: function (text) {
            console.log(text);
            $.post('http://localhost/dotmatrix/', JSON.stringify({
              printer: 'DotMatrix',
              data: text,
              autocut: true
            }), function (response) {
              console.log(response);
            });
          }
        });
      });
    });
  </script>
  {{-- page scripts --}}
@endsection
