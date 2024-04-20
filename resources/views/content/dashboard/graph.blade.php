<div class="row">
<!--  -->
<input type="hidden" name="user_id" id="User_Id" value="{{$user->id}}">

 <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php $authuser = Auth::user();

  if ($user->device_id != "") {   
    $targetdevice_id = explode(',', $user->device_id); ?>
    <?php if ($authuser->role == 'admin') { ?>
    <div class="mb-4">
      <div class="row">
        <div class="col-md-6 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Nombre:</h5>
              <p class="card-text">{{$user->name}}</p>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Correo electrónico:</h5>
              <p class="card-text">{{$user->email}}</p>
            </div>
          </div>
        </div>
        <!-- <div class="col-md-4 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Country:</h5>
              <p class="card-text">{{$user->country}}</p>
            </div>
          </div>
        </div> -->
      </div>
    </div>


    <?php } ?>

    <?php foreach ($targetdevice_id as $value) { 
      $userlatlong = \App\Models\SetLatLong::where('user_id', $user->id)->where('device_id', $value)->get()->first();
      $alarms = \App\Models\Setalarm::where('user_id', $user->id)->where('device_id', $value)->get()->first();
      $alarmdata = [];
      if (!empty($alarms)) {
        $alarmdata = json_decode($alarms->alarmdata);
      }
      
      ?>
      <?php
        if (isset($userlatlong) && $userlatlong->latitude != '' && $userlatlong->longitude != '') {
          $setlatitude = $userlatlong->latitude;
          $setlongitude = $userlatlong->longitude;
        }
       ?>
       <input type="hidden" name="setlatitude" id="setlatitude<?php echo $value; ?>" value="{{ isset($setlatitude) ? $setlatitude : '' }}">
       <input type="hidden" name="setlongitude" id="setlongitude<?php echo $value; ?>" value="{{ isset($setlongitude) ? $setlongitude : '' }}">
       <input type="hidden" name="onloadlatitude" id="onloadlatitude<?php echo $value; ?>" value="">
       <input type="hidden" name="onloadlongitude" id="onloadlongitude<?php echo $value; ?>" value="">

      <div class="card mb-2" id="DeviceId">
        <!-- <div class="card-body"> -->
          <!-- <div class="demo-inline-spacing"> -->
            <div class="row" style="padding: 15px 10px 15px 10px;">
              <div class="col-md-5 col-sm-6">
                  <div class="btn-group graphDiv align-items-center" device-id="<?php echo $value; ?>" onclick="show_summary('<?php echo $value; ?>')" style="cursor: pointer; color: #215732;">
                      <?php
                      $device_name = $value;

                      $change_text_data = \App\Models\ChangeDeviceName::where('user_id', $user->id)->where('device_id', $value)->first();

                      if (!empty($change_text_data)) {
                          $device_name = $change_text_data->change_name;
                      }
                      ?>
                      <span class="device_name_text">Dispositivo - <?php echo $device_name; ?></span>
                      <span class="no_data_found">
                          <h6 class="card-header m-0 me-2 pb-3 no_data_found<?php echo $value; ?>" style="display: none;"></h6>
                      </span>
                  </div>

              </div>
              <div class="col-md-1 col-sm-6">
                  <div class="spinner-border " id="spinner<?php echo $value; ?>" role="status" style="color: #215732; display: none;">
                      <span class="visually-hidden">Loading...</span>
                  </div>
              </div>

              <div class="btn-group col-md-6 col-sm-6 " style="justify-content: end;">
                
                <i class="fas fa-cog" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="justify-content: end;"></i>

                <ul class="dropdown-menu dropdown-menu-start dropdown-menu-lg-end">
                  <li><button class="dropdown-item btn btn-outline-secondary show_summary" data-bs-toggle="modal" type="button" onclick="show_alarmhistory('<?php echo $value; ?>')" data-bs-target="#summury<?php echo $value; ?>">Alarm History</button></li>

                  <li><button class="dropdown-item btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#datefilter<?php echo $value; ?>">Filtrar</button></li>

                  <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-target="#exportdata<?php echo $value; ?>">Exportar</button></li>

                  <?php if ($authuser->role == 'user') { ?>
                  <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#changeNameModal<?php echo $value; ?>">Personalizar</button></li>
                  <?php } ?>

                  <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-target="#graphtimechange<?php echo $value; ?>">Graph Change</button></li>

                  <!-- <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-target="#setalarm<?php echo $value; ?>">Ajustar alarma</button></li> -->

                </ul>
              </div>

              </br>

              <span class="weather_wiedget<?php echo $value; ?> weather_hd" style="display: none;">
                <section class="">
                  <div class="container py-5">
                  
                  <button class="btn"  type="button" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#mapedit<?php echo $value; ?>"><i class="fas fa-edit"></i></button>
                    <div id="wrapper-bg" class="row d-flex justify-content-center align-items-center h-100" style="background-color: #eeefed">
                      <!-- <span id="wrapper-bg" class="card text-white bg-image shadow-4-strong"
                          style="background-image: url('img/clouds.gif')"> -->
                      <div class="col-sm-3">
                        <div class="card-header p-4 border-0">
                          <div class="text-center mb-3">
                            <p class="h2 mb-1" id="wrapper-name<?php echo $value; ?>"></p>
                            <p class="mb-1" id="wrapper-description<?php echo $value; ?>"></p>
                            <p class="display-1 mb-1" id="wrapper-temp<?php echo $value; ?>"></p>
                            <span class="">Pressure: <span id="wrapper-pressure<?php echo $value; ?>"></span></span>
                            <span class="mx-2">|</span>
                            <span class="">Humidity: <span id="wrapper-humidity<?php echo $value; ?>"></span></span>
                          </div>
                        </div>
                      </div>
                      <div class="col-sm-5">
                          <div class="card-body p-4 border-top border-bottom mb-2">
                            <div class="row text-center">
                              <div class="col-2">
                                <strong class="d-block mb-2">Now</strong>
                                <img id="wrapper-icon-hour-now<?php echo $value; ?>" src="" class="" alt="" />
                                <strong class="d-block" id="wrapper-hour-now<?php echo $value; ?>"></strong>
                              </div>

                              <div class="col-2">
                                <strong class="d-block mb-2" id="wrapper-time1<?php echo $value; ?>"></strong>
                                <img id="wrapper-icon-hour1<?php echo $value; ?>" src="" class="" alt="" />
                                <strong class="d-block" id="wrapper-hour1<?php echo $value; ?>"></strong>
                              </div>

                              <div class="col-2">
                                <strong class="d-block mb-2" id="wrapper-time2<?php echo $value; ?>"></strong>
                                <img id="wrapper-icon-hour2<?php echo $value; ?>" src="" class="" alt="" />
                                <strong class="d-block" id="wrapper-hour2<?php echo $value; ?>"></strong>
                              </div>

                              <div class="col-2">
                                <strong class="d-block mb-2" id="wrapper-time3<?php echo $value; ?>"></strong>
                                <img id="wrapper-icon-hour3<?php echo $value; ?>" src="" class="" alt="" />
                                <strong class="d-block" id="wrapper-hour3<?php echo $value; ?>"></strong>
                              </div>

                              <div class="col-2">
                                <strong class="d-block mb-2" id="wrapper-time4<?php echo $value; ?>"></strong>
                                <img id="wrapper-icon-hour4<?php echo $value; ?>" src="" class="" alt="" />
                                <strong class="d-block" id="wrapper-hour4<?php echo $value; ?>"></strong>
                              </div>

                              <div class="col-2">
                                <strong class="d-block mb-2" id="wrapper-time5<?php echo $value; ?>"></strong>
                                <img id="wrapper-icon-hour5<?php echo $value; ?>" src="" class="" alt="" />
                                <strong class="d-block" id="wrapper-hour5<?php echo $value; ?>"></strong>
                              </div>
                            </div>
                          </div>
                      </div>
                      <div class="col-sm-4">
                        <div class="card-body px-5">
                            <div class="row align-items-center">
                              <div class="col-lg-6">
                                <strong>Today</strong>
                              </div>

                              <div class="col-lg-2 text-center">
                                <img id="wrapper-icon-today<?php echo $value; ?>" src="" class="w-100" alt="" />
                              </div>

                              <div class="col-lg-4 text-end">
                                <span id="wrapper-forecast-temp-today<?php echo $value; ?>"></span>
                              </div>
                            </div>

                            <div class="row align-items-center">
                              <div class="col-lg-6">
                                <strong>Tomorrow</strong>
                              </div>

                              <div class="col-lg-2 text-center">
                                <img id="wrapper-icon-tomorrow<?php echo $value; ?>" src="" class="w-100" alt="" />
                              </div>

                              <div class="col-lg-4 text-end">
                                <span id="wrapper-forecast-temp-tomorrow<?php echo $value; ?>">28</span>
                              </div>
                            </div>

                            <div class="row align-items-center">
                              <div class="col-lg-6">
                                <strong>Day after tomorrow</strong>
                              </div>

                              <div class="col-lg-2 text-center">
                                <img id="wrapper-icon-dAT<?php echo $value; ?>" src="" class="w-100" alt="" />
                              </div>

                              <div class="col-lg-4 text-end">
                                <span id="wrapper-forecast-temp-dAT<?php echo $value; ?>">28</span>
                              </div>
                            </div>
                          </div>
                      </div>
                    <!-- </span> -->

                    </div>

                  </div>
                </section>
              </span>

              <span class="show_alarm_history<?php echo $value; ?>" id="show_alarm_history"></span>
              <span class="append_graph_blank" id="append_graph<?php echo $value; ?>"></span>
              <span class="append_graph_single" id="append_graph_single<?php echo $value; ?>"></span>

            </div>

            <!-- <div class="row row-bordered g-0 show_alarm_history<?php echo $value; ?>" >
            </div>
            <br>
            <div class="row row-bordered g-0 append_graph_blank" id="append_graph<?php echo $value; ?>">
            </div>

            <div class="row row-bordered g-0 append_graph_single" id="append_graph_single<?php echo $value; ?>">
            </div> -->

          <!-- </div> -->
        <!-- </div> -->
      </div>

      <!-- Modal Graph Time Change-->
      <div class="modal fade" id="graphtimechange<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 57rem">
              <div class="modal-content">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6 col-12 mb-4">
                        <div class="modal-header" style="padding: 0rem 0rem 0rem;">
                            <span for="flatpickr-date" class="form-label">Time</span>
                        </div>
                        
                        <select id="changetime" value="" name="changetime" class="select2 form-select">
                          <option value="">Select</option>
                          <option value="15">15 Min</option>
                          <option value="30">30 Min</option>
                          <option value="60">60 Min</option>
                        </select>
                      </div>
                      <div class="col-md-6 col-12 mb-4">
                        <div class="modal-header" style="padding: 0rem 0rem 0rem;">
                            <span for="flatpickr-date" class="form-label">Matrix</span>
                        </div>
                        <select id="matrix" value="" name="matrix" class="select2 form-select">
                          <option value="">Select</option>
                          <option value="AVG">Avarage</option>
                          <option value="MIN">Minimun</option>
                          <option value="MAX">Maximum</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" device-id="<?php echo $value; ?>" class="btn btn-primary changegraphtime">Entregar</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerca</button>
                  </div>
              </div>
          </div>
      </div>

      <!-- Modal Set Alarm-->
      <div class="modal fade" id="setalarm<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 57rem">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="changeNameModalLabel"><?php echo $value; ?> Ajustar alarma</h5>
                  </div>
                  <div class="modal-body">
                      <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="sensorTable<?php echo $value; ?>">
                           <thead>
                              <tr style="text-align:center; font-family:math">
                                 <th>Nombre del sensor</th>
                                 <th>Valor mínimo</th>
                                 <th>Valor máximo</th>
                              </tr>
                           </thead>
                           <tbody>
                            <tr style="text-align:center;" class="minmaxadd">
                                <td><font style="vertical-align: inherit;"><span class="Sensor_Name"></span> </font>
                                  <input type="hidden" name="sname" value="" class="sname">
                                </td>
                                <td class="min-td"><input type="number" value="" class="form-control min-value" /></td>
                                <td class="max-td"><input type="number" value="" class="form-control max-value" /></td>
                            </tr>
                           </tbody>
                        </table>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerca</button>
                      <button class="btn btn-primary" data-bs-dismiss="modal" id="saveSettings" onclick="saveSettings('<?php echo $value; ?>')">Guardar ajustes</button>
                  </div>
              </div>
          </div>
      </div>

      <!-- Modal Summary-->
      <div class="modal fade" id="summury<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 57rem">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="changeNameModalLabel"><?php echo $value; ?> Alarm History</h5>
                  </div>
                  <div class="modal-body">
                      <div class="table-responsive text-nowrap show_summary<?php echo $value; ?>">
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerca</button>
                  </div>
              </div>
          </div>
      </div>
      
      <!-- Modal Map-->
      <div class="modal fade" id="mapedit<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 57rem">
              <div class="modal-content">
                  <div class="card-body">
                    <div id="map<?php echo $value; ?>" style="height: 400px;"></div>
                  </div>
                  <div class="modal-footer">
                    <input type="text" name="LAT" class="LAT<?php echo $value; ?>" readonly>
                    <input type="text" name="LON" class="LON<?php echo $value; ?>" readonly>
                      <button type="button" device-id="<?php echo $value; ?>" class="btn btn-primary maplatlong">Entregar</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerca</button>
                  </div>
              </div>
          </div>
      </div>

      <!-- Leaflet and Leaflet Control Search CSS -->


      <!-- Leaflet and Leaflet Control Search JavaScript -->
      <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
      <!-- JavaScript -->
      <script>
          $(document).ready(function () {
              $('#mapedit<?php echo $value; ?>').on('shown.bs.modal', function () {
                  // Initialize Leaflet map
                  var latitude = $("#onloadlatitude<?php echo $value; ?>").val();
                  var longitude = $("#onloadlongitude<?php echo $value; ?>").val();
                  var map = L.map('map<?php echo $value; ?>').setView([latitude, longitude], 13); // Set default view coordinates

                  // Add a tile layer (e.g., OpenStreetMap)
                  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                  }).addTo(map);

                  // Add Leaflet Control Search plugin
                  map.on('click', function(e) {
                      var lat = e.latlng.lat;
                      var lng = e.latlng.lng;
                      $(".LAT<?php echo $value; ?>").val(lat);
                      $(".LON<?php echo $value; ?>").val(lng);
                      console.log('Latitude: ' + lat + ', Longitude: ' + lng);
                  });
              });
          });
      </script>

      <!-- Modal Data Filter-->
      <div class="modal fade" id="datefilter<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 57rem">
              <div class="modal-content">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6 col-12 mb-4">
                        <div class="modal-header" style="padding: 0rem 0rem 0rem;">
                            <span for="flatpickr-date" class="form-label">Partir de la fecha</span>
                        </div>
                        
                        <input class="form-control from_date" type="date" value="2021-06-18T12:30:00" id="html5-datetime-local-input" />
                      </div>
                      <div class="col-md-6 col-12 mb-4">
                        <div class="modal-header" style="padding: 0rem 0rem 0rem;">
                            <span for="flatpickr-date" class="form-label">Hasta la fecha</span>
                        </div>
                        <input class="form-control to_date" type="date" value="2021-06-18T12:30:00" id="html5-datetime-local-input" />
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" device-id="<?php echo $value; ?>" class="btn btn-primary datefilter">Entregar</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerca</button>
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
                            <span for="flatpickr-date" class="form-label">Partir de la fecha</span>
                        </div>
                        
                        <input class="form-control from_date_export<?php echo $value; ?>" type="date" value="2021-06-18T12:30:00" id="html5-datetime-local-input" />
                      </div>
                      <div class="col-md-6 col-12 mb-4">
                        <div class="modal-header" style="padding: 0rem 0rem 0rem;">
                            <span for="flatpickr-date" class="form-label">Hasta la fecha</span>
                        </div>
                        <input class="form-control to_date_export<?php echo $value; ?>" type="date" value="2021-06-18T12:30:00" id="html5-datetime-local-input" />
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-primary" onclick="datefilterexport('<?php echo $value; ?>')">Exportar</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerca</button>
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
                          <h5 class="modal-title" id="changeNameModalLabel">Personalizar nombre</h5>
                          <!-- <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                          </button> -->
                      </div>
                      <div class="modal-body">
                          <input type="text" name="change_name" id="name_textbox<?php echo $value; ?>" class="form-control" placeholder="Enter new name">
                      </div>
                      <div class="modal-footer">
                          <button type="button" class="btn btn-primary" onclick="changedevicename('<?php echo $value; ?>')">Cambiar</button>
                          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerca</button>
                      </div>
                  </div>
              </div>
          </div>

      <?php } ?>
    <?php }
  }
?>

</div>


<script type="text/javascript">

  var baseUrl = "{{ url('/') }}";

  $('.maplatlong').on('click', function() {
      var device_id = $(this).attr('device-id');
      var user_id = $("#User_Id").val();
      var LAT = $(".LAT"+device_id).val();
      var LON = $(".LON"+device_id).val();
      console.log(LAT);
      console.log(LON);
      save_latlong(device_id,user_id,LAT,LON);
      // Call the function
      // graphdata(device_id, from_date, to_date);
      
  });

  function save_latlong(device_id,user_id,LAT,LON) {
    $.ajax({
        type: 'post',
        url: '{{ route("savelatlong") }}',
        data: {device_id:device_id,user_id:user_id,LAT:LAT,LON:LON,_token:csrfToken},
        dataType: 'json',
        success: function (response) {
          if (response.success == 'success') {
            $("#mapedit"+device_id).modal('hide');
            Swal.fire({
                // title: 'You Dial Number!',
                title: response.msg,
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
                title: response.msg,
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
        }
    });
  }

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
    getUserLocation(device_id);
    $("#show_alarm_history").html('');
    $(".weather_hd").hide();
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
            var latitude = $('#onloadlatitude'+device_id).val();
            var longitude = $('#onloadlongitude'+device_id).val();
            showweather(device_id,latitude,longitude);
            //return false;
            if (response.success == 'success') {

                // location.reload(true);
                $('.show_alarm_history'+device_id).html(response.html);

            }else{
                
            }
            // $('.loader').fadeOut();
        }
    });

  }

  function show_alarmhistory(device_id) {
    getUserLocation(device_id);
    
    var device_id = device_id;
    var user_id = $("#User_Id").val();
    // console.log(user_id);
    $.ajax({
        type: 'POST',
        url: '{{ route("get-alarm-history") }}',
        data: {device_id:device_id,user_id:user_id,_token:"{{ csrf_token() }}"},
        success: function (response) {
            console.log(response);
            //return false;
            if (response.success == 'success') {
                // location.reload(true);
                $('.show_summary'+device_id).html(response.html);

            }else{
                $("#alarmhistry"+device_id).modal('hide');
                $('.show_summary'+device_id).html("");
                
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

  function get_sensor_alarm(sensorName,device_id) {
    //$('.minmaxadd').empty();
    $(".sname").val(sensorName);
    $(".Sensor_Name").text(sensorName);
    console.log(sensorName);
    console.log(device_id);
    var user_id = $("#User_Id").val();
    // return false;
    $.ajax({
        type: 'POST',
        url: '{{ route("get-alarm-data-by-sensorname") }}',
        data: {device_id:device_id,user_id:user_id,sensorName:sensorName,_token:"{{ csrf_token() }}"},
        success: function (response) {
            console.log(response);
            //return false;
            if (response.success == 'success') {

                // location.reload(true);
                $('.minmaxadd').html(response.html);

            }else{
              $('.min-td').html(response.mintd);
              $('.max-td').html(response.maxtd);
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

  function showweather(device_id,latitude,longitude){
    $(".weather_wiedget"+device_id).show();
    console.log('deviceid'+device_id);
    console.log('latitude'+latitude);
    console.log('longitude'+longitude);
    // API call
    let queryUrl = "https://api.openweathermap.org/data/2.5/onecall?";
    let lat = "lat=" + latitude + "&";
    let lon = "lon=" + longitude + "&";

    let apiOptions = "units=metric&exclude=minutely,alerts&";
    let apiKey = "appid=dbb76c5d98d5dbafcb94441c6a10236e";
    let file = queryUrl + lat + lon + apiOptions + apiKey;
    console.log(file);
    fetch(file)
    .then((response) => response.json())
    .then((data) => {
    // Weather main data
    let main = data.current.weather[0].main;
    let description = data.current.weather[0].description;
    let temp = Math.round(data.current.temp);
    let pressure = data.current.pressure;
    let humidity = data.current.humidity;
    let name = data.timezone;

    document.getElementById("wrapper-description"+device_id).innerHTML = description;
    document.getElementById("wrapper-temp"+device_id).innerHTML = temp + "°C";
    document.getElementById("wrapper-pressure"+device_id).innerHTML = pressure;
    document.getElementById("wrapper-humidity"+device_id).innerHTML = humidity + "°C";
    document.getElementById("wrapper-name"+device_id).innerHTML = name;

    // Weather hourly data
    let hourNow = data.hourly[0].temp;
    let hour1 = data.hourly[1].temp;
    let hour2 = data.hourly[2].temp;
    let hour3 = data.hourly[3].temp;
    let hour4 = data.hourly[4].temp;
    let hour5 = data.hourly[5].temp;

    document.getElementById("wrapper-hour-now"+device_id).innerHTML = hourNow + "°";
    document.getElementById("wrapper-hour1"+device_id).innerHTML = hour1 + "°";
    document.getElementById("wrapper-hour2"+device_id).innerHTML = hour2 + "°";
    document.getElementById("wrapper-hour3"+device_id).innerHTML = hour3 + "°";
    document.getElementById("wrapper-hour4"+device_id).innerHTML = hour4 + "°";
    document.getElementById("wrapper-hour5"+device_id).innerHTML = hour5 + "°";

    // Time
    let timeNow = new Date().getHours();
    console.log(timeNow);
    let time1 = timeNow + 1;
    let time2 = time1 + 1;
    let time3 = time2 + 1;
    let time4 = time3 + 1;
    let time5 = time4 + 1;

    document.getElementById("wrapper-time1"+device_id).innerHTML = time1;
    document.getElementById("wrapper-time2"+device_id).innerHTML = time2;
    document.getElementById("wrapper-time3"+device_id).innerHTML = time3;
    document.getElementById("wrapper-time4"+device_id).innerHTML = time4;
    document.getElementById("wrapper-time5"+device_id).innerHTML = time5;

    // Weather daily data
    let tomorrowTemp = Math.round(data.daily[0].temp.day);
    let dATTemp = Math.round(data.daily[1].temp.day);
    let tomorrowMain = data.daily[0].weather[0].main;
    let dATTempMain = data.daily[1].weather[0].main;

    document.getElementById("wrapper-forecast-temp-today"+device_id).innerHTML =
    temp + "°";
    document.getElementById("wrapper-forecast-temp-tomorrow"+device_id).innerHTML =
    tomorrowTemp + "°";
    document.getElementById("wrapper-forecast-temp-dAT"+device_id).innerHTML =
    dATTemp + "°";

    // Icons
    let iconBaseUrl = "http://openweathermap.org/img/wn/";
    let iconFormat = ".webp";

    // Today
    let iconCodeToday = data.current.weather[0].icon;
    let iconFullyUrlToday = iconBaseUrl + iconCodeToday + iconFormat;
    document.getElementById("wrapper-icon-today"+device_id).src = iconFullyUrlToday;

    // Tomorrow
    let iconCodeTomorrow = data.daily[0].weather[0].icon;
    let iconFullyUrlTomorrow = iconBaseUrl + iconCodeTomorrow + iconFormat;
    document.getElementById(
    "wrapper-icon-tomorrow"+device_id
    ).src = iconFullyUrlTomorrow;

    // Day after tomorrow
    let iconCodeDAT = data.daily[1].weather[0].icon;
    let iconFullyUrlDAT = iconBaseUrl + iconCodeDAT + iconFormat;
    document.getElementById("wrapper-icon-dAT"+device_id).src = iconFullyUrlDAT;

    // Icons hourly

    // Hour now
    let iconHourNow = data.hourly[0].weather[0].icon;
    let iconFullyUrlHourNow = iconBaseUrl + iconHourNow + iconFormat;
    document.getElementById(
    "wrapper-icon-hour-now"+device_id
    ).src = iconFullyUrlHourNow;

    // Hour1
    let iconHour1 = data.hourly[1].weather[0].icon;
    let iconFullyUrlHour1 = iconBaseUrl + iconHour1 + iconFormat;
    document.getElementById("wrapper-icon-hour1"+device_id).src = iconFullyUrlHour1;

    // Hour2
    let iconHour2 = data.hourly[2].weather[0].icon;
    let iconFullyUrlHour2 = iconBaseUrl + iconHour2 + iconFormat;
    document.getElementById("wrapper-icon-hour2"+device_id).src = iconFullyUrlHour1;

    // Hour3
    let iconHour3 = data.hourly[3].weather[0].icon;
    let iconFullyUrlHour3 = iconBaseUrl + iconHour3 + iconFormat;
    document.getElementById("wrapper-icon-hour3"+device_id).src = iconFullyUrlHour3;

    // Hour4
    let iconHour4 = data.hourly[4].weather[0].icon;
    let iconFullyUrlHour4 = iconBaseUrl + iconHour4 + iconFormat;
    document.getElementById("wrapper-icon-hour4"+device_id).src = iconFullyUrlHour4;

    // Hour5
    let iconHour5 = data.hourly[5].weather[0].icon;
    let iconFullyUrlHour5 = iconBaseUrl + iconHour5 + iconFormat;
    document.getElementById("wrapper-icon-hour5"+device_id).src = iconFullyUrlHour5;

    });
  }

  function getUserLocation(deviceid) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            showPosition(position, deviceid);
        });
    } else {
        console.log("Geolocation is not supported by this browser.");
    }
}

function showPosition(position, deviceid) {
    var latitude = position.coords.latitude;
    var longitude = position.coords.longitude;

    
    var setlatitude = $('#setlatitude'+deviceid).val();
    var setlongitude = $('#setlongitude'+deviceid).val();

    // console.log("setlatitude: " + setlatitude);
    // console.log("setlongitude: " + setlongitude);

    if (setlatitude === '' && setlongitude === '') {
      $('#onloadlatitude'+deviceid).val(latitude);
      $('#onloadlongitude'+deviceid).val(longitude);
    }else{
      $('#onloadlatitude'+deviceid).val(setlatitude);
      $('#onloadlongitude'+deviceid).val(setlongitude);
    }

    // You can send the latitude, longitude, and deviceid to your Laravel backend via AJAX
    console.log("Latitude: " + latitude);
    console.log("Longitude: " + longitude);
    console.log("Device ID: " + deviceid);
}

// Call the function with the deviceid parameter




</script>
