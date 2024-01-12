@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection


@section('content')

<div class="row">
<!--  -->
<input type="hidden" name="user_id" id="User_Id" value="{{$user->id}}">
<?php
  if ($user->device_id != "") {
    $targetdevice_id = explode(',', $user->device_id);
    foreach ($targetdevice_id as $value) { ?>
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
              <h5 class="card-header m-0 me-2 pb-3">Device - <?php echo $device_name; ?></h5>
            </div>
            <div class="col-md-8">
              <input type="button" onclick="showtextbox('<?php echo $value; ?>')" id="change_device_id" class="btn btn-warning" value="Change Name" style="margin-top: 11px;">
              <span style="margin-top: 11px; display: none;" id="change_name<?php echo $value; ?>">
                <input type="text" name="change_name" id="name_textbox<?php echo $value; ?>" value="">
                <input type="submit" class="btn btn-primary" name="submit" value="Change" onclick="changedevicename('<?php echo $value; ?>')">
                <input type="button" class="btn btn-danger" name="close" value="Close" onclick="closetextbox('<?php echo $value; ?>')">
              </span>
            </div>
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

@endsection

@section('page-script')
<script src="{{asset('assets/js/dashboards-analytics.js')}}"></script>

<script type="text/javascript">

  $(document).ready(function() {
  // Function to handle div click
  $('.graphDiv').on('click', function() {
    var device_id = $(this).attr('device-id');
    graphdata(device_id); // Replace 'your_device_id' with the actual device ID
    // Call the function every 10 seconds
    setInterval(function() {
      graphdata(device_id); // Replace 'your_device_id' with the actual device ID
    }, 10000);
  });

  });
  function graphdata(device_id) {
    //alert(device_id);
    $.ajax({
        type: 'post',
        url: '{{ route("getgraphdata") }}',
        data: {device_id:device_id,_token:"{{ csrf_token() }}"},
        dataType: 'json',
        success: function (response) {
            // console.log(response);

              // localStorage.screenname = "callcenter";
              // setCurrentScreen(localStorage.screenname);
              if(response.status == "success"){

                var sensorData = response.data.sensordata;
                var sensorconfig = response.sensorconfig;
                console.log(sensorconfig);

                $('#append_graph'+response.devide_id).html("");
                var devide_id = response.devide_id;
                // Iterate over each sensor
                $.each(sensorData, function(sensorName, sensorValues) {
                    if (sensorconfig[sensorName]['type'] != 'multi'){
                      return true;
                    }
                    console.log("Sensor Name: " + sensorName);
                    console.log("Sensor Value: " + sensorValues.color);
                    var readableSensorName = convertSensorName(sensorName);
                    var sensorxvalue = [];
                    var sensoryvalue = [];
                    // Iterate over each sensor value

                    let cardColor, headingColor, axisColor, shadeColor, borderColor, legendColor, chartColors, labelColor ;

                    cardColor = config.colors.white;
                    headingColor = config.colors.headingColor;
                    axisColor = config.colors.axisColor;
                    borderColor = config.colors.borderColor;
                    legendColor = config.colors.legendColor;
                    chartColors = config.colors.chartColors;
                    labelColor  = config.colors.labelColor;

                    var divElement = $('<div>', {
                        id: 'lineChart'+sensorName+response.devide_id,
                        class: 'px-2 col-md-6'
                    });

                    var innerDiv = $('<div>', {
                        'class': 'px-2'
                    });

                    divElement.append(innerDiv);

                    $('#append_graph'+response.devide_id).append(divElement);

                    $.each(sensorValues.data, function(index, value) {
                        console.log("X: " + value.x + ", Y: " + value.y);
                        // Do something with the value

                        if (response.data != "") {

                          sensorxvalue.push(value.x);
                          sensoryvalue.push(value.y);

                          const lineChartEl = document.querySelector('#lineChart'+sensorName+response.devide_id),
                          lineChartConfig = {
                            chart: {
                              height: 400,
                              type: 'line',
                              parentHeightOffset: 0,
                              zoom: {
                                enabled: false
                              },
                              toolbar: {
                                show: false
                              }
                            },
                            series: [
                              {
                                name: readableSensorName,
                                data: sensoryvalue
                              }
                            ],
                            title: {
                              text: readableSensorName, // Set the title text here
                              align: 'left',
                              style: {
                                fontSize: '12px',
                                color: sensorValues.color
                              }
                            },
                            dataLabels: {
                              enabled: true
                            },
                            stroke: {
                              curve: 'smooth'
                            },
                            legend: {
                              show: true,
                              position: 'top',
                              horizontalAlign: 'start',
                              labels: {
                                colors: legendColor,
                                useSeriesColors: false
                              }
                            },
                            colors: [sensorValues.color],
                            grid: {
                              borderColor: borderColor,
                              xaxis: {
                                lines: {
                                  show: true
                                }
                              }
                            },
                            tooltip: {
                              shared: false
                            },
                            xaxis: {
                              // type: 'datetime',
                              categories: sensorxvalue,
                              tickAmount: 5,
                              axisBorder: {
                                show: false
                              },
                              axisTicks: {
                                show: false
                              },
                              labels: {
                                style: {
                                  colors: labelColor,
                                  fontSize: '13px'
                                }
                              }
                            },
                            yaxis: {
                              labels: {
                                style: {
                                  colors: labelColor,
                                  fontSize: '13px'
                                }
                              }
                            }
                          };
                          if (typeof lineChartEl !== undefined && lineChartEl !== null) {
                            const lineChart = new ApexCharts(lineChartEl, lineChartConfig);
                            lineChart.render();
                          }

                        }

                    });
                });
                
                
                $('#append_graph_single'+response.devide_id).html("");
                $.each(sensorData, function(sensorName, sensorValue) {
                    if (sensorconfig[sensorName]['type'] != 'single'){
                      return true;
                    }

                     var cardHtml = `
                      <div class="card">
                        <div class="card-body">
                          <div class="card-title d-flex align-items-start justify-content-between">
                          </div>
                          <span class="fw-semibold d-block mb-1" style="color:${sensorValue.color}">${sensorName}</span>
                          <h5 class="card-title mb-2">X:${sensorValue.data.x}</h5>
                          <h5 class="card-title mb-2">Y:${sensorValue.data.y}</h5>
                        </div>
                      </div>
                    `;

                    // Append the HTML content to the container

                    $('#append_graph_single'+response.devide_id).append(cardHtml);

                    console.log("X: " + sensorValue.data.x + ", Y: " + sensorValue.data.y);

                });

            }

        }
    });
  }

  function convertSensorName(sensorName) {
      // Split the sensor name by camel case and join with space
      return sensorName.replace(/([a-z])([A-Z])/g, '$1 $2').toUpperCase();
  }

  function showtextbox(device_id) {
    $("#change_name"+device_id).show();
  }

  function closetextbox(device_id) {
    $("#change_name"+device_id).hide();
  }

  function changedevicename(device_id) {
    var device_id = device_id;
    var user_id = $("#User_Id").val();
    var change_text = $("#name_textbox"+device_id).val();
    // console.log(device_id);
    // console.log(user_id);
    // console.log(change_text);

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
@endsection


