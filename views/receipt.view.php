
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
										<button type="button" data-target="#modelUnit" data-toggle="modal" class="btn btn-primary"><strong>Add Animal</strong></button>
                </div>
                <!-- Content Row -->
								 
                <div class="table-responsive">
								<table id="departmentTable" class="table table-striped" style="width: 100%;">
                  <thead>
                    <tr>
											<th>#</th>
											<th>Animal</th>
											<th>Status</th>
											<!-- <th>View Store</th> -->
											<th>RecordedBy</th>
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
				<h5 class="modal-title text-primary"><strong>Animal</strong></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true" class="text-danger">&times;</span>
					</button>
			</div>
			<div class="modal-body">
				<form id="formUnit">
					<input type="hidden" id="unitId" name="unitId">
					<div class="form-group">
							<label for="Unit">Animal</label>
							<input class="form-control" id="unitName" type="text" name="unit" placeholder="Enter Animal">
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
	$(document).ready(function(){
		$('#departmentTable').DataTable({
			ajax:{
				url: 'model/unit.table.php',
				dataSrc: '',
			},
			columns: [
				{ "data": null, render: (data, type, row, meta) => meta.row + 1 },
				{ "data": "Department" },
        { "data": "Status" },
        
				/* {
					"data": null,
					"render": function (data, type, row) {
						return `
							<a href="/view-store?id=${row.deptID}" class="btn btn-info">
								<span class="fas fa-fw fa-eye"></span>
							</a>
						`;
					}
				},
 */
				{ "data": "registerby" },

				{ "data": null,
					"render": function(data, type, row){
						return `<button class="btn btn-info" data-id="${row.deptID}" id='editDepartment'><span class="fas fa-fw fa-edit"></span></button>`;
					}
				}
			]

		});
	});
</script>


















<style>
body { font-family: Arial; padding:20px; }
.receipt {
    width: 400px;
    margin: auto;
    border: 1px solid #000;
    padding: 20px;
}
@media print {
    button { display:none; }
}
</style>

<div class="receipt">

    <h3 style="text-align:center;">Payment Receipt</h3>
    <hr>

    <p><strong>Name:</strong> <?= $person['fullname'] ?></p>
    <p><strong>Driver:</strong> <?= $transport['driver_name'] ?></p>
    <p><strong>Motor No:</strong> <?= $transport['bossno'] ?></p>

    <hr>

    <p>1st Payment: ₦<?= number_format($person['first_payment']) ?></p>
    <p>2nd Payment: ₦<?= number_format($person['second_payment']) ?></p>
    <p>3rd Payment: ₦<?= number_format($person['third_payment']) ?></p>

    <h4>Total Paid: ₦<?= number_format($person['total']) ?></h4>

    <br>
    <small>Date: <?= date('d M Y') ?></small>
</div>

<br>
<center>
<button onclick="window.print()">Print Receipt</button>
</center>
