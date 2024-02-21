<div class="row">
<!--  -->
<input type="hidden" name="user_id" id="User_Id" value="{{$user->id}}">
<?php $authuser = Auth::user();
  if ($user->device_id != "") {   
    $targetdevice_id = explode(',', $user->device_id); ?>
    <?php if ($authuser->role == 'admin') { ?>
    <div class="mb-4">
  <div class="row">
    <div class="col-md-4 mb-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Name:</h5>
          <p class="card-text">{{$user->name}}</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Email:</h5>
          <p class="card-text">{{$user->email}}</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
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

    <?php foreach ($targetdevice_id as $value) { 
      $alarms = \App\Models\Setalarm::where('user_id', $user->id)->where('device_id', $value)->get()->first();
      $alarmdata = [];
      if (!empty($alarms)) {
        $alarmdata = json_decode($alarms->alarmdata);
      }
      
      ?>

      <div class="card mb-2" id="DeviceId">
        <!-- <div class="card-body"> -->
          <!-- <div class="demo-inline-spacing"> -->
            <div class="row" style="padding: 15px 10px 15px 10px;">
              <div class="btn-group col-md-5 col-sm-6  graphDiv align-items-center" device-id="<?php echo $value; ?>" style="cursor: pointer; color: blue;">
                <?php
                $device_name = $value;

                $change_text_data = \App\Models\ChangeDeviceName::where('user_id', $user->id)->where('device_id', $value)->first();

                if (!empty($change_text_data)) {
                  $device_name = $change_text_data->change_name;
                }
              ?>
              <span class="device_name_text">Device - <?php echo $device_name; ?></span>
              <span class="no_data_found">
                  <h6 class="card-header m-0 me-2 pb-3 no_data_found<?php echo $value; ?>" style="display: none;"></h6>
              </span>
              </div>
              <div class="col-md-1 col-sm-6 align-items-center justify-content-left">
                <div class="spinner-border m-3" id="spinner<?php echo $value; ?>" role="status" style="color: blue; display: none;">
                    <span class="visually-hidden">Loading...</span>
                </div>
              </div>

              <div class="btn-group col-md-6 col-sm-6 ">
                <button type="button" class="btn btn-primary dropdown-toggle overflow-hidden d-sm-inline-flex d-block text-truncate justify-content-center hide-arrow" data-bs-toggle="dropdown"  aria-haspopup="true" aria-expanded="false">
                  Action
                </button>

                <ul class="dropdown-menu dropdown-menu-start dropdown-menu-lg-end">
                  <li><button class="dropdown-item btn btn-outline-secondary show_summary" data-bs-toggle="modal" type="button" onclick="show_summary('<?php echo $value; ?>')" data-bs-target="#summury<?php echo $value; ?>">Summary</button></li>

                  <li><button class="dropdown-item btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#datefilter<?php echo $value; ?>">Filter</button></li>

                  <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-target="#exportdata<?php echo $value; ?>">Export</button></li>

                  <?php if ($authuser->role == 'user') { ?>
                  <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#changeNameModal<?php echo $value; ?>">Customize</button></li>
                  <?php } ?>

                  <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-target="#setalarm<?php echo $value; ?>">Set Alarm</button></li>

                </ul>
              </div>
            </div>

            <div class="row row-bordered g-0 append_graph_blank" id="append_graph<?php echo $value; ?>">
            </div>

            <div class="row row-bordered g-0 append_graph_single" id="append_graph_single<?php echo $value; ?>">
            </div>

          <!-- </div> -->
        <!-- </div> -->
      </div>

      <!-- Modal Set Alarm-->
      <div class="modal fade" id="setalarm<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 57rem">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="changeNameModalLabel"><?php echo $value; ?> Set Alarm</h5>
                  </div>
                  <div class="modal-body">
                      <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="sensorTable<?php echo $value; ?>">
                           <thead>
                              <tr style="text-align:center; font-family:math">
                                 <th>Sensor Name</th>
                                 <th>Min Value</th>
                                 <th>Max Value</th>
                              </tr>
                           </thead>
                           <tbody>
                            <?php
                            $sensorConfig = config('global');
                            foreach ($sensorConfig as $sensorName => $sensorDetails) {
                                if ($sensorDetails['key'] != 'location') { 
                                    // Flag to check if sensor data exists
                                    $sensorDataExists = false;
                                    if (!empty($alarmdata)) {
                                      foreach ($alarmdata as $sensorData) {
                                          if ($sensorData->sensor_name == $sensorDetails['key']) {
                                              // Output input box with max and min values from sensor data
                                              $sensorDataExists = true;
                                              ?>
                                              <tr style="text-align:center;">
                                                  <td><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">{{$sensorDetails['key']}} </font></font>
                                                    <input type="hidden" name="sname" value="{{$sensorDetails['key']}}" class="sname">
                                                  </td>
                                                  <td><input type="number" value="<?php echo $sensorData->min_value; ?>" class="form-control min-value" /></td>
                                                  <td><input type="number" value="{{$sensorData->max_value}}" class="form-control max-value" /></td>
                                              </tr>
                                              <?php
                                          }
                                      }
                                    }
                                    
                                    // If no sensor data found, output default input boxes
                                    if (!$sensorDataExists) {
                                        ?>
                                        <tr style="text-align:center;">
                                            <td><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">{{$sensorDetails['key']}} </font></font>
                                              <input type="hidden" name="sname" value="{{$sensorDetails['key']}}" class="sname">
                                            </td>
                                            <td><input type="number" value="" class="form-control min-value" /></td>
                                            <td><input type="number" value="" class="form-control max-value" /></td>
                                            
                                        </tr>
                                        <?php
                                    }
                                }
                            } 
                            ?>
                              
                           </tbody>
                        </table>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                      <button class="btn btn-primary" data-bs-dismiss="modal" id="saveSettings" onclick="saveSettings('<?php echo $value; ?>')">Save Settings</button>
                  </div>
              </div>
          </div>
      </div>

      <!-- Modal Summary-->
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

  

      <!-- Modal Data Filter-->
      <div class="modal fade" id="datefilter<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 57rem">
              <div class="modal-content">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6 col-12 mb-4">
                        <div class="modal-header" style="padding: 0rem 0rem 0rem;">
                            <span for="flatpickr-date" class="form-label">From Date</span>
                        </div>
                        
                        <input class="form-control from_date" type="date" value="2021-06-18T12:30:00" id="html5-datetime-local-input" />
                      </div>
                      <div class="col-md-6 col-12 mb-4">
                        <div class="modal-header" style="padding: 0rem 0rem 0rem;">
                            <span for="flatpickr-date" class="form-label">To Date</span>
                        </div>
                        <input class="form-control to_date" type="date" value="2021-06-18T12:30:00" id="html5-datetime-local-input" />
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" device-id="<?php echo $value; ?>" class="btn btn-primary datefilter">Submit</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                  </div>
              </div>
          </div>
      </div>


 
      <!-- Modal Export Data-->
      <div class="modal fade" id="exportdata<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 57rem">
              <div class="modal-content">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6 col-12 mb-4">
                        <div class="modal-header" style="padding: 0rem 0rem 0rem;">
                            <span for="flatpickr-date" class="form-label">From Date</span>
                        </div>
                        
                        <input class="form-control from_date_export<?php echo $value; ?>" type="date" value="2021-06-18T12:30:00" id="html5-datetime-local-input" />
                      </div>
                      <div class="col-md-6 col-12 mb-4">
                        <div class="modal-header" style="padding: 0rem 0rem 0rem;">
                            <span for="flatpickr-date" class="form-label">To Date</span>
                        </div>
                        <input class="form-control to_date_export<?php echo $value; ?>" type="date" value="2021-06-18T12:30:00" id="html5-datetime-local-input" />
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-primary" onclick="datefilterexport('<?php echo $value; ?>')">Export</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                  </div>
              </div>
          </div>
      </div>


      <?php if ($authuser->role == 'user') { ?>

          <!-- Modal Change Username-->
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

      <?php } ?>
    <?php }
  }
?>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">

  var baseUrl = "{{ url('/') }}";

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

  function datefilterexport(device_id) {
    var device_id = device_id;
    var user_id = $("#User_Id").val();
    var from_date = $(".from_date_export"+device_id).val();
    var to_date = $(".to_date_export"+device_id).val();
    console.log(from_date);
    console.log(to_date);
    // console.log(user_id);
    // console.log(change_text);
    $.ajax({
        type: 'POST',
        url: '{{ route("file-export") }}',
        data: {device_id:device_id,user_id:user_id,from_date:from_date,to_date:to_date,_token:"{{ csrf_token() }}"},
        success: function (response) {
          $("#exportdata"+device_id).modal('hide');
            if (response.status === 'failure') {
                // Show alert message if no data found
                alert(response.message);
            } else {
                // If data found, proceed with downloading
                $("#exportdata" + device_id).modal('hide');
                // Create a temporary link element
                var link = document.createElement('a');
                link.href = URL.createObjectURL(new Blob([response]));
                link.setAttribute('download', 'sensor_data.csv');

                // Trigger the click event on the link to start the download
                document.body.appendChild(link);
                link.click();

                // Clean up
                document.body.removeChild(link);
            }
        }
    });

  }

  function saveSettings(device_id) {
    var settings = [];
    var user_id = $("#User_Id").val();
    var device_id = device_id;
      $('#sensorTable'+device_id+' tbody tr').each(function() {
        // var sensorName = $(this).find('td:first-child').text().trim();
        var sensorName = $(this).find('.sname').val();
        var minValue = $(this).find('.min-value').val();
        var maxValue = $(this).find('.max-value').val();
        settings.push({ sensorName: sensorName, minValue: minValue, maxValue: maxValue });
      });

      // Send settings data to the server using AJAX for updating the database
      $.ajax({
        type: 'POST',
        url: '{{ route("update-alarm") }}',
        data: { settings: settings,user_id:user_id,device_id:device_id,_token:"{{ csrf_token() }}" },
        success: function(response) {
          // Handle success response
          Swal.fire({
              // title: 'You Dial Number!',
              title: 'Alarm Set Successfully!',
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
        },
        error: function(xhr, status, error) {
          // Handle error
          Swal.fire({
              // title: 'You Dial Number!',
              title: 'Alarm Does Not Update Successfully!',
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
      });

  }


</script>
