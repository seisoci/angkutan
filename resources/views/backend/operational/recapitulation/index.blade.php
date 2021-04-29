{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

<!--begin::Card-->
<div class="card card-custom gutter-b mb-8">
  <div class="card-header">
    <div class="card-title">
      <h3 class="card-label text-center">
        Laporan Rekapitulasi
      </h3>
    </div>
  </div>
  <form action="{{ route('backend.recapitulation.index') }}">
    <div class="card-body">
      @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif
      <div class="form-group row">
        <label class="col-form-label text-left col-lg-3 col-sm-12">No. Polisi</label>
        <div class=" col-lg-4 col-md-9 col-sm-12">
          <select id="select2Transport" class="form-control" name="transport_id">
            @if ($transport && $transport != 'Semua Mobil')
            <option value="{{ $transport->id }}">{{ $transport->num_pol }}</option>
            @endif
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-form-label text-left col-lg-3 col-sm-12">Supir</label>
        <div class=" col-lg-4 col-md-9 col-sm-12">
          <select id="select2Driver" class="form-control" name="driver_id">
            @if ($driver && $driver != 'Semua Supir')
            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
            @endif
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-form-label text-left col-lg-3 col-sm-12">Tanggal Mulai (Dari)</label>
        <div class="col-lg-4 col-md-9 col-sm-12">
          <div class="input-group date">
            <input type="text" class="form-control datepicker" readonly name="date_begin"
              value="{{ $date_begin ?? '' }}" />
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
            <input type="text" class="form-control datepicker" readonly name="date_end" value="{{ $date_end ?? '' }}" />
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
      <button type="submit" class="btn btn-primary font-weight-bold">Cari Data</a>
    </div>
  </form>
</div>
<div class="card card-custom">
  <div class="card-body">
    {{-- Laporan Pendapatan Mobil --}}
    <div class="d-flex justify-content-lg-between mb-10">
      <div>
        <h4 class="text-dark-75"><u>Laporan Pendapatan Mobil</u></h4>
        <p class="text-dark-75 font-weight-normal my-0">No. Polisi: {{ $transport->num_pol ?? $transport }}</p>
        <p class="text-dark-75 font-weight-normal my-0">Priode: {{ $date_begin ?? ''}} sd {{ $date_end ?? '' }}</p>
      </div>
      <div>
        <h4 class="text-dark-75"><u>ALUSINDO</u></h4>
        <p class="text-muted font-weight-normal my-0">{{ $profile['address'] ?? '' }}</p>
        <p class="text-muted font-weight-normal my-0">Telp: {{ $profile['telp'] }}</p>
        <p class="text-muted font-weight-normal my-0">Fax: {{ $profile['fax'] }}</p>
      </div>
    </div>
    @if(!empty($data))
    <table class="table table-bordered w-full small">
      <thead>
        <tr class="table-primary">
          <th class="text-center">No.</th>
          <th class="text-center">Tanggal</th>
          <th class="text-center">S. Jalan</th>
          <th class="text-center">Pelanggan</th>
          <th class="text-center">Dari</th>
          <th class="text-center">Tujuan</th>
          <th class="text-center">Jenis Barang</th>
          <th class="text-center">Tarif(Rp.)</th>
          <th class="text-center">Qty(Unit)</th>
          <th class="text-center">Total(Rp.)</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data as $item)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>{{ $item->prefix.'-'.$item->num_bill }}</td>
          <td>{{ $item->costumer->name }}</td>
          <td>{{ $item->routefrom->name }}</td>
          <td>{{ $item->routeto->name }}</td>
          <td>{{ $item->cargo->name }}</td>
          <td class="text-right">{{ number_format($item->basic_price, 2,'.', ',') }}</td>
          <td class="text-right">{{ $item->payload }}</td>
          <td class="text-right">{{ number_format($item->total_basic_price, 2, '.', ',') }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="9" class="text-right">Total Rp. </td>
          <td class="text-right">{{ number_format($data->sum('total_basic_price'), 2, '.', ',') }}</td>
        </tr>
      </tfoot>
    </table>
    <div class="separator separator-solid separator-border-1 my-20"></div>
    {{-- Laporan Biaya Operasional --}}
    <div class="d-flex justify-content-lg-between mb-10">
      <div>
        <h4 class="text-dark-75"><u>Laporan Biaya Operasional</u></h4>
      </div>
      <div>
        <h4 class="text-dark-75"><u>ALUSINDO</u></h4>
        <p class="text-muted font-weight-normal my-0">{{ $profile['address'] ?? '' }}</p>
        <p class="text-muted font-weight-normal my-0">Telp: {{ $profile['telp'] }}</p>
        <p class="text-muted font-weight-normal my-0">Fax: {{ $profile['fax'] }}</p>
      </div>
    </div>
    @foreach ($data as $item)
    @php $noOperational = 1; @endphp
    <table class="table w-full small">
      <thead>
        <tr class="table-primary">
          <th class="text-center">No.</th>
          <th>Tanggal</th>
          <th>Master Biaya</th>
          <th>Keterangan</th>
          <th class="text-right">Jumlah</th>
          <th>S. Jalan</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="text-center">{{ $noOperational++ }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>UANG JALAN</td>
          <td></td>
          <td class="text-right">{{ number_format($item->road_money, 2, '.', ',') }}</td>
          <td>{{ $item->prefix.'-'.$item->num_bill }}</td>
        </tr>
        @foreach ($item->operationalexpense as $itemExpense)
        <tr>
          <td class="text-center">{{ $noOperational++ }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>{{ $itemExpense->expense->name }}</td>
          <td>{{ $itemExpense->description }}</td>
          <td class="text-right">{{ number_format($itemExpense->amount, 2, '.', ',') }}</td>
          <td>{{ $item->prefix.'-'.$item->num_bill }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-right">Sub Total Rp. </td>
          <td class="text-right">
            {{ number_format($item->total_operational, 2, '.', ',') }}</td>
          <td></td>
        </tr>
        @if($loop->last)
        <tr>
          <td colspan="4" class="text-right">Total Operational Rp. </td>
          <td class="text-right">
            {{ number_format($data->sum('total_operational'), 2, '.', ',') }}</td>
          <td></td>
        </tr>
        @endif
        <tr>
          <td colspan="6"></td>
        </tr>
      </tfoot>
    </table>
    @endforeach
    <div class="separator separator-solid separator-border-1 my-20"></div>
    {{-- Laporan Sparepart --}}
    <div class="d-flex justify-content-lg-between mb-10">
      <div>
        <h4 class="text-dark-75"><u>Laporan Sparepart</u></h4>
      </div>
      <div>
        <h4 class="text-dark-75"><u>ALUSINDO</u></h4>
        <p class="text-muted font-weight-normal my-0">{{ $profile['address'] ?? '' }}</p>
        <p class="text-muted font-weight-normal my-0">Telp: {{ $profile['telp'] }}</p>
        <p class="text-muted font-weight-normal my-0">Fax: {{ $profile['fax'] }}</p>
      </div>
    </div>
    <table class="table w-full small">
      <thead>
        <tr class="table-primary">
          <th class="text-center">No.</th>
          <th>Tanggal</th>
          <th>S. Jalan</th>
          <th>Nama Supir</th>
          <th>No. Polisi</th>
          <th class="text-right">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data as $item)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>{{ $item->prefix.'-'.$item->num_bill }}</td>
          <td>{{ $item->driver->name }}</td>
          <td>{{ $item->transport->num_pol }}</td>
          <td class="text-right">{{ number_format($item->total_sparepart, 2, '.', ',') }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5" class="text-right">Total Rp. </td>
          <td class="text-right">{{ number_format($data->sum('total_sparepart'), 2, '.', ',') }}
          </td>
        </tr>
        <tr>
          <td colspan="6"></td>
        </tr>
      </tfoot>
    </table>
    <div class="separator separator-solid separator-border-1 my-20"></div>
    {{-- Laporan Gaji Supir --}}
    <div class="d-flex justify-content-lg-between mb-10">
      <div>
        <h4 class="text-dark-75"><u>Laporan Gaji Supir</u></h4>
      </div>
      <div>
        <h4 class="text-dark-75"><u>ALUSINDO</u></h4>
        <p class="text-muted font-weight-normal my-0">{{ $profile['address'] ?? '' }}</p>
        <p class="text-muted font-weight-normal my-0">Telp: {{ $profile['telp'] }}</p>
        <p class="text-muted font-weight-normal my-0">Fax: {{ $profile['fax'] }}</p>
      </div>
    </div>
    <table class="table w-full small">
      <thead>
        <tr class="table-primary">
          <th class="text-center">No.</th>
          <th>Tanggal</th>
          <th>S. Jalan</th>
          <th>Nama Supir</th>
          <th>No. Polisi</th>
          <th class="text-right">Gaji</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data as $item)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td>{{ $item->date_begin }}</td>
          <td>{{ $item->prefix.'-'.$item->num_bill }}</td>
          <td>{{ $item->driver->name }}</td>
          <td>{{ $item->transport->num_pol }}</td>
          <td class="text-right">{{ number_format($item->total_salary, 2, '.', ',') }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5" class="text-right">Total Rp. </td>
          <td class="text-right">{{ number_format($data->sum('total_salary'), 2, '.', ',') }}</td>
        </tr>
        <tr>
          <td colspan="6"></td>
        </tr>
      </tfoot>
    </table>
    <div class="separator separator-solid separator-border-1 my-20"></div>
    <table class="table w-full small">
      <tbody>
        <tr>
          <td>Total Pendapatan</td>
          <td class="text-right">{{ number_format($data->sum('total_basic_price'), 2, '.', ',') }}</td>
        </tr>
        <tr>
          <td>Total Biaya</td>
          <td class="text-right">
            {{ number_format(($data->sum('total_operational') + $data->sum('total_sparepart') + $data->sum('total_salary')), 2, '.', ',') }}
          </td>
        </tr>
        <tr>
          <td>Total Bersih</td>
          <td class="text-right">
            {{ number_format($data->sum('total_basic_price') - ($data->sum('total_operational') + $data->sum('total_sparepart') + $data->sum('total_salary')), 2, '.', ',') }}
          </td>
        </tr>
      </tbody>
      <tfoot>
      </tfoot>
    </table>
    <div class="d-flex justify-content-end">
      <a class="btn btn-info btn-sm mr-4"
        href="{{ route('backend.recapitulation.document', ['transport_id='.$transport_id.'', 'driver_id='.$driver_id.'', 'date_begin='.$date_begin.'', 'date_end='.$date_end.'', 'type=PDF']) }}"><i
          class="fas fa-file-pdf"></i>
        Export
        PDF</a>
      <a class="btn btn-success btn-sm mr-4"
        href="{{ route('backend.recapitulation.document', ['transport_id='.$transport_id.'', 'driver_id='.$driver_id.'', 'date_begin='.$date_begin.'', 'date_end='.$date_end.'', 'type=EXCEL']) }}"><i
          class="far fa-file-excel"></i>
        Export
        Excel</a>
      <a target="_blank" name="" class="btn btn-primary btn-sm"
        href="{{ route('backend.recapitulation.print', ['transport_id='.$transport_id.'', 'driver_id='.$driver_id.'', 'date_begin='.$date_begin.'', 'date_end='.$date_end.'', 'type=EXCEL']) }}"><i
          class="fas fa-print"></i>
        Print</a>
    </div>
    @endif
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

    $('.datepicker').datepicker({
      format: 'yyyy-mm-dd',
      todayHighlight: !0,
      todayBtn: "linked",
      clearBtn: !0,
    });

    $("#select2Driver").select2({
      placeholder: "Search Driver",
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
    });
  });
</script>
@endsection
