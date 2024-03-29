@extends('layout.default')

@section('content')
  <div class="card card-custom">
    <div class="card-header">
      <h3 class="card-title">
        {{ $config['page_title'] }}
      </h3>
    </div>
    <form id="formStore" method="POST" action="{{ route('backend.joborders.store') }}">
      @csrf
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <div class="card-body">
        <div class="form-group" style="display:none;">
          <div class="alert alert-custom alert-light-danger" role="alert">
            <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
            <div class="alert-text">
            </div>
          </div>
        </div>
        <div class="row">
          <input type="hidden" name="type_salary">
          <input type="hidden" name="salary_amount">
          <div class="col-md-4">
            <div class="form-group">
              <label>Tanggal Muat</label>
              <div class="input-group date">
                <input type="text" class="form-control datepicker" name="date_begin" readonly="readonly"
                       placeholder="Tanggal Muat">
                <div class="input-group-append">
                <span class="input-group-text">
                  <i class="la la-calendar-check-o"></i>
                </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="activeSelect">Expedisi<span class="text-danger">*</span></label>
              <select id="selectExpedition" name="type" class="form-control">
                <option>Pilih Jenis Expedisi</option>
                <option value="self">Sendiri</option>
                <option value="ldo">LDO (Luar)</option>
              </select>
            </div>
            <div class="form-group" id="ExpeditionLDO" style="display: none">
              <label>Pilih LDO<span class="text-danger">*</span></label>
              <select id="select2AnotherExpedition" class="form-control" style="width:100% !important"
                      name="another_expedition_id">
              </select>
            </div>
            <div class="form-group">
              <label>No. Pol<span class="text-danger">*</span></label>
              <select id="select2Transport" class="form-control" name="transport_id">
              </select>
            </div>
            <div class="form-group">
              <label>Nama Supir<span class="text-danger">*</span></label>
              <select id="select2Drivers" class="form-control" name="driver_id">
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Nama Pelanggan<span class="text-danger">*</span></label>
              <select id="select2Costumers" class="form-control" name="costumer_id">
              </select>
            </div>
            <div class="form-group">
              <label>Rute Dari<span class="text-danger">*</span></label>
              <select id="select2RoadFrom" class="form-control" name="route_from">
              </select>
            </div>
            <div class="form-group">
              <label>Rute Ke<span class="text-danger">*</span></label>
              <select id="select2RoadTo" class="form-control" name="route_to">
              </select>
            </div>
            <div class="form-group">
              <label>Muatan<span class="text-danger">*</span></label>
              <select id="select2Cargo" class="form-control" name="cargo_id">
              </select>
            </div>
            <input type="hidden" name="km">
            @hasanyrole('super-admin|admin')
            <div class="form-group" style="display: none">
              <label>Total Ongkosan Dasar LDO</label>
              <input id="totalpayloadldo" type="text" class="form-control currency" disabled/>
            </div>
            @endhasanyrole
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="activeSelect">Pilih Kapasitas<span class="text-danger">*</span></label>
              <select id="select2TypeCapacity" name="type_capacity" class="form-control">
              </select>
            </div>
            <div class="form-group">
              <label for="activeSelect">Tipe Ongkosan<span class="text-danger">*</span></label>
              <select id="selectTypeOngkosan" name="type_payload" class="form-control">
                <option>-- Pilih Ongkosan --</option>
                <option value="calculate">Kalkulasi (Uang Jalan Master * KG)</option>
                <option value="fix">FIX</option>
              </select>
            </div>
            @hasanyrole('super-admin|admin')
            <div class="form-group">
              <label>Harga Dasar</label>
              <input type="hidden" name="basic_price" class="form-control currency basicprice"/>
              <input type="text" class="form-control currency basicprice" disabled/>
            </div>
            @else
              <div class="form-group" style="display: none;">
                <label>Harga Dasar</label>
                <input type="hidden" name="basic_price" class="form-control currency basicprice"/>
                <input type="text" class="form-control currency basicprice" disabled/>
              </div>
              @endhasanyrole
              <div class="form-group" style="display: none">
                <label>Harga Dasar LDO</label>
                <input type="text" name="basic_price_ldo" class="form-control currencyldo"/>
              </div>
              <div class="form-group">
                <label>Muatan</label>
                <div class="input-group">
                  <input type="text" name="payload" class="form-control text-right ton">
                  <div class="input-group-append">
                    <span class="input-group-text">KG</span>
                  </div>
                </div>
              </div>
              @hasanyrole('super-admin|admin')
              <div class="form-group">
                <label>Total Ongkosan Dasar</label>
                <input id="totalPayload" type="text" class="form-control currency" disabled/>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Tax PPH %</label>
                    <div class="input-group">
                      <input type="text" class="form-control" id="taxPercent" disabled/>
                      <input type="hidden" class="form-control" name="tax_percent"/>
                      <div class="input-group-append"><span class="input-group-text">%</span></div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Pajak PPH</label>
                    <div class="input-group">
                      <input type="text" class="form-control currency" id="taxFee" disabled/>
                      <div class="input-group-append"><span class="input-group-text">Rp.</span></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Total Ongkosan Dasar (Setelah Pajak)</label>
                <input type="text" id="totalPayloadAfterTax" class="form-control currency" disabled/>
              </div>
              <div class="form-group">
                <label>Fee Pemberian</label>
                <input type="text" id="fee_thanks" class="form-control currency" disabled/>
                <input type="hidden" class="form-control currency" name="fee_thanks"/>
              </div>
              <div class="form-group">
                <label>Total Ongkosan Dasar (Setelah Pemberian)</label>
                <input type="text" id="totalPayloadAfterThanks" class="form-control currency" disabled/>
              </div>
              @else
                <div class="form-group" style="display: none">
                  <label>Total Ongkosan Dasar</label>
                  <input id="totalPayload" type="text" class="form-control currency" disabled/>
                </div>
                <div class="row" style="display: none">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Tax PPH %</label>
                      <div class="input-group">
                        <input type="text" class="form-control" id="taxPercent" disabled/>
                        <input type="hidden" class="form-control" name="tax_percent"/>
                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group" style="display: none">
                      <label>Pajak PPH</label>
                      <div class="input-group">
                        <input type="text" class="form-control currency" id="taxFee" disabled/>
                        <div class="input-group-append"><span class="input-group-text">Rp.</span></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group" style="display: none">
                  <label>Total Ongkosan Dasar (Setelah Pajak)</label>
                  <input type="text" id="totalPayloadAfterTax" class="form-control currency" disabled/>
                </div>
                <div class="form-group" style="display: none">
                  <label>Fee Pemberian</label>
                  <input type="text" id="fee_thanks" class="form-control currency" disabled/>
                  <input type="hidden" class="form-control" name="fee_thanks" class="currency"/>
                </div>
                <div class="form-group" style="display: none">
                  <label>Total Ongkosan Dasar (Setelah Pemberian)</label>
                  <input type="text" id="totalPayloadAfterThanks" class="form-control currency" disabled/>
                </div>
                @endhasanyrole
                <div class="form-group">
                  <label style="display: none">Uang Jalan</label>
                  <input type="text" name="road_money" class="form-control currency" style="display: none" readonly/>
                </div>
                @hasanyrole('super-admin|admin')
                <div class="form-group">
                  <label>Grand Total Kotor</label>
                  <input type="text" name="grandtotalgross" class="form-control currency" disabled/>
                </div>
                @endhasanyrole
          </div>
          @hasanyrole('super-admin|admin')
          <div class="col-md-4">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group" style="display: none">
                  <label>Pot. Spare Part</label>
                  <div class="input-group">
                    <input id="percentSparepart" name="cut_sparepart_percent" type="text" class="form-control"
                           value="{{ $sparepart->value }}" disabled>
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group" style="display: none">
                  <label>Gaji Supir</label>
                  <div class="input-group">
                    <input id="percentSalary" name="salary_percent" type="text" class="form-control"
                           value="{{ $gaji->value }}" disabled>
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group" style="display: none">
                  <label>Pot. SparePart (Est)</label>
                  <input name="cut_sparepart" type="text" class="form-control currency" disabled>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group" style="display: none">
                  <label>Gaji Supir (Est)</label>
                  <input name="salary" type="text" class="form-control currency" disabled>
                </div>
              </div>
            </div>
          </div>
          @endhasanyrole
          @hasanyrole('super-admin|admin')
          <div class="col-md-4">
            <div class="form-group" style="display: none">
              <label>Grand Total Bersih LDO</label>
              <input name="grandtotalnettoldo" type="text" class="form-control currency" disabled>
            </div>
            <div class="form-group">
              <label>Grand Total Bersih</label>
              <input name="grandtotalnetto" type="text" class="form-control currency" disabled>
            </div>
          </div>
          @endhasanyrole
          <div class="offset-md-8 col-md-4">
            <div class="form-group">
              <label>Keterangan</label>
              <textarea class="form-control" name="description" rows="5"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" onclick="window.history.back();">Cancel</button>
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>
@endsection

@section('styles')
@endsection

@section('scripts')
  <script type="text/javascript">
    $(document).ready(function () {
      function initCurrency() {
        $(".currency").inputmask('decimal', {
          groupSeparator: '.',
          digits: 0,
          rightAlign: true,
          autoUnmask: true,
          allowMinus: false,
          removeMaskOnSubmit: true
        });
      }

      initCurrency();
      initTransportDriverSelf();

      $(".currencyldo").inputmask('decimal', {
        groupSeparator: '.',
        digits: 0,
        rightAlign: true,
        autoUnmask: true,
        allowMinus: false,
        removeMaskOnSubmit: true
      });

      $(".ton").inputmask({
        alias: 'decimal',
        autoGroup: true,
        digits: 3,
        digitsOptional: false,
        allowMinus: false,
        placeholder: '0.00'
      });

      $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        todayHighlight: !0,
      });

      $('#selectExpedition').on('change', function (e) {
        if (this.value === 'self') {
          initTransportDriverSelf();
          $('input[name=road_money]').parent().find('label').css("display", "block");
          $('input[name=road_money]').css('display', 'block');
          $("#select2AnotherExpedition").parent().css("display", "none");
          $('input[name="basic_price_ldo"]').parent().css("display", "none");
          $("#percentSparepart").parent().parent().css("display", "block");
          $("#percentSparepart").parent().parent().find('label').css("display", "block");
          $("#percentSalary").parent().parent().css("display", "block");
          $("#percentSalary").parent().parent().find('label').css("display", "block");
          $('input[name="cut_sparepart"]').parent().css("display", "block");
          $('input[name="cut_sparepart"]').parent().find('label').css("display", "block");
          $('input[name="salary"]').parent().css("display", "block");
          $('input[name="salary"]').parent().find('label').css("display", "block");
          $('input[name="grandtotalgross"]').parent().css("display", "block");
          $('input[name="grandtotalgross"]').parent().find('label').css("display", "block");
          $('input[name="grandtotalnettoldo"]').parent().css("display", "none");
          $('input[name="grandtotalnettoldo"]').parent().find('label').css("display", "none");
          $('#totalpayloadldo').parent().css("display", "none");
          $('#totalpayloadldo').parent().find('label').css("display", "none");
          $('#totalpayloadldoaftertax').parent().css("display", "none");
          $('#totalpayloadldoaftertax').parent().find('label').css("display", "none");
          $('#totalpayloadldoafterthanks').parent().css("display", "none");
          $('#totalpayloadldoafterthanks').parent().find('label').css("display", "none");

          callSelf();
        } else {
          initTransportDriverLDO();
          $('input[name=road_money]').css('display', 'none');
          $('input[name=road_money]').parent().find('label').css("display", "none");
          $("#select2AnotherExpedition").parent().css("display", "block");
          $("#select2AnotherExpedition").parent().find('label').css("display", "block");
          $('input[name="basic_price_ldo"]').parent().css("display", "block");
          $('input[name="basic_price_ldo"]').parent().find('label').css("display", "block");
          $('input[name="grandtotalnettoldo"]').parent().css("display", "block");
          $('input[name="grandtotalnettoldo"]').parent().find('label').css("display", "block");
          $('input[name="grandtotalgross"]').parent().css("display", "none");
          $('input[name="grandtotalgross"]').parent().find('label').css("display", "none");
          $('#totalpayloadldo').parent().css("display", "block");
          $('#totalpayloadldo').parent().find('label').css("display", "block");
          $('#totalpayloadldoaftertax').parent().css("display", "block");
          $('#totalpayloadldoaftertax').parent().find('label').css("display", "block");
          $('#totalpayloadldoafterthanks').parent().css("display", "block");
          $('#totalpayloadldoafterthanks').parent().find('label').css("display", "block");
          $("#percentSparepart").parent().parent().css("display", "none");
          $("#percentSalary").parent().parent().css("display", "none");
          $('input[name="cut_sparepart"]').parent().css("display", "none");
          $('input[name="salary"]').parent().css("display", "none");
          callLdo();
        }
        $("#select2RoadFrom").empty().val("");
        $("#select2RoadTo").empty().trigger("change");
        $("#select2TypeCapacity").empty().trigger("change");
        $('.basicprice, #totalpayloadldo, #selectTypeOngkosan, #selectTypeOngkosan, #totalPayload, input[name=basic_price],' +
          'input[name=basic_price_ldo], input[name=basic_price_ldo], input[name=payload], input[name=road_money],' +
          'input[name=grandtotalgross], input[name=cut_sparepart], input[name=salary], input[name=grandtotalnetto]' +
          'input[name=tax_percent], #taxPercent, .currency').val('');
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
      }).on('change', function () {
        $("#select2Transport, #select2Drivers").empty().trigger('change');
      });

      function initTransportDriverSelf() {
        $("#select2Transport").select2({
          placeholder: "Search No. Pol",
          allowClear: true,
          ajax: {
            url: "{{ route('backend.transports.select2joborder') }}",
            dataType: "json",
            delay: 250,
            cache: true,
            data: function (e) {
              return {
                type: $('#select2AnotherExpedition').find(":selected").val() || null,
                status: 'aktif',
                q: e.term || '',
                page: e.page || 1
              }
            },
          }
        }).on('change', function () {
          $("#select2TypeCapacity").empty().trigger("change");
          $('.basicprice, .currency, #totalpayloadldo, #selectTypeOngkosan, #selectTypeOngkosan, #totalPayload, ' +
            'input[name=basic_price], input[name=basic_price_ldo], input[name=payload], input[name=road_money], ' +
            'input[name=grandtotalgross], input[name=cut_sparepart], input[name=salary], input[name=grandtotalnetto], ' +
            'input[name=tax_percent], #taxPercent, input[name=km]').val('');
        });

        $("#select2Drivers").select2({
          placeholder: "Search Supir",
          allowClear: true,
          ajax: {
            url: "{{ route('backend.drivers.select2joborder') }}",
            dataType: "json",
            cache: true,
            data: function (e) {
              return {
                status: 'active',
                type: $('#select2AnotherExpedition').find(":selected").val(),
                q: e.term || '',
                page: e.page || 1
              }
            },
          }
        }).on('change', function () {
          $("#select2TypeCapacity").empty().trigger("change");
          $('.basicprice, .currency, #totalpayloadldo, #selectTypeOngkosan, #selectTypeOngkosan, #totalPayload, ' +
            'input[name=basic_price], input[name=basic_price_ldo], input[name=payload], input[name=road_money], ' +
            'input[name=grandtotalgross], input[name=cut_sparepart], input[name=salary], input[name=grandtotalnetto], ' +
            'input[name=tax_percent], #taxPercent, input[name=km]').val('');
        });
      }

      function initTransportDriverLDO() {
        $("#select2Transport").select2({
          placeholder: "Search No. Pol",
          allowClear: true,
          ajax: {
            url: "{{ route('backend.transports.select2joborder') }}",
            dataType: "json",
            delay: 250,
            cache: true,
            data: function (e) {
              return {
                type: $('#select2AnotherExpedition').find(":selected").val() || null,
                status: 'aktif',
                q: e.term || '',
                page: e.page || 1
              }
            },
          },
          tags: true,
          createTag: function (params) {
            return {
              id: params.term,
              text: params.term,
              newOption: true
            }
          }
        }).on('change', function () {
          $("#select2TypeCapacity").empty().trigger("change");
          $('.basicprice, .currency, #totalpayloadldo, #selectTypeOngkosan, #selectTypeOngkosan, #totalPayload, ' +
            'input[name=basic_price], input[name=basic_price_ldo], input[name=payload], input[name=road_money], ' +
            'input[name=grandtotalgross], input[name=cut_sparepart], input[name=salary], input[name=grandtotalnetto], ' +
            'input[name=tax_percent], #taxPercent, input[name=km]').val('');
        });

        $("#select2Drivers").select2({
          placeholder: "Search Supir",
          allowClear: true,
          ajax: {
            url: "{{ route('backend.drivers.select2joborder') }}",
            dataType: "json",
            cache: true,
            data: function (e) {
              return {
                status: 'active',
                type: $('#select2AnotherExpedition').find(":selected").val(),
                q: e.term || '',
                page: e.page || 1
              }
            },
          },
          tags: true,
          createTag: function (params) {
            return {
              id: params.term,
              text: params.term,
              newOption: true
            }
          }
        }).on('change', function () {
          $("#select2TypeCapacity").empty().trigger("change");
          $('.basicprice, .currency, #totalpayloadldo, #selectTypeOngkosan, #selectTypeOngkosan, #totalPayload, ' +
            'input[name=basic_price], input[name=basic_price_ldo], input[name=payload], input[name=road_money], ' +
            'input[name=grandtotalgross], input[name=cut_sparepart], input[name=salary], input[name=grandtotalnetto], ' +
            'input[name=tax_percent], #taxPercent, input[name=km]').val('');
        });
      }

      $("#select2Costumers").select2({
        placeholder: "Search Pelanggan",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.joborders.select2costumers') }}",
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
      }).on('change', function () {
        $("#select2Cargo, #select2RoadFrom,#select2RoadTo, #select2TypeCapacity").empty().trigger("change");
        $('#totalpayloadldo').val('');
        $('#selectTypeOngkosan').val('');
        $('#totalPayload').val('');
        $('.basicprice, .currency, #taxPercent, input[name=basic_price], input[name=basic_price_ldo], input[name=payload], ' +
          'input[name=road_money], input[name=grandtotalgross], input[name=cut_sparepart], input[name=salary]' +
          'input[name=grandtotalnetto], input[name=tax_percent], input[name=km]').val('');
      });

      $("#select2RoadFrom").select2({
        placeholder: "Search Rute Dari",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.joborders.select2routefrom') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function (e) {
            return {
              costumer_id: $('#select2Costumers').find(":selected").val() || null,
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      }).on('change', function () {
        $("#select2Cargo, #select2RoadTo, #select2TypeCapacity").empty().trigger("change");
        $('#totalpayloadldo').val('');
        $('#selectTypeOngkosan').val('');
        $('#totalPayload').val('');
        $('.basicprice, .currency, #taxPercent, input[name=basic_price], input[name=basic_price_ldo], input[name=payload], ' +
          'input[name=road_money], input[name=grandtotalgross], input[name=cut_sparepart], input[name=salary]' +
          'input[name=grandtotalnetto], input[name=tax_percent], input[name=km]').val('');
      });

      $("#select2RoadTo").select2({
        placeholder: "Search Rute Ke",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.joborders.select2routeto') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function (e) {
            return {
              costumer_id: $('#select2Costumers').find(":selected").val() || null,
              route_from: $('#select2RoadFrom').find(":selected").val() | null,
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      }).on('change', function () {
        $("#select2TypeCapacity, #select2Cargo").empty().trigger("change");
        $('#totalpayloadldo').val('');
        $('#selectTypeOngkosan').val('');
        $('#totalPayload').val('');
        $('.basicprice, .currency, #taxPercent, input[name=basic_price], input[name=basic_price_ldo], input[name=payload], ' +
          'input[name=road_money], input[name=grandtotalgross], input[name=cut_sparepart], input[name=salary]' +
          'input[name=grandtotalnetto], input[name=tax_percent], input[name=km]').val('');
      });

      $("#select2Cargo").select2({
        placeholder: "Search Muatan",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.joborders.select2cargos') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function (e) {
            return {
              costumer_id: $('#select2Costumers').find(":selected").val() || null,
              route_from: $('#select2RoadFrom').find(":selected").val() || null,
              route_to: $('#select2RoadTo').find(":selected").val() || null,
              q: e.term || '',
              page: e.page || 1
            }
          },
        },
      }).on('change', function () {
        $("#select2TypeCapacity").empty().trigger("change");
        $('#totalpayloadldo').val('');
        $('#selectTypeOngkosan').val('');
        $('#totalPayload').val('');
        $('.basicprice, .currency, #taxPercent, input[name=basic_price], input[name=basic_price_ldo], input[name=payload], ' +
          'input[name=road_money], input[name=grandtotalgross], input[name=cut_sparepart], input[name=salary]' +
          'input[name=grandtotalnetto], input[name=tax_percent], input[name=km]').val('');
      });

      $("#select2TypeCapacity").select2({
        placeholder: "Search Kapasitas",
        allowClear: true,
        ajax: {
          url: "{{ route('backend.typecapacities.select2') }}",
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
      }).on('change', function () {
        $('#selectTypeOngkosan').val('');
        $('#totalPayload').val('');
        $('.basicprice, .currency, #taxPercent, input[name=basic_price], input[name=basic_price_ldo], input[name=payload], ' +
          'input[name=road_money], input[name=grandtotalgross], input[name=cut_sparepart], input[name=salary]' +
          'input[name=grandtotalnetto], input[name=tax_percent], input[name=km]').val('');
      });

      $('#selectTypeOngkosan').on('change', function () {
        $('.basicprice, #totalpayloadldo, #totalPayload, input[name=basic_price], input[name=basic_price_ldo],' +
          'input[name=payload], input[name=road_money], input[name=grandtotalgross], input[name=cut_sparepart], ' +
          'input[name=salary], input[name=grandtotalnetto], input[name=km]').val('');
        getData();
        if (this.value == 'fix') {
          $('input[name="payload"]').prop('disabled', true).val(1);
        } else {
          $('input[name="payload"]').prop('disabled', false).val(0);
        }
      });

      function callBorongan() {
        let payload = 1;
        let basicPrice = parseFloat($('.basicprice').val()) || 0;
        let roadMoney = parseFloat($('input[name=road_money]').val()) || 0;
        let fee_thanks = parseFloat($('#fee_thanks').val()) || 0;
        let tax_pph = (parseFloat($('#taxPercent').val()) || 0) / 100;
        let sumPayload = basicPrice * payload;
        let taxPPH = sumPayload * tax_pph;
        let sumPayloadAfterTax = sumPayload - taxPPH;
        let sumPayloadAfterThanks = sumPayloadAfterTax - fee_thanks;
        let totalGross = sumPayloadAfterThanks - roadMoney;
        let pecentSparePart = parseFloat('{{ $sparepart->value }}') / 100;
        let pecentSalary = parseFloat('{{ $gaji->value }}') / 100;
        let sparepart = totalGross * pecentSparePart;
        let typeSalary = $('input[name=type_salary]').val();
        let salaryAmont = parseFloat($('input[name=salary_amount]').val()) || 0;
        let salary = typeSalary === "dynamic" ? (totalGross - sparepart) * pecentSalary : salaryAmont;
        let totalNetto = totalGross - sparepart - salary;
        $('#totalPayload').val(sumPayload);
        $('#taxFee').val(taxPPH);
        $('#totalPayloadAfterTax').val(sumPayloadAfterTax);
        $('#totalPayloadAfterThanks').val(sumPayloadAfterThanks);
        $('input[name="grandtotalgross"]').val(totalGross);
        $('input[name="cut_sparepart"]').val(sparepart);
        $('input[name="salary"]').val(salary);
        $('input[name="grandtotalnetto"]').val(totalNetto);
      }

      function callSelf() {
        let basicPrice = parseFloat($('.basicprice').val()) || 0;
        let payload = parseFloat($('input[name=payload]').val()) || 0;
        let roadMoney = parseFloat($('input[name=road_money]').val()) || 0;
        let fee_thanks = parseFloat($('#fee_thanks').val()) || 0;
        let tax_pph = (parseFloat($('#taxPercent').val()) || 0) / 100;
        let sumPayload = basicPrice * payload;
        let taxPPH = sumPayload * tax_pph;
        let sumPayloadAfterTax = sumPayload - taxPPH;
        let sumPayloadAfterThanks = sumPayloadAfterTax - fee_thanks;
        let totalGross = sumPayloadAfterThanks - roadMoney;
        let pecentSparePart = parseFloat('{{ $sparepart->value }}') / 100;
        let pecentSalary = parseFloat('{{ $gaji->value }}') / 100;
        let sparepart = totalGross * pecentSparePart;
        let typeSalary = $('input[name=type_salary]').val();
        let salaryAmont = parseFloat($('input[name=salary_amount]').val()) || 0;
        let salary = typeSalary === "dynamic" ? (totalGross - sparepart) * pecentSalary : salaryAmont;
        let totalNetto = totalGross - sparepart - salary;
        $('#totalPayload').val(sumPayload);
        $('#taxFee').val(taxPPH);
        $('#totalPayloadAfterTax').val(sumPayloadAfterTax);
        $('#totalPayloadAfterThanks').val(sumPayloadAfterThanks);
        $('input[name="grandtotalgross"]').val(totalGross);
        $('input[name="cut_sparepart"]').val(sparepart);
        $('input[name="salary"]').val(salary);
        $('input[name="grandtotalnetto"]').val(totalNetto);
      }

      function callLdo() {
        let basicPrice = parseFloat($('.basicprice').val()) || 0;
        let basicPriceLDO = parseFloat($('input[name="basic_price_ldo"]').val()) || 0;
        let payload = parseFloat($('input[name=payload]').val()) || 0;
        let roadMoney = parseFloat($('input[name=road_money]').val()) || 0;
        let fee_thanks = parseFloat($('#fee_thanks').val()) || 0;
        let tax_pph = (parseFloat($('#taxPercent').val()) || 0) / 100;

        //self
        let sumPayload = basicPrice * payload;
        let taxPPH = sumPayload * tax_pph;
        let sumPayloadAfterTax = sumPayload - taxPPH;
        let sumPayloadAfterThanks = sumPayloadAfterTax - fee_thanks;

        //ldo
        let sumPayloadLDO = basicPriceLDO * payload;
        let totalGrossLDO = sumPayloadLDO - roadMoney;
        let totalNetto = sumPayloadAfterThanks - sumPayloadLDO;
        $('#totalPayload').val(sumPayload);
        $('#totalpayloadldo').val(sumPayloadLDO);
        $('#taxFee').val(taxPPH);
        $('#totalPayloadAfterTax').val(sumPayloadAfterTax);
        $('#totalPayloadAfterThanks').val(sumPayloadAfterThanks);
        $('input[name="grandtotalnettoldo"]').val(totalGrossLDO);
        $('input[name="grandtotalnetto"]').val(totalNetto);
      }

      $('input[name=payload],input[name=basic_price_ldo],input[name=road_money]').on('keyup', function () {
        let select = $('#selectExpedition').find(":selected").val();
        if (select === 'self') {
          callSelf();
        } else if ('ldo') {
          callLdo()
        }
      });

      function getData() {
        let formData = {
          costumer_id: $('#select2Costumers').find(":selected").val(),
          route_from: $('#select2RoadFrom').find(":selected").val(),
          route_to: $('#select2RoadTo').find(":selected").val(),
          cargo_id: $('#select2Cargo').find(":selected").val(),
          transport_id: $('#select2Transport').find(":selected").val(),
          type_capacity_id: $('#select2TypeCapacity').find(":selected").val(),
          type: $('#selectTypeOngkosan').find(":selected").val(),
          driver_id: $('#select2Drivers').find(":selected").val(),
        }
        $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          type: 'POST',
          url: "{{ route('backend.joborders.roadmoney') }}",
          data: formData,
          success: function (response) {
            if (response.data) {
              let data = response.data.pivot;
              let taxfee = response.taxfee;
              $('input[name=km]').val(response.taxfee.km);
              let transport = response.type.type_car;
              let type = response.data.pivot.type;
              if (transport === 'engkel') {
                $('input[name=road_money]').val(data.road_engkel);
                $('.basicprice').val(data.expense);
                $('input[name=tax_percent], #taxPercent').val(taxfee.tax_pph);
                $('input[name=fee_thanks], #fee_thanks').val(taxfee.fee_thanks);
                $('input[name=type_salary]').val(taxfee.type_salary);
                $('input[name=salary_amount]').val(taxfee.salary_amount);
                if (type === 'fix') {
                  callBorongan();
                }
              } else if (transport === 'tronton') {
                $('input[name=road_money]').val(data.road_tronton);
                $('.basicprice').val(data.expense);
                if (type === 'fix') {
                  callBorongan();
                }
              }
            } else {
              $('input[name=road_money]').val('');
            }
          }
        });
      }

      $("#formStore").submit(function (e) {
        $('.currency').inputmask('remove');
        e.preventDefault();
        let form = $(this);
        let btnSubmit = form.find("[type='submit']");
        let btnSubmitHtml = btnSubmit.html();
        let url = form.attr("action");
        let data = new FormData(this);
        $.ajax({
          beforeSend: function () {
            btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading ...").prop("disabled", "disabled");
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
          },
          error: function (response) {
            initCurrency();
            btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
            toastr.error(response.responseJSON.message, 'Failed !');
          }
        });
      });
    });
  </script>
@endsection
