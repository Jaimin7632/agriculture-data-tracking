@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/dashboards-analytics.js')}}"></script>
@endsection

@section('content')
<div class="row">
  <div class="col-lg-4 col-md-12 col-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{asset('assets/img/icons/unicons/user.png')}}" alt="chart success" class="rounded">
          </div>
          <div class="dropdown">
            <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
              <a class="dropdown-item" href="javascript:void(0);">View More</a>
              <a class="dropdown-item" href="javascript:void(0);">Delete</a>
            </div>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total User</span>
        <h3 class="card-title mb-2">{{$totalusercount}}</h3>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-12 col-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{asset('assets/img/icons/unicons/activeuser.png')}}" alt="Credit Card" class="rounded">
          </div>
          <div class="dropdown">
            <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
              <a class="dropdown-item" href="javascript:void(0);">View More</a>
              <a class="dropdown-item" href="javascript:void(0);">Delete</a>
            </div>
          </div>
        </div>
        <span>Active User</span>
        <h3 class="card-title text-nowrap mb-1">{{$activeusercount}}</h3>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-12 col-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{asset('assets/img/icons/unicons/inactive.png')}}" alt="Credit Card" class="rounded">
          </div>
          <div class="dropdown">
            <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
              <a class="dropdown-item" href="javascript:void(0);">View More</a>
              <a class="dropdown-item" href="javascript:void(0);">Delete</a>
            </div>
          </div>
        </div>
        <span>In Active User</span>
        <h3 class="card-title text-nowrap mb-1">{{$inactiveusercount}}</h3>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-4 col-md-12 col-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{asset('assets/img/icons/unicons/adminuser.png')}}" alt="chart success" class="rounded">
          </div>
          <div class="dropdown">
            <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
              <a class="dropdown-item" href="javascript:void(0);">View More</a>
              <a class="dropdown-item" href="javascript:void(0);">Delete</a>
            </div>
          </div>
        </div>
        <span class="fw-semibold d-block mb-1">Total Admin User</span>
        <h3 class="card-title mb-2">{{$adminusercount}}</h3>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-12 col-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{asset('assets/img/icons/unicons/activeuser.png')}}" alt="Credit Card" class="rounded">
          </div>
          <div class="dropdown">
            <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
              <a class="dropdown-item" href="javascript:void(0);">View More</a>
              <a class="dropdown-item" href="javascript:void(0);">Delete</a>
            </div>
          </div>
        </div>
        <span>Total Sensor Data</span>
        <h3 class="card-title text-nowrap mb-1">{{$sensordatacount}}</h3>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-12 col-6 mb-4">
    <div class="card">
      <div class="card-body">
        <div class="card-title d-flex align-items-start justify-content-between">
          <div class="avatar flex-shrink-0">
            <img src="{{asset('assets/img/icons/unicons/inactive.png')}}" alt="Credit Card" class="rounded">
          </div>
          <div class="dropdown">
            <button class="btn p-0" type="button" id="cardOpt6" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt6">
              <a class="dropdown-item" href="javascript:void(0);">View More</a>
              <a class="dropdown-item" href="javascript:void(0);">Delete</a>
            </div>
          </div>
        </div>
        <span>Last 2 Days Unique Device Count</span>
        <h3 class="card-title text-nowrap mb-1">{{$uniqueDeviceCount}}</h3>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-12 col-md-12 col-6 mb-4">
    <span class="fw-semibold d-block mb-1">In 30 Days Expiry Users</span>
    <!-- <div class="card"> -->
      <table id="expiry_user" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Created Date</th>
                <th>Expiry Date</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($expiryusers as $key => $value) { ?>
              <tr>
                  <td><?php echo $value['name']; ?></td>
                  <td><?php echo $value['email']; ?></td>
                  <td><?php echo \Carbon\Carbon::parse($value['created_at'])->format('Y-m-d H:i:s'); ?></td>
                  <td><?php echo $value['expiry_date']; ?></td>
              </tr>
            <?php }
          ?>
            </tbody>
        <tfoot>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Created Date</th>
                <th>Expiry Date</th>
            </tr>
        </tfoot>
      </table>
    <!-- </div> -->
  </div>
</div>

<div class="row">
  <div class="col-lg-12 col-md-12 col-6 mb-4">
    <span class="fw-semibold d-block mb-1">In Active Users</span>
    <table id="inactive_user" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Created Date</th>
                <th>Expiry Date</th>
            </tr>
        </thead>
        <tbody>
          <?php 
            foreach ($inactiveusers as $key => $value) { ?>
              <tr>
                  <td><?php echo $value['name']; ?></td>
                  <td><?php echo $value['email']; ?></td>
                  <td><?php echo \Carbon\Carbon::parse($value['created_at'])->format('Y-m-d H:i:s'); ?></td>
                  <td><?php echo $value['expiry_date']; ?></td>
              </tr>
            <?php }
          ?>
            </tbody>
        <tfoot>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Created Date</th>
                <th>Expiry Date</th>
            </tr>
        </tfoot>
      </table>
  </div>
</div>



@endsection

