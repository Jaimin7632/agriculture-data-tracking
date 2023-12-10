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

<?php
  if ($user->device_id != "") {
    $targetdevice_id = explode(',', $user->device_id);
    foreach ($targetdevice_id as $value) { ?>
      <div class="col-12 col-lg-12 order-2 order-md-3 order-lg-2 mb-4">
        <div class="card" id="DeviceId" onclick="graphdata('<?php echo $value; ?>')">
          <h5 class="card-header m-0 me-2 pb-3">Device - <?php echo $value; ?></h5>
          <div class="row row-bordered g-0" id="append_graph<?php echo $value; ?>">
            <!-- <div class="col-md-6">
               <div id="lineChart<?php echo $value; ?>" class="px-2"></div>
            </div> -->
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
  function graphdata(device_id) {
    //alert(device_id);
    $.ajax({
        type: 'post',
        url: '{{ route("getgraphdata") }}',
        data: {device_id:device_id,_token:"{{ csrf_token() }}"},
        dataType: 'json',
        success: function (response) {
            console.log(response);

              // localStorage.screenname = "callcenter";
              // setCurrentScreen(localStorage.screenname);
              if(response.status == "success"){

                var devide_id = response.devide_id;

                var soialSensorxValues = [];
                var soialSensoryValues = [];
                var pressureSensorxValues = [];
                var pressureSensoryValues = [];
                var humiditySensorxValues = [];
                var humiditySensoryValues = [];
                var temperatureSensorxValues = [];
                var temperatureSensoryValues = [];

                let cardColor, headingColor, axisColor, shadeColor, borderColor, legendColor, chartColors, labelColor ;

                  cardColor = config.colors.white;
                  headingColor = config.colors.headingColor;
                  axisColor = config.colors.axisColor;
                  borderColor = config.colors.borderColor;
                  legendColor = config.colors.legendColor;
                  chartColors = config.colors.chartColors;
                  labelColor  = config.colors.labelColor;

                if (response.data.soialSensorValues != "") {
                  console.log(response.data.soialSensorValues);

                  for (var c = 0; c < response.data.soialSensorValues.length; c++) {
                      soialSensorxValues.push(response.data.soialSensorValues[c].x);
                      soialSensoryValues.push(response.data.soialSensorValues[c].y);
                  }

                  var divElement = $('<div>', {
                      id: 'lineChart'+response.devide_id,
                      class: 'px-2 col-md-6'
                  });

                  var innerDiv = $('<div>', {
                      'class': 'px-2'
                  });

                  divElement.append(innerDiv);

                  $('#append_graph'+response.devide_id).append(divElement);
                  const displayEveryNthPoint = 10;

                  const lineChartEl = document.querySelector('#lineChart'+response.devide_id),
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
                        name: 'SoilSensorValue',
                        data: soialSensoryValues.filter((value, index) => index % displayEveryNthPoint === 0)
                      }
                    ],
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
                    colors: ['#F1948A'],
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
                      categories: soialSensorxValues,
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
                if (response.data.pressureSensorValues != "") {
                  console.log(response.data.pressureSensorValues);
                  for (var c = 0; c < response.data.pressureSensorValues.length; c++) {
                      pressureSensorxValues.push(response.data.pressureSensorValues[c].x);
                      pressureSensoryValues.push(response.data.pressureSensorValues[c].y);
                  }

                  var divElement = $('<div>', {
                      id: 'lineChart1'+response.devide_id,
                      class: 'px-2 col-md-6'
                  });

                  var innerDiv = $('<div>', {
                      'class': 'px-2'
                  });

                  divElement.append(innerDiv);

                  $('#append_graph'+response.devide_id).append(divElement);

                  const lineChartEl = document.querySelector('#lineChart1'+response.devide_id),
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
                        name: 'PressureSensorValue',
                        data: pressureSensoryValues
                      }
                    ],
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
                    colors: ['#C39BD3'],
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
                      categories: pressureSensorxValues,
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

                if (response.data.humiditySensorValues != "") {
                  console.log(response.data.humiditySensorValues);
                  for (var c = 0; c < response.data.humiditySensorValues.length; c++) {
                      humiditySensorxValues.push(response.data.humiditySensorValues[c].x);
                      humiditySensoryValues.push(response.data.humiditySensorValues[c].y);
                  }

                  var divElement = $('<div>', {
                      id: 'lineChart2'+response.devide_id,
                      class: 'px-2 col-md-6'
                  });

                  var innerDiv = $('<div>', {
                      'class': 'px-2'
                  });

                  divElement.append(innerDiv);

                  $('#append_graph'+response.devide_id).append(divElement);

                  const lineChartEl = document.querySelector('#lineChart2'+response.devide_id),
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
                        name: 'HumiditySensorValue',
                        data: humiditySensoryValues
                      }
                    ],
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
                    colors: ['#52BE80'],
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
                      categories: humiditySensorxValues,
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

                if (response.data.temperatureSensorValues != "") {
                  console.log(response.data.temperatureSensorValues);
                  for (var c = 0; c < response.data.temperatureSensorValues.length; c++) {
                      temperatureSensorxValues.push(response.data.temperatureSensorValues[c].x);
                      temperatureSensoryValues.push(response.data.temperatureSensorValues[c].y);
                  }

                  var divElement = $('<div>', {
                      id: 'lineChart3'+response.devide_id,
                      class: 'px-2 col-md-6'
                  });

                  var innerDiv = $('<div>', {
                      'class': 'px-2'
                  });

                  divElement.append(innerDiv);

                  $('#append_graph'+response.devide_id).append(divElement);

                  const lineChartEl = document.querySelector('#lineChart3'+response.devide_id),
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
                        name: 'TemperatureSensorValue',
                        data: temperatureSensoryValues
                      }
                    ],
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
                    colors: ['#E67E22'],
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
                      categories: temperatureSensorxValues,
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

            }

        }
    });
  }
</script>
@endsection


