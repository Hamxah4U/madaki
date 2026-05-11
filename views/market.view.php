
<?php	
		require 'partials/security.php';
    require 'partials/header.php';
		require 'model/Database.php';
?>

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
            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800"></h1>
										<button type="button" data-target="#modelUnit" data-toggle="modal" class="btn btn-primary"><strong>Add Market</strong></button>
                </div>
                <!-- Content Row -->
								 
                <div class="table-responsive">
								<table id="departmentTable" class="table table-striped" style="width: 100%;">
                  <thead>
                    <tr>
											<th>#</th>
											<th>Market</th>
											<th>Money In</th>
											<th>Status</th>
											<!-- <th>View Store</th> -->
											<!-- <th>RecordedBy</th> -->
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

<!-- Modal -->
<div class="modal fade" id="modelUnit" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-primary"><strong>Market Registration Window</strong></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true" class="text-danger">&times;</span>
					</button>
			</div>
			<div class="modal-body">
				<form id="formUnit">
					<input type="hidden" id="unitId" name="unitId">
					<div class="form-group">
							<label for="Unit">Market</label>
							<input class="form-control" id="unitName" type="text" name="unit" placeholder="Enter Market">
							<small class="text-danger" id="errorUnit"></small>
					</div>
					<button type="submit" class="btn btn-primary" id="action-btn" data-mode="add">Save</button>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="addmoney" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-primary"><strong>Money In</strong></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true" class="text-danger">&times;</span>
					</button>
			</div>
			<div class="modal-body">
				<form id="formAmount">
					<input type="text" id="unitId" name="unitId" hidden>
					<div class="form-group">
							<label for="Unit">Amount</label>
							<input class="form-control" id="unitName" type="text" name="unit" placeholder="Enter Amount" required>
							<small class="text-danger" id="errorUnit"></small>
					</div>
					<button type="submit" class="btn btn-primary" id="action-btn" data-mode="add">Save</button>
				</form>
			</div>
		</div>
	</div>
</div>

<?php
    require 'partials/footer.php';
?>

<script>

	$(document).on("click", ".closeMarket", function(){

			let marketId = $(this).data("id");
			let button = $(this);

			Swal.fire({
					title: 'Close this market?',
					text: "This market will be marked as closed.",
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Yes, Close It'
			}).then((result) => {

					if(result.isConfirmed){

							$.ajax({
									url: "model/close_market.php",
									type: "POST",
									data: {
											id: marketId
									},

									success: function(response){

											if(response.trim() == "success"){

													Swal.fire({
															icon: 'success',
															title: 'Market Closed',
															timer: 2000,
															showConfirmButton: false
													}).then(() => location.reload());

													// // optional button update
													// button
													// 		.removeClass("btn-warning")
													// 		.addClass("btn-secondary")
													// 		.text("Closed")
													// 		.prop("disabled", true);

											} else {

													Swal.fire({
															icon: 'error',
															title: 'Failed to close market'
													});

											}

									},

									error: function(xhr){

											console.log(xhr.responseText);

											Swal.fire({
													icon: 'error',
													title: 'Server Error'
											});

									}

							});

					}

			});

	});

</script>

<script>
	$('#addmoney').on('show.bs.modal', function (event) {
    let button = $(event.relatedTarget);
    let id = button.data('id');

    $(this).find('#unitId').val(id);
	});
</script>

<script>
$(document).ready(function () {

    // Open modal and set ID
    $('#addmoney').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let id = button.data('id');

        $('#unitId').val(id);
    });

    // Submit form with AJAX
    $('#formAmount').submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: 'model/add_moneyin.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',

            beforeSend: function () {
                $('#action-btn').html('Saving...');
                $('#action-btn').prop('disabled', true);
            },

            success: function (response) {

                if (response.status === 'success') {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Reset form
                    $('#formAmount')[0].reset();

                    // Hide modal
                    $('#addmoney').modal('hide');

                    // Reload only DataTable
                    $('#departmentTable').DataTable().ajax.reload(null, false);

                } else {

                    // Swal.fire({
                    //     icon: 'error',
                    //     title: 'Error',
                    //     text: response.message
                    // });
										

                }
            },

            error: function (xhr) {

                console.log(xhr.responseText);

                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: xhr.responseText
                });

            },

            complete: function () {
                $('#action-btn').html('Save');
                $('#action-btn').prop('disabled', false);
            }
        });
    });

});
</script>

<script>
	$(document).ready(function(){
		$('#departmentTable').DataTable({
			ajax:{
				url: 'model/market.table.php',
				dataSrc: '',
			},
			columns: [
				{ "data": null, render: (data, type, row, meta) => meta.row + 1 },
				{ "data": "market_name" },
       {
					"data": "moneyinTotal",
					render: function(data) {

							return '₦' + parseFloat(data || 0).toLocaleString('en-NG', {
									minimumFractionDigits: 2,
									maximumFractionDigits: 2
							});
					}
			},
        { "data": "status" },
        // { "data": "created_by" },
				{
            data: null,
            render: function(data, type, row){
                return `
                    <button class="btn btn-info" data-id="${row.id}" id="editDepartment">Edit</button>
                    <a href="view-market?marketId=${row.id}" class="btn btn-success">View market</a>
										<button class="btn btn-warning closeMarket" data-id="${row.id}">Close market</button>
										<button type="button" data-id="${row.id}" data-target="#addmoney" data-toggle="modal" class="btn btn-primary"><strong>Money In</strong></button>

                `;
            }
        }

          
			]

		});
	});
</script>

<script>
	function resetForm() {
		$('#formUnit')[0].reset(); 
		$('#unitId').val(''); 
		$('#errorUnit').text(''); 
		$('#action-btn').removeClass('btn-info').addClass('btn-primary').text('Save').data('mode', 'add'); 
  }

	$(document).ready(function(){
		$('#formUnit').on('submit', function(e){
			e.preventDefault();
			const mode = $('#action-btn').data('mode');
			const url = mode === 'edit' ? 'model/update_market.php' : 'model/add_market.php';
			const iconType = mode === 'edit' ? 'info' : 'success';
			$.ajax({
				url: url,//mode === 'edit' ? 'model/unit.edit.php' : 'model/unit.form.php', 
				dataType: 'JSON',
				data: $(this).serialize(),
				type: 'POST',
				success: function(response){
					if(response.status){
						//alert('success'+ response.message);
						const Toast = Swal.mixin({
							toast: true,
							position: "top-end",
							showConfirmButton: false,
							timer: 2000,
							timerProgressBar: true,
							didOpen: (toast) => {
								toast.onmouseenter = Swal.stopTimer;
								toast.onmouseleave = Swal.resumeTimer;
							}
						});
						Toast.fire({
							icon: iconType,//"success",
							title: response.message || response.success
						});
						$('#departmentTable').DataTable().ajax.reload();
						$('#modelUnit').modal('hide');
						resetForm();
					}else{
						$('#errorUnit').text(response.errors.unit || response.errors.unitExist || '');
					}
				},
				error: function(xhr, status, error){
					alert('Error:' + xhr + status + error);
				}
			});
		});

		$('body').on('click', '#editDepartment', function(e){
			e.preventDefault();
			let id = $(this).data('id');
			$.get(`model/market.edit.php?deptId=${id}`, function(response){
				$('#unitId').val(response.id); // Set the department ID for update
				$('#unitName').val(response.market_name);
				$('#action-btn').removeClass('btn-primary').addClass('btn-info').text('Update').data('mode', 'edit');
				$('#modelUnit').modal('show');
			}, 'json');
			
				$('#modelUnit').on('hidden.bs.modal', function () {
					resetForm();
				});

		});

	});
</script>
