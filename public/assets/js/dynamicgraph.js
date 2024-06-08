var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
var getGraphDataRoute = window.get_graph_data_route || '';

// $(document).ready(function() {
  // Function to handle div click
var intervalID = null; // Initialize the interval ID variable

$('.graphDiv').on('click', function() {
    var device_id = $(this).attr('device-id');
    // var $this = $(this).attr('data-loaded'+device_id)
    // if ($this == 'false') {

      var User_Id = $('#User_Id').val();
      $('.append_graph_blank').html("");
      $('.append_graph_single').html("");
      $("#show_alarm_history").html('');
      $('#spinner'+device_id).show();
      var from_date = $(".from_date").val();
      var to_date = $(".to_date").val();
      console.log(device_id);
      
      // Call the function
      graphdata(device_id, User_Id, from_date, to_date);
      // $(this).attr('data-loaded'+device_id, 'true');
      // Clear previous interval, if any
      clearInterval(intervalID);
      
      // Start a new interval
      intervalID = setInterval(function() {
          graphdata(device_id, User_Id, from_date, to_date);
      }, 50000);

    // }else {
    //     console.log('AJAX call skipped for device ID:');
    // }
});


$('.datefilter').on('click', function() {
    var device_id = $(this).attr('device-id');
    var User_Id = $('#User_Id').val();
    $('.append_graph_blank').html("");
    $('.append_graph_single').html("");
    $('#spinner'+device_id).show();
    $('#datefilter'+device_id).modal('hide');
    var from_date = $(".from_date").val();
    var to_date = $(".to_date").val();
    console.log(device_id);
    
    // Call the function
    graphdata(device_id, User_Id, from_date, to_date);
    
    // Clear previous interval, if any
    clearInterval(intervalID);
    
    // Start a new interval
    intervalID = setInterval(function() {
        graphdata(device_id, User_Id, from_date, to_date);
    }, 50000);
});

$('.changegraphtime').on('click', function() {
    var device_id = $(this).attr('device-id');
    var User_Id = $('#User_Id').val();
    $('.append_graph_blank').html("");
    $('.append_graph_single').html("");
    $('#spinner'+device_id).show();
    $('#datefilter'+device_id).modal('hide');

    var changetime = document.getElementById("changetime");
    var selectedValuechangetime = changetime.value;
    var changematrix = document.getElementById("matrix");
    var selectedValuechangematrix = changematrix.value;
    
    console.log(device_id);
    
    // Call the function
    graphdata(device_id,User_Id, '','',selectedValuechangetime, selectedValuechangematrix);
    
    // Clear previous interval, if any
    clearInterval(intervalID);
    
    // Start a new interval
    intervalID = setInterval(function() {
        graphdata(device_id,User_Id, '','',selectedValuechangetime, selectedValuechangematrix);
    }, 50000);
});

// });
  function graphdata(device_id,User_Id,from_date,to_date,changetime = null, changematrix = null) {
    $("#show_alarm_history").html('');
    $(".weather_hd").hide();
    getUserLocation(device_id);
    $.ajax({
        type: 'post',
        url: getGraphDataRoute,
        data: {device_id:device_id,User_Id:User_Id,from_date:from_date,to_date:to_date,changetime:changetime,changematrix:changematrix,_token:csrfToken},
        dataType: 'json',
        success: function (response) {
          //console.log('resp');
            //console.log(response);
            $('#spinner'+device_id).hide();
              // localStorage.screenname = "callcenter";
              // setCurrentScreen(localStorage.screenname);
            if(response.status == "success"){
                $('.show_alarm_history'+device_id).html(response.html);
                var latitude = $('#onloadlatitude'+device_id).val();
                var longitude = $('#onloadlongitude'+device_id).val();
                showweather(device_id,latitude,longitude);
                

                $('.no_data_found').hide();
                $('.no_data_found'+response.devide_id).hide();
                var sensorData = response.data.sensordata;
                var sensorconfig = response.sensorconfig;
                //console.log(sensorData);
                if ($.isEmptyObject(sensorData)) {
                    $('.no_data_found').show();
                    $('.no_data_found'+response.devide_id).show();
                    return false;
                }
                $('#append_graph'+response.devide_id).html("");
                var devide_id = response.devide_id;
                // Iterate over each sensor
                $.each(sensorData, function(sensorName, sensorValues) {
                  //console.log(sensorValues.type);
                    if (sensorValues.type != 'multi'){
                      return true;
                    }
                    //console.log(sensorValues);

                    var sensoricon = sensorValues.icon ?? '';
                    var sensorcolor = sensorValues.color ?? '';
                    
                    // var readableSensorName = convertSensorName(sensorValues.spname);
                    // if (readableSensorName == 'HUMIDITY SENSOR') {
                    //   readableSensorName = 'AIR '+readableSensorName;
                    // }else if(readableSensorName == 'TEMPERATURE SENSOR'){
                    //   readableSensorName = 'AIR '+readableSensorName;
                    // }

                    var readableSensorName = sensorValues.changename;


                    var sensorxvalue = [];
                    var sensoryvalue = [];
                    // Iterate over each sensor value
                    console.log("Sensor Name: " + readableSensorName);
                    let cardColor, headingColor, axisColor, shadeColor, borderColor, legendColor, chartColors, labelColor ;

                    cardColor = config.colors.white;
                    headingColor = config.colors.headingColor;
                    axisColor = config.colors.axisColor;
                    borderColor = config.colors.borderColor;
                    legendColor = config.colors.legendColor;
                    chartColors = config.colors.chartColors;
                    labelColor  = config.colors.labelColor;

                    var container = $('<div>', {
                        id: 'lineChart'+sensorName+response.devide_id,
                        class: 'px-2 col-12',
                        style: 'margin: 40px 0px;'
                    });

                    var container = $('<div>', {
                        id: 'lineChart'+sensorName+response.devide_id,
                        class: 'px-2 col-12'
                    });
                    // Create a container div for centering
                    var divElement = $('<div>', {
                      style: 'text-align: center; margin-top: 20px;'  // Adjust margin as needed
                    });

                    var imageUrl = baseUrl +'/'+ sensoricon;
                    
                    var chartTitle = $('<h3>', {
                        // html: '<button class="btn" data-bs-toggle="modal" type="button" onclick="get_sensor_alarm(\'' + sensorName + '\', \'' + devide_id + '\')" data-bs-target="#setalarm' + devide_id + '"><i class="fa fa-cog"></i></button> ' + sensorValues.icon + readableSensorName,
                        // style: 'color: ' + sensorValues.color + '; font-size: 20px; margin-bottom: 10px;'
                        html: `
                          <div class="btn-group">
                              <i class="fas fa-cog" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                              <ul class="dropdown-menu">
                                  <li>
                                      <button class="dropdown-item" type="button" onclick="get_sensor_alarm('${sensorName}', '${devide_id}')" data-bs-toggle="modal" data-bs-target="#setalarm${devide_id}">
                                          Set Alarm
                                      </button>
                                  </li>
                                  <li>
                                      <button class="dropdown-item" type="button" onclick="get_graph_name('${sensorName}', '${devide_id}')" data-bs-toggle="modal" data-bs-target="#setgraphname${devide_id}">
                                          Set Graph Name
                                      </button>
                                  </li>
                              </ul>
                          </div>
                          ${sensoricon}${readableSensorName}
                          <span style="color: #4c4e4f; font-size: 28px;">
                              ${Math.round(sensorValues.data[sensorValues.data.length - 1].y * 100) / 100} ${sensorValues.unit}
                          </span>
                      `,
                      style: 'color: #4c4e4f; font-size: 20px; margin-bottom: 10px;'
                    });

                    // Last Y Value
                    // var lastYValue = $('<h3>', {
                    //   text: Math.round(sensorValues.data[sensorValues.data.length - 1].y * 100)/100 + ' ' + sensorValues.unit,
                    //   style: 'color: #4c4e4f; font-size: 28px;'  // Adjust font-size as needed
                    // });

                    // Append elements to the container
                    divElement.append(chartTitle);
                    // divElement.append(lastYValue);

                    // Append the container to the 'divElement'
                    divElement.append(container);


                    $('#append_graph'+response.devide_id).append(divElement);

                    $.each(sensorValues.data, function(index, value) {
                        sensorxvalue.push(value.x);
                        sensoryvalue.push(value.y);
                    });

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
                        colors: [sensorcolor],
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
                          //type: 'datetime',
                          categories: sensorxvalue,
                          labels: {
                            style: {
                              colors: labelColor,
                              fontSize: '13px'
                            }
                          }
                        },
                        yaxis: {
                          //type: 'datetime',
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


                $('#append_graph_single'+response.devide_id).html("");
                $.each(sensorData, function(sensorName, sensorValue) {
                    if (sensorValue.type != 'single') {
                        return true; // Skip to the next iteration if the sensor type is not 'single'
                    }

                    var sensor_name = convertSensorName(sensorName);
                    var mapContainerId = 'map_' + sensorName; // Unique identifier for each map container
                    var cardHtml = `<hr>
                        <div class="card">
                            <div class="card-body" style="text-align: center">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                </div>
                                <h3 class="mb-1" style="font-size: 24px; color:black">${sensor_name}</h3>
                                <h3 class="mb-2" style="font-size: 34px; color: #4c4e4f">${sensorValue.data.y}</h3>
                                <h3 class="mb-2" style="font-size: 12px; color:black">${sensorValue.data.x}</h3>`;

                        if (sensorValue.type === 'location') {
                            // Include location-related HTML elements
                            cardHtml += `<h3 class="mb-2" style="font-size: 24px; color: #4c4e4f">${sensorValue.data.address}</h3>
                            <div id="${mapContainerId}" style="height: 400px;"></div>`;
                        }

                        cardHtml += `</div></div>`;

                    // Append the HTML content to the container
                    $('#append_graph_single' + response.devide_id).append(cardHtml);

                    if (sensorValue.type == 'location') {
                      // Initialize the map for this sensor
                      var map = L.map(mapContainerId).setView([sensorValue.data.Latitude, sensorValue.data.Longitude], 13);

                      // Add a tile layer (OSM) to the map
                      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                      }).addTo(map);

                      // Add a marker to the map
                      var marker = L.marker([sensorValue.data.Latitude, sensorValue.data.Longitude]).addTo(map);

                      console.log("X: " + sensorValue.data.x + ", Y: " + sensorValue.data.y);
                    }
                    
                });


                $.each(sensorData, function(sensorName, sensorValue) {
                    if (sensorValue.type != 'location') {
                        return true; // Skip to the next iteration if the sensor type is not 'single'
                    }

                    var sensor_name = convertSensorName(sensorName);
                    var mapContainerId = 'map_' + sensorName; // Unique identifier for each map container
                    var cardHtml = `<hr>
                        <div class="card">
                            <div class="card-body" style="text-align: center">
                                <div class="card-title d-flex align-items-start justify-content-between">
                                </div>
                                <h3 class="mb-1" style="font-size: 24px; color:black">${sensor_name}</h3>
                                <h3 class="mb-2" style="font-size: 34px; color: #4c4e4f">${sensorValue.data.y}</h3>
                                <h3 class="mb-2" style="font-size: 12px; color:black">${sensorValue.data.x}</h3>`;

                        if (sensorValue.type === 'location') {
                            // Include location-related HTML elements
                            cardHtml += `<h3 class="mb-2" style="font-size: 24px; color: #4c4e4f">${sensorValue.data.address}</h3>
                            <div id="${mapContainerId}" style="height: 400px;"></div>`;
                        }

                        cardHtml += `</div></div>`;

                    // Append the HTML content to the container
                    $('#append_graph_single' + response.devide_id).append(cardHtml);

                    
                      // Initialize the map for this sensor
                      var map = L.map(mapContainerId).setView([sensorValue.data.Latitude, sensorValue.data.Longitude], 13);

                      // Add a tile layer (OSM) to the map
                      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                      }).addTo(map);

                      // Add a marker to the map
                      var marker = L.marker([sensorValue.data.Latitude, sensorValue.data.Longitude]).addTo(map);

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

  