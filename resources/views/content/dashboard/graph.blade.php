<div class="row">
<!--  -->
<input type="hidden" name="user_id" id="User_Id" value="{{$user->id}}">
<?php $authuser = Auth::user();
  if ($user->device_id != "") {
    $targetdevice_id = explode(',', $user->device_id); ?>
    <?php if ($authuser->role == 'admin') { ?>
    <div class="col-12 col-lg-12 order-2 order-md-3 order-lg-2 mb-4">
      <div class="row">
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Name:</h5>
              <p class="card-text">{{$user->name}}</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Email:</h5>
              <p class="card-text">{{$user->email}}</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Country:</h5>
              <p class="card-text">{{$user->country}}</p>
            </div>
          </div>
        </div>
      </div>
    </div>



    <?php } ?>

    <div class="col-12 mb-4">
      <div class="card">
        <!-- <h5 class="card-header">Flatpickr</h5> -->
        <div class="card-body">
          <div class="row">
            <!-- Date Picker-->
            <div class="col-md-6 col-12 mb-4">
              <label for="flatpickr-date" class="form-label">From Date</label>
              <input class="form-control from_date" type="date" value="2021-06-18T12:30:00" id="html5-datetime-local-input" />
            </div>
            <!-- /Date Picker -->

            <!-- Time Picker-->
            <div class="col-md-6 col-12 mb-4">
              <label for="flatpickr-date" class="form-label">To Date</label>
              <input class="form-control to_date" type="date" value="2021-06-18T12:30:00" id="html5-datetime-local-input" />
            </div>
            <!-- /Time Picker -->
            <!-- <div class="col-md-4 col-12 mb-4">
              <label for="flatpickr-date" class="form-label"></label>
              <button type="submit" id="datefilter" class="btn btn-primary m-4">Submit</button>
            </div> -->
          </div>
        </div>
      </div>
    </div>

    <?php foreach ($targetdevice_id as $value) { ?>
      <div class="col-12 col-lg-12 order-2 order-md-3 order-lg-2 mb-4">

        <div class="card" id="DeviceId">
          <div class="row">
            <div class="col-md-3 graphDiv" device-id ="<?php echo $value; ?>" style="cursor: pointer; color: blue;">
              <?php
                $device_name = $value;

                $change_text_data = \App\Models\ChangeDeviceName::where('user_id', $user->id)->where('device_id', $value)->first();

                if (!empty($change_text_data)) {
                  $device_name = $change_text_data->change_name;
                }
              ?>
              <h5 class="card-header m-0 me-2 pb-3 device_name_text">Device - <?php echo $device_name; ?></h5>
              <span class="no_data_found">
              <h6 class="card-header m-0 me-2 pb-3 no_data_found<?php echo $value; ?>" style="display: none;"></h6></span>
            </div>
            <div class="col-md-1">
              <div class="spinner-border m-3" id="spinner<?php echo $value; ?>" role="status" style="color: blue; display: none;">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <div class="col-md-4 text-end ">
                <button type="button" class="btn btn-outline-secondary m-3 show_summary" data-bs-toggle="modal" onclick="show_summary('<?php echo $value; ?>')" data-bs-target="#summury<?php echo $value; ?>">Show Summary</button>

                <!-- Modal -->
                <div class="modal fade" id="summury<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document" style="max-width: 57rem">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="changeNameModalLabel"><?php echo $value; ?> Summary</h5>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive text-nowrap show_summary<?php echo $value; ?>">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($authuser->role == 'user') { ?>
              <div class="col-md-4 text-end ">
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

          <div class="row row-bordered g-0 append_graph_blank" id="append_graph<?php echo $value; ?>">
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

  function show_summary(device_id) {
    $("#changeNameModal"+device_id).modal('hide');
    var device_id = device_id;
    var user_id = $("#User_Id").val();
    console.log(user_id);
    // console.log(user_id);
    // console.log(change_text);
    $.ajax({
        type: 'POST',
        url: '{{ route("get-show-summary") }}',
        data: {device_id:device_id,user_id:user_id,_token:"{{ csrf_token() }}"},
        success: function (response) {
            console.log(response);
            //return false;
            if (response.success == 'success') {

                // location.reload(true);
                $('.show_summary'+device_id).html(response.html);

            }else{
                // Swal.fire({
                //     // title: 'You Dial Number!',
                //     title: 'Device Name Does Not Update Successfully!',
                //     icon: 'failure',
                //     allowOutsideClick: false,
                //     confirmButtonText: 'Ok',
                //     customClass: {
                //         confirmButton: 'btn btn-danger ml-1'
                //     },
                //     buttonsStyling: true
                // }).then(function(result) {
                //   if (result.isConfirmed) {
                //       location.reload(true);
                //   } else {
                //       location.reload(true);
                //   }
                // });
            }
            // $('.loader').fadeOut();
        }
    });

  }

</script>
