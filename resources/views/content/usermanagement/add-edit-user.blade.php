@extends('layouts/contentNavbarLayout')

@section('title', isset($userdata) ? 'Edit User' : 'Add User')

@section('content')
<style type="text/css">
  .invalid-error{
    width: 100%;
    margin-top: 0.3rem;
    font-size: 85%;
    color: #ff3e1d;
  }
</style>


<!-- Basic Layout -->
<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ isset($userdata) ? 'editar usuario' : 'Agregar usuario' }}</h5> 
      </div>
      <div class="card-body">
        <form method="POST" action="{{ isset($userdata) ? route('update-user', $userdata->id) : route('insert-update-user') }}" id="change_lang">
          @csrf
          <input type="hidden" name="user_id" value="{{ isset($userdata) ? $userdata->id : '' }}">
          <div class="mb-3">
            <label class="form-label" for="basic-default-fullname">Nombre completo</label>
            <input type="text" name="name" value="{{ isset($userdata) ? $userdata->name : old('name') }}" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" id="basic-default-fullname" placeholder="John Doe" required/>

            @if ($errors->has('name'))
                <span class="invalid-error" role="alert">
                    <strong>{{ $errors->first('name') }}</strong>
                </span>
            @endif

          </div>

          <div class="mb-3">
            <label class="form-label" for="basic-default-fullname">ID del dispositivo</label>
            <input type="button" onclick="generateUUID()" style="margin-bottom: 10px;margin-left: 10px;" class="btn rounded-pill me-2 btn-success" value="Generate Device Id">
            <input type="hidden" name="device_id" value="{{ isset($userdata) ? $userdata->device_id : old('device_id') }}" class="form-control{{ $errors->has('device_id') ? ' is-invalid' : '' }}" id="hiddenUUIDs" placeholder=""/>

            @if ($errors->has('device_id'))
                <span class="invalid-error" role="alert">
                    <strong>{{ $errors->first('device_id') }}</strong>
                </span>
            @endif

          </div>

          

          <div id="uuidContainer">
            <?php if (isset($userdata) && $userdata->device_id != "") {
              $targetdevice_id = explode(',', $userdata->device_id);
              foreach ($targetdevice_id as $value) { ?>
                <input type="text" class="deviceIdInput" id="closeTextBox<?php echo $value; ?>" value="<?php echo $value; ?>">
                <input type="button" class="closeButton" id="closeButton<?php echo $value; ?>" style="color: red;" onclick='closeDeviceId(`{{ $value }}`)' value="✖">
              <?php }
            } ?>
          </div>

          <div class="mb-3 col-md-12">
            <label class="form-label" style="margin-top: 10px" for="timezone">Zona horaria</label>
            <?php $selectedTimezones = ""; if (isset($userdata) && isset($userdata->timezone)) {
              $selectedTimezones = $userdata->timezone;
            } ?>
            <select id="timezone" value="{{ isset($userdata) ? $userdata->timezone : old('timezone') }}" name="timezone" class="select2 form-select">
              <option value="">Select</option>
              <?php foreach ($timezones as $time_zone) { ?>
                   <option value="<?= $time_zone->timezone ?>" <?= ($selectedTimezones == $time_zone->timezone) ? 'selected' : '' ?>>
                      <?= '(' . $time_zone->utc_offset . ') ' . $time_zone->timezone ?>
                  </option>
              <?php } ?>
              
            </select>
            @if ($errors->has('timezone'))
                <span class="invalid-error" role="alert">
                    <strong>{{ $errors->first('timezone') }}</strong>
                </span>
            @endif
          </div>

          <div class="mb-3">
            <label class="form-label" for="basic-default-fullname">Role</label>
            <div class="form-check">
              <input name="role" class="form-check-input" type="radio" value="admin" {{ isset($userdata) && $userdata->role === 'admin' ? 'checked' : '' }} id="defaultRadio1" />
              <label class="form-check-label" for="defaultRadio1">
                Administrador
              </label>
            </div>
            <div class="form-check">
              <input name="role" class="form-check-input" type="radio" value="user" id="defaultRadio2" {{ isset($userdata) && $userdata->role === 'user' ? 'checked' : '' }} />
              <label class="form-check-label" for="defaultRadio2">
                Usuario
              </label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="basic-default-email">Correo electrónico</label>
            <div class="input-group input-group-merge">
              <input type="text" name="email" value="{{ isset($userdata) ? $userdata->email : old('email') }}" id="basic-default-email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="john.doe" aria-label="john.doe" aria-describedby="basic-default-email2" />
              <!-- <span class="input-group-text" id="basic-default-email2">@example.com</span> -->

              @if ($errors->has('email'))
                  <span class="invalid-error" role="alert">
                      <strong>{{ $errors->first('email') }}</strong>
                  </span>
              @endif

            </div>
            <div class="form-text"> Puedes usar letras, números y puntos. </div>
          </div>

          <div class="row">
            <div class="mb-3 col-md-6">
              <label class="form-label" for="basic-default-fullname">Estado</label>
              <div class="form-check">
                <input name="status" class="form-check-input" type="radio" value="active" {{ isset($userdata) && $userdata->status === 'active' ? 'checked' : '' }} id="defaultRadio3" />
                <label class="form-check-label" for="defaultRadio3">
                  Activo
                </label>
              </div>
              <div class="form-check">
                <input name="status" class="form-check-input" type="radio" value="inactive" id="defaultRadio4" {{ isset($userdata) && $userdata->status === 'inactive' ? 'checked' : '' }} />
                <label class="form-check-label" for="defaultRadio4">
                  Inactivo
                </label>
              </div>
            </div>

            <div class="mb-3 col-md-6">
              <label for="flatpickr-date" class="form-label">Fecha de caducidad</label>
              <input class="form-control" name="expiry_date" value="{{ isset($userdata) ? $userdata->expiry_date : old('expiry_date') }}" type="date" value="" id="html5-date-input" />
            </div>
          </div>

          <?php 
          if (isset($userdata)) { ?>
          <?php }else{ ?>
            <div class="mb-3">
              <label class="form-label" for="basic-default-phone">Contraseña</label>
              <input type="password" name="password" value="" id="basic-default-phone" class="form-control" placeholder="" />

              @if ($errors->has('password'))
                  <span class="invalid-error" role="alert">
                      <strong>{{ $errors->first('password') }}</strong>
                  </span>
              @endif

            </div>
            <div class="mb-3">
              <label class="form-label" for="basic-default-phone">Confirmar contraseña</label>
              <input type="password" name="password_confirmation" id="basic-default-phone" class="form-control" placeholder="" required/>
            </div>
          <?php } ?>
          

          <button type="submit" class="btn btn-primary">Entregar</button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

<!-- <script>

function generateAndAppendUUID() {
    // Generate a UUID
    const uuid = generateUUID();

    // Get the text box
    const textBox = document.getElementById('uuidTextBox');

    // Append the generated UUID, adding a comma if there are existing IDs
    textBox.value = textBox.value ? `${textBox.value},${uuid}` : uuid;
}

function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 8 | 0,
            v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(8);
    });
}

</script> -->
<script src="{{ asset(mix('assets/vendor/libs/jquery/jquery.js')) }}"></script>
<script>


function closeDeviceId(DeviceId){

  //alert(DeviceId);
  const closeButton = document.getElementById("closeButton"+DeviceId);
  const closeTextBox = document.getElementById("closeTextBox"+DeviceId);
  console.log(closeButton);
  //closeButton.addEventListener("click", function() {
    // Remove the textbox and update the hidden field
    // textbox.remove();
    closeButton.remove();
    closeTextBox.remove();
  //});  
  updateHiddenField();
}  
function generateUUID() {

  // Generate UUID
  const uuid = generateUUIDString();

  // Create a new textbox to display the UUID
  const textbox = document.createElement("input");
  textbox.type = "text";
  textbox.className = "deviceIdInput";
  textbox.id = "closeTextBox"+uuid;
  textbox.value = uuid;
  // textbox.readOnly = true;

  // Create a close button
  const closeButton = document.createElement("button");
  // closeButton.textContent = "Close";
  closeButton.innerHTML = "&#10006;"; // HTML entity for a cross (✖)
  closeButton.style.color = "red";
  closeButton.addEventListener("click", function() {
    // Remove the textbox and update the hidden field
    textbox.remove();
    closeButton.remove();
    updateHiddenField();
  });

  textbox.addEventListener("input", function() {
    updateHiddenField();
  });

  // Append the textbox and close button to the container
  const container = document.getElementById("uuidContainer");
  container.appendChild(textbox);
  container.appendChild(closeButton);

  // Update the hidden field
  updateHiddenField();
}

function updateHiddenField() {
  const uuidTextboxes = document.querySelectorAll("#uuidContainer input[type='text']");
  const uuids = Array.from(uuidTextboxes).map(textbox => textbox.value).join(',');

  // Update the hidden field with comma-separated UUIDs
  document.getElementById("hiddenUUIDs").value = uuids;
}

function generateUUIDString() {
  let result = '';
  const characters = '0123456789';
  const charactersLength = 8;

  for (let i = 0; i < 8; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }

  return result;
}

$(document).ready(function() {
  $('.deviceIdInput').on('change', function() {
      var newValue = $(this).val();
      // console.log('New value:', newValue);
      updateHiddenField();
      // You can perform additional actions here based on the new value
      // For example, update other elements or send the new value to the server
  });
});

</script>

