<!DOCTYPE html>
<html>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/fontawesome.min.css"/>
<head>
  <style>

    body {
      font-family: Arial, sans-serif;
      -webkit-print-color-adjust: exact !important;
    }

    .table-title tbody tr td {
      padding-top: 0;
      padding-bottom: 0;
      line-height: 10px;
    }

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
      display: table-row-group;
    }

    tfoot {
      display: table-row-group;
    }

    .divider {
      width: 100%;
      display: block;
      border: 1px solid #36298e !important;
      color: #36298e !important;
      background-color: #36298e;
    }

    body {
      font-size: 10px;

    }

    .tableTagihan {
      font-size: 11px;
      border: black 1px solid;
      border-collapse: collapse;
      padding: 4px;
    }

    .tableTagihan th {
      border: black 1px solid;
      padding: 4px;
    }

    .tableTagihan td {
      border: black 1px solid;
      padding: 4px;
    }

    .tableRekening {
      font-size: 14px;
      font-weight: bold;
      border-collapse: collapse;
      border: 1px solid black;
    }

    .tableRekening td:nth-child(1) {
      padding-left: 4px;
    }

    .tableRekening td:nth-child(2) {
      padding-right: 4px;
    }

    .headerTagihan div {
      padding-bottom: 4px;
      font-size: 14px;
    }

    .ttdPembayaran {
      margin-top: 50px;
    }

    footer {
      position: fixed;
      width: 100%;
      bottom: 0;
    }

    header {
      position: fixed; /* Display only on print page (each) */
      top: 0; /* Because it's header */
    }

    .tableKwitansi {
      margin-top: 20px;
      font-size: 14px;
    }

    .tableKwitansi td {
      padding-top: 10px;
    }

    .tableKwitansiRekening {
      margin-top: 20px;
      font-size: 14px;
    }

    .tableKwitansiRekening td {
      padding-top: 4px;
      font-weight: bold;
    }


    @media print {
      @page {
        size: A4 portrait;
      }
    }
  </style>
</head>

<body>
<div style="display: flex; flex-direction: column; align-items: end; margin-top: 150px">
  <div class="ttdPembayaran" style="font-size: 12px; display: flex; flex-direction: column; width: 300px;">
    <div style="text-align: center; font-weight: bold">Kwitansi</div>
    <div style="text-align: center;">No: {{ $data->num_invoice }}</div>
  </div>
</div>
<table class="tableKwitansi">
  <tbody>
  <tr>
    <td style="width: 175px;">Telah diterima dari</td>
    <td>: {{ $data->costumer->name }}</td>
  </tr>
  <tr>
    <td style="width: 175px;">Uang Sejumlah</td>
    <td>: {{ ucwords(Terbilang::terbilang(($data->total_bill+$data->total_piutang))) }}</td>
  </tr>
  <tr>
    <td style="width: 175px;">Untuk Keperluan</td>
    <td>: Ongkos Angkut: {{ count($data->joborders) ?? 0 }} Surat Jalan</td>
  </tr>
  <tr>
    <td style="width: 175px;">Jumlah IDR</td>
    <td>: Rp. {{ number_format(($data->total_bill+$data->total_piutang), 2,',','.') }}</td>
  </tr>
  <tr style="border-top: 1px solid black; height: 10px">
    <td colspan="2"></td>
  </tr>
  </tbody>
</table>
<table class="tableKwitansiRekening">
  <tbody>
  <tr>
    <td colspan="2" style="font-weight: bold">PEMBAYARAN DI TRANSFER KE REKENING:</td>
  </tr>
  <tr>
    <td style="width: 150px; font-weight: bold">NOMOR REKENING</td>
    <td style="font-weight: bold">: {{ $bank->no_account }}</td>
  </tr>
  <tr>
    <td style="width: 150px; font-weight: bold">ATAS NAMA</td>
    <td style="font-weight: bold">: {{ $bank->name }}</td>
  </tr>
  <tr>
    <td style="width: 150px; font-weight: bold">BANK</td>
    <td style="font-weight: bold">: {{ $bank->name_bank }}</td>
  </tr>
  <tr>
    <td style="width: 150px; font-weight: bold">CABANG</td>
    <td style="font-weight: bold">: {{ $bank->branch }}</td>
  </tr>
  </tbody>
</table>
<div style="float: right;">
  <div class="ttdPembayaran" style="font-size: 12px; display: flex; flex-direction: column; width: 300px;">
    <div style="text-align: center">Bandar Lampung, {{ \Carbon\Carbon::now()->format('d F Y') }}</div>
    <div
      style="margin-top: 130px; text-align: center; font-weight: bold">{{ $data->costumer->cooperation->owner }}</div>
    <div style="text-align: center; font-weight: bold">{{ $data->costumer->cooperation->name }}</div>
  </div>
</div>
<p style="page-break-after: always;">&nbsp;</p>
<div class="headerTagihan" style="margin-top: 75px">
  <div>Bandar Lampung, {{ \Carbon\Carbon::now()->format('d M Y') }}</div>
  <div>Kepada Yang Terhormat,</div>
  <div>{{ $data->costumer->name }}</div>
  <div>No. Surat: {{ $data->num_invoice }}</div>
  <div>Perihal: Tagihan Jasa Expedisi</div>
  <div>Dengan Hormat,</div>
  <div>Kami lampirkan tagihan jasa ekspedisi dengan rute sebagai berikut:</div>
</div>
<table class="tableTagihan" style="width: 100%">
  <thead>
  <tr>
    <th>No</th>
    <th style="width: 60px;">Tanggal</th>
    <th>No. Surat Jalan</th>
    <th>Muat Dari</th>
    <th>Tujuan</th>
    <th style="width: 70px;">No Pol</th>
    <th style="width: 100px">No Shipment</th>
    <th>Biaya (Rp)</th>
  </tr>
  </thead>
  <tbody>
  @foreach($data->joborders as $item)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $item->date_begin }}</td>
      <td>{{ $item->no_sj  }}</td>
      <td>{{ $item->routefrom->name }}</td>
      <td>{{ $item->routeto->name }}</td>
      <td>{{ $item->transport->num_pol }}</td>
      <td>{{ $item->transport->no_shipment }}</td>
      <td style="text-align: right">{{ number_format($item->total_basic_price , 2, ',','.') }}</td>
    </tr>
    @foreach($item->piutangklaimcustomer as $piutangklaim)
      @if($piutangklaim->type == 'tambah')
        <tr>
          <td></td>
          <td colspan="6">{{ $piutangklaim->description }}</td>
          <td style="text-align: right">{{ number_format($piutangklaim->amount , 2, ',','.') }}</td>
        </tr>
      @endif
    @endforeach
  @endforeach
  <tr>
    <td colspan="7" style="text-align: right">Total</td>
    <td style="font-weight: bold; text-align: right">{{ number_format(($data->total_bill+$data->total_piutang) , 2, ',','.') }}</td>
  </tr>
  </tbody>
</table>
<table class="tableRekening">
  <tbody>
  <tr>
    <td colspan="2">PEMBAYARAN DI TRANSFER KE REKENING:</td>
  </tr>
  <tr>
    <td style="width: 150px">NOMOR REKENING</td>
    <td>: {{ $bank->no_account }}</td>
  </tr>
  <tr>
    <td style="width: 150px">ATAS NAMA</td>
    <td>: {{ $bank->name }}</td>
  </tr>
  <tr>
    <td style="width: 150px">BANK</td>
    <td>: {{ $bank->name_bank }}</td>
  </tr>
  <tr>
    <td style="width: 150px">CABANG</td>
    <td>: {{ $bank->branch }}</td>
  </tr>
  <tr>
    <td style="width: 150px">EXPEDISI</td>
    <td>: {{ $data->costumer->cooperation->nickname ?? '' }}</td>
  </tr>
  <tr style="border-top: 1px solid black; height: 10px">
    <td colspan="2"></td>
  </tr>
  </tbody>
</table>
<div class="ttdPembayaran" style="font-size: 12px; display: flex; flex-direction: column; width: 330px">
  <div>Atas perhatian dan kerjasamanya kami ucapkan terima kasih</div>
  <div style="margin-top: 15px; text-align: center">Regards</div>
  <div style="margin-top: 130px; text-align: center; font-weight: bold">{{ $data->costumer->cooperation->owner }}</div>
  <div style="text-align: center; font-weight: bold">{{ $data->costumer->cooperation->name }}</div>
</div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script type="text/javascript">
  window.onload = function (e) {
    window.print();
  }
  window.setTimeout(function () {
    window.close();
  }, 2000);
</script>
