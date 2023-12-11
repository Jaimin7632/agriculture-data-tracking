@extends('layouts/contentNavbarLayout')

@section('title', 'Account settings - Account')



@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Account Settings /</span> Account
</h4>

<div class="row">
  <div class="col-md-12">
    <ul class="nav nav-pills flex-column flex-md-row mb-3">
      <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class="bx bx-user me-1"></i> Account</a></li>
      <!-- <li class="nav-item"><a class="nav-link" href="{{url('pages/account-settings-notifications')}}"><i class="bx bx-bell me-1"></i> Notifications</a></li>
      <li class="nav-item"><a class="nav-link" href="{{url('pages/account-settings-connections')}}"><i class="bx bx-link-alt me-1"></i> Connections</a></li> -->
    </ul>
    <div class="card mb-4">
      <h5 class="card-header">Profile Details</h5>
      <!-- Account -->
      <!-- <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-4">
          <img src="{{asset('assets/img/avatars/1.png')}}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
          <div class="button-wrapper">
            <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
              <span class="d-none d-sm-block">Upload new photo</span>
              <i class="bx bx-upload d-block d-sm-none"></i>
              <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg" />
            </label>
            <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
              <i class="bx bx-reset d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Reset</span>
            </button>

            <p class="text-muted mb-0">Allowed JPG, GIF or PNG. Max size of 800K</p>
          </div>
        </div>
      </div> -->
      <hr class="my-0">
      <div class="card-body">
        <!-- <form id="formAccountSettings" method="POST" action="{{ route('update-userdetails') }}"> -->
          @csrf
          <input type="hidden" name="user_id" id="user_id" value="{{$user->id}}">
          <div class="row">
            <div class="mb-3 col-md-6">
              <label for="firstName" class="form-label">Full Name</label>
              <input class="form-control" type="text" id="fullname" name="name" value="{{$user->name}}" autofocus />
            </div>
            <!-- <div class="mb-3 col-md-6">
              <label for="lastName" class="form-label">Last Name</label>
              <input class="form-control" type="text" name="lastName" id="lastName" value="Doe" />
            </div> -->
            <div class="mb-3 col-md-6">
              <label for="email" class="form-label">E-mail</label>
              <input class="form-control" type="text" id="email" name="email" value="{{$user->email}}" placeholder="john.doe@example.com" />
            </div>

            <div class="mb-3 col-md-12">
              <label class="form-label" for="country">Country</label>
              <?php $selectedCountry = ""; if (isset($user) && isset($user->country)) {
                $selectedCountry = $user->country;
              } ?>
              <select id="country" value="{{ $user->country }}" name="country" class="select2 form-select">
                <option value="">Select</option>
                <?php foreach ($countryData as $country) { ?>
                   <option value="<?= $country->name ?>" <?= ($selectedCountry == $country->name) ? 'selected' : '' ?>>
                      <?= $country->name ?>
                  </option>
                <?php } ?>
              </select>
            </div>

            <!-- <div class="mb-3 col-md-6">
              <label for="organization" class="form-label">Organization</label>
              <input type="text" class="form-control" id="organization" name="organization" value="{{config('variables.creatorName')}}" />
            </div>
            <div class="mb-3 col-md-6">
              <label class="form-label" for="phoneNumber">Phone Number</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text">US (+1)</span>
                <input type="text" id="phoneNumber" name="phoneNumber" class="form-control" placeholder="202 555 0111" />
              </div>
            </div>
            <div class="mb-3 col-md-6">
              <label for="address" class="form-label">Address</label>
              <input type="text" class="form-control" id="address" name="address" placeholder="Address" />
            </div>
            <div class="mb-3 col-md-6">
              <label for="state" class="form-label">State</label>
              <input class="form-control" type="text" id="state" name="state" placeholder="California" />
            </div>
            <div class="mb-3 col-md-6">
              <label for="zipCode" class="form-label">Zip Code</label>
              <input type="text" class="form-control" id="zipCode" name="zipCode" placeholder="231465" maxlength="6" />
            </div>
            <div class="mb-3 col-md-6">
              <label class="form-label" for="country">Country</label>
              <select id="country" class="select2 form-select">
                <option value="">Select</option>
                <option value="Australia">Australia</option>
                <option value="Bangladesh">Bangladesh</option>
                <option value="Belarus">Belarus</option>
                <option value="Brazil">Brazil</option>
                <option value="Canada">Canada</option>
                <option value="China">China</option>
                <option value="France">France</option>
                <option value="Germany">Germany</option>
                <option value="India">India</option>
                <option value="Indonesia">Indonesia</option>
                <option value="Israel">Israel</option>
                <option value="Italy">Italy</option>
                <option value="Japan">Japan</option>
                <option value="Korea">Korea, Republic of</option>
                <option value="Mexico">Mexico</option>
                <option value="Philippines">Philippines</option>
                <option value="Russia">Russian Federation</option>
                <option value="South Africa">South Africa</option>
                <option value="Thailand">Thailand</option>
                <option value="Turkey">Turkey</option>
                <option value="Ukraine">Ukraine</option>
                <option value="United Arab Emirates">United Arab Emirates</option>
                <option value="United Kingdom">United Kingdom</option>
                <option value="United States">United States</option>
              </select>
            </div>
            <div class="mb-3 col-md-6">
              <label for="language" class="form-label">Language</label>
              <select id="language" class="select2 form-select">
                <option value="">Select Language</option>
                <option value="en">English</option>
                <option value="fr">French</option>
                <option value="de">German</option>
                <option value="pt">Portuguese</option>
              </select>
            </div>
            <div class="mb-3 col-md-6">
              <label for="timeZones" class="form-label">Timezone</label>
              <select id="timeZones" class="select2 form-select">
                <option value="">Select Timezone</option>
                <option value="-12">(GMT-12:00) International Date Line West</option>
                <option value="-11">(GMT-11:00) Midway Island, Samoa</option>
                <option value="-10">(GMT-10:00) Hawaii</option>
                <option value="-9">(GMT-09:00) Alaska</option>
                <option value="-8">(GMT-08:00) Pacific Time (US & Canada)</option>
                <option value="-8">(GMT-08:00) Tijuana, Baja California</option>
                <option value="-7">(GMT-07:00) Arizona</option>
                <option value="-7">(GMT-07:00) Chihuahua, La Paz, Mazatlan</option>
                <option value="-7">(GMT-07:00) Mountain Time (US & Canada)</option>
                <option value="-6">(GMT-06:00) Central America</option>
                <option value="-6">(GMT-06:00) Central Time (US & Canada)</option>
                <option value="-6">(GMT-06:00) Guadalajara, Mexico City, Monterrey</option>
                <option value="-6">(GMT-06:00) Saskatchewan</option>
                <option value="-5">(GMT-05:00) Bogota, Lima, Quito, Rio Branco</option>
                <option value="-5">(GMT-05:00) Eastern Time (US & Canada)</option>
                <option value="-5">(GMT-05:00) Indiana (East)</option>
                <option value="-4">(GMT-04:00) Atlantic Time (Canada)</option>
                <option value="-4">(GMT-04:00) Caracas, La Paz</option>
              </select>
            </div>
            <div class="mb-3 col-md-6">
              <label for="currency" class="form-label">Currency</label>
              <select id="currency" class="select2 form-select">
                <option value="">Select Currency</option>
                <option value="usd">USD</option>
                <option value="euro">Euro</option>
                <option value="pound">Pound</option>
                <option value="bitcoin">Bitcoin</option>
              </select>
            </div> -->
          </div>
          <div class="mt-2">
            <input type="button" id="update_profile" class="btn btn-primary me-2" value="Save changes">
            <!-- <button type="submit" class="btn btn-primary me-2">Save changes</button> -->
            <button type="reset" class="btn btn-outline-secondary">Cancel</button>
          </div>
        <!-- </form> -->
      </div>
      <!-- /Account -->
    </div>
    <!-- <div class="card">
      <h5 class="card-header">Delete Account</h5>
      <div class="card-body">
        <div class="mb-3 col-12 mb-0">
          <div class="alert alert-warning">
            <h6 class="alert-heading fw-bold mb-1">Are you sure you want to delete your account?</h6>
            <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
          </div>
        </div>
        <form id="formAccountDeactivation" onsubmit="return false">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" />
            <label class="form-check-label" for="accountActivation">I confirm my account deactivation</label>
          </div>
          <button type="submit" class="btn btn-danger deactivate-account">Deactivate Account</button>
        </form>
      </div>
    </div> -->

    <div class="card mb-4">
      <h5 class="card-header">Change Password</h5>
      <!-- Account -->
      <!-- <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-4">
          <img src="{{asset('assets/img/avatars/1.png')}}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
          <div class="button-wrapper">
            <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
              <span class="d-none d-sm-block">Upload new photo</span>
              <i class="bx bx-upload d-block d-sm-none"></i>
              <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg" />
            </label>
            <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
              <i class="bx bx-reset d-block d-sm-none"></i>
              <span class="d-none d-sm-block">Reset</span>
            </button>

            <p class="text-muted mb-0">Allowed JPG, GIF or PNG. Max size of 800K</p>
          </div>
        </div>
      </div> -->
      <hr class="my-0">
      <div class="card-body">

        <!-- <form id="formAccountSettings" method="POST" onsubmit="return false"> -->
          <div class="row">
            <div class="mb-3 col-md-6 form-password-toggle">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Old Password</label>
              </div>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" id="old_password" name="password" value="" placeholder="" />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
            </div>

            <div class="mb-3 col-md-6 form-password-toggle">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="password">New Passsword</label>
              </div>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" id="password" name="password" value="" placeholder="" />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
            </div>

            <div class="mb-3 col-md-6 form-password-toggle">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Confirm Passsword</label>
              </div>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" value="" placeholder="" />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
            </div>
          </div>
          <div class="mt-2">
            <input type="button" id="update_password" class="btn btn-primary me-2" value="Save changes">
            <!-- <button type="submit" class="btn btn-primary me-2">Save changes</button> -->
            <!-- <button type="reset" class="btn btn-outline-secondary">Cancel</button> -->
          </div>
        <!-- </form> -->
      </div>
      <!-- /Account -->
    </div>

  </div>
</div>
@endsection

@section('page-script')
<script src="{{asset('assets/js/pages-account-settings-account.js')}}"></script>

<script type="text/javascript">
  $("#update_profile").on("click", function(e){
        var fullname = $("#fullname").val();
        var email = $("#email").val();
        var user_id = $("#user_id").val();
        var country = $("#country").val();

        console.log(fullname);
        console.log(email);

        $.ajax({
            type: 'POST',
            url: '{{ route("update-userdetails") }}', 
            data: {fullname:fullname,user_id:user_id,country:country,email:email,_token:"{{ csrf_token() }}"},
            // dataType: 'json',
            // beforeSend: function() {
            //     $('.loader').show();
            // },
            success: function (response) {
                console.log(response);
                if (response.success == 'success') {
                    // location.reload(true);
                    Swal.fire({
                        // title: 'You Dial Number!',
                        title: 'Details Update Successfully!',
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
                        title: 'Details Does Not Update Successfully!',
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
                }
                // $('.loader').fadeOut();
            }
        });
        
  });

  $("#update_password").on("click", function(e){
        var old_password = $("#old_password").val();
        var password = $("#password").val();
        var password_confirmation = $("#password_confirmation").val();

        console.log(old_password);
        console.log(password);
        console.log(password_confirmation);

        $.ajax({
            type: 'POST',
            url: '{{ route("update-user-password") }}', 
            data: {old_password:old_password,password:password,password_confirmation:password_confirmation,_token:"{{ csrf_token() }}"},
            // dataType: 'json',
            // beforeSend: function() {
            //     $('.loader').show();
            // },
            success: function (response) {
                console.log(response);
                $('.loader').hide();
                if (response.success == 'success') {
                    // location.reload(true);
                    Swal.fire({
                        // title: 'You Dial Number!',
                        title: 'Password Update Successfully!',
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
                        title: 'Password Does Not Update Successfully!',
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
                }
                // $('.loader').fadeOut();
            }
        });
        
  });

</script>

@endsection

