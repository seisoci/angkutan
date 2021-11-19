{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
  {{-- Dashboard 1 --}}
  <!--begin::Card-->
  <div class="card card-custom mt-6">
    <div class="card-header flex-wrap py-3">
      <div class="card-title">
        <h3 class="card-label">{{ $config['page_title'] }}
        </h3>
      </div>
      <div class="card-toolbar">
      </div>
    </div>
    <div class="card-body">
      <form id="formUpdate" action="{{ route('backend.invoicecostumers.update', Request::segment(3)) }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @method('PUT')
        <div class="row align-items-center border border-dark py-10 px-4">
          <div class="col-12">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-4 col-form-label">Tanggal Invoice:</label>
                  <div class="col-md-6">
                    <input type="text" class="form-control rounded-0 w-100" name="invoice_date"
                           value="{{ $data->invoice_date }}" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-4 col-form-label">Prefix:</label>
                  <div class="col-lg-6">
                    <input type="text" class="form-control rounded-0 w-100" name="invoice_date"
                           value="{{ $data->prefix }}" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-4 col-form-label">No. Invoice Tagihan:</label>
                  <div class="col-lg-6">
                    <input class="form-control rounded-0" value="{{ $data->num_bill }}" disabled>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group row">
                  <label class="col-lg-3  col-form-label">Tgl Jatuh Tempo:</label>
                  <div class="col-lg-9">
                    <input class="form-control rounded-0" value="{{ $data->due_date }}" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Pelanggan:</label>
                  <div class="col-lg-9">
                    <input class="form-control rounded-0" value="{{ $data->costumer->name }}" disabled>
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-lg-3 col-form-label">Memo:</label>
                  <div class="col-lg-9">
                    <textarea name="memo" class="form-control rounded-0" disabled>{{ $data->memo }}</textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table id="table_invoice" class="table table-striped">
              <thead>
              <tr>
                <th>#</th>
                <th scope="col" class="text-center">No.</th>
                <th scope="col">Tanggal</th>
                <th scope="col">No. JobOrder</th>
                <th scope="col">No. Polisi</th>
                <th scope="col">No. SJ</th>
                <th scope="col">No. Shipment</th>
                <th scope="col">Pelanggan</th>
                <th scope="col">Rute Dari</th>
                <th scope="col">Rute Ke</th>
                <th scope="col">Jenis Barang</th>
                <th scope="col">Qty (Unit)</th>
                <th scope="col">Harga Dasar</th>
                <th scope="col">Pajak (%)</th>
                <th scope="col">Pajak (Rp.)</th>
                <th scope="col">Fee</th>
                <th scope="col" class="text-right">Total Tagihan (Rp.)</th>
              </tr>
              </thead>
              <tbody>
              @foreach($data->joborders as $item)
                <tr id="jo_{{ $item->id }}">
                  <td>
                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addModal"
                            data-id="{{ $item->id }}">+
                    </button>
                  </td>
                  <td class="text-center">{{ $loop->iteration }}</td>
                  <td>{{  $item->date_begin }}</td>
                  <td>{{ $item->prefix . '-' . $item->num_bill  }}</td>
                  <td>{{ $item->transport->num_pol  }}</td>
                  <td>{{ $item->no_sj  }}</td>
                  <td>{{ $item->no_shipment  }}</td>
                  <td>{{ $item->costumer->name }}</td>
                  <td>{{ $item->routefrom->name }}</td>
                  <td>{{ $item->routeto->name }}</td>
                  <td>{{ $item->cargo->name }}</td>
                  <td class="text-right currency">{{ $item->basic_price }}</td>
                  <td class="text-center">{{ $item->payload }}</td>
                  <td class="text-center">{{ $item->tax_percent ?? 0 }}</td>
                  <td class="text-right currency">{{ $item->tax_amount }}</td>
                  <td class="text-right currency">{{ $item->fee_thanks }}</td>
                  <td class="text-right currency">{{ $item->total_basic_price }}</td>
                </tr>
                @foreach($item->piutangklaim as $piutangklaim)
                  <tr>
                    <td>
                      <button type="button" class="btn btn-sm btn-danger deleteItem">-</button>
                    </td>
                    <td><input type="hidden"
                               name="job_orderid[{{ $item->id }}][{{ $piutangklaim->type == 'tambah' ? 'tambah' : 'kurang' }}][nominal]"
                               value="{{ $piutangklaim->amount }}"></td>
                    <td>
                      <span
                        class="badge {{ $piutangklaim->type == 'tambah' ? 'badge-success' : 'badge-danger' }}">{{ $piutangklaim->type == 'tambah' ? 'Penambahan' : 'Pengurangan' }}</span>
                    </td>
                    <td colspan="13">{{ $piutangklaim->description }}<input type="hidden"
                                                                            name="job_orderid[{{ $item->id }}][{{ $piutangklaim->type == 'tambah' ? 'tambah' : 'kurang' }}][keterangan]"
                                                                            value="{{ $piutangklaim->description }}">
                    </td>
                    <td class="text-right currency">{{ $piutangklaim->amount }}</td>
                  </tr>
                @endforeach
              @endforeach
              </tbody>
              <tfoot>
              <tr>
                <td colspan="14" class="font-weight-bolder text-right">Total</td>
                <td class="text-right font-weight-bolder currency">{{ $data->joborders->sum('tax_amount') }}</td>
                <td class="text-right font-weight-bolder currency">{{ $data->joborders->sum('fee_thanks') }}</td>
                <td class="text-right font-weight-bolder currency">{{ $data->joborders->sum('total_basic_price') }}</td>
              </tr>
              </tfoot>
            </table>
          </div>
          <h2 class="pt-10"><u>Pembayaran</u></h2>
          <div class="table-responsive">
            <table class="table table-borderless">
              <thead>
              <tr>
                <th scope="col" style="min-width: 150px">Tanggal Pembayaran</th>
                <th scope="col" style="min-width: 200px">Keterangan</th>
                <th scope="col" style="min-width: 150px">Master Akun</th>
                <th scope="col" style="min-width: 150px">Nominal</th>
                <th scope="col" style="min-width: 150px">Total Dibayar</th>
              </tr>
              </thead>
              <tbody>
              @foreach($data->paymentcostumers as $item)
                <tr>
                  <td><input type="text" class="form-control rounded-0 datepicker w-100" placeholder="Tanggal Invoice"
                             disabled value="{{ $item->date_payment }}"></td>
                  <td><input class="form-control rounded-0" value="{{ $item->description }}" disabled></td>
                  <td><input type="text" class="form-control rounded-0"
                             value="{{ $item->coa->code." - ".$item->coa->name }}" disabled></td>
                  <td><input type="text" class="currency rounded-0 form-control" value="{{ $item->payment }}" disabled>
                  </td>
                  <td><input type="text" class="currency rounded-0 form-control"
                             value="{{ $item->payment }}" disabled>
                  </td>
                </tr>
              @endforeach
              <tr>
                <td><input type="text" class="form-control rounded-0 datepicker w-100" name="payment[date_payment]"
                           placeholder="Tanggal Invoice" readonly></td>
                <td><input name="payment[description]" class="form-control rounded-0"></td>
                <td><select name="coa_id" class="form-control rounded-0" style="min-width: 250px">
                    @foreach($selectCoa->coa as $item)
                      <option value="{{ $item->id }}">{{ $item->code .' - '. $item->name }}</option>
                    @endforeach
                  </select></td>
                <td><input type="text" name="payment[payment]" class="currency rounded-0 form-control"></td>
                <td><input type="text" name="payment[total_payment]"
                           class="currency rounded-0 form-control totalPayment" disabled>
                </td>
              </tr>
              </tbody>
              <tfoot>
              <tr>
                <td colspan="4" class="text-right">Total Tagihan + Piutang</td>
                <td class="text-right">
                  <input type="text" class="currency rounded-0 form-control total_bill"
                         disabled>
                  <input type="hidden" class="total_bill" name="total_bill">'
                </td>
              </tr>
              <tr>
                <td colspan="4" class="text-right">Total Piutang</td>
                <td class="text-right"><input type="text" class="currency rounded-0 form-control total_piutang"
                                              value="{{ $data->total_piutang }}"
                                              disabled>
                </td>
              </tr>
              <tr>
                <td colspan="4" class="text-right">Total Klaim</td>
                <td class="text-right"><input type="text" class="currency rounded-0 form-control total_klaim"
                                              value="{{ $data->total_cut }}"
                                              disabled>
                </td>
              </tr>
              <tr>
                <td colspan="4" class="text-right">Total Pembayaran</td>
                <td class="text-right"><input type="text" class="currency rounded-0 form-control total_payment"
                                              value="{{ $data->total_payment }}"
                                              disabled>
                </td>
              </tr>
              <tr>
                <input type="hidden" name="rest_payment" class="currency rounded-0 form-control rest_payment">
                <td colspan="4" class="text-right">Sisa Pembayaran</td>
                <td class="text-right"><input type="text" class="currency rounded-0 form-control rest_payment"
                                              disabled>
                </td>
              </tr>
              <td colspan="5" class="text-right">
                <button type="submit" class="btn btn-primary">Submit</button>
              </td>
              </tfoot>
            </table>
          </div>
        </div>
      </form>
    </div>
  </div>
  {{--  Modal--}}
  <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
       aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Tambah</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close"></i>
          </button>
        </div>
        <div class="modal-body">
          <form>
            <input type="hidden" name="tb_job_order_id">
            <div class="form-group">
              <label for="selectType" class="col-form-label">Tipe:</label>
              <select class="form-control" name="type" id="selectType">
                <option value="tambah">Tambah</option>
                <option value="kurang">Kurang</option>
              </select>
            </div>
            <div class="form-group">
              <label for="message-text" class="col-form-label">Nominal:</label>
              <input class="form-control currency" name="nominal">
            </div>
            <div class="form-group">
              <label for="message-text" class="col-form-label">Keterangan:</label>
              <textarea class="form-control" name="keterangan" rows="4"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button id="addRow" type="button" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </div>
  </div>
@endsection

{{-- Styles Section --}}
@section('styles')
  <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
  <style>
    .select2-container--default .select2-selection--single {
      border-radius: 0 !important;
    }
  </style>
@endsection

{{-- Scripts Section --}}
@section('scripts')
  {{-- vendors --}}
  <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>

  {{-- page scripts --}}
  <script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
  <script type="text/javascript">
    $(document).ready(function () {
      initCurrency();
      initDate();
      initCalculate();

      function initDate() {
        $('.datepicker').datepicker({
          format: 'yyyy-mm-dd',
          todayBtn: "linked",
          clearBtn: true,
          todayHighlight: true,
        });
      }

      function initCurrency() {
        $(".currency").inputmask('decimal', {
          groupSeparator: '.',
          digits: 0,
          rightAlign: true,
          removeMaskOnSubmit: true,
          autoUnmask: true,
        });

        $('.deleteItem').on('click', function () {
          $(this).parent().parent().empty();
          initCalculate();
        });
      }

      function initCalculate() {
        let totalKlaim = 0;
        let totalPiutang = 0;
        $("input[name*='[kurang][nominal]']").each(function () {
          totalKlaim += parseInt($(this).val()) || 0;
        });
        $("input[name*='[tambah][nominal]']").each(function () {
          totalPiutang += parseInt($(this).val()) || 0;
        });

        let total_bill = parseFloat('{{ $total->sum('total_basic_price') }}') || 0;
        let totalPaymentDB = parseFloat("{{ $data->paymentcostumers_sum_payment }}") || 0;
        let total_payment = parseFloat($('input[name="payment[payment]"]').val()) || 0;
        let totalTagihan = total_bill + totalPiutang;
        let rest_payment = total_bill - total_payment + totalPiutang - totalKlaim - totalPaymentDB;
        let totalPaymentFull = total_payment + totalPaymentDB;
        console.log(totalTagihan);
        $('.total_bill').val(totalTagihan);
        $('input[name="payment[total_payment]"]').val(total_payment);
        $('.total_payment').val(totalPaymentFull);
        $('.rest_payment').val(rest_payment);
        $('.total_klaim').val(totalKlaim);
        $('.total_piutang').val(totalPiutang);
      }

      $('input[name="payment[payment]"],input[name="total_cut"],input[name="total_piutang"],#diskon').on('keyup', function () {
        initCalculate();
      });

      $('#addModal').on('show.bs.modal', function (event) {
        let id = $(event.relatedTarget).data('id');
        $(this).find('.modal-body').find('input[name="tb_job_order_id"]').val(id);

      });
      $('#addModal').on('hidden.bs.modal', function (event) {
        $(this).find('.modal-body').find('input[name="tb_job_order_id"]').val('');
        $(this).find('.modal-body').find('input[name="nominal"]').val('');
        $(this).find('.modal-body').find('textarea[name="keterangan"]').val('');
      });

      $('#addRow').on('click', function () {
        let jobOrderId = $(this).parent().parent().find('input[name="tb_job_order_id"]').val();
        let keterangan = $(this).parent().parent().find('textarea[name="keterangan"]').val();
        let nominal = $(this).parent().parent().find('input[name="nominal"]').val();
        let select = $(this).parent().parent().find('select[name="type"]').val();
        let typeVar = '';
        if (select == 'tambah' && !$.trim($('#jo_' + jobOrderId + '_tambahan').html())) {
          typeVar = 'jo_' + jobOrderId + '_tambahan';
          $("#jo_" + jobOrderId).after('<tr id="' + typeVar + '">' +
            '<td><button type="button" class="btn btn-sm btn-danger deleteItem">-</button></td>' +
            '<td><input type="hidden" name="job_orderid[' + jobOrderId + '][tambah][nominal]" value="' + nominal + '"></td>' +
            '<td><span class="badge badge-success">Penambahan</span></td>' +
            '<td colspan="13">' + keterangan + '<input type="hidden" name="job_orderid[' + jobOrderId + '][tambah][keterangan]" value="' + keterangan + '"></td>' +
            '<td class="text-right money">' + nominal + '</td>' +
            '</tr>');
        } else if (select == 'kurang' && !$.trim($('#jo_' + jobOrderId + '_pengurangan').html())) {
          typeVar = 'jo_' + jobOrderId + '_pengurangan';
          console.log(nominal);
          $("#jo_" + jobOrderId).after('<tr id="' + typeVar + '">' +
            '<td><button type="button" class="btn btn-sm btn-danger deleteItem">-</button></td>' +
            '<td><input type="hidden" name="job_orderid[' + jobOrderId + '][kurang][nominal]" value="' + nominal + '"></td>' +
            '<td><span class="badge badge-danger">Pengurangan</span></td>' +
            '<td colspan="13">' + keterangan + '<input type="hidden" name="job_orderid[' + jobOrderId + '][kurang][keterangan]" value="' + keterangan + '"></td>' +
            '<td class="text-right money">' + nominal + '</td>' +
            '</tr>'
          );
        }

        $(".money").inputmask({
          'alias': 'decimal',
          'groupSeparator': ',',
          'autoGroup': true,
          'digits': 2,
          'digitsOptional': false,
        });

        initCalculate();
        $('#addModal').modal('hide');

        $('.deleteItem').on('click', function () {
          $(this).parent().parent().empty();
          initCalculate();
        });
      });

      $("#formUpdate").submit(function (e) {
        $('.currency').inputmask('remove');
        e.preventDefault();
        let form = $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        let spinner = $('<span role="status" class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
        let url = form.attr("action");
        let data = new FormData(this);
        $.ajax({
          beforeSend: function () {
            btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading...").prop("disabled", "disabled");
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          cache: false,
          processData: false,
          contentType: false,
          type: "POST",
          url: url,
          data: data,
          success: function (response) {
            initCurrency();
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            if (response.status === "success") {
              toastr.success(response.message, 'Success !');
              setTimeout(function () {
                if (response.redirect === "" || response.redirect === "reload") {
                  location.reload();
                } else {
                  location.href = response.redirect;
                }
              }, 1000);
            } else {
              $("[role='alert']").parent().removeAttr("style");
              $(".alert-text").html('');
              $.each(response.error, function (key, value) {
                $(".alert-text").append('<span style="display: block">' + value + '</span>');
              });
              toastr.error((response.message || "Please complete your form"), 'Failed !');
            }
          }, error: function (response) {
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
            initCurrency();
          }
        });
      });

    });
  </script>
@endsection
