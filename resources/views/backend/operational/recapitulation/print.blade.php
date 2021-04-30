<!DOCTYPE html>
<html>

<head>
  @foreach(config('layout.resources.css') as $style)
  <link href="{{ config('layout.self.rtl') ? asset(Metronic::rtlCssPath($style)) : asset($style) }}" rel="stylesheet"
    type="text/css" />
  @endforeach

  <style type="text/css">
    @media print {

      table {
        page-break-after: auto;
      }

      tr {
        page-break-inside: avoid;
        page-break-after: auto;
      }

      td {
        page-break-inside: avoid;
        page-break-after: auto;
      }

      thead {
        display: table-header-group;
      }

      tfoot {
        display: table-footer-group;
      }

      * {
        font-family: Arial;
        font-size: 12px;
      }

      body {
        margin: 3em;
        color: #fff;
        background-color: #000;
      }

      hr {
        border: 1px #000 solid;
        width: 100%;
        display: block;
      }

      table tr td {
        padding: 2px;
      }

      @page {
        size: A4 potrait;
      }

      .float-right {
        float: right !important;
      }

      .float-left {
        float: left !important;
      }

      .table-header {
        position: relative;
        display: grid;
        grid-template-columns: 50% 50%;
      }
    }
  </style>
</head>

<body>
  @if(!empty($data))
  <div class="table-header">
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
  <table class="table table-bordered w-full small">
    <thead>
      <tr>
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
  <div class="table-header">
    <div>
      <h4 class="text-dark-75"><u>Laporan Biaya Operasional</u></h4>
    </div>
    <div>
      <h4 class="text-dark-75 "><u>ALUSINDO</u></h4>
      <p class="text-muted font-weight-normal my-0">{{ $profile['address'] ?? '' }}</p>
      <p class="text-muted font-weight-normal my-0">Telp: {{ $profile['telp'] }}</p>
      <p class="text-muted font-weight-normal my-0">Fax: {{ $profile['fax'] }}</p>
    </div>
  </div>
  @foreach ($data as $item)
  @php $noOperational = 1; @endphp
  <table class="table w-full small">
    <thead>
      <tr>
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
  <div class="break-page">
    <div class="table-header">
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
  </div>
  <table class="table w-full small">
    <thead>
      <tr>
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
  <div class="table-header">
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
      <tr>
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
  @endif
</body>
@foreach(config('layout.resources.js') as $script)
<script src="{{ asset($script) }}" type="text/javascript"></script>
<script>
  window.onload = function(e){
    window.print();
  }
  window.setTimeout(function(){
    window.close();
  }, 2000);
</script>
@endforeach

</html>
