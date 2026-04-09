
<?php
	require 'partials/security.php';
  require 'partials/header.php';
  require 'model/Database.php';

  if(!isset($_GET['id']) || empty($_GET['id'])) {
    require 'controllers/404.php';
    exit();
  }
  $agentID = (int)$_GET['id'];

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
					<form id="addAgentLoan">
            <input type="hidden" name="agent_id" id="agent_id" value="<?= $agentID ?>">

            <div class="form-row">
              <div class="form-group col-md-3">
                <label><strong>Market:</strong></label>
                <input  type="text" name="agent_name" class="form-control" />
								<small class="text-danger" id="errorName"></small>
              </div>
               <div class="form-group col-md-3">
                <label><strong>Qty:</strong></label>
                <input name="amount" type="number" id="amount" class="form-control" />
                <small class="text-danger" id="errorAmount"></small>
              </div>
              <div class="form-group col-md-3">
                <label><strong>Amount:</strong></label>
                <input name="amount" type="number" id="amount" class="form-control" />
                <small class="text-danger" id="errorAmount"></small>
              </div>
              <div class="form-group col-md-3">
                <label><strong>Profit:</strong></label>
                <input name="amount" type="number" id="amount" class="form-control" />
                <small class="text-danger" id="errorAmount"></small>
              </div>
            </div>

            <button type="submit" class="btn btn-primary mb-3"><strong>Save</strong></button>
          </form>
            
          <div class="responsive">
            <!-- table goes here -->
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
          alert('error:' + xhr + status + error);
        }
      });
    });
  });
</script>
