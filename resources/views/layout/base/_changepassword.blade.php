{{-- Modal --}}
<div class="modal fade" id="modalChangePassword" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Change Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <i aria-hidden="true" class="ki ki-close"></i>
        </button>
      </div>
      <form id="formChangePassword" action="{{ route('backend.users.changepassword') }}">
        @csrf
        <div class="modal-body">
          <div class="form-group" style="display:none;">
            <div class="alert alert-custom alert-light-danger" role="changepassword">
              <div class="alert-icon"><i class="flaticon-danger text-danger"></i></div>
              <div class="alert-changePassword">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Old Password</label>
            <input type="password" name="old_password" class="form-control form-control-solid"
              placeholder="Input Old Password" />
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" class="currency form-control form-control-solid"
              placeholder="Input New Password" />
          </div>
          <div class="form-group">
            <label>Retype New Password</label>
            <input type="password" name="password_confirmation" class="currency form-control form-control-solid"
              placeholder="Input Retype Password" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- Javascript--}}

<script>
  $(document).ready(function(){
    $('#modalChangePassword').on('show.bs.modal', function (event) {
    });

    $('#modalChangePassword').on('hidden.bs.modal', function (event) {
      $(this).find('.modal-body').find('input[name="old_password"]').val('');
      $(this).find('.modal-body').find('input[name="password"]').val('');
      $(this).find('.modal-body').find('input[name="password_confirmation"]').val('');
    });

    $("#formChangePassword").submit(function(e) {
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
            $('#modalChangePassword').modal('hide');
            $("[role='changepassword']").parent().css("display", "none");
          } else {
            $("[role='changepassword']").parent().removeAttr("style");
            $(".alert-changePassword").html('');
            $.each(response.error, function(key, value) {
              $(".alert-changePassword").append('<span style="display: block">'+value+'</span>');
            });
            toastr.error("Please complete your form", 'Failed !');
          }
        },
        error: function(response) {
          btnSubmit.removeClass("disabled").html(btnSubmitHtml).removeAttr("disabled");
          toastr.error(response.responseJSON.message, 'Failed !');
          $('#modalChangePassword').modal('hide');
        }
      });
    });
  });
</script>
