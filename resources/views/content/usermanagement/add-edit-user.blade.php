@extends('layouts/contentNavbarLayout')

@section('title', ' Vertical Layouts - Forms')

@section('content')
<style type="text/css">
  .invalid-error{
    width: 100%;
    margin-top: 0.3rem;
    font-size: 85%;
    color: #ff3e1d;
  }
</style>
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Forms/</span> Vertical Layouts</h4>

<!-- Basic Layout -->
<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Add User</h5> <small class="text-muted float-end">Default label</small>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ isset($userdata) ? route('update-user', $userdata->id) : route('insert-update-user') }}">
          @csrf
          <input type="hidden" name="user_id" value="{{ isset($userdata) ? $userdata->id : '' }}">
          <div class="mb-3">
            <label class="form-label" for="basic-default-fullname">Full Name</label>
            <input type="text" name="name" value="{{ isset($userdata) ? $userdata->name : old('name') }}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" id="basic-default-fullname" placeholder="John Doe" required/>

            @if ($errors->has('name'))
                <span class="invalid-error" role="alert">
                    <strong>{{ $errors->first('name') }}</strong>
                </span>
            @endif

          </div>

          <div class="mb-3 col-md-12">
            <label class="form-label" for="country">Country</label>
            <select id="country" value="{{ isset($userdata) ? $userdata->country : old('country') }}" name="country" class="select2 form-select">
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
            @if ($errors->has('country'))
                <span class="invalid-error" role="alert">
                    <strong>{{ $errors->first('country') }}</strong>
                </span>
            @endif
          </div>

          <div class="col-3">
            <label class="form-label" for="basic-default-fullname">Role</label>
            <div class="form-check">
              <input name="role" class="form-check-input" type="radio" value="admin" {{ isset($userdata) && $userdata->role === 'admin' ? 'checked' : '' }} id="defaultRadio1" />
              <label class="form-check-label" for="defaultRadio1">
                Admin
              </label>
            </div>
            <div class="form-check">
              <input name="role" class="form-check-input" type="radio" value="user" id="defaultRadio2" {{ isset($userdata) && $userdata->role === 'user' ? 'checked' : '' }} />
              <label class="form-check-label" for="defaultRadio2">
                User
              </label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="basic-default-email">Email</label>
            <div class="input-group input-group-merge">
              <input type="text" name="email" value="{{ isset($userdata) ? $userdata->email : old('email') }}" id="basic-default-email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="john.doe" aria-label="john.doe" aria-describedby="basic-default-email2" />
              <!-- <span class="input-group-text" id="basic-default-email2">@example.com</span> -->

              @if ($errors->has('email'))
                  <span class="invalid-error" role="alert">
                      <strong>{{ $errors->first('email') }}</strong>
                  </span>
              @endif

            </div>
            <div class="form-text"> You can use letters, numbers & periods </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="basic-default-phone">Passsword</label>
            <input type="password" name="password" value="{{ isset($userdata) ? $userdata->password : old('password') }}" id="basic-default-phone" class="form-control" placeholder="" />

            @if ($errors->has('password'))
                <span class="invalid-error" role="alert">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
            @endif

          </div>
          <div class="mb-3">
            <label class="form-label" for="basic-default-phone">Confirm Passsword</label>
            <input type="password" name="password_confirmation" id="basic-default-phone" class="form-control" placeholder="" required/>
          </div>

          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection