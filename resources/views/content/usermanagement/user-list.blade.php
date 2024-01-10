@extends('layouts/contentNavbarLayout')

@section('title', 'Users List')

@section('content')
<link rel="stylesheet" href="../assets/css/tabulator.min.css">
<link rel="stylesheet" href="../assets/sweet-alert2/sweetalert2.min.css">
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Users /</span> Users List
</h4>

<!-- Basic Bootstrap Table -->
<div class="card">
  <h5 class="card-header">Users List</h5>
  <div class="table-responsive text-nowrap container">
    

    <table id="users_list" class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Created Date</th>
                <th>Expiry Date</th>
                <th>Device id</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($finalDataArr as $userdata)
              <tr>
                  <td>{{ $userdata['name'] }}</td>
                  <td>{{ $userdata['email'] }}</td>
                  <td>{{ $userdata['status'] }}</td>
                  <td>{{ $userdata['created_at'] }}</td>
                  <td>{{ $userdata['expiry_date'] }}</td>
                  <td>{{ $userdata['device_id'] }}</td>
                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('users.edit', $userdata['id']) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                        <a class="dropdown-item" onclick='deleteUser(`{{ $userdata["id"] }}`)' href="javascript:void(0);"><i class="bx bx-trash me-1"></i> Delete</a>
                        <a class="dropdown-item" href="{{ route('users.dashboard', $userdata['id']) }}"><i class="menu-icon tf-icons bx bx-home-circle"></i> User Dashboard</a>
                      </div>
                    </div>
                  </td>
              </tr>
            @endforeach
      </tbody>
    </table>

  </div>
</div>
<!--/ Basic Bootstrap Table -->
<!--/ Responsive Table -->
@endsection

<script src="../assets/sweet-alert2/sweetalert2.min.js"></script>
<script src="../assets/sweet-alert2/sweet-alert.init.js"></script>

<script type="text/javascript">

  /*document.addEventListener("DOMContentLoaded", function () {
    //define data array
  var tabledata = JSON.parse('<?= json_encode($finalDataArr, JSON_HEX_APOS); ?>');
  console.log(tabledata);
  for (var i = 0; i < tabledata.length; i++){
      //tabledata[i]['action'] = '';
  }
  //tabledata

  var table = new Tabulator("#contractlist_table", {
      data: tabledata, //load row data from array
      layout: "fitColumns", //fit columns to width of table
      //autoColumns:true, //create columns from data field names
      // responsiveLayout: "collapse", //hide columns that dont fit on the table
      tooltips: true, //show tool tips on cells
      addRowPos: "top", //when adding a new row, add it to the top of the table
      history: true, //allow undo and redo actions on the table
      pagination: "local", //paginate the data
      paginationSize: 50, //allow 7 rows per page of data
      paginationCounter:"rows", //display count of paginated rows in footer
      // paginationSizeSelector: fileResponse.sizeArray, //enable page size select element with these options
      //movableColumns: true, //allow column order to be changed
      fitColumns:true,
      resizableRows: true, //allow row order to be changed
      paginationSizeSelector:[10, 25, 50, 100], //enable page size select element with these options    
      columnDefaults:{
          tooltip:true //show tool tips on cells
      },
      columns: [//define the table columns
          {title: "Name", field: "name", headerFilter:true, headerTooltip: true},
          {title: "Email", field: "email",  headerFilter:true, headerTooltip: true},
          {title: "CreatedAt", field: "created_at",  headerFilter:true, headerTooltip: true,},
          {
            title: "<div class='text-center'>Actions</div>",
            // title: "Actions",
            field: "action",
            width: 160,
            headerTooltip: true,
            headerSort : false,
            download:false,
            tooltip:false, 
            formatter: function(cell, formatterParams) {
                var str = cell.getRow();
                var actionHtml = "";
                    actionHtml += "<div class='tooltip4'>";
                    actionHtml += "<a href='{{ url('edit_user') }}/" + str._row.data.id + "'>";
                        actionHtml += "<i class='fa fa-edit'></i> ";
                         actionHtml += "</a>";
                        actionHtml += "<i onclick='deleteUser(`"+str._row.data.id+"`)' class='fa fa-trash-o'></i>";
                        //actionHtml += "<span class='tooltiptext4'>Edit</span>";
                    actionHtml += "</div>";
                return "<div class='text-center'>" + actionHtml+ "</div>";
            }
        },
          
      ]
  });
});*/

function deleteUser(user_id) {
   Swal.fire({
        title: 'Delete User',
        text: 'This operation will delete the user. Do you really wants to delete user?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-danger ml-1'
        },
        buttonsStyling: true
    }).then(function(result) {
        if (result.isConfirmed) {
          console.log('delete');
            $.ajax({
                type: 'POST',
                url: '{{ route("delete-user-data") }}',
                data: {
                    "user_id": user_id,
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    console.log(response.status);
                     // return false;
                    if (response.success == 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'User Deleted!',
                            text: "User Deleted successfully",
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        }).then(({
                            value
                        }) => {
                            location.reload(true);
                        });
                    } else {
                        Swal.fire({
                            title: 'Cancelled',
                            text: 'Error while email sent :)',
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        }).then(({
                            value
                        }) => {
                            location.reload(true);
                        });
                    }
                }
            });
        } else {
            location.reload(true);
        }
    });
}

  
</script>