<?php
	
	require 'model/Database.php';    
        require 'partials/security.php'; 
        require 'partials/header.php';
?>

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
						<div class="row">
              <div class="col-sm-12">
                <div class="table-responsive">
                  <table class="table table-bordered text-nowrap" id="marketTable">
    <thead class="thead-light">
        <tr>
            <th colspan="4">
                <button type="button" data-target="#addmarket" data-toggle="modal" class="btn btn-primary"><strong>Add Market</strong></button>
            </th>
        </tr>
        <tr>
            <th>#</th>
            <th>Market</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $stmtmarket1 =$db->conn->prepare("SELECT * FROM `market_2` ORDER BY `name` ");
            $stmtmarket1->execute();
            $rowms =$stmtmarket1->fetchAll();
            foreach ($rowms as $key =>$rowm) : ?>
        <tr>
            <td><?= $key + 1 ?></td>
            <td><?= htmlspecialchars($rowm['name']) ?></td>
            <td><?= htmlspecialchars($rowm['status']) ?></td>
            <td>
                <button type="button" class="btn btn-sm btn-info edit-btn" data-id="<?= $rowm['id'] ?>" data-name="<?= htmlspecialchars($rowm['name']) ?>">Edit</button>
                <button type="button" class="btn btn-sm btn-danger status-btn" data-id="<?= $rowm['id'] ?>" data-status="inactive">Deactivate</button>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>

                </div>
              </div>

             
            </div>
				</div>
				<!-- /.container-fluid -->

		</div>
		<!-- End of Main Content -->

<?php
    require 'partials/footer.php';
?>

<!-- Add Market Modal -->
<div class="modal fade" id="addmarket" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="addMarketForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Market</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Market Name</label>
                        <input type="text" name="market_name" class="form-control" required>
                        <small class="text-danger" id="errorAddMarketName"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Market</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Market Modal -->
<div class="modal fade" id="editmarket" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="editMarketForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Market</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="market_id" id="edit_market_id">
                    <div class="form-group">
                        <label>Market Name</label>
                        <input type="text" name="market_name" id="edit_market_name" class="form-control" required>
                        <small class="text-danger" id="errorEditMarketName"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Market</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
	$(document).ready(function() {

    $('#marketTable').DataTable({
            "pageLength": 100,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
        });
    // 1. Add Market AJAX
    $('#addMarketForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'model/market_actions.php', // Fixed path (removed extra 'mode')
            type: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if(response.status === 'success') {
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                alert('An error occurred. Check console for details.');
            }
        });
    });

    // 2. Open Edit Modal and Populate Fields
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#edit_market_id').val(id);
        $('#edit_market_name').val(name);
        $('#editmarket').modal('show');
    });

    // 3. Edit Market AJAX
    $('#editMarketForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'model/market_actions.php',
            type: 'POST',
            data: $(this).serialize() + '&action=edit',
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if(response.status === 'success') {
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                alert('An error occurred. Check console for details.');
            }
        });
    });

    // 4. Deactivate Market Status AJAX
    $(document).on('click', '.status-btn', function() {
        if(confirm('Are you sure you want to deactivate this market?')) {
            var id = $(this).data('id');
            var status = $(this).data('status');
            $.ajax({
                url: 'model/market_actions.php',
                type: 'POST',
                data: { id: id, status: status, action: 'status' },
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    if(response.status === 'success') {
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                    alert('An error occurred. Check console for details.');
                }
            });
        }
    });
});
</script>