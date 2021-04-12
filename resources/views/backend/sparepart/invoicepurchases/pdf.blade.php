<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />


  <title>Admin | Detail Supir</title>


  <meta name="description" content="" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />


  <link rel="shortcut icon" href="media/logos/favicon.ico" />


  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">


  <link href="{{ asset('plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('plugins/custom/prismjs/prismjs.bundle.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('css/style.bundle.css') }}" rel="stylesheet" type="text/css" />


  <link href="{{ asset('css/themes/layout/header/base/light.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('css/themes/layout/header/menu/light.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('css/themes/layout/aside/dark.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('css/themes/layout/brand/dark.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
  <div class="d-flex flex-column-fluid">
    <div class=" container ">
      <div class="card card-custom overflow-hidden">
        <div class="card-body p-0">
          <!-- begin: Invoice-->
          <!-- begin: Invoice header-->
          <div class="row justify-content-center py-8 px-8 py-md-27 px-md-0">
            <div class="col-md-9">
              <div class="d-flex justify-content-between pb-10 pb-md-20 flex-column flex-md-row">
                <h1 class="display-4 font-weight-boldest mb-10">INVOICE <br>PURCHASE ORDER</h1>
                <div class="d-flex flex-column align-items-md-end px-0">
                  <!--begin::Logo-->
                  <a href="#" class="mb-5">
                    <img
                      src="{{ $profile['logo_url'] != NULL ? asset("/images/thumbnail/".$profile['logo_url']) : asset('media/bg/no-content.svg') }}"
                      width="75px" height="75px" />
                  </a>
                  <!--end::Logo-->
                  <span class="d-flex flex-column align-items-md-end opacity-70">
                    <span>{{ $profile['name'] ?? '' }}</span>
                    <span>{{ $profile['telp'] ?? ''}}</span>
                    <span>{{ $profile['email'] ?? '' }}</span>
                    <span>{{ $profile['address'] ?? '' }}</span>
                  </span>
                </div>
              </div>
              <div class="border-bottom w-100"></div>
              <div class="d-flex justify-content-between pt-6">
                <div class="d-flex flex-column flex-root">
                  <span class="font-weight-bolder mb-2">Tanggal & Waktu</span>
                  <span class="opacity-70">{{ $data->created_at ?? '' }}</span>
                </div>
                <div class="d-flex flex-column flex-root">
                  <span class="font-weight-bolder mb-2">NO. INVOICE</span>
                  <span class="opacity-70">{{ $data->prefix_invoice ?? '' }}</span>
                </div>
                <div class="d-flex flex-column flex-root">
                  <span class="font-weight-bolder mb-2">INVOICE PEMBELIAN KE.</span>
                  <span class="opacity-70">{{ $data->supplier->name ?? '' }}
                    <br />{{ $data->supplier->phone }} <br />{{ $data->supplier->address }}</span>
                </div>
              </div>
            </div>
          </div>
          <!-- end: Invoice header-->
          <!-- begin: Invoice body-->
          <div class="row justify-content-center py-8 px-8 py-md-10 px-md-0">
            <div class="col-md-9">
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th class="pl-0 font-weight-bold text-muted text-uppercase">Barang</th>
                      <th class="text-right font-weight-bold text-muted text-uppercase">Unit</th>
                      <th class="text-right font-weight-bold text-muted text-uppercase">Harga</th>
                      <th class="text-right pr-0 font-weight-bold text-muted text-uppercase">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php $grandtotal = 0 @endphp
                    @foreach ($data->purchases as $item)
                    @php $grandtotal += $item->qty * $item->price @endphp
                    <tr class="font-weight-boldest">
                      <td class="pl-0 pt-7">{{ $item->sparepart->name }}</td>
                      <td class="text-right pt-7">{{ $item->qty }}</td>
                      <td class="text-right pt-7">{{ number_format($item->price,0, ',', '.') }}</td>
                      <td class="text-danger pr-0 pt-7 text-right">
                        {{ number_format($item->qty * $item->price,0, ',', '.') }}
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <!-- end: Invoice body-->
          <!-- begin: Invoice footer-->
          <div class="row justify-content-center bg-gray-100 py-8 px-8 py-md-10 px-md-0">
            <div class="col-md-9">
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th class="font-weight-bold text-muted text-uppercase"></th>
                      <th class="font-weight-bold text-muted text-uppercase"></th>
                      <th class="font-weight-bold text-muted text-uppercase"></th>
                      <th class="font-weight-bold text-muted text-uppercase text-right">Grand Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="font-weight-bolder">
                      <td></td>
                      <td></td>
                      <td></td>
                      <td class="text-danger font-size-h3 font-weight-boldest text-right">
                        {{ number_format($grandtotal,0, ',', '.') }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <!-- end: Invoice footer-->
          <!-- begin: Invoice action-->
          <!-- end: Invoice action-->
          <!-- end: Invoice-->
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('plugins/global/plugins.bundle.js') }}" type="text/javascript"></script>
  <script src="{{ asset('plugins/custom/prismjs/prismjs.bundle.js') }}" type="text/javascript"></script>
  <script src="{{ asset('js/scripts.bundle.js') }}" type="text/javascript"></script>

</body>

</html>
