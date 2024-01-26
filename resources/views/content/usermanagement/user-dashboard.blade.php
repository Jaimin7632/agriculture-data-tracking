@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection


@section('content')

@include('content/dashboard/graph')

@endsection

@section('page-script')
<script src="{{asset('assets/js/dashboards-analytics.js')}}"></script>
<script type="text/javascript">

  window.base_url = "{{ url('/') }}";
  window.get_graph_data_route = "{{ route('getgraphdata') }}";

</script>
<script src="{{asset('assets/js/dynamicgraph.js')}}"></script>

@endsection


