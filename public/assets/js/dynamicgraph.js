var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
var getGraphDataRoute = window.get_graph_data_route || '';

// $(document).ready(function() {
  // Function to handle div click
  $('.graphDiv').on('click', function() {
    var device_id = $(this).attr('device-id');
    console.log(device_id);
    graphdata(device_id); // Replace 'your_device_id' with the actual device ID
    // Call the function every 10 seconds
    setInterval(function() {
      graphdata(device_id); // Replace 'your_device_id' with the actual device ID
    }, 60000);
  });

// });
  function graphdata(device_id) {
    $.ajax({
        type: 'post',
        url: getGraphDataRoute,
        data: {device_id:device_id,_token:csrfToken},
        dataType: 'json',
        success: function (response) {
            // console.log(response);

              // localStorage.screenname = "callcenter";
              // setCurrentScreen(localStorage.screenname);
              if(response.status == "success"){

                var sensorData = response.data.sensordata;
                var sensorconfig = response.sensorconfig;


                $('#append_graph'+response.devide_id).html("");
                var devide_id = response.devide_id;
                // Iterate over each sensor
                $.each(sensorData, function(sensorName, sensorValues) {
                    if (sensorconfig[sensorName]['type'] != 'multi'){
                      return true;
                    }
                    /*console.log("Sensor Name: " + sensorName);
                    console.log("Sensor Value: " + sensorValues.color);*/
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
                        class: 'px-2 col-md-12',
                        style: 'margin: 20px 0px;'
                    });

                    var divElement = $('<div>', {
                        id: 'lineChart'+sensorName+response.devide_id,
                        class: 'px-2 col-md-12'
                    });
                    // Create a container div for centering
                    var container = $('<div>', {
                      style: 'text-align: center; margin-top: 20px;'  // Adjust margin as needed
                    });

                    // Chart Title
                    var chartTitle = $('<h3>', {
                      text: readableSensorName,
                      style: 'color: black; font-size: 24px; margin-bottom: 10px;'  // Adjust font-size and margin as needed
                    });

                    // Last Y Value
                    var lastYValue = $('<h3>', {
                      text: Math.round(sensorValues.data[sensorValues.data.length - 1].y * 100)/100 + ' ' + sensorconfig[sensorName]['unit'],
                      style: 'color: #4c4e4f; font-size: 34px;'  // Adjust font-size as needed
                    });

                    // Append elements to the container
                    container.append(chartTitle);
                    container.append(lastYValue);

                    // Append the container to the 'divElement'
                    divElement.append(container);


                    $('#append_graph'+response.devide_id).append(divElement);

                    $.each(sensorValues.data, function(index, value) {
                        sensorxvalue.push(value.x);
                        sensoryvalue.push(value.y);
                    });

                    /*$.each(sensorValues.data, function(index, value) {*/
                        // console.log("X: " + value.x + ", Y: " + value.y);
                        // Do something with the value

                        if (response.data != "") {

                          console.log(sensorxvalue);
                          console.log(sensoryvalue);

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
                              enabled: false
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

                    /*});*/
                });


                $('#append_graph_single'+response.devide_id).html("");
                $.each(sensorData, function(sensorName, sensorValue) {
                    if (sensorconfig[sensorName]['type'] != 'single'){
                      return true;
                    }

                     var cardHtml = `<hr>
                      <div class="card">
                        <div class="card-body" style="text-align: center">
                          <div class="card-title d-flex align-items-start justify-content-between">
                          </div>
                          <h3 class="fw-semibold d-block mb-1" style="font-size: 24px;">${sensorName}</h3>
                          <h3 class="card-title mb-2" style="font-size: 34px;">${sensorValue.data.y}</h5></h3>
                          <h3 class="card-title mb-2" style="font-size: 12px;">${sensorValue.data.x}</h5></h3>
                          <h3 class="card-title mb-2" style="font-size: 24px;">${sensorValue.data.address}</h5></h3>
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
