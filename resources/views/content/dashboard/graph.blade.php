<div class="row">
<!--  -->
<input type="hidden" name="user_id" id="User_Id" value="{{$user->id}}">
<?php $authuser = Auth::user();
  if ($user->device_id != "") {
    $targetdevice_id = explode(',', $user->device_id); ?>
    <?php if ($authuser->role == 'admin') { ?>
    <div class="col-12 col-lg-12">
      <div id="DeviceId">
        <div class="row">
          <center><strong><h4>{{$user->name}}</h4></strong></center>
        </div>
      </div>
    </div>
    <?php } ?>
    <?php foreach ($targetdevice_id as $value) { ?>
      <div class="col-12 col-lg-12 order-2 order-md-3 order-lg-2 mb-4">

        <div class="card" id="DeviceId">
          <div class="row">
            <div class="col-md-4 graphDiv" device-id ="<?php echo $value; ?>" style="cursor: pointer; color: blue;">
              <?php
                $device_name = $value;

                $change_text_data = \App\Models\ChangeDeviceName::where('user_id', $user->id)->where('device_id', $value)->first();

                if (!empty($change_text_data)) {
                  $device_name = $change_text_data->change_name;
                }
              ?>
              <h5 class="card-header m-0 me-2 pb-3 device_name_text">Device - <?php echo $device_name; ?></h5>
            </div>
            <?php if ($authuser->role == 'user') { ?>
              <div class="col-md-8 text-end ">
                <button type="button" class="btn btn-outline-secondary m-3" data-bs-toggle="modal" data-bs-target="#changeNameModal<?php echo $value; ?>" >
                    Customize Name
                </button>

                <!-- Modal -->
                <div class="modal fade" id="changeNameModal<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="changeNameModalLabel">Customize Name</h5>
                                <!-- <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button> -->
                            </div>
                            <div class="modal-body">
                                <input type="text" name="change_name" id="name_textbox<?php echo $value; ?>" class="form-control" placeholder="Enter new name">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" onclick="changedevicename('<?php echo $value; ?>')">Change</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

          </div>

          <div class="row row-bordered g-0" id="append_graph<?php echo $value; ?>">
            <!-- <div class="col-md-6">
               <div id="lineChart<?php echo $value; ?>" class="px-2"></div>
            </div> -->
          </div>

          <div class="row row-bordered g-0 append_graph_single" id="append_graph_single<?php echo $value; ?>">
          </div>
        </div>
      </div>
    <?php }
  }
?>

</div>

<script type="text/javascript">

  function changedevicename(device_id) {
    $("#changeNameModal"+device_id).modal('hide');
    var device_id = device_id;
    var user_id = $("#User_Id").val();
    var change_text = $("#name_textbox"+device_id).val();
    // console.log(device_id);
    // console.log(user_id);
    // console.log(change_text);
    if (change_text =="") {
        return false;
    }
    $.ajax({
        type: 'POST',
        url: '{{ route("change-device-name") }}',
        data: {device_id:device_id,user_id:user_id,change_text:change_text,_token:"{{ csrf_token() }}"},
        // dataType: 'json',
        // beforeSend: function() {
        //     $('.loader').show();
        // },
        success: function (response) {
            console.log(response);
            //return false;
            if (response.success == 'success') {

                // location.reload(true);
                Swal.fire({
                    // title: 'You Dial Number!',
                    title: 'Device Name Update Successfully!',
                    icon: 'success',
                    allowOutsideClick: false,
                    confirmButtonText: 'Ok',
                    customClass: {
                        confirmButton: 'btn btn-danger ml-1'
                    },
                    buttonsStyling: true
                }).then(function(result) {
                  if (result.isConfirmed) {
                      location.reload(true);
                  } else {
                      location.reload(true);
                  }
                });

            }else{
                Swal.fire({
                    // title: 'You Dial Number!',
                    title: 'Device Name Does Not Update Successfully!',
                    icon: 'failure',
                    allowOutsideClick: false,
                    confirmButtonText: 'Ok',
                    customClass: {
                        confirmButton: 'btn btn-danger ml-1'
                    },
                    buttonsStyling: true
                }).then(function(result) {
                  if (result.isConfirmed) {
                      location.reload(true);
                  } else {
                      location.reload(true);
                  }
                });
            }
            // $('.loader').fadeOut();
        }
    });
  }

</script>
