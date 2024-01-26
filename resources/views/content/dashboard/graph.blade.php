<div class="row">
<!--  -->
<input type="hidden" name="user_id" id="User_Id" value="{{$user->id}}">
<?php
  if ($user->device_id != "") {
    $targetdevice_id = explode(',', $user->device_id); ?>
    <?php if ($user->role == 'admin') { ?>
    <div class="col-12 col-lg-12">
      <div id="DeviceId">
        <div class="row">
          <center><strong><h4>{{$user->name}}</h4></strong></center>
        </div>
      </div>
    </div>
    <?php } ?>
    <?php foreach ($targetdevice_id as $value) { ?>
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
            <?php if ($user->role == 'user') { ?>
              <div class="col-md-8">
                <input type="button" onclick="showtextbox('<?php echo $value; ?>')" id="change_device_id" class="btn btn-warning" value="Change Name" style="margin-top: 11px;">
                <span style="margin-top: 11px; display: none;" id="change_name<?php echo $value; ?>">
                  <input type="text" name="change_name" id="name_textbox<?php echo $value; ?>" value="">
                  <input type="submit" class="btn btn-primary" name="submit" value="Change" onclick="changedevicename('<?php echo $value; ?>')">
                  <input type="button" class="btn btn-danger" name="close" value="Close" onclick="closetextbox('<?php echo $value; ?>')">
                </span>
              </div>
            <?php } ?>
            
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