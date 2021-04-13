{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
<div class="card card-custom">
  <div class="card-header">
    <h3 class="card-title">
      {{ $config['page_title'] }}
    </h3>
  </div>
  <!--begin::Form-->
  <form id="formStore" action="{{ route('backend.drivers.store') }}">
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
        <div class="col-md-4">
          <div class="form-group">
            <label for="activeSelect">Expedisi<span class="text-danger">*</span></label>
            <select id="selectExpedition" class="form-control" id="activeSelect">
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
            <select id="select2RoadTo" class="form-control" name="rotue_to">
            </select>
          </div>
          <div class="form-group">
            <label>Muatan<span class="text-danger">*</span></label>
            <select id="select2Cargo" class="form-control" name="cargo_id">
            </select>
          </div>
          <div class="form-group" style="display: none">
            <label>Total Ongkosan Dasar LDO</label>
            <input id="totalpayloadldo" type="text" class="form-control currency" disabled />
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="activeSelect">Pilih Kapasitas<span class="text-danger">*</span></label>
            <select id="select2TypeCapacity" class="form-control">
            </select>
          </div>
          <div class="form-group">
            <label for="activeSelect">Tipe Ongkosan<span class="text-danger">*</span></label>
            <select id="selectTypeOngkosan" class="form-control">
              <option>-- Pilih Ongkosan --</option>
              <option value="calculate">Kalkulasi (Uang Jalan Master * KG)</option>
              <option value="fix">FIX</option>
            </select>
          </div>
          <div class="form-group">
            <label>Harga Dasar</label>
            <input type="text" name="basic_price" class="form-control currency" disabled />
          </div>
          <div class="form-group" style="display: none">
            <label>Harga Dasar LDO</label>
            <input type="text" name="basic_price_ldo" class="form-control currency" style="width:100% !important" />
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Muatan</label>
                <div class="input-group">
                  <input type="number" name="payload" class="form-control">
                  <div class="input-group-append">
                    <span class="input-group-text">KG</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Convert to</label>
                <div class="input-group">
                  <input id="convertToTon" type="number" class="form-control" disabled>
                  <div class="input-group-append">
                    <span class="input-group-text">Ton</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Total Ongkosan Dasar</label>
            <input id="totalPayload" type="text" class="form-control currency" disabled />
          </div>
          <div class="form-group">
            <label>Uang Jalan</label>
            <input type="text" name="road_money" class="form-control currency" disabled />
          </div>
          <div class="form-group">
            <label>Grand Total Kotor</label>
            <input type="text" name="grandtotalgross" class="form-control currency" disabled />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group" style="display: none">
                <label>Potongan Spare Part</label>
                <div class="input-group">
                  <input id="percentSparepart" type="text" class="form-control" value="{{ $sparepart->value }}"
                    disabled>
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
                  <input id="percentSalary" type="text" class="form-control" value="{{ $gaji->value }}" disabled>
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
                <label>Potongan SparePart</label>
                <input name="cutsparepart" type="text" class="form-control currency" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group" style="display: none">
                <label>Gaji Supir</label>
                <input name="salary" type="text" class="form-control currency" disabled>
              </div>
            </div>
          </div>
        </div>
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
      </div>
      <div class="card-footer d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" onclick="window.history.back();">Cancel</button>
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>
    </div>
  </form>
  <!--end::Form-->
</div>
@endsection

{{-- Styles Section --}}
@section('styles')
@endsection

{{-- Scripts Section --}}
@section('scripts')
{{-- vendors --}}

{{-- page scripts --}}
<script type="text/javascript">
  $(document).ready(function(){
    $(".currency").inputmask('decimal', {
      groupSeparator: '.',
      digits:0,
      rightAlign: true,
      autoUnmask: true,
      removeMaskOnSubmit: true
    });

    $('#selectExpedition').on('change', function (e) {
      if(this.value == 'self'){
        $("#select2AnotherExpedition").parent().css("display", "none");
        $('input[name="basic_price_ldo"]').parent().css("display", "none");
        $("#percentSparepart").parent().parent().css("display", "block");
        $("#percentSparepart").parent().parent().find('label').css("display", "block");
        $("#percentSalary").parent().parent().css("display", "block");
        $("#percentSalary").parent().parent().find('label').css("display", "block");
        $('input[name="cutsparepart"]').parent().css("display", "block");
        $('input[name="cutsparepart"]').parent().find('label').css("display", "block");
        $('input[name="salary"]').parent().css("display", "block");
        $('input[name="salary"]').parent().find('label').css("display", "block");
        $('input[name="grandtotalgross"]').parent().css("display", "block");
        $('input[name="grandtotalgross"]').parent().find('label').css("display", "block");
        $('input[name="grandtotalnettoldo"]').parent().css("display", "none");
        $('input[name="grandtotalnettoldo"]').parent().find('label').css("display", "none");
        $('#totalpayloadldo').parent().css("display", "none");
        $('#totalpayloadldo').parent().find('label').css("display", "none");

      }else{
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
        $("#percentSparepart").parent().parent().css("display", "none");
        $("#percentSalary").parent().parent().css("display", "none");
        $('input[name="cutsparepart"]').parent().css("display", "none");
        $('input[name="salary"]').parent().css("display", "none");
      }
    });

    $("#select2AnotherExpedition").select2({
      placeholder: "Search No. Pol",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.anotherexpedition.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            var query = {
              q: e.term || '',
              page: e.page || 1
            }
            return query
          },
      },
    });

    $("#select2Transport").select2({
      placeholder: "Search No. Pol",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.transports.select2tonase') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            var query = {
              type: $('#select2AnotherExpedition').find(":selected").val() || null,
              q: e.term || '',
              page: e.page || 1
            }
            return query
          },
      },
    });

    $("#select2Drivers").select2({
      placeholder: "Search Supir",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.drivers.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            var query = {
              type: $('#select2AnotherExpedition').find(":selected").val(),
              q: e.term || '',
              page: e.page || 1
            }
            return query
          },
      },
    });

    $("#select2Costumers").select2({
      placeholder: "Search Pelanggan",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.roadmonies.select2costumers') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            var query = {
              q: e.term || '',
              page: e.page || 1
            }
            return query
          },
      },
    });

    $("#select2RoadFrom").select2({
      placeholder: "Search Rute Dari",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.roadmonies.select2routefrom') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            var query = {
              costumer_id: $('#select2Costumers').find(":selected").val(),
              q: e.term || '',
              page: e.page || 1
            }
            return query
          },
      },
    });

    $("#select2RoadTo").select2({
      placeholder: "Search Rute Ke",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.roadmonies.select2routeto') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            var query = {
              costumer_id : $('#select2Costumers').find(":selected").val(),
              route_from  : $('#select2RoadFrom').find(":selected").val(),
              q: e.term || '',
              page: e.page || 1
            }
            return query
          },
      },
    });

    $("#select2Cargo").select2({
      placeholder: "Search Muatan",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.roadmonies.select2cargos') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            var query = {
              costumer_id : $('#select2Costumers').find(":selected").val(),
              route_from  : $('#select2RoadFrom').find(":selected").val(),
              route_to    : $('#select2RoadTo').find(":selected").val(),
              q: e.term || '',
              page: e.page || 1
            }
            return query
          },
      },
    });

    $("#select2TypeCapacity").select2({
      placeholder: "Search Kapasitas",
      allowClear: true,
      ajax: {
          url: "{{ route('backend.typecapacities.select2') }}",
          dataType: "json",
          delay: 250,
          cache: true,
          data: function(e) {
            var query = {
              q: e.term || '',
              page: e.page || 1
            }
            return query
          },
      },
    });

    $('#selectTypeOngkosan').on('change', function() {
      getData();
      if(this.value == 'fix'){
        $('input[name="payload"]').prop('disabled', true).val(1);
      }else{
        $('input[name="payload"]').prop('disabled', false).val('');
      }
    });

    function callBorongan(){
      let payload = 1;
      let basicPrice = parseInt($('input[name="basic_price"]').val());
      let roadMoney = parseInt($('input[name="road_money"]').val());
      let sumPayload = basicPrice * payload;
      let convertTo = (payload / 1000);
      let totalGross = sumPayload - roadMoney;
      let pecentSparePart = parseFloat('{{ $sparepart->value }}') / 100;
      let pecentSalary = parseFloat('{{ $gaji->value }}') / 100;
      let sparepart = totalGross * pecentSparePart;
      let salary = (totalGross - sparepart) * pecentSalary;
      let totalNetto = totalGross - sparepart - salary;
      $('#convertToTon').val(convertTo);
      $('#totalPayload').val(sumPayload);
      $('input[name="grandtotalgross"]').val(totalGross);
      $('input[name="cutsparepart"]').val(sparepart);
      $('input[name="salary"]').val(salary);
      $('input[name="grandtotalnetto"]').val(totalNetto);
    }

    $('input[name=payload],input[name=basic_price_ldo]').on('keyup', function(){
      var select = $('#selectExpedition').find(":selected").val();
      if(select == 'self'){
        let basicPrice    = parseInt($('input[name="basic_price"]').val());
        let payload       = parseInt($('input[name="payload"]').val());
        let roadMoney     = parseInt($('input[name="road_money"]').val());
        let sumPayload    = basicPrice * payload;
        let convertTo     = (payload / 1000);
        let totalGross    = sumPayload - roadMoney;
        let pecentSparePart = parseFloat('{{ $sparepart->value }}') / 100;
        let pecentSalary  = parseFloat('{{ $gaji->value }}') / 100;
        let sparepart     = totalGross * pecentSparePart;
        let salary        = (totalGross - sparepart) * pecentSalary;
        let totalNetto    = totalGross - sparepart - salary;
        $('#convertToTon').val(convertTo);
        $('#totalPayload').val(sumPayload);
        $('input[name="grandtotalgross"]').val(totalGross);
        $('input[name="cutsparepart"]').val(sparepart);
        $('input[name="salary"]').val(salary);
        $('input[name="grandtotalnetto"]').val(totalNetto);
      }else{
        let basicPrice    = parseInt($('input[name="basic_price"]').val());
        let basicPriceLDO = parseInt($('input[name="basic_price_ldo"]').val());
        let payload       = parseInt($('input[name="payload"]').val());
        let roadMoney     = parseInt($('input[name="road_money"]').val());
        let sumPayload    = basicPrice * payload;
        let sumPayloadLDO = basicPriceLDO * payload;
        let convertTo     = (payload / 1000);
        let totalGrossLDO = sumPayloadLDO - roadMoney;
        let totalNetto    = sumPayload - sumPayloadLDO;
        $('#convertToTon').val(convertTo);
        $('#totalPayload').val(sumPayload);
        $('#totalpayloadldo').val(sumPayloadLDO);
        $('input[name="grandtotalnettoldo"]').val(totalGrossLDO);
        $('input[name="grandtotalnetto"]').val(totalNetto);
      }
    });

    function getData() {
      var formData = {
        costumer_id : $('#select2Costumers').find(":selected").val(),
        route_from  : $('#select2RoadFrom').find(":selected").val(),
        route_to    : $('#select2RoadTo').find(":selected").val(),
        cargo_id    : $('#select2Cargo').find(":selected").val(),
        transport_id: $('#select2Transport').find(":selected").val(),
        type_capacity_id : $('#select2TypeCapacity').find(":selected").val(),
        type        : $('#selectTypeOngkosan').find(":selected").val(),
      }
      $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          type:'POST',
          url: "{{ route('backend.roadmonies.roadmoney') }}",
          data: formData,
          success:function(response) {
            console.log(response.data.pivot);
            if(response.data){
              let data = response.data.pivot;
              let transport = response.type.type_car;
              if(transport == 'engkel'){
                $('input[name=road_money]').val(data.road_engkel);
                $('input[name=basic_price]').val(data.expense);
              }else if(transport == 'tronton'){
                $('input[name=road_money]').val(data.road_tronton);
                $('input[name=basic_price]').val(data.expense);
              }

              callBorongan();
            }else{
              $('input[name=road_money]').val('');
            }
          }
      });
    }


    $("#formStore").submit(function(e) {
      e.preventDefault();
      var form = $(this);
      var btnSubmit = form.find("[type='submit']");
      var btnSubmitHtml = btnSubmit.html();
      var url = form.attr("action");
      var data = new FormData(this);
      $.ajax({
        beforeSend: function() {
          btnSubmit.addClass("disabled").html("<i class='fa fa-spinner fa-pulse fa-fw'></i> Loading ...").prop("disabled","disabled");
        },
        cache: false,
        processData: false,
        contentType: false,
        type: "POST",
        url: url,
        data: data,
        success: function(response) {
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          if (response.status == "success") {
            toastr.success(response.message, 'Success !');
            setTimeout(function() {
              if(response.redirect == "" || response.redirect == "reload"){
								location.reload();
							} else {
								location.href = response.redirect;
							}
            }, 1000);
          } else {
            $("[role='alert']").parent().removeAttr("style");
            $(".alert-text").html('');
            $.each(response.error, function(key, value) {
              $(".alert-text").append('<span style="display: block">'+value+'</span>');
            });
            toastr.error("Please complete your form", 'Failed !');
          }
        },
        error: function(response) {
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          toastr.error(response.responseJSON.message, 'Failed !');
        }
      });
    });

    $(".image").change(function() {
      let thumb = $(this).parent().find('img');
      if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          thumb.attr('src', e.target.result);
        }
        reader.readAsDataURL(this.files[0]);
      }
    });
  });
</script>
@endsection
