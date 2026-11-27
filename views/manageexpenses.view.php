<?php
require 'model/Database.php';
require 'partials/security.php';
require 'partials/header.php';

// One-time message set by model/exp_list_actions.php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Load the table rows on the server (no JSON / AJAX)
$stmt = $db->conn->prepare('SELECT id, name, status FROM exp_list ORDER BY name ASC');
$stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- Page Wrapper -->
<div id="wrapper">
  <?php require 'partials/sidebar.php' ?>

  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">

      <?php require 'partials/nav.php'; ?>

      <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800"></h1>
          <button class="btn btn-primary" type="button" id="addBtn" data-target="#modalExpense" data-toggle="modal">
            <strong>Add Expenses</strong>
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-striped" id="usersTable" style="width: 100%;">
            <thead>
              <tr>
                <th>#</th>
                <th>Expenses</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($expenses as $i => $exp): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($exp['name']) ?></td>
                <td>
                  <?php if ($exp['status'] === 'active'): ?>
                    <span class="badge badge-success">Active</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Inactive</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-info edit-btn"
                          data-id="<?= (int)$exp['id'] ?>"
                          data-name="<?= htmlspecialchars($exp['name'], ENT_QUOTES) ?>">
                    Edit
                  </button>

                  <form method="POST" action="model/exp_list_actions.php" style="display:inline;"
                        onsubmit="return confirm('Change the status of this expense?');">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$exp['id'] ?>">
                    <button type="submit" class="btn btn-sm <?= $exp['status'] === 'active' ? 'btn-warning' : 'btn-success' ?>">
                      <?= $exp['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                    </button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>

<?php require 'partials/footer.php'; ?>

<!-- Modal -->
<div class="modal fade" id="modalExpense" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-primary"><strong id="modalTitle">Expenses Registration Window</strong></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" class="text-danger"><strong>&times;</strong></span>
        </button>
      </div>
      <div class="modal-body">
        <form method="POST" action="model/exp_list_actions.php" id="expenseForm">
          <input type="hidden" name="action" id="formAction" value="add">
          <input type="hidden" name="id" id="expId" value="">

          <div class="form-group">
            <label for="expName">Expenses</label>
            <input id="expName" class="form-control" type="text" name="name" maxlength="150" required>
          </div>

          <button type="submit" class="btn btn-primary" id="action-btn"><strong>Save</strong></button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {

  // Search / sort / paging on the plain HTML table
  $('#usersTable').DataTable({ pageLength: 100 });

  // Add: reset the form
  $('#addBtn').on('click', function () {
    $('#formAction').val('add');
    $('#expId').val('');
    $('#expName').val('');
    $('#modalTitle').text('Expenses Registration Window');
    $('#action-btn').html('<strong>Save</strong>');
  });

  // Edit: fill the form from the row's data attributes
  $(document).on('click', '.edit-btn', function () {
    $('#formAction').val('edit');
    $('#expId').val($(this).data('id'));
    $('#expName').val($(this).data('name'));
    $('#modalTitle').text('Edit Expense');
    $('#action-btn').html('<strong>Update</strong>');
    $('#modalExpense').modal('show');
  });

  <?php if ($flash): ?>
  Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true
  }).fire({
    icon: <?= json_encode($flash['type'] === 'success' ? 'success' : 'error') ?>,
    title: <?= json_encode($flash['msg']) ?>
  });
  <?php endif; ?>
});
</script>