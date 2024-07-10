<div class="row">
<!--  -->
<input type="hidden" name="user_id" id="User_Id" value="{{$user->id}}">

<link rel="stylesheet" href="{{ asset('assets/css/Control.Geocoder.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/leaflet.css') }}" />

<script src="{{asset('assets/js/jquery-3.6.0.min.js')}}"></script>
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

      $attribute = \App\Models\Attributes::where('user_id', $user->id)->where('device_id', $value)->get()->first();

      $attributedata = [];
      if (!empty($attribute)) {
        $attributedata = json_decode($attribute->attributes);
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
                  <div class="btn-group  align-items-center" device-id="<?php echo $value; ?>"  style="cursor: pointer; color: #215732;">
                      <?php
                      $device_name = $value;

                      $change_text_data = \App\Models\ChangeDeviceName::where('user_id', $user->id)->where('device_id', $value)->first();

                      if (!empty($change_text_data)) {
                          $device_name = $change_text_data->change_name;
                      }
                      ?>
                      <span class="device_name_text">Dispositivo - <?php echo $device_name; ?></span>
                      <!-- <span class="no_data_found">
                          <h6 class="card-header m-0 me-2 pb-3 no_data_found<?php echo $value; ?>" style="display: none;"></h6>
                      </span> -->

                      <!-- <span class="graphDiv opengraph<?php echo $value; ?> accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionTwo<?php echo $value; ?>" aria-expanded="false" aria-controls="accordionTwo" device-id="<?php echo $value; ?>" onclick="show_summary('<?php echo $value; ?>')"></span>  -->
                      <span class="graphDiv opengraph<?php echo $value; ?> accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordionTwo<?php echo $value; ?>" aria-expanded="false" aria-controls="accordionTwo" device-id="<?php echo $value; ?>" data-loaded<?php echo $value; ?>="false"></span> 
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

                  
                  <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#changeNameModal<?php echo $value; ?>">Personalizar</button></li>
                  

                  <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-target="#graphtimechange<?php echo $value; ?>">Graph Change</button></li>

                  <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-target="#setattribute<?php echo $value; ?>">Set Attribute</button></li>
                  <!-- <li><button class="dropdown-item btn btn-outline-secondary"  type="button" data-bs-toggle="modal" data-bs-target="#setalarm<?php echo $value; ?>">Ajustar alarma</button></li> -->

                </ul>
              </div>

              </br>

              <div id="accordionTwo<?php echo $value; ?>" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                  <span class="show_alarm_history<?php echo $value; ?>" id="show_alarm_history"></span>

                  <div class="mainweather container weather_wiedget<?php echo $value; ?> weather_hd" style="display: none;">
                    <button class="btn"  type="button" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#mapedit<?php echo $value; ?>"><i class="fas fa-edit"></i></button>
                    <div class="row">
                      <div class="col-xs-12">
                        <!-- <div class="col-xs-12 col-sm-6 col-sm-offset-3 col-lg-4 col-lg-offset-4 weather-panel"> -->
                        <div class="col-xs-12 weather-panel">
                          <div class="col-xs-6 weather_head">
                            <!-- <h2>Lucerne<br><small>May 24, 2016</small></h2>
                            <p class="h3"><i class="mi mi-fw mi-lg mi-rain-heavy"></i> Rainy</p> -->
                            <h4 class="text-white" id="timezone<?php echo $value; ?>">Lucerne</h4><br><h2><small class="text-white" id="daynow<?php echo $value; ?>">May 24, 2016</small><h2>
                            <p class="h3 text-white" id="wrapper-description"><i class="mi mi-fw mi-lg mi-rain-heavy" id="cloud<?php echo $value; ?>"></i></p>
                          </div>
                          <div class="col-xs-6 text-center">
                            <div class="h1 text-white">
                              <span id="temperature<?php echo $value; ?>">12°</span>
                              <br>
                              <small id="pressure<?php echo $value; ?>">Pressure: 8°</small>
                              <small id="humidity<?php echo $value; ?>">Humidity: 13°</small>
                              <small id="windspeed<?php echo $value; ?>">Wind Speed: 13°</small>
                              <small id="winddirection<?php echo $value; ?>">Wind Direction: 13°</small>
                            </div>
                          </div>
                          <div class="col-xs-12">
                            <ul class="list-inline row forecast">
                              <li class="col-xs-4 col-sm-2 text-center">
                                <h3 class="h5 text-white" id="day1<?php echo $value; ?>">Wed</h3>
                                <!-- <p><i class="mi mi-fw mi-2x mi-cloud-sun"></i><br>9°/18°</p> -->
                                <p id="day1ph<?php echo $value; ?>">9°/18°</p>
                                <p id="day1ww<?php echo $value; ?>">9°/18°</p>
                              </li>
                              <li class="col-xs-4 col-sm-2 text-center">
                                <h3 class="h5 text-white" id="day2<?php echo $value; ?>">Thu</h3>
                                <!-- <p><i class="mi mi-fw mi-2x mi-sun"></i><br>12°/23°</p> -->
                                <p id="day2ph<?php echo $value; ?>">9°/18°</p>
                                <p id="day2ww<?php echo $value; ?>">9°/18°</p>
                              </li>
                              <li class="col-xs-4 col-sm-2 text-center">
                                <h3 class="h5 text-white" id="day3<?php echo $value; ?>">Fri</h3>
                                <!-- <p><i class="mi mi-fw mi-2x mi-cloud-sun"></i><br>14°/24°</p> -->
                                <p id="day3ph<?php echo $value; ?>">9°/18°</p>
                                <p id="day3ww<?php echo $value; ?>">9°/18°</p>
                              </li>
                              <li class="col-xs-4 col-sm-2 text-center">
                                <h3 class="h5 text-white" id="day4<?php echo $value; ?>">Sat</h3>
                                <!-- <p><i class="mi mi-fw mi-2x mi-rain"></i><br>15°/23°</p> -->
                                <p id="day4ph<?php echo $value; ?>">9°/18°</p>
                                <p id="day4ww<?php echo $value; ?>">9°/18°</p>
                              </li>
                              <li class="col-xs-4 col-sm-2 text-center">
                                <h3 class="h5 text-white" id="day5<?php echo $value; ?>">Sun</h3>
                                <!-- <p><i class="mi mi-fw mi-2x mi-rain-heavy"></i><br>15°/22°</p> -->
                                <p id="day5ph<?php echo $value; ?>">9°/18°</p>
                                <p id="day5ww<?php echo $value; ?>">9°/18°</p>
                              </li>
                              <li class="col-xs-4 col-sm-2 text-center">
                                <h3 class="h5 text-white" id="day6<?php echo $value; ?>">Mon</h3>
                                <!-- <p><i class="mi mi-fw mi-2x mi-clouds"></i><br>12°/17°</p> -->
                                <p id="day6ph<?php echo $value; ?>">9°/18°</p>
                                <p id="day6ww<?php echo $value; ?>">9°/18°</p>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- </span> -->
                  <span class="append_graph_blank" id="append_graph<?php echo $value; ?>"></span>

                  <span class="append_graph_single" id="append_graph_single<?php echo $value; ?>"></span>

                </div>
              </div>
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


      <!-- Modal Set Attribute-->
      <div class="modal fade" id="setattribute<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document" style="max-width: 57rem">
              <div class="modal-content">
                  <form class="dynamic-form<?php echo $value; ?>" method="post">
                    <input type="hidden" id="attrdeviceid" value="<?php echo $value; ?>">
                    <div class="card-body">
                      <?php foreach ($attributedata as $key => $attributevalue) { ?>
                          <div class="row mb-3">
                            <div class="col-sm-2">
                              <input type="text" name="attrkey[]" class="form-control" id="basic-default-name" value="<?= $key; ?>">
                            </div>
                            <div class="col-sm-10">
                              <input type="text" name="attrval[]" class="form-control" id="basic-default-name" value="<?= $attributevalue; ?>">
                            </div>
                          </div>
                      <?php }  ?>
                      
                      <div id="input-container<?php echo $value; ?>">
                        <!-- Initial input fields can be placed here -->
                      </div>

                      <div class="row justify-content-end">
                        <div class="col-sm-10">
                          <button type="button" device-attr-id="<?php echo $value; ?>" class="add-input" class="btn btn-primary"><i class='bx bx-plus' ></i></button>
                          <button type="button" device-remove-id="<?php echo $value; ?>" class="remove-input" class="btn btn-primary"><i class='bx bx-minus' ></i></button>
                        </div>
                      </div>
                    
                  </div>
                  <div class="modal-footer">
                      <button type="submit" device-id="<?php echo $value; ?>" class="btn btn-primary setattributeadd">Entregar</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                  </div>
                  </form>
              </div>
          </div>
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
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
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
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                      <button class="btn btn-primary" data-bs-dismiss="modal" id="saveSettings" onclick="saveSettings('<?php echo $value; ?>')">Guardar ajustes</button>
                  </div>
              </div>
          </div>
      </div>

      <!-- Modal Set Graph Name-->
      <div class="modal fade" id="setgraphname<?php echo $value; ?>" tabindex="-1" role="dialog" aria-labelledby="changeNameModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="changeNameModalLabel">Change Graph Name</h5>
                  </div>
                  <div class="modal-body">
                      <input type="text" name="change_graph_name" id="graph_name_textbox<?php echo $value; ?>" class="form-control" placeholder="Enter new name">
                      <input type="hidden" name="original_graph_name" id="original_graph_name_textbox<?php echo $value; ?>" class="form-control" placeholder="Enter new name">
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-primary" onclick="changegraphname('<?php echo $value; ?>')">Cambiar</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
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
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
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
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                  </div>
              </div>
          </div>
      </div>

      <!-- Leaflet and Leaflet Control Search CSS -->


      <!-- Leaflet and Leaflet Control Search JavaScript -->
      <script src="{{asset('assets/js/leaflet.js')}}"></script>
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
                      //console.log('Latitude: ' + lat + ', Longitude: ' + lng);
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
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
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
                    <div class="spinner-border " id="exportcsv<?php echo $value; ?>" role="status" style="color: #215732; display: none;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                      <button type="button" class="btn btn-primary" onclick="datefilterexport('<?php echo $value; ?>')">Exportar</button>
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                  </div>
              </div>
          </div>
      </div>

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
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                  </div>
              </div>
          </div>
      </div>

      
    <?php }
  }
?>

</div>


<script type="text/javascript">

$(document).ready(function() {
  

  $('.add-input').click(function() {
    var device_id = $(this).attr('device-attr-id');
    // console.log(device_id);
    $('#input-container'+device_id).append(`
      <div id="attrdiv${device_id}" class="row mb-3">
        <div class="col-sm-2">
          <input type="text" name="attrkey[]" class="form-control" id="basic-default-name" value="">
        </div>
        <div class="col-sm-10">
          <input type="text" name="attrval[]" class="form-control" id="basic-default-name" value="">
        </div>
      </div>
    `);
  });

  // Remove the last added input fields
  // $('.remove-input').click(function() {
  //   var device_id = $(this).attr('device-remove-id');
  //   $('#input-container+device_id .row.mb-3').last().remove();
  // });

  $(document).on('click', '.remove-input', function() {
    var device_id = $(this).attr('device-remove-id');
    console.log(device_id);
    $('#attrdiv'+device_id).remove();
  });

  // Handle form submission
  $(document).on('submit', '[class^="dynamic-form"]', function(event) {
    event.preventDefault(); // Prevent the default form submission

    let keys = $(this).find("input[name='attrkey[]']").map(function() {
          return $(this).val();
    }).get();
    let values = $(this).find("input[name='attrval[]']").map(function() {
      return $(this).val();
    }).get();

    let attributes = [];
    for (let i = 0; i < keys.length; i++) {
      attributes.push({ key: keys[i], value: values[i] });
    }

    console.log(attributes);
    let user_id = $("#User_Id").val();
    let device_id = $(this).find("#attrdeviceid").val();

    $.ajax({
        type: 'POST',
        url: '{{ route("set-attribute-key-value") }}',
        data: {attributes:attributes,device_id:device_id,user_id:user_id,_token:"{{ csrf_token() }}"},
        success: function (response) {
          console.log(response);
          if (response.success == 'success') {
            $("#setattribute"+device_id).modal('hide');
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

  });

});

  const dayNameMap = {
      Sun: 'Sunday',
      Mon: 'Monday',
      Tue: 'Tuesday',
      Wed: 'Wednesday',
      Thu: 'Thursday',
      Fri: 'Friday',
      Sat: 'Saturday'
  };

  var baseUrl = "{{ url('/') }}";

  $('.maplatlong').on('click', function() {
      var device_id = $(this).attr('device-id');
      var user_id = $("#User_Id").val();
      var LAT = $(".LAT"+device_id).val();
      var LON = $(".LON"+device_id).val();
      // console.log(LAT);
      // console.log(LON);
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
            //console.log(response);
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

  function changegraphname(device_id) {
    
    var device_id = device_id;
    var user_id = $("#User_Id").val();
    var change_text = $("#graph_name_textbox"+device_id).val();
    var original_name = $("#original_graph_name_textbox"+device_id).val();
    // console.log(device_id);
    // console.log(user_id);
    // console.log(change_text);
    if (change_text =="") {
        return false;
    }
    $.ajax({
        type: 'POST',
        url: '{{ route("change-graph-name") }}',
        data: {device_id:device_id,user_id:user_id,change_text:change_text,original_name:original_name,_token:"{{ csrf_token() }}"},
        // dataType: 'json',
        success: function (response) {
          $("#setgraphname"+device_id).modal('hide');
            //console.log(response);
            //return false;
            if (response.success == 'success') {

                Swal.fire({
                    // title: 'You Dial Number!',
                    title: 'Graph Name Update Successfully!',
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
                    title: 'Graph Name Does Not Update Successfully!',
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

/*  function show_summary(device_id) {
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
            //console.log(response);
            var latitude = $('#onloadlatitude'+device_id).val();
            var longitude = $('#onloadlongitude'+device_id).val();
            
            //return false;
            if (response.success == 'success') {
              showweather(device_id,latitude,longitude);
                // location.reload(true);
              // $('.show_alarm_history'+device_id).html(response.html);

            }else{
               showweather(device_id,latitude,longitude); 
            }
            // $('.loader').fadeOut();
        }
    });

  }*/


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
            //console.log(response);
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
    $("#exportcsv"+device_id).show();
    //console.log(from_date);
    //console.log(to_date);
    // console.log(user_id);
    // console.log(change_text);
    $.ajax({
        type: 'POST',
        url: '{{ route("file-export") }}',
        data: {device_id:device_id,user_id:user_id,from_date:from_date,to_date:to_date,_token:"{{ csrf_token() }}"},
        success: function (response) {
          $("#exportcsv"+device_id).hide();
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
    //console.log(sensorName);
    //console.log(device_id);
    var user_id = $("#User_Id").val();
    // return false;
    $.ajax({
        type: 'POST',
        url: '{{ route("get-alarm-data-by-sensorname") }}',
        data: {device_id:device_id,user_id:user_id,sensorName:sensorName,_token:"{{ csrf_token() }}"},
        success: function (response) {
            //console.log(response);
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

  function get_graph_name(sensorName,device_id) {
    $("#original_graph_name_textbox"+device_id).val(sensorName);
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
    console.log('deviceid16'+device_id);
    console.log('latitude16'+latitude);
    console.log('longitude16'+longitude);
    // API call
    let queryUrl = "https://api.openweathermap.org/data/2.5/onecall?";
    let lat = "lat=" + latitude + "&";
    let lon = "lon=" + longitude + "&";

    let apiOptions = "units=metric&exclude=minutely,alerts&";
    // let windSpeedParam = "wind_speed&";
    let apiKey = "appid=dbb76c5d98d5dbafcb94441c6a10236e";
    let file = queryUrl + lat + lon + apiOptions + apiKey;
    console.log(file);
    fetch(file)
    .then((response) => response.json())
    .then((data) => {
      console.log(data);

    // Daily Data
    let dayNames = [];
    let daydates = [];
    let windspeeds = [];
    let winddirections = [];
    let pressures = [];
    let humiditys = [];
    for (let i = 0; i < 7; i++) {
        const dayTimestamp = data.daily[i].dt;
        const date = new Date(dayTimestamp * 1000);
        const options = { weekday: 'short' };
        const dayName = date.toLocaleDateString('en-US', options);
        dayNames.push(getFullDayName(dayName));
        const daydate = date.toDateString();
        daydates.push(convertDateString(daydate));
        const windspeed = data.daily[i].wind_speed;
        windspeeds.push(windspeed);
        const winddirection = data.daily[i].wind_deg;
        winddirections.push(winddirection);
        const pressure = data.daily[i].pressure;
        pressures.push(pressure);
        const humidity = data.daily[i].humidity;
        humiditys.push(humidity);
    }

    document.getElementById("daynow"+device_id).innerHTML = daydates[0];
    document.getElementById("day1"+device_id).innerHTML = dayNames[1];
    document.getElementById("day2"+device_id).innerHTML = dayNames[2];
    document.getElementById("day3"+device_id).innerHTML = dayNames[3];
    document.getElementById("day4"+device_id).innerHTML = dayNames[4];
    document.getElementById("day5"+device_id).innerHTML = dayNames[5];
    document.getElementById("day6"+device_id).innerHTML = dayNames[6];
    document.getElementById("timezone"+device_id).innerHTML = data.timezone;

    // Weather main data
    let main = data.current.weather[0].main;
    let description = data.current.weather[0].description;
    let temp = Math.round(data.current.temp);
    let pressure = data.current.pressure;
    let humidity = data.current.humidity;
    let name = data.timezone;
    let windspeed = data.current.wind_speed;
    let winddirection = getWindDirection(data.current.wind_deg);

    $("#cloud"+device_id).text(description);
    $("#temperature"+device_id).text(temp + "°C");
    $("#pressure"+device_id).text("Pressure:" + pressure +' |');
    $("#humidity"+device_id).text("Humidity:" + humidity + "%" +' |');
    $("#windspeed"+device_id).text("Wind Speed:" + parseFloat((windspeed * 3.6).toFixed(2)) + "(km/h)" +' |');
    $("#winddirection"+device_id).text("Wind Direction:" + winddirection);

    let day1ph = pressures[1]+'/'+humiditys[1]+'%';
    var winddirections1 = getWindDirection(winddirections[1]);
    let day1ww = parseFloat((windspeeds[1] * 3.6).toFixed(2))+'(km/h) /'+winddirections1;

    let day2ph = pressures[2]+'/'+humiditys[2]+'%';
    var winddirections2 = getWindDirection(winddirections[2]);
    let day2ww = parseFloat((windspeeds[2] * 3.6).toFixed(2))+'(km/h) /'+winddirections2;

    let day3ph = pressures[3]+'/'+humiditys[3]+'%';
    var winddirections3 = getWindDirection(winddirections[3]);
    let day3ww = parseFloat((windspeeds[3] * 3.6).toFixed(2))+'(km/h) /'+winddirections3;

    let day4ph = pressures[4]+'/'+humiditys[4]+'%';
    var winddirections4 = getWindDirection(winddirections[4]);
    let day4ww = parseFloat((windspeeds[4] * 3.6).toFixed(2))+'(km/h) /'+winddirections4;

    let day5ph = pressures[5]+'/'+humiditys[5]+'%';
    var winddirections5 = getWindDirection(winddirections[5]);
    let day5ww = parseFloat((windspeeds[5] * 3.6).toFixed(2))+'(km/h) /'+winddirections5;

    let day6ph = pressures[6]+'/'+humiditys[6]+'%';
    var winddirections6 = getWindDirection(winddirections[6]);
    let day6ww = parseFloat((windspeeds[6] * 3.6).toFixed(2))+'(km/h) /'+winddirections6;

    $("#day1ph"+device_id).text(day1ph);
    $("#day1ww"+device_id).text(day1ww);

    $("#day2ph"+device_id).text(day2ph);
    $("#day2ww"+device_id).text(day2ww);

    $("#day3ph"+device_id).text(day3ph);
    $("#day3ww"+device_id).text(day3ww);

    $("#day4ph"+device_id).text(day4ph);
    $("#day4ww"+device_id).text(day4ww);

    $("#day5ph"+device_id).text(day5ph);
    $("#day5ww"+device_id).text(day5ww);

    $("#day6ph"+device_id).text(day6ph);
    $("#day6ww"+device_id).text(day6ww);
    /*document.getElementById("cloud"+device_id) = description;
    document.getElementById("temperature"+device_id) = temp + "°C";
    document.getElementById("pressure"+device_id) = pressure;
    document.getElementById("humidity"+device_id) = humidity + "%";
    document.getElementById("windspeed"+device_id) = windspeed + "(m/s)";
    document.getElementById("winddirection"+device_id) = winddirection + "°";*/

    

    /*document.getElementById("wrapper-description"+device_id).innerHTML = description;
    document.getElementById("wrapper-temp"+device_id).innerHTML = temp + "°C";
    document.getElementById("wrapper-pressure"+device_id).innerHTML = pressure;
    document.getElementById("wrapper-windspeed"+device_id).innerHTML = windspeed + "(m/s)";
    document.getElementById("wrapper-winddirection"+device_id).innerHTML = winddirection + "°";
    document.getElementById("wrapper-humidity"+device_id).innerHTML = humidity + "%";
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
    document.getElementById("wrapper-hour5"+device_id).innerHTML = hour5 + "°";*/

    // Time
    /*let timeNow = new Date().getHours();
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
    document.getElementById("wrapper-icon-hour5"+device_id).src = iconFullyUrlHour5;*/

    });
  }

  // Function to convert a date string to the full day name format
  function convertDateString(dateString) {
      // Parse the date string into a Date object
      const date = new Date(dateString);
      
      // Options for toLocaleDateString to get the full day name
      const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
      
      // Generate the formatted date string
      const formattedDate = date.toLocaleDateString('en-US', options);
      
      return formattedDate;
  }

  // Function to convert abbreviated day name to full day name
  function getFullDayName(abbreviatedName) {
      return dayNameMap[abbreviatedName] || 'Invalid day name';
  }

  function getWindDirection(deg) {
      if (deg >= 348.75 || deg < 11.25) return deg+"° N";
      if (deg >= 11.25 && deg < 33.75) return deg+"° NNE";
      if (deg >= 33.75 && deg < 56.25) return deg+"° NE";
      if (deg >= 56.25 && deg < 78.75) return deg+"° ENE";
      if (deg >= 78.75 && deg < 101.25) return deg+"° E";
      if (deg >= 101.25 && deg < 123.75) return deg+"° ESE";
      if (deg >= 123.75 && deg < 146.25) return deg+"° SE";
      if (deg >= 146.25 && deg < 168.75) return deg+"° SSE";
      if (deg >= 168.75 && deg < 191.25) return deg+"° S";
      if (deg >= 191.25 && deg < 213.75) return deg+"° SSW";
      if (deg >= 213.75 && deg < 236.25) return deg+"° SW";
      if (deg >= 236.25 && deg < 258.75) return deg+"° WSW";
      if (deg >= 258.75 && deg < 281.25) return deg+"° W";
      if (deg >= 281.25 && deg < 303.75) return deg+"° WNW";
      if (deg >= 303.75 && deg < 326.25) return deg+"° NW";
      if (deg >= 326.25 && deg < 348.75) return deg+"° NNW";
      return "Invalid"; // In case the degree value is out of range
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
      // console.log("Latitude: " + latitude);
      // console.log("Longitude: " + longitude);
      // console.log("Device ID: " + deviceid);
  }

// Call the function with the deviceid parameter




</script>
