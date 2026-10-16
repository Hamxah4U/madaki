<?php
	
	require 'model/Database.php';    
        require 'partials/security.php'; 
        require 'partials/header.php';
?>
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
    <!-- Page Wrapper -->
<div id="wrapper">
    <!-- Sidebar -->
    <?php require 'partials/sidebar.php' ?>

    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
  <div id="content-wrapper" class="d-flex flex-column">

		<!-- Main Content -->
		<div id="content">

				<!-- Topbar -->
				<?php
						require 'partials/nav.php';
				?>
				<!-- End of Topbar -->

				<!-- Begin Page Content -->
				<div class="container-fluid">

						<!-- Page Heading -->
						<div class="d-sm-flex align-items-center justify-content-between mb-4">
								<h1 class="h3 mb-0 text-gray-800"></h1>
							<button class="btn btn-primary" type="button" data-target="#modalCustomers" data-toggle="modal"><strong>Add Customer</strong></button>
						</div>

						<!-- Content Row -->
						<div class="table-responsive"> 
						<table class="table table-striped" id="usersTable" style="width: 100%;">
							<thead>
								<tr>
									<th>#</th>
									<th>Fullname</th>
									<th>Email</th>
									<th>Phone</th>
                  <th>Address</th>
									<th>Status</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>

							</tbody>
						</table>
						</div>
						<!-- Content Row -->
				</div>
				<!-- /.container-fluid -->

		</div>
		<!-- End of Main Content -->

<?php
    require 'partials/footer.php';
?>

<!-- Modal -->
<div class="modal fade" id="modalCustomers" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-primary"><strong>Customers Registration Window</strong></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true" class="text-danger"><strong>&times;</strong></span>
					</button>
			</div>
			<div class="modal-body">
				<form id="userForm">
					<input type="hidden" name="userID" id="userID">
					<div class="form-group">
						<label for="my-input">Fullname</label>
						<input id="fname" class="form-control" type="text" name="fname">
						<small class="text-danger" id="errorFname"></small>
					</div>
					
					<div class="form-group">
						<label for="my-input">Email</label>
						<input id="email" class="form-control" type="email" name="email">
						<small class="text-danger" id="errorEmail"></small>
					</div>

					<div class="form-group">
						<label for="my-input">Phone</label>
						<input id="phone" class="form-control" type="number" name="phone">
						<small class="text-danger" id="errorPhone"></small>
					</div>

          <div class="form-group">
						<label for="my-input">Address</label>
						<textarea id="address" class="form-control" name="address"></textarea>
            <small class="text-danger" id="errorAddress"></small>
          </div>

					<!-- <button id="action-btn" type="submit" class="btn btn-primary">Save</button> -->
					<button type="submit" class="btn btn-primary" id="action-btn" data-mode='add'><strong>Save</strong></button>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
    // 1. Initialize DataTable and load data via AJAX
    var table = $('#usersTable').DataTable({
     pageLength: 100,

        "ajax": {
            "url": "model/customer_actions.php",
            "type": "POST",
            "data": { "action": "fetch" },
            "dataSrc": "data"
        },
        "columns": [
            { 
                "data": null, 
                "render": function (data, type, row, meta) {
                    return meta.row + 1; 
                } 
            },
            { "data": "fullname" },
            { "data": "email" },
            { "data": "phone" },
            { "data": "address" },
            { 
                "data": "status",
                "render": function(data) {
                    return data === 'active' 
                        ? '<span class="badge badge-success">Active</span>' 
                        : '<span class="badge badge-danger">Inactive</span>';
                }
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `<button class="btn btn-sm btn-info edit-btn" 
                                data-id="${row.id}" 
                                data-fullname="${row.fullname}" 
                                data-email="${row.email}" 
                                data-phone="${row.phone}" 
                                data-address="${row.address}">
                                Edit
                            </button>`;
                }
            }
        ]
    });

    // 2. Reset modal form when clicking "Add Customer" button
    $('[data-target="#modalCustomers"]').on('click', function() {
        $('#userForm')[0].reset();
        $('#userID').val('');
        $('#action-btn').attr('data-mode', 'add').html('<strong>Save</strong>');
        $('.text-danger').text('');
    });

    // 3. Open Edit Modal and populate fields
    $(document).on('click', '.edit-btn', function() {
        $('#userID').val($(this).data('id'));
        $('#fname').val($(this).data('fullname'));
        $('#email').val($(this).data('email'));
        $('#phone').val($(this).data('phone'));
        $('#address').val($(this).data('address'));
        
        // Switch button mode to edit
        $('#action-btn').attr('data-mode', 'edit').html('<strong>Update</strong>');
        $('.text-danger').text('');$('#modalCustomers').modal('show');
    });

    // 4. Handle Form Submission (Add & Edit) via AJAX & SweetAlert2
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text(''); // Clear previous inline warnings

        var mode = $('#action-btn').attr('data-mode'); // 'add' or 'edit'
        var formData = $(this).serialize() + '&action=' + mode;

        $.ajax({
            url: 'model/customer_actions.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modalCustomers').modal('hide');

                    Swal.mixin({
                      toast: true,
                      position: "top-end",
                      showConfirmButton: false,
                      timer: 1500,
                      timerProgressBar: true,
                      didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                      }
                    }).fire({
                      icon: "success",
                      title: "success!"
                    });

                    table.ajax.reload(null, false); // Reload table data without refreshing page
                } else {
                    // Show error inline if field is specified, or trigger SweetAlert
                    if (response.field === 'email') {
                        $('#errorEmail').text(response.message);
                    } else if (response.field === 'phone') {
                        $('#errorPhone').text(response.message);
                    } else {
                        // Swal.fire({
                        //     icon: 'error',
                        //     title: 'Validation Error',
                        //     text: response.message
                        // });
                    }
                }
            },
            error: function(xhr, status, error) {
                Swal.toast({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'An unexpected error occurred. Please try again.'
                });
                console.log(xhr.responseText);
            }
        });
    });
});
</script>


