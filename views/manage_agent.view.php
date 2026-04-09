
<?php
		require 'partials/security.php';
    require 'partials/header.php';
    require 'model/Database.php';
?>
 

  <!-- Page Wrapper -->
<div id="wrapper">
  <!-- Sidebar -->
  <?php require 'partials/sidebar.php' ?>

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
							<h1 class="h3 mb-0 text-danger">New Agent Window</h1>
              <a href="/billing">
                <!-- <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#modelProduct"><strong>Billing</strong></button> -->
              </a>
					</div>

					<!-- Content Row -->
					<form id="addAgent">
            <input type="hidden" name="purchaseprice" id="Pprice">

            <div class="form-row">
              <div class="form-group col-md-6">
                <label><strong>Agent Fullname:</strong></label>
                <input  type="text" name="agent_name" class="form-control" />
								<small class="text-danger" id="errorName"></small>
              </div>
              <div class="form-group col-md-6">
                <label><strong>Amount Given:</strong></label>
                <input name="amount" type="number" id="amount" class="form-control" />
                <small class="text-danger" id="errorAmount"></small>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label><strong>Phone Number:</strong></label>
                <input  type="text" name="phone" id="phone" class="form-control" />
								<small class="text-danger" id="errorPhone"></small>
              </div>
              <div class="form-group col-md-6">
                <label><strong>Email:</strong></label>
                <input name="email" type="email" id="email" class="form-control" />
                <small class="text-danger" id="errorEmail"></small>
              </div>
            </div>

            <button type="submit" class="btn btn-primary mb-3"><strong>Add</strong></button>
          </form>
            
          <div class="responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Fullname</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Amount</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                    $sqlagent2 = $db->conn->prepare('SELECT * FROM `users_tbl` WHERE `Role` = "Agent II" ');
                    $sqlagent2->execute();
                    $rowagent2 = $sqlagent2->fetchAll();
                    foreach($rowagent2 as $index => $agent): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= $agent['Fullname'] ?></td>
                    <td><?= $agent['Phone'] ?></td>
                    <td><?= $agent['Email'] ?></td>
                    <td><?= $agent['amount'] ?></td>
                    <td>
                      <a href="/edit-agent?id=<?= $agent['userID'] ?>" class="btn btn-sm btn-info"><strong>Edit</strong></a>
                      <a href="/delete-agent?id=<?= $agent['userID'] ?>" class="btn btn-sm btn-danger"><strong>Delete</strong></a>
                      <a href="/view-agent?id=<?= $agent['userID'] ?>" class="btn btn-sm btn-success"><strong>View</strong></a>
                    </td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
			</div>
		</div>
		<!-- End of Main Content -->
<?php  require 'partials/footer.php'; ?>

<script>
  $(document).ready(function(){
    $('#addAgent').on('submit', function(e){
      e.preventDefault();
      $.ajax({
        url: 'model/add_agent.php',
        dataType: 'JSON',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response){
          if(response.status){
          //  alert(response.success.message);
					const Toast = Swal.mixin({
							toast: true,
							position: "top-end",
							showConfirmButton: false,
							timer: 3000,
							timerProgressBar: true,
							didOpen: (toast) => {
								toast.onmouseenter = Swal.stopTimer;
								toast.onmouseleave = Swal.resumeTimer;
							}
						});
						Toast.fire({
							icon: "success",
							title: response.success.message,
						});
            setTimeout(() => {
              location.reload();
            }, 1500);
            $('.text-danger').text('');
						$('#addTransaction')[0].reset();

          }else{
						
            $('#errorName').text(response.errors.driver_name || '');
            $("#errorPhone").text(response.errors.phone || '');
            $('#errorDriverAmount').text(response.errors.driver_amount || '');
            $('#errorAmountPerAnimal').text(response.errors.amount_per_animal || '');
          }
        },
        error: function(xhr, status, error){
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Response:", xhr.responseText);

            alert("Something went wrong:\n" + xhr.responseText);
        }
      });
    });
  });
</script>
