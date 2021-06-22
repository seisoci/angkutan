<!DOCTYPE html>
<html>

<head>

  <style type="text/css">
    table {
      width: 100%;
      page-break-after: auto;
    }

    @media print {
      .table-title td,
      th {
        padding: 0;
      }

      table {
        width: 100%;
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
        display: table-row-group;
      }

      tfoot {
        display: table-row-group;
      }

      body {
        padding: 1em;
        background-color: #000;
      }

      hr {
        border: 1px #000 solid;
        width: 100%;
        display: block;
      }

      @page {
        size: A4 landscape;
      }

      .table-outline {
        border: 1px solid black;
      }

      .table-outline thead th {
        border-top: 1px solid #000 !important;
        border-bottom: 1px solid #000 !important;
        border-left: 1px solid #000;
        border-right: 1px solid #000;
      }

      .table-outline td {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        border-top: none !important;
      }
    }
  </style>
</head>

<body>
<div class="row justify-content-center py-8 px-8 px-md-0">
  <div class="col-md-11">
    <h2 style="text-align: center"><u>Laporan Invoice Pelanggan & Fee</u>
    </h2>
    <table class="table table-borderless table-title">
      <tbody>
      <tr>
        <td scope="col" class="font-weight-normal" style="width:50%">Printed: {{ $config['current_time'] }}
        </td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="width:20%">{{ $profile['name'] ?? '' }}</td>
      </tr>
      <tr>
        <td scope="col" class="font-weight-normal" style="width:50%">Priode: {{ $date ?? 'All' }}
        </td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="width:18%">{{ $profile['address'] ?? '' }}</td>
      </tr>
      <tr>
        <td scope="col" class="font-weight-normal" style="width:50%">Status Pembayaran: {{ !empty($status_pembayaran) ? ucwords($status_pembayaran) : 'All' }}
        </td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="width:18%"> {{ $profile['telp'] ?? ''}}</td>
      </tr>
      <tr>
        <td></td>
        <td scope="col" class="text-left" style="width:10%"></td>
        <td scope="col" class="text-left" style="width:18%">FAX {{ $profile['fax'] ?? ''}}</td>
      </tr>
      </tbody>
    </table>
    <div class="separator separator-solid separator-border-1"></div>
    @foreach ($data as $item)
      <table class="table table-borderless"
             style="padding-top: 0 !important; padding-bottom: 0 !important; margin-bottom: 0 !important; border-left: 1px solid #000 !important;
             border-right: 1px solid #000 !important;border-top: 1px solid #000 !important;font-family: monospace; font-size:12px;">
        <tbody>
        <tr>
          <td scope="col" class="font-weight-normal" style="width:10%; font-weight: bold;">Invoice Number
          </td>
          <td width="1%">:</td>
          <td scope="col" class="text-left" style="width:30%">{{ $item->num_invoice }}</td>
          <td scope="col" class="text-left" style="width:20%"></td>
          <td scope="col" class="text-left" style="width:21%; font-weight: bold;">Total Pajak</td>
          <td scope="col" class="text-left" style="width:1%">:</td>
          <td scope="col" class="text-left" style="width:25%; text-align: right !important;">{{ number_format($item->total_tax, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td scope="col" class="font-weight-normal" style="width:10%; font-weight: bold;">Tgl Invoice</td>
          <td width="1%">:</td>
          <td scope="col" class="text-left" style="width:30%">{{ $item->invoice_date }}</td>
          <td scope="col" class="text-left" style="width:10%"></td>
          <td scope="col" class="text-left" style="width:21%; font-weight: bold;">Total Fee</td>
          <td scope="col" class="text-left" style="width:1%">:</td>
          <td scope="col" class="text-left" style="width:25%; text-align: right !important;">{{ number_format($item->total_fee_thanks, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td scope="col" class="font-weight-normal" style="width:10%; font-weight: bold;">Tgl Jth. Tempo Invoice</td>
          <td width="1%">:</td>
          <td scope="col" class="text-left" style="width:30%">{{ $item->due_date }}</td>
          <td scope="col" class="text-left" style="width:10%"></td>
          <td scope="col" class="text-left" style="width:21%; font-weight: bold;">Total Pembayaran</td>
          <td scope="col" class="text-left" style="width:1%">:</td>
          <td scope="col" class="text-left" style="width:25%; text-align: right !important;">{{ number_format($item->total_payment, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td scope="col" class="font-weight-normal" style="width:15%; font-weight: bold;">Nama Pelanggan</td>
          <td width="1%">:</td>
          <td scope="col" class="text-left" style="width:30%">{{ $item->costumer->name }}</td>
          <td scope="col" class="text-left" style="width:10%"></td>
          <td scope="col" class="text-left" style="width:21%; font-weight: bold;">Potongan</td>
          <td scope="col" class="text-left" style="width:1%">:</td>
          <td scope="col" class="text-left" style="width:25%; text-align: right !important;">{{ number_format($item->total_cut, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td scope="col" class="font-weight-normal" style="width:10%; font-weight: bold;">Total Tagihan
          </td>
          <td width="1%">:</td>
          <td scope="col" class="text-left" style="width:30%">{{ number_format($item->total_bill, 2, ',', '.') }}</td>
          <td scope="col" class="text-left" style="width:20%"></td>
          <td scope="col" class="text-left" style="width:21%; font-weight: bold;">Sisa Tagihan</td>
          <td scope="col" class="text-left" style="width:1%">:</td>
          <td scope="col" class="text-left" style="width:25%; text-align: right !important;">{{ number_format($item->rest_payment, 2, ',', '.') }}</td>
        </tr>
        </tbody>
      </table>
      <table class="table"
             style="padding-top: 0 !important; padding-bottom: 0 !important; border-left: 1px solid #000 !important;
             border-right: 1px solid #000 !important;border-bottom: 1px solid #000 !important; margin-bottom: 10px;font-family: monospace; font-size:12px;">
        <thead>
        <tr>
          <th>#</th>
          <th style="text-align: left !important;">No. Surat Jalan</th>
          <th style="text-align: left !important;">Tgl Muat</th>
          <th style="text-align: left !important;">No. Polisi</th>
          <th style="text-align: left !important;">Nama Supir</th>
          <th style="text-align: left !important;">Rute Dari</th>
          <th style="text-align: left !important;">Rute Tujuan</th>
          <th style="text-align: left !important;">Muatan</th>
          <th style="text-align: right !important;">Pajak (Rp.)</th>
          <th style="text-align: right !important;">Fee</th>
          <th style="text-align: right !important;">Total Tagihan</th>
        </tr>
        </thead>
        <tbody>
        @foreach($item->joborders as $child)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $child->num_prefix }}</td>
            <td>{{ $child->date_begin }}</td>
            <td>{{ $child->transport->num_pol }}</td>
            <td>{{ $child->driver->name }}</td>
            <td>{{ $child->routefrom->name }}</td>
            <td>{{ $child->routeto->name }}</td>
            <td>{{ $child->cargo->name }}</td>
            <td  style="text-align: right !important;">{{ number_format($child->tax_amount, 2, ',', '.') }}</td>
            <td  style="text-align: right !important;">{{ number_format($child->fee_thanks, 2, ',', '.') }}</td>
            <td  style="text-align: right !important;">{{ number_format($child->total_basic_price, 2, ',', '.') }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endforeach
  </div>
</div>
</body>
@foreach(config('layout.resources.js') as $script)
  <script src="{{ asset($script) }}" type="text/javascript"></script>
  <script>
    window.onload = function (e) {
      window.print();
    }
    window.setTimeout(function () {
      window.close();
    }, 2000);
  </script>
@endforeach

</html>
