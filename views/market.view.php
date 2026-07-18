<?php	
	require 'model/Database.php';    
        require 'partials/security.php'; 
        require 'partials/header.php';
?>

<style>
  /* Changes the pointer to a hand icon on row cells, except the actions column */
  .clickable-row td:not(:last-child) {
    cursor: pointer;
  }
  /* Gives a subtle color change when hovering over a clickable row */
  .clickable-row:hover td:not(:last-child) {
    background-color: rgba(0, 0, 0, 0.04) !important;
  }
</style>

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
          <button type="button" data-target="#modelUnit" data-toggle="modal" class="btn btn-primary"><strong>Add
              Market</strong></button>
        </div>
        <!-- Content Row -->

        <div class="table-responsive">
          <table id="departmentTable" class="table table-striped text-nowrap" style="width: 100%;">
            <thead>
              <tr>
                <th>#</th>
                <!-- <th>Market</th> -->
                <th>First Agent</th>
                <th>Second Agent</th>
                <th>Money In</th>
                <th>Money Out</th>
                <th>Diff. Bal.</th>
                <!-- <th>Expenses</th> -->
                <!-- <th>Status</th> -->
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
    <div class="modal fade" id="modelUnit" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
      aria-hidden="true">
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

              <div class="form-group">
                <label for="Unit">First Agent</label>
                <?php
                  $stmtAgent = $db->conn->prepare("SELECT * FROM `users_tbl` WHERE `Role` = :userRole");
                  $stmtAgent->execute([':userRole' => 'Agent']);
                  $rowAgents = $stmtAgent->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <select name="agent" id="agent1" class="form-control">
                  <option value="">--select agent--</option>
                  <?php foreach($rowAgents as $rowAgent):?>
                  <option value="<?= $rowAgent['userID'] ?>"><?= $rowAgent['Fullname'] ?></option>
                  <?php endforeach ?>
                </select>
                <small class="text-danger" id="errorAgent"></small>
              </div>

              <div class="form-group">
                <label for="Unit">Second Agent</label>
                <?php
                  $stmtAgent = $db->conn->prepare("SELECT * FROM `users_tbl` WHERE `Role` = :userRole");
                  $stmtAgent->execute([':userRole' => 'Agent']);
                  $rowAgents = $stmtAgent->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <select name="secondagent" id="agent2" class="form-control">
                  <option value="">--select agent--</option>
                  <?php foreach($rowAgents as $rowAgent):?>
                  <option value="<?= $rowAgent['userID'] ?>"><?= $rowAgent['Fullname'] ?></option>
                  <?php endforeach ?>
                </select>
                <small class="text-danger" id="errorAgent2"></small>
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
                <input class="form-control" id="unitName" type="number" name="unit" placeholder="Enter Amount" required>
                <small class="text-danger" id="errorUnit"></small>
              </div>
              <div class="form-group">
                <label for="">Date</label>
                <input type="date" name="date" id="date" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary" id="action-btn" data-mode="add">Save</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <?php require 'partials/footer.php' ?>

    <script>
      $(document).on("click", ".closeMarket", function() {

        let marketId = $(this).data("id");
        let button = $(this);

        Swal.fire({
          title: 'Close this market?',
          text: "This market will be marked as closed.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, Close It'
        }).then((result) => {

          if (result.isConfirmed) {

            $.ajax({
              url: "model/close_market.php",
              type: "POST",
              data: {
                id: marketId
              },

              success: function(response) {

                if (response.trim() == "success") {

                  
                  Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
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

              error: function(xhr) {

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
      $('#addmoney').on('show.bs.modal', function(event) {
        let button = $(event.relatedTarget);
        let id = button.data('id');

        $(this).find('#unitId').val(id);
      });
    </script>

    <script>
      $(document).ready(function() {

        // Open modal and set ID
        $('#addmoney').on('show.bs.modal', function(event) {
          let button = $(event.relatedTarget);
          let id = button.data('id');

          $('#unitId').val(id);
        });

        // Submit form with AJAX
        $('#formAmount').submit(function(e) {
          e.preventDefault();

          $.ajax({
            url: 'model/add_moneyin.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',

            beforeSend: function() {
              $('#action-btn').html('Saving...');
              $('#action-btn').prop('disabled', true);
            },

            success: function(response) {

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

            error: function(xhr) {

              console.log(xhr.responseText);

              Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: xhr.responseText
              });

            },

            complete: function() {
              $('#action-btn').html('Save');
              $('#action-btn').prop('disabled', false);
            }
          });
        });

      });
    </script>

    <script>
      $(document).ready(function() {
        $('#departmentTable').DataTable({
          ajax: {
            url: 'model/market.table.php',
            dataSrc: '',
          },
          // ---- ADD THIS CALLBACK TO MAKE ROWS CLICKABLE ----
          createdRow: function(row, data, dataIndex) {
            // Add a CSS class so we can change the mouse pointer to a hand on hover
            $(row).addClass('clickable-row');
            
            // Listen for clicks on the row, but ignore the buttons column (last column)
            $(row).on('click', 'td:not(:last-child)', function() {
              window.location.href = `view-market?marketId=${data.id}`;
            });
          },
          // --------------------------------------------------
          columns: [{
              "data": null,
              render: (data, type, row, meta) => meta.row + 1
            },
            {
              "data": "agent_name"
            },
            {"data" : "second_agent_name"},
            {
              data: null,
              render: function(data, type, row) {
                let total = (parseFloat(row.moneyOutTotal) || 0) + (parseFloat(row.ttotherexp) || 0);
                return '₦' + total.toLocaleString('en-NG', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                });
              }
            },
            {
              data: null,
              render: function(data, type, row) {
                let total = (parseFloat(row.totalMoneyInAnimal) || 0) + (parseFloat(row.ttexp) || 0);
                return '₦' + total.toLocaleString('en-NG', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                });
              }
            },
            {
              data: null,
              render: function(data, type, row) {
                let total1 = (parseFloat(row.totalMoneyInAnimal) || 0) + (parseFloat(row.ttexp) || 0);
                let total2 = (parseFloat(row.moneyOutTotal) || 0) + (parseFloat(row.ttotherexp) || 0);
                let total = total1 - total2;
                return '₦' + total.toLocaleString('en-NG', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                });
              }
            },
            {
              data: null,
              render: function(data, type, row) {
              // <a href="view-market?marketId=${row.id}" class="btn btn-success">View market</a>
                return `
                      <button class="btn btn-info" data-id="${row.id}" id="editDepartment">Edit</button>
                      <button class="btn btn-warning closeMarket" data-id="${row.id}">Close market</button>
                      <button type="button" data-id="${row.id}" data-target="#addmoney" data-toggle="modal" class="btn btn-primary"><strong>Money In</strong></button>
                      <a href="money-history?marketId=${row.id}" class="btn btn-info">Money History</a>
                  `;
              }
            }
          ]
        });
      });
      /* $(document).ready(function() {
        $('#departmentTable').DataTable({
          ajax: {
            url: 'model/market.table.php',
            dataSrc: '',
          },
          columns: [{
              "data": null,
              render: (data, type, row, meta) => meta.row + 1
            },
            // { "data": "market_name" },
            {
              "data": "agent_name"
            },
            {"data" : "second_agent_name"},
            {
              data: null,
              render: function(data, type, row) {

                let total = (parseFloat(row.moneyOutTotal) || 0) +
                  (parseFloat(row.ttotherexp) || 0);

                return '₦' + total.toLocaleString('en-NG', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                });
              }
            },

            {
              data: null,
              render: function(data, type, row) {

                let total = (parseFloat(row.totalMoneyInAnimal) || 0) +
                  (parseFloat(row.ttexp) || 0);

                return '₦' + total.toLocaleString('en-NG', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                });
              }
            },

            {
              data: null,
              render: function(data, type, row) {

                let total1 = (parseFloat(row.totalMoneyInAnimal) || 0) + (parseFloat(row.ttexp) || 0);
                let total2 = (parseFloat(row.moneyOutTotal) || 0) + (parseFloat(row.ttotherexp) || 0);
                let total = total1 - total2;

                return '₦' + total.toLocaleString('en-NG', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                });
              }
            },

            // { "data": "status" },
            // { "data": "created_by" },
            {
              data: null,
              render: function(data, type, row) {
                return `
                      <button class="btn btn-info" data-id="${row.id}" id="editDepartment">Edit</button>
                      <a href="view-market?marketId=${row.id}" class="btn btn-success">View market</a>
                      <button class="btn btn-warning closeMarket" data-id="${row.id}">Close market</button>
                      <button type="button" data-id="${row.id}" data-target="#addmoney" data-toggle="modal" class="btn btn-primary"><strong>Money In</strong></button>
                      <a href="money-history?marketId=${row.id}" class="btn btn-info">Money History</a>
                  `;
              }
            }


          ]

        });
      }); */
    </script>

    <script>
      function resetForm() {
        $('#formUnit')[0].reset();
        $('#unitId').val('');
        $('#errorUnit').text('');
        $('#action-btn').removeClass('btn-info').addClass('btn-primary').text('Save').data('mode', 'add');
      }

      $(document).ready(function() {
        $('#formUnit').on('submit', function(e) {
          e.preventDefault();
          const mode = $('#action-btn').data('mode');
          const url = mode === 'edit' ? 'model/update_market.php' : 'model/add_market.php';
          const iconType = mode === 'edit' ? 'info' : 'success';
          $.ajax({
            url: url, //mode === 'edit' ? 'model/unit.edit.php' : 'model/unit.form.php', 
            dataType: 'JSON',
            data: $(this).serialize(),
            type: 'POST',
            success: function(response) {
              if (response.status) {
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
                  icon: iconType, //"success",
                  title: response.message || response.success
                });
                $('#departmentTable').DataTable().ajax.reload();
                $('#modelUnit').modal('hide');
                resetForm();
              } else {
                $('#errorUnit').text(response.errors.unit || response.errors.unitExist || '');
                $('#errorAgent').text(response.errors.agent || '');
              }
            },
            error: function(xhr, status, error) {
              alert('Error:' + xhr + status + error);
            }
          });
        });

        $('body').on('click', '#editDepartment', function(e) {
          e.preventDefault();
          let id = $(this).data('id');
          $.get(`model/market.edit.php?deptId=${id}`, function(response) {
            $('#unitId').val(response.id); // Set the department ID for update
            $('#unitName').val(response.market_name);
            $('#agent1').val(response.agent_id);
            $('#agent2').val(response.secondagent);

            $('#action-btn').removeClass('btn-primary').addClass('btn-info').text('Update').data('mode',
              'edit');
            $('#modelUnit').modal('show');
          }, 'json');

          $('#modelUnit').on('hidden.bs.modal', function() {
            resetForm();
          });

        });

      });
    </script>