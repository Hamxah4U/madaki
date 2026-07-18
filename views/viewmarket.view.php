<?php	
	require 'model/Database.php';    
        require 'partials/security.php'; 
        require 'partials/header.php';

    if (!isset($_GET['marketId']) || empty($_GET['marketId'])) {
      require 'controllers/404.php';
      exit();
    }
    $code = (int)$_GET['marketId'];

    $stmtmarket = $db->conn->prepare("
        SELECT 
            d.Department AS animal_name,
            m3.name AS currentMarketName,
            m.market_name AS oldmarket,
            mt.*
        FROM market_transaction mt
        LEFT JOIN market m ON m.id = mt.market_code 
        LEFT JOIN market_3 m3 ON m3.id = mt.market_id
        LEFT JOIN department_tbl d ON d.deptID = mt.animal_id
        WHERE mt.market_code = :market_code
        ORDER BY m3.name ASC, mt.date_create DESC, mt.sn_number ASC
    ");

    $stmtmarket->execute([
        ':market_code' => $code
    ]);

    $rowmarkets = $stmtmarket->fetchAll();

    $currentMarketGroup = '';
    $currentDateGroup = '';
    $groupTotal = 0;
    $grandTotal = 0;

    
    $animalAmountAubtoal = 0;

    $market = $db->conn->prepare("SELECT * FROM `market` WHERE `id` = :id");
    $market->execute([':id' => $_GET['marketId']]);
    $currentMarket = $market->fetch();

    $market3 = $db->conn->prepare("SELECT * FROM `market_3` ORDER BY `name`");
    $market3->execute();
    $rowMarket3 = $market3->fetchAll(); 
    
     $stmtS = $db->conn->prepare("SELECT * FROM `market` WHERE agent_id = ? OR secondagent = ?");
      $stmtS->execute([$_SESSION['userID'], $_SESSION['userID']]);
      $rowS = $stmtS->fetch();
      
      $fagent = $rowS['agent_id'] ?? null;
      $sagent = $rowS['secondagent'] ?? null;
      $superRole = $_SESSION['super_role'] ?? null;

?>

<style>
  @media print {

    .actionColumn,
    .editBtn,
    .deleteBtn,
    .btn {
      display: none !important;
    }

    /* Utility to hide specific table rows during group printing */
    .d-none-print {
      display: none !important;
    }
  }

  /* Card & Inputs Refinements */
  .card {
      border-radius: 8px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04) !important;
  }

  .form-control:focus {
      box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
      border-color: #4e73df;
  }

  /* Rounded Close Action Buttons */
  .removeRow {
      width: 32px;
      height: 32px;
      padding: 0;
      line-height: 30px;
      text-align: center;
      border-radius: 50% !important;
      transition: all 0.2s ease;
  }

  .removeRow:hover {
      background-color: #e74a3b;
      color: #fff;
  }

  /* Custom Alternative Button Styling */
  .btn-dark-alt {
      background-color: #2c3e50;
      color: #fff;
      border-color: #2c3e50;
      transition: all 0.2s ease;
  }
  .btn-dark-alt:hover {
      background-color: #1a252f;
      color: #fff;
  }

  /* Table Design Overrides */
  #peopleTable th {
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #e3e6f0;
  }
  #peopleTable td {
      vertical-align: middle;
  }

  /* Select2 Custom Design Integration overrides */
  .select2-container--default .select2-selection--single {
      border: 1px solid #d1d3e2 !important;
      height: calc(1.5em + .75rem + 2px) !important;
      padding: .375rem .75rem !important;
      border-radius: 0.35rem !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 1.5 !important;
      color: #6e707e !important;
      padding-left: 0 !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 38px !important;
  }
</style>

<!-- Page Wrapper -->
<div id="wrapper">
  <!-- Sidebar -->
  <div class="actionColumn"> <?php require 'partials/sidebar.php' ?></div>

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

        <div class="container-fluid" id="animalFormSection">
                <div class="table-responsive">
                  <div class="card shadow-sm border-0 mb-4 no-print">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 text-primary font-weight-bold">
                    <i class="fas fa-plus-circle mr-2"></i> Record New Animal Entry
                </h5>
            </div>
            <div class="card-body">
                <form id="animalForm" method="POST">
                    <input type="text" name="market_code" value="<?= htmlspecialchars($_GET['marketId']) ?>" hidden>
                    <input type="hidden" id="edit_id" name="edit_id" value="<?= $editData['id'] ?? '' ?>">
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle border" id="peopleTable">
                            <thead class="bg-light text-secondary font-weight-bold">
                                <tr>
                                    <th style="width: 120px;"># / Number</th>
                                    <th>Animal</th>
                                    <th style="width: 180px;">Amount</th>
                                    <th class="actionColumn text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr>
                                    <td>
                                        <input required type="number" name="number[]" class="form-control form-control-alternative" placeholder="E.g. 1">
                                    </td>
                                    <td>
                                        <select name="animal[]" class="form-control animal-select w-100">
                                            <option value="">-- select animal --</option>
                                            <?php
                                            $stmt = $db->conn->prepare("SELECT * FROM `department_tbl` ORDER BY Department");
                                            $stmt->execute();
                                            $rows = $stmt->fetchAll();
                                            foreach($rows as $row) : ?>
                                                <option value="<?= $row['deptID'] ?>"><?= htmlspecialchars($row['Department']) ?></option>
                                            <?php endforeach ?>
                                        </select>
                                        <span class="text-danger small mt-1 d-block" id="animalError"></span>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0">₦</span>
                                            </div>
                                            <input type="number" name="amount[]" value="<?= $editData['total'] ?? '' ?>" class="form-control pl-1" placeholder="0.00">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle removeRow" title="Remove line row">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Control Toolbar Rows -->
                    <div class="row align-items-center mb-3">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <button type="button" class="btn btn-outline-primary btn-block" id="addRow">
                                <i class="fas fa-plus mr-1"></i> Add Animal
                            </button>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="created_date[]" class="form-control" required value="<?= $editData['date_create'] ?? null ?>">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                
                                <select name="market_id[]" class="form-control market-select" required>
                                    <option value="">-- select market target --</option>
                                    <?php foreach($rowMarket3 as $rowMarket3_1): ?>
                                        <option value="<?php echo $rowMarket3_1['id'] ?>"><?php echo htmlspecialchars($rowMarket3_1['name']) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end align-items-center flex-wrap" style="gap: 12px;">
                        <button type="button" class="btn btn-dark-alt px-4 no-print shadow-sm" onclick="printDiv('printArea')">
                            <i class="fas fa-print mr-2"></i> Print Complete Statement
                        </button>
                        <?php if($fagent == $_SESSION['userID'] || $superRole == 'Super Admin'): ?>
                            <button type="submit" name="save" class="btn btn-primary px-5 shadow-sm font-weight-bold" id="saveBtn">
                                <i class="fas fa-check-circle mr-2"></i> Submit Record
                            </button>
                        <?php endif ?>
                    </div>              
                </form>
            </div>
        </div>
          
          <div class="print-container" id="printArea">
            <!-- Header -->
            <div class="print-header">
              <h3>BASHIR MADAKI TRANSPORTATION RECORD for <strong><?= $currentMarket['market_name'] ?></strong></h3>
              <small><?= date('d M Y') ?></small>
            </div>

            <div id="printHeading" style="display:none;text-align:center;margin-bottom:20px;">
              <h3 id="groupTitle">BASHIR MADAKI TRANSPORTATION RECORD for
                <strong><?= $currentMarket['market_name'] ?></strong></h3>
            </div>

            <table class="table table-bordered text-nowrap">
              <thead>
                <tr>
                  <th>Number</th>
                  <th>Amount</th>
                  <th>Animal</th>
                  <th class="no-print">Action</th>
                </tr>
              </thead>

              <tbody>

                <?php foreach($rowmarkets as $index => $rowmarket): ?>

                <?php
                  // Extract just the date part (YYYY-MM-DD) in case it contains a timestamp
                  $transactionDate = date('Y-m-d', strtotime($rowmarket['date_create']));
                  $displayDate = date('d M Y', strtotime($transactionDate));

                  // Trigger group split if either market name OR date changes
                  if($currentMarketGroup != $rowmarket['currentMarketName'] || $currentDateGroup != $transactionDate):

                      // Close previous group with subtotal
                      if($currentMarketGroup != ''):
                ?>
                <tr style="background:#ffeeba; font-weight:bold;">
                  <td colspan="1">Subtotal</td>
                  <td>₦<?= number_format($groupTotal, 2) ?></td>
                  <td colspan="2"></td>
                </tr>
                <?php
                    $groupTotal = 0;
                    endif;

                    // Update tracking variables to the current row's data
                    $currentMarketGroup = $rowmarket['currentMarketName'];
                    $currentDateGroup = $transactionDate;
                    
                    // Combine them into a single string label for headers and print selection matching
                    $combinedGroupLabel = $currentMarketGroup . " (" . $displayDate . ")";
                ?>

                <!-- Group Header -->
                <tr class="table-dark text-primary groupHeader">
                  <th colspan="4">
                    <strong><?= $combinedGroupLabel ?></strong>
                    <button type="button" class="btn btn-dark btn-sm printGroupBtn no-print"
                      data-group="<?= $combinedGroupLabel ?>">
                      Print
                    </button>
                  </th>
                </tr>

                <?php endif; ?>

                <?php
                    $groupTotal += $rowmarket['amount'];
                    $grandTotal += $rowmarket['amount'];
                ?>
                <tr id="row<?= $rowmarket['id'] ?>">
                  <td><?= $rowmarket['sn_number'] ?></td>
                  <td><?= number_format($rowmarket['amount'], 2) ?></td>
                  <td><?= $rowmarket['animal_name'] ?></td>
                  <td class="no-print">
                    <button type="button" class="btn btn-info btn-sm editBtn" data-id="<?= $rowmarket['id'] ?>"
                      data-animal="<?= $rowmarket['animal_id'] ?>" data-amount="<?= $rowmarket['amount'] ?>"
                      data-date="<?= date('Y-m-d', strtotime($rowmarket['date_create'])) ?>"
                      data-market="<?= $rowmarket['market_id'] ?>" data-number="<?= $rowmarket['sn_number'] ?>">
                      Edit
                    </button>
                    <button type="button" class="btn btn-danger btn-sm deleteBtn"
                      data-id="<?= $rowmarket['id'] ?>">Delete</button>
                  </td>
                </tr>

                <?php endforeach; ?>

                <!-- Last Group Subtotal -->
                <tr style="background:#ffeeba; font-weight:bold;">
                  <td colspan="1">Subtotal</td>
                  <td>₦<?= number_format($groupTotal, 2) ?></td>
                  <td colspan="2"></td>
                </tr>

                <!-- Grand Total -->
                <tr style="background:#f1f1f1; font-weight:bold;">
                  <td colspan="1">Grand Total</td>
                  <td>₦<?= number_format($grandTotal, 2) ?></td>
                  <td colspan="2">
                    <?php
                        $stmtEx = $db->conn->prepare('SELECT COALESCE(SUM(`amount`), 0) AS tt_ex FROM `expenses` WHERE `status` = \'exp\' AND driver_id = :id AND agent_id IS NOT NULL AND agent_id != 0;');
                        $stmtEx->execute(['id' => $_GET['marketId']]);
                        $agentExpenses = $stmtEx->fetch(PDO::FETCH_ASSOC);

                        $over_or_short = $agentExpenses['tt_ex'] - $grandTotal;
                        // echo $over_or_short < 0 ? 'Over' : 'Short';
                        if($over_or_short > 0) {
                            echo '<span class="text-success">Over: ₦' . number_format(abs($over_or_short), 2) . '</span>';
                        } elseif ($over_or_short < 0) {
                            echo '<span class="text-danger">Short: ₦' . number_format($over_or_short, 2) . '</span>';
                        } else {
                            echo '<span class="text-primary">Balanced</span>';
                        }

                    ?>
                  </td>
                </tr>

              </tbody>

            </table>

            <div class="">
              <div class="row">
                <div class="col-sm-6" id="expenses">
                  <div class="table-responsive">
                  <table class="table table-bordered text-nowrap">
                    <thead>
                      <tr>
                        <?php 
                          $stmt = $db->conn->prepare('SELECT * FROM market WHERE agent_id = :agent_id ');
                          $stmt->execute(['agent_id' => $_SESSION['userID'], ]);
                          $market = $stmt->fetch(PDO::FETCH_ASSOC);
                          if($market && $market['agent_id'] == $_SESSION['userID'] ||  $_SESSION['super_role'] == 'Super Admin'): ?>
                          
                          <button class="btn btn-primary" type="button" data-target="#modalUser"
                            data-toggle="modal"><strong>Expenses</strong></button>
                          <button class="btn btn-dark no-print" onclick="expenses('expenses')">Print</button>
                        <?php else: ?>
                          <span class="text-danger">You are not authorized to add expenses.</span>
                        <?php endif ?>                        
                      </tr>
                      <tr>
                        <th>#</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th class="no-print">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $exponly = 0;
                        $pending_total = 0;
                        $exp = $db->conn->prepare("SELECT * FROM `expenses` WHERE `fstatus` = 'market' AND `status` = 'exp' AND driver_id = :id AND agent_id IS NOT NULL AND agent_id != 0 ");
                        $exp->execute(['id' => $_GET['marketId']]);
                        $row_exps = $exp->fetchALL();
                        foreach ($row_exps as $index => $row_exp) :
                          // $exponly  += $row_exp['amount'];
                          if ($row_exp['pstatus'] == 'approved') {
                              $exponly += $row_exp['amount'];
                          } elseif ($row_exp['pstatus'] == 'pending') {
                              $pending_total += $row_exp['amount'];
                          }                                 
                        ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $row_exp['reason'] ?></td>
                        <td><?= number_format($row_exp['amount']) ?></td>
                        <td><?= $row_exp['daterecorded'] ?></td>
                        <td><?= $row_exp['timerecorded'] ?></td>
                        <td class="no-print">
                          <?php
                              if ($row_exp['pstatus'] == 'approved') {
                                  echo "<button class='btn btn-sm btn-success' disabled>Approved</button>";
                              } elseif ($row_exp['pstatus'] == 'pending' && ($superRole == 'Super Admin' || $_SESSION['userID'] == $sagent)) {
                                  echo '<a href="/approved-exp?id=' . $row_exp['id'] . '&tid=' . $_GET['marketId'] . '" class="btn btn-sm btn-warning" onclick="return confirm(\'Mark this expense as paid?\')">Click to Approve</a>';
                              } else {
                                  echo '<span class="badge badge-secondary">Pending Approval</span>';
                              }
                          ?>
                                                    
                          <?php  if($market && $market['agent_id'] == $_SESSION['userID'] ||  $_SESSION['super_role'] == 'Super Admin'):  ?>
                            <?php if($row_exp['pstatus'] == 'pending') : ?>
                            <a href="/delete-only-exp?id=<?= $row_exp['id'] ?>&tid=<?= $_GET['marketId'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                            <button class="btn btn-info editExpenseBtn" data-toggle="modal" data-target="#modalUser" data-id="<?= $row_exp['id'] ?>" data-amount="<?= $row_exp['amount'] ?>" data-reason="<?= htmlspecialchars($row_exp['reason']) ?>">Edit</button>                          <?php endif ?>

                           <?php endif ?>
                        </td>
                      </tr>
                      <?php endforeach ?>
                    </tbody>
                    <tfoot>
                      <!-- Approved Sum Row -->
                      <tr class="text-success" style="font-size: 1.05rem;">
                        <td colspan="2" class="text-right"><strong>Total Approved:</strong></td>
                        <td colspan="4"><strong>₦<?= number_format($exponly) ?></strong></td>
                      </tr>

                      <!-- Pending Sum Row -->
                      <tr class="text-warning" style="font-size: 1.05rem;">
                        <td colspan="2" class="text-right"><strong>Total Pending:</strong></td>
                        <td colspan="4"><strong>₦<?= number_format($pending_total) ?></strong></td>
                      </tr>

                      <!-- Grand Total (Combined) Row -->
                      <tr style="background: #e9ecef; font-size: 1.1rem; border-top: 1px solid #ccc; border-bottom: 2px double #6c757d;">
                        <td colspan="2" class="text-right text-dark"><strong>Grand Total:</strong></td>
                        <td colspan="4" class="text-dark"><strong>₦<?= number_format($exponly + $pending_total) ?></strong></td>
                      </tr>
                    </tfoot>
                    
                  </table>
                  </div>
                </div>

                <div class="col-sm-6" id="other_expenses">
                  <div class="table-responsive">
                  <table class="table table-bordered text-nowrap">
                    <thead>                      
                      <tr>
                        <?php 
                          // 1. Get the current Market ID from the URL (adjust 'marketId' if your URL parameter uses 'id')
                          $currentMarketId = $_GET['marketId'] ?? $_GET['id'] ?? null;

                          // 2. Target the specific market AND the logged-in second agent
                          $stmt = $db->conn->prepare("SELECT * FROM market WHERE id = :market_id AND secondagent = :secondagent");
                          $stmt->execute([
                              'market_id'   => $currentMarketId,
                              'secondagent' => $_SESSION['userID']
                          ]);
                          $market = $stmt->fetch(PDO::FETCH_ASSOC);

                          // 3. Check roles and permissions
                          $superRole = $_SESSION['super_role'] ?? null;
                          if (($market && $market['secondagent'] == $_SESSION['userID']) || $superRole == 'Super Admin'): 
                        ?>
    
                          <button type="button" data-target="#modelOtherExpenses" data-toggle="modal" class="btn btn-primary">
                            <strong>Other Expenses</strong>
                          </button>
                          <button class="btn btn-dark no-print" onclick="other_expenses('other_expenses')">Print</button>
                          
                        <?php else: ?>
                          <span class="text-danger">You are not authorized to add other expenses.</span>                                                    
                        <?php endif ?>
                      </tr>    
                      <tr>
                        <th>#</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th class="no-print">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $exponlyOtherEx = 0;
                        $pendingExpTotal = 0;
                        // $exp = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'other_exp' AND driver_id = :id");
                        $exppp = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'other_exp' AND driver_id = :id AND driver_id IS NOT NULL AND driver_id != 0 ");
                        $exppp->execute(['id' => $_GET['marketId']]);
                        $row_exps = $exppp->fetchALL();
                        foreach ($row_exps as $index => $row_exp) :
                          
                          if ($row_exp['pstatus'] == 'approved') {
                              $exponlyOtherEx += $row_exp['amount'];
                          } elseif ($row_exp['pstatus'] == 'pending') {
                              $pendingExpTotal += $row_exp['amount'];
                          } 
                        ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $row_exp['reason'] ?></td>
                        <td><?= number_format($row_exp['amount']) ?></td>
                        <td><?= $row_exp['daterecorded'] ?></td>
                        <td><?= $row_exp['timerecorded'] ?></td>
                        <td class="no-print">

                          <?php
                            if ($row_exp['pstatus'] == 'approved') {
                                echo "<button class='btn btn-sm btn-success' disabled>Approved</button>";
                            } elseif ($row_exp['pstatus'] == 'pending' && $superRole || $_SESSION['userID'] == $fagent) {
                                echo '<a href="/approved-exp?id=' . $row_exp['id'] . '&tid=' . $_GET['marketId'] . '" class="btn btn-sm btn-warning" onclick="return confirm(\'Mark this expense as paid?\')">Click to Approve</a>';
                            } else {
                                echo '<span class="badge badge-secondary">Pending Approval</span>';
                            }
                          ?>

                          <?php if($row_exp['pstatus'] == 'pending'): ?>
                            <a href="/delete-other-expenses?id=<?= $row_exp['id'] ?>&tid=<?= $_GET['marketId'] ?>"
                              class="btn btn-sm btn-danger no-print"
                              onclick="return confirm('Delete this record?')">Delete</a>
                          <button class="btn btn-info editOtherExpenseBtn" data-toggle="modal" data-target="#modelOtherExpenses" data-id="<?= $row_exp['id'] ?>" data-amount="<?= $row_exp['amount'] ?>" data-reason="<?= htmlspecialchars($row_exp['reason']) ?>">Edit</button>                          <?php endif ?>
                        
                        </td>
                      </tr>
                      <?php endforeach ?>
                    </tbody>
                    <tfoot>
                     <!-- Approved Sum Row -->
                      <tr class="text-success" style="font-size: 1.05rem;">
                        <td colspan="2" class="text-right"><strong>Total Approved:</strong></td>
                        <td colspan="4"><strong>₦<?= number_format($exponlyOtherEx) ?></strong></td>
                      </tr>

                      <!-- Pending Sum Row -->
                      <tr class="text-warning" style="font-size: 1.05rem;">
                        <td colspan="2" class="text-right"><strong>Total Pending:</strong></td>
                        <td colspan="4"><strong>₦<?= number_format($pendingExpTotal) ?></strong></td>
                      </tr>

                      <!-- Grand Total (Combined) Row -->
                      <tr style="background: #e9ecef; font-size: 1.1rem; border-top: 1px solid #ccc; border-bottom: 2px double #6c757d;">
                        <td colspan="2" class="text-right text-dark"><strong>Grand Total:</strong></td>
                        <td colspan="4" class="text-dark"><strong>₦<?= number_format($exponlyOtherEx + $pendingExpTotal) ?></strong></td>
                      </tr>
                    </tfoot>
                  </table>
                  </div>
                </div> 

              </div>

              <div class="row">
                <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Agent'): ?>
                <div class="col-sm-6" id="comments">
                  <div class="table-responsive">
                    <table class="table table-bordered text-nowrap">
                      <thead>
                        <tr>
                          <?php if ($superRole == 'Super Admin' || $_SESSION['userID'] == $fagent): ?>
                          <button type="button" data-target="#modelComment" data-toggle="modal" class="btn btn-primary"><strong>Comments</strong></button>
                          <button class="btn btn-dark no-print" onclick="comments('comments')">Print</button>
                          <?php else: ?>
                            <span class="text-danger">You are not authorized</span>
                          <?php endif ?>
                        </tr>
                        <tr>
                          <th>#</th>
                          <th>Comment</th>
                          <th>Amount</th>
                          <th>Date</th>
                          <th>Time</th>
                          <th class="no-print">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $comment = $db->conn->prepare("SELECT * FROM `expenses` WHERE `fstatus` = 'market' AND `status` = 'comment' AND driver_id = :id");
                          $comment->execute(['id' => $_GET['marketId']]);
                          $row_comments = $comment->fetchALL();
                          $totalAmountForComment = 0;
                          foreach ($row_comments as $index => $row_comment) :
                              $totalAmountForComment += $row_comment['amount']; 
                        ?>
                        <tr>
                          <td><?= $index + 1 ?></td>
                          <td><?= $row_comment['reason'] ?></td>
                          <td class="text-danger"><?= number_format($row_comment['amount']) ?></td>
                          <td><?= date('d M Y', strtotime($row_comment['daterecorded'])) ?></td>
                          <td><?= date('h:i A', strtotime($row_comment['timerecorded'])) ?></td>
                          <td class="no-print">
                            <a href="/delete-comment?id=<?= $row_comment['id'] ?>&tid=<?= $_GET['marketId'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                            <button class="btn btn-info btn-edit-comment" data-id="<?= $row_comment['id'] ?>" data-comment="<?= htmlspecialchars($row_comment['reason']) ?>" data-amount="<?= $row_comment['amount'] ?>">Edit</button>
                          </td>
                        </tr>
                        <?php endforeach ?>
                        <tfoot>
                          <tr style="background:#f1f1f1; font-weight:bold;">
                            <td colspan="2">Total</td>
                            <td colspan="4">₦<?= number_format($totalAmountForComment) ?></td>
                          </tr>
                        </tfoot>
                      </tbody>
                    </table>
                  </div>
                </div>
                <?php endif ?>

                <div class="col-sm-6" id="other_comments">
                  <div class="table-responsive">
                    <table class="table table-bordered text-nowrap">
                      <thead>
                        <tr>
                          <?php if ($superRole == 'Super Admin' || $_SESSION['userID'] == $sagent): ?>
                          <button type="button" data-target="#modelotherComment" data-toggle="modal" class="btn btn-primary"><strong>Other Comments</strong></button>
                          <button class="btn btn-dark no-print" onclick="other_comments('other_comments')">Print</button>
                          <?php else: ?>
                            <span class="text-danger">You are not authorized</span>
                          <?php endif ?>
                          
                        </tr>
                        <tr>
                          <th>#</th>
                          <th>Other Comment</th>
                          <th>Date</th>
                          <th>Time</th>
                          <th class="no-print">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $comment = $db->conn->prepare("SELECT * FROM `expenses` WHERE `fstatus` = 'market' AND `status` = 'other_comment' AND driver_id = :id");
                          $comment->execute(['id' => $_GET['marketId']]);
                          $row_comments = $comment->fetchALL();
                          foreach ($row_comments as $index => $row_comment) : 
                        ?>
                        <tr>
                          <td><?= $index + 1 ?></td>
                          <td><?= $row_comment['reason'] ?></td>
                          <td><?= date('d M Y', strtotime($row_comment['daterecorded'])) ?></td>
                          <td><?= date('h:i A', strtotime($row_comment['timerecorded'])) ?></td>
                          <td class="no-print">
                            <?php if ($_SESSION['role'] == 'Admin'): ?>
                            <a href="/delete-comment?id=<?= $row_comment['id'] ?>&tid=<?= $_GET['marketId'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                            <button class="btn btn-info btn-edit-othercomment" data-id="<?= $row_comment['id'] ?>" data-comment="<?= htmlspecialchars($row_comment['reason']) ?>">Edit</button>
                            <?php endif ?>
                          </td>
                        </tr>
                        <?php endforeach ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <?php if ($_SESSION['role'] == 'Admin'): ?>
              <div class="row">
                <div class="col-sm-12" id="diary">
                  <div class="table table-responsive">
                    <table class="table table-bordered text-nowrap">
                      <thead>
                        <tr><button type="button" data-target="#modeldiary" data-toggle="modal" class="btn btn-primary"><strong>Diary</strong></button>
                          <button class="btn btn-dark no-print" onclick="diary('diary')">Print</button>
                        </tr>
                        <tr>
                          <th>#</th>
                          <th>Diary Note</th>
                          <th>Date</th>
                          <th>Time</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                          $diary = $db->conn->prepare("SELECT * FROM `expenses` WHERE `fstatus` = 'market' AND `status` = 'diary' AND driver_id = :id");
                          $diary->execute(['id' => $_GET['marketId']]);
                          $row_diarys = $diary->fetchALL();
                          foreach ($row_diarys as $index => $row_diary) : 
                        ?>
                        <tr>
                          <td><?= $index + 1 ?></td>
                          <td><?= $row_diary['reason'] ?></td>
                          <td><?= $row_diary['daterecorded'] ?></td>
                          <td><?= $row_diary['timerecorded'] ?></td>
                          <td class="no-print">
                            <a href="/delete-other-expenses?id=<?= $row_diary['id'] ?>&tid=<?= $_GET['marketId'] ?>" class="btn btn-sm btn-danger no-print" onclick="return confirm('Delete this record?')">Delete</a>
                            <button class="btn btn-info btn-edit-diary" data-id="<?= $row_diary['id'] ?>" data-comment="<?= htmlspecialchars($row_diary['reason']) ?>">Edit</button>
                          </td>
                        </tr>
                        <?php endforeach ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <?php endif ?>

            </div>
          </div>
        </div>
      </div>

    </div>
    <!-- End of Main Content -->

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Payment Receipt</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body" id="receiptContent">
            Loading...
          </div>

          <div class="modal-footer">
            <button onclick="printReceipt()" class="btn btn-primary">Print</button>
            <button onclick="shareWhatsApp()" class="btn btn-success">WhatsApp</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button class="btn btn-success" id="whatsappPdfBtn">
              WhatsApp PDF <span id="phoneLabel"></span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- model -->
     <div class="modal fade" id="modeldiary" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="fas fa-comment"></i> Add Diary
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <form id="diarForm">
              <input type="hidden" name="id" id="diary_id">
              <input type="text" name="fstatus" id="fstatus" value="market" hidden>

              <div class="form-group">
                <label><strong>Diary</strong></label>
                <textarea id="diary_text" name="comment" class="form-control" rows="4" required></textarea>
                <small class="text-danger" id="errorComment"></small>
              </div>

              <input type="hidden" name="transport_id" value="<?= $_GET['marketId'] ?? '' ?>">

              <div class="modal-footer p-0 pt-3">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i> Save Diary
                </button>
                
              </div>

            </form>
          </div>

        </div>
      </div>
    </div>

    <div class="modal fade" id="modelotherComment" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="fas fa-comment"></i> Add Other Comment
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <form id="othercommentForm">
              <input type="text" name="fstatus" id="fstatus" value="market" hidden>
              <input type="hidden" name="id" id="othercomment_id" hidden>

              <div class="form-group">
                <label><strong>Other Comment</strong></label>
                <textarea name="comment" id="othercomment_text" class="form-control" rows="4" required></textarea>
                <small class="text-danger" id="errorComment"></small>
              </div>

              <input hidden type="text" name="transport_id" value="<?= $_GET['marketId'] ?? '' ?>">

              <div class="modal-footer p-0 pt-3">
                <button type="submit" class="btn btn-primary" id="othercomment-btn" data-mode="add">
                  <i class="fas fa-save"></i> Save Comment
                </button>
               
              </div>

            </form>
          </div>

        </div>
      </div>
    </div>

    <div class="modal fade" id="modelComment" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="fas fa-comment"></i> Add Comment
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <form id="commentForm">
              <input type="text" name="id" id="comment_id" hidden>
              <input type="text" name="fstatus" id="fstatus" value="market" hidden>
              <div class="form-group">
                <label><strong>Amount</strong></label>
                <input type="number" id="commentAmount" name="amount" class="form-control" required>
              </div>
              <div class="form-group">
                <label><strong>Comment</strong></label>
                <textarea name="comment" id="comment_text" class="form-control" required></textarea>
                <small class="text-danger" id="errorComment"></small>
              </div>

              <input type="hidden" name="transport_id" value="<?= $_GET['marketId'] ?? '' ?>">

              <div class="modal-footer p-0 pt-3">
                <button type="submit" class="btn btn-primary" id="comment-btn" data-mode="add">
                  <i class="fas fa-save"></i> Save Comment
                </button>
               
              </div>

            </form>
          </div>

        </div>
      </div>
    </div>

    <div class="modal fade" id="modalUser" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-primary"><strong>Expenses Window</strong></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true" class="text-danger"><strong>&times;</strong></span>
            </button>
          </div>
          <div class="modal-body">
            <form id="userForm">
              <input type="text" name="id" id="edit_id" hidden>
              <input type="text" name="userID" id="userID" value="<?= $_GET['marketId'] ?>" hidden>
              <input type="text" name="fstatus" id="fstatus" value="market" hidden>
              <?php
                  $stmtAgent = $db->conn->prepare('SELECT * FROM market WHERE id = :id');
                  $stmtAgent->execute([':id' => $_GET['marketId']]);
                  $row = $stmtAgent->fetch();
              ?>
              <input type="text" name="agent_id" id="" value="<?= $row['agent_id']; ?>" hidden>

              <div class="form-group">
                <label for="my-input">Amount</label>
                <input id="Amount" class="form-control" type="number" name="amount">
                <small class="text-danger" id="errorAmount"></small>
              </div>

              <div class="form-group">
                <label for="my-input">Reason</label>
                <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                <small class="text-danger" id="errorReason"></small>
              </div>
              <button type="submit" class="btn btn-primary" id="action-btn"
                data-mode='add'><strong>Save</strong></button>
            </form>
          </div>
        </div>
      </div>
    </div>


    <div class="modal fade" id="modelOtherExpenses" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-primary"><strong>Other Expenses</strong></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true" class="text-danger">&times;</span>
            </button>
          </div>
          <div class="modal-body">
             <input type="text" name="fstatus" id="fstatus" value="market" hidden>
            <form id="formUnit">
              
             

              <input type="text" name="id" id="edit_id" hidden>
              <input type="text" name="userID" id="userID" value="<?= $_GET['marketId'] ?>" hidden>
              <input type="text" name="fstatus" id="fstatus" value="market" hidden>
              <input type="text" name="agent_id" id="" hidden>
              
              <!-- 2. Add a hidden mode tracking input field -->
              <input type="hidden" name="mode" id="other_mode" value="add">

              <div class="form-group">
                <label for="my-input">Amount</label>
                <input id="other_amount_field" class="form-control" type="number" name="amount" required>
                <small class="text-danger" id="errorAmount"></small>
              </div>
              <input type="hidden" name="transport_id" value="<?= $transport_id ?? '' ?>">
              <div class="form-group">
                <label for="my-input">Reason</label>
                <textarea name="reason" id="other_reason_field" class="form-control" rows="3" required></textarea>
                <small class="text-danger" id="errorReason"></small>
              </div>
              
              <!-- 3. Changed ID from 'action-btn' to 'other-action-btn' -->
              <button type="submit" class="btn btn-primary" id="other-action-btn" data-mode='add'><strong>Save</strong></button>
            </form>
          </div>
        </div>
      </div>
    </div>


<?php require 'partials/footer.php'; ?>

<script>
  function comments() {
      var content = document.getElementById('comments').innerHTML;
      var win = window.open('', '', 'width=900,height=650');

      win.document.write(`
                <html>
                <head>
                    <title><?= $driverInfo['driver_name'] ?>>Transportation Record</title>
                    <style>
                        @page { size: A4; margin: 10mm; }

                        body {
                            font-family: Arial, sans-serif;
                            font-size: 14px;
                            padding: 10px;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 10px;
                        }

                        th, td {
                            border: 1px solid #000;
                            padding: 5px;
                            text-align: left;
                        }

                        th {
                            background: #f2f2f2;
                            /*text-align: center;*/
                        }

                        .print-header {
                            /*text-align: center;*/
                            margin-bottom: 10px;
                        }

                        .print-header h3 {
                            margin: 0;
                            font-size: 18px;
                        }

                        .no-print { display:none; }
                        .overpaid { background:#f8d7da; }
                        .table-success { background:#d4edda; }
                    </style>
                </head>
                <body>
                    ${content}
                </body>
                </html>
            `);

      win.document.close();
      win.focus();
      win.print();
    }

    function other_comments() {
      var content = document.getElementById('other_comments').innerHTML;
      var win = window.open('', '', 'width=900,height=650');

      win.document.write(`
                <html>
                <head>
                    <title><?= $driverInfo['driver_name'] ?>>Transportation Record</title>
                    <style>
                        @page { size: A4; margin: 10mm; }

                        body {
                            font-family: Arial, sans-serif;
                            font-size: 14px;
                            padding: 10px;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 10px;
                        }

                        th, td {
                            border: 1px solid #000;
                            padding: 5px;
                            text-align: left;
                        }

                        th {
                            background: #f2f2f2;
                            /*text-align: center;*/
                        }

                        .print-header {
                            /*text-align: center;*/
                            margin-bottom: 10px;
                        }

                        .print-header h3 {
                            margin: 0;
                            font-size: 18px;
                        }

                        .no-print { display:none; }
                        .overpaid { background:#f8d7da; }
                        .table-success { background:#d4edda; }
                    </style>
                </head>
                <body>
                    ${content}
                </body>
                </html>
            `);

      win.document.close();
      win.focus();
      win.print();
    }

    function diary() {
      var content = document.getElementById('diary').innerHTML;
      var win = window.open('', '', 'width=900,height=650');

      win.document.write(`
                <html>
                <head>
                    <title><?= $driverInfo['driver_name'] ?>>Transportation Record</title>
                    <style>
                        @page { size: A4; margin: 10mm; }

                        body {
                            font-family: Arial, sans-serif;
                            font-size: 14px;
                            padding: 10px;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 10px;
                        }

                        th, td {
                            border: 1px solid #000;
                            padding: 5px;
                            text-align: left;
                        }

                        th {
                            background: #f2f2f2;
                            /*text-align: center;*/
                        }

                        .print-header {
                            /*text-align: center;*/
                            margin-bottom: 10px;
                        }

                        .print-header h3 {
                            margin: 0;
                            font-size: 18px;
                        }

                        .no-print { display:none; }
                        .overpaid { background:#f8d7da; }
                        .table-success { background:#d4edda; }
                    </style>
                </head>
                <body>
                    ${content}
                </body>
                </html>
            `);

      win.document.close();
      win.focus();
      win.print();
    }
</script>

<script>
  $(document).on('click', '.editExpenseBtn', function() {
        let id = $(this).data('id');
        let amount = $(this).data('amount');
        let reason = $(this).data('reason');

        // For standard expenses modal (#modalUser)
        $('#modalUser #edit_id').val(id);
        $('#modalUser #Amount').val(amount);
        $('#modalUser #reason').val(reason);

        $('#modalUser #action-btn')
            .text('Update')
            .removeClass('btn-primary')
            .addClass('btn-info')
            .attr('data-mode', 'edit');
    });

    $(document).on('click', '.editOtherExpenseBtn', function() {
      let id = $(this).data('id');
      let amount = $(this).data('amount');
      let reason = $(this).data('reason');

      // Update target input values using the uniquely targeted selectors
      $('#modelOtherExpenses #other_edit_id').val(id);
      $('#modelOtherExpenses #other_amount_field').val(amount);
      $('#modelOtherExpenses #other_reason_field').val(reason);
      
      // Set our hidden form variable mode to edit
      $('#modelOtherExpenses #other_mode').val('edit');

      $('#modelOtherExpenses #other-action-btn')
          .text('Update')
          .removeClass('btn-primary')
          .addClass('btn-info')
          .attr('data-mode', 'edit');
    });
</script>

<script type="text/javascript">
      $(document).ready(function() {
          let rowCount = 1;
          
          // // 🔸 RESTORE DYNAMIC ADD PERSON ROWS LISTENERS 
          // $('#addRow').click(function() {
          //     rowCount++;
              
          //     // 1. Create the new row as a jQuery object
          //     let $newRow = $(`
          //     <tr>
          //         <td>${rowCount}</td>
          //         <td><input type="text" name="fullname[]" class="form-control" required></td>
          //         <td>
          //             <select name="market[]" class="form-control select-market" required>
                          
          //             </select>
          //         </td>
          //         <td><input type="number" name="total_animal[]" style="width: 66px;" class="form-control"></td>
          //         <td><input type="number" name="death_animal[]" style="width: 66px;" class="form-control"></td>
          //         <td><input type="number" name="surviving_animal[]" style="width: 66px;" class="form-control"></td>
          //         <td><input type="number" name="first_payment[]" style="width: 85px;" class="form-control"></td>
          //         <td><input type="number" name="second_payment[]" style="width: 85px;" class="form-control"></td>
          //         <td><input type="number" name="third_payment[]" style="width: 85px;" class="form-control"></td>
          //         <td><input type="number" name="total[]" style="width: 86px;" class="form-control"></td>
          //         <td><button style="width: 32px;" type="button" class="btn btn-danger removeRow">X</button></td>
          //     </tr>`);
              
          //     // 2. Append the new row to the table body
          //     $('#tableBody').append($newRow);
              
          //     // 3. Find ONLY the select element in this specific new row and initialize Select2 on it
          //     initSelect2($newRow.find('.select-market'));
          // });

          $(document).on('click', '.removeRow', function() {
              if ($('#tableBody tr').length > 1) {
                  $(this).closest('tr').remove();
              } else {
                  alert("At least one row must remain.");
              }
          });
      });

      // 🔸 HANDLE PRINTING FOR UNIQUE DUPLICATE NAMES
      function printDuplicateSection(customerName) {
          // 1. Extract the table header
          let headerRow = document.querySelector("#printGroupMarket thead") || document.querySelector("table thead");
          let tableHeader = headerRow ? headerRow.innerHTML : "";
          
          // 2. Extract rows matching the specific duplicate customer group
          let rowsHtml = "";
          let matchingRows = document.querySelectorAll(`tr[data-duplicate-group="${customerName}"]`);
          
          matchingRows.forEach(row => {
              rowsHtml += row.outerHTML;
          });

          // Fallback if no matching data is found
          if (!rowsHtml) {
              alert("Could not extract printable data matching: " + customerName);
              return;
          }

          // 3. Extract the top header content if it exists
          let headArea = document.getElementById("headArea") ? document.getElementById("headArea").innerHTML : "";

          // 4. Wrap the data into a structured layout  /*${headArea}*/
          let fullContent = `
              <div class="print-header">
                <center><h1>BASHIR MADAKI TRANSPORTATION  RECEIPT</h1></center>
                  <h3>Group: ${customerName}</h3>
                  <table>
                    <tr>
                        <th>Driver</th>
                        <th>Yan Waju</th>
                        <th>Motor No</th>
                        <th>Date</th>
                    </tr>
                    <tr>
                        <td><?= !empty($driverInfo['driver_name']) ? $driverInfo['driver_name'] : '' ?></td>
                        <td><?= !empty($driverInfo['yan_waju']) ? $driverInfo['yan_waju'] : '' ?></td>
                        <td><?= !empty($driverInfo['bossno']) ? $driverInfo['bossno'] : '' ?></td>
                        <td><?= !empty($driverInfo['deliverydate']) ? $driverInfo['deliverydate'] : '' ?></td>
                    </tr>
                  </table>
              </div>
              <table>
                  <thead>
                      ${tableHeader}
                  </thead>
                  <tbody>
                      ${rowsHtml}
                  </tbody>
              </table>
          `;

          // 5. Open the print window using the reliable mobile approach
          var win = window.open('', '', 'width=900,height=650');

          win.document.write(`
              <!DOCTYPE html>
              <html>
              <head>
                  <title>Duplicate Group Ledger - ${customerName}</title>
                  <style>
                      @page { size: A4; margin: 10mm; }

                      body {
                          font-family: Arial, sans-serif;
                          font-size: 14px;
                          padding: 10px;
                      }

                      table {
                          width: 100%;
                          border-collapse: collapse;
                          margin-bottom: 10px;
                      }

                      th, td {
                          border: 1px solid #000;
                          padding: 5px;
                          text-align: left;
                      }

                      th {
                          background: #f2f2f2;
                      }

                      .print-header {
                          margin-bottom: 10px;
                      }

                      .print-header h3 {
                          margin: 10px 0 0 0;
                          font-size: 18px;
                      }

                      .no-print { display:none; }
                      .overpaid { background:#f8d7da; }
                      .table-success { background:#d4edda; }
                  </style>
              </head>
              <body>
                  ${fullContent}
              </body>
              </html>
          `);

          win.document.close();
          win.focus();
          win.print();
      }

      // 🔸 HANDLE PRINTING FOR MARKET GROUPS
      function printMarketSection(marketName) {
          // 1. Extract the table header
          let headerRow = document.querySelector("#printGroupMarket thead") || document.querySelector("table thead");
          let tableHeader = headerRow ? headerRow.innerHTML : "";
          
          // 2. Extract only the rows matching the specific market
          let rowsHtml = "";
          let matchingRows = document.querySelectorAll(`tr[data-market-group="${marketName}"]`);
          
          matchingRows.forEach(row => {
              rowsHtml += row.outerHTML;
          });

          // Fallback if no data is found
          if (!rowsHtml) {
              alert("Could not extract printable data for market: " + marketName);
              return;
          }

          // 3. Extract the top head area content if it exists
          let headArea = document.getElementById("headArea") ? document.getElementById("headArea").innerHTML : "";

          // 4. Combine the extracted parts into a single structured table
          let fullContent = `
              <div class="print-header">
                  ${headArea}
                  <h3>Market: ${marketName}</h3>
              </div>
              <table>
                  <thead>
                      ${tableHeader}
                  </thead>
                  <tbody>
                      ${rowsHtml}
                  </tbody>
              </table>
          `;

          // 5. Open the print window using your preferred mobile-friendly approach
          var win = window.open('', '', 'width=900,height=650');

          win.document.write(`
              <!DOCTYPE html>
              <html>
              <head>
                  <title>Market Summary Report - ${marketName}</title>
                  <style>
                      @page { size: A4; margin: 10mm; }

                      body {
                          font-family: Arial, sans-serif;
                          font-size: 14px;
                          padding: 10px;
                      }

                      table {
                          width: 100%;
                          border-collapse: collapse;
                          margin-bottom: 10px;
                      }

                      th, td {
                          border: 1px solid #000;
                          padding: 5px;
                          text-align: left;
                      }

                      th {
                          background: #f2f2f2;
                      }

                      .print-header {
                          margin-bottom: 10px;
                      }

                      .print-header h3 {
                          margin: 10px 0 0 0;
                          font-size: 18px;
                      }

                      .no-print { display:none; }
                      .overpaid { background:#f8d7da; }
                      .table-success { background:#d4edda; }
                  </style>
              </head>
              <body>
                  ${fullContent}
              </body>
              </html>
          `);

          win.document.close();
          win.focus();
          win.print();
      }

      // Helper window layout function
      function openPrintWindow(title, headArea, labelText, tableHeader, rowsHtml) {
          let printWindow = window.open('', '', 'height=750,width=1050');
          printWindow.document.write('<html><head><title>' + title + '</title>');
          
          document.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
              printWindow.document.write('<link rel="stylesheet" href="' + link.href + '">');
          });
          
          printWindow.document.write('<style>@media print { .no-print { display: none !important; } } body { padding: 30px; }</style>');
          printWindow.document.write('</head><body>');
          printWindow.document.write(headArea); 
          printWindow.document.write('<br><h4 class="text-center" style="margin: 20px 0;">Summary Statement: <b>' + labelText + '</b></h4>');
          printWindow.document.write('<table class="table table-bordered text-nowrap"><thead>' + tableHeader + '</thead><tbody>' + rowsHtml + '</tbody></table>');
          printWindow.document.write('</body></html>');
          printWindow.document.close();
          
          printWindow.onload = function() {
              printWindow.focus();
              printWindow.print();
              printWindow.close();
          };
      }

      // Existing fallback global triggers
      function printDiv(divName) {
          // 1. Grab the content of the full print area
          var content = document.getElementById(divName) ? document.getElementById(divName).innerHTML : "";
          
          if (!content) {
              alert("Could not find the printable section: " + divName);
              return;
          }

          // 2. Open the clean pop-up window setup for mobile
          var win = window.open('', '', 'width=900,height=650');

          win.document.write(`
              <!DOCTYPE html>
              <html>
              <head>
                  <title>Print All Documents</title>
                  <style>
                      @page { size: A4; margin: 10mm; }

                      body {
                          font-family: Arial, sans-serif;
                          font-size: 14px;
                          padding: 10px;
                      }

                      table {
                          width: 100%;
                          border-collapse: collapse;
                          margin-bottom: 10px;
                      }

                      th, td {
                          border: 1px solid #000;
                          padding: 5px;
                          text-align: left;
                      }

                      th {
                          background: #f2f2f2;
                      }

                      .print-header {
                          margin-bottom: 10px;
                      }

                      .print-header h3 {
                          margin: 0;
                          font-size: 18px;
                      }

                      .no-print { display:none; }
                      .overpaid { background:#f8d7da; }
                      .table-success { background:#d4edda; }
                  </style>
              </head>
              <body>
                  ${content}
              </body>
              </html>
          `);

          win.document.close();
          win.focus();
          win.print();
      }

      function printHead(divName) {
          // 1. Grab the content of the target area ('headArea')
          var content = document.getElementById(divName) ? document.getElementById(divName).innerHTML : "";
          
          if (!content) {
              alert("Could not find the printable section: " + divName);
              return;
          }

          // 2. Open the clean window setup that works flawlessly on mobile browsers
          var win = window.open('', '', 'width=900,height=650');

          win.document.write(`
              <!DOCTYPE html>
              <html>
              <head>
                  <title>Print Document</title>
                  <style>
                      @page { size: A4; margin: 10mm; }

                      body {
                          font-family: Arial, sans-serif;
                          font-size: 14px;
                          padding: 10px;
                      }

                      table {
                          width: 100%;
                          border-collapse: collapse;
                          margin-bottom: 10px;
                      }

                      th, td {
                          border: 1px solid #000;
                          padding: 5px;
                          text-align: left;
                      }

                      th {
                          background: #f2f2f2;
                      }

                      .print-header {
                          margin-bottom: 10px;
                      }

                      .print-header h3 {
                          margin: 0;
                          font-size: 18px;
                      }

                      .no-print { display:none; }
                      .overpaid { background:#f8d7da; }
                      .table-success { background:#d4edda; }
                  </style>
              </head>
              <body>
                  ${content}
              </body>
              </html>
          `);

          win.document.close();
          win.focus();
          win.print();
      }

      // Back-end binding modals handlers
      $(document).on('click', '.btn-edit-comment', function() {
        let id = $(this).data('id');
        let comment = $(this).data('comment');
        let commentAmount = $(this).data('amount');

        $('#comment_id').val(id);
        $('#comment_text').val(comment);
        $('#commentAmount').val(commentAmount);

        $('#comment-btn').text('Update Comment').removeClass('btn-success').addClass('btn-info').data('mode', 'edit');
        $('#modelComment').modal('show');
      });

      $(document).on('click', '.btn-edit-othercomment', function() {
        let id = $(this).data('id');
        let comment = $(this).data('comment');

        $('#othercomment_id').val(id);
        $('#othercomment_text').val(comment);

        $('#othercomment-btn').text('Update Comment').removeClass('btn-success').addClass('btn-info').data('mode', 'edit');
        $('#modelotherComment').modal('show');
      });

      $(document).on('click', '.btn-edit-diary', function() {
        let id = $(this).data('id');
        let comment = $(this).data('comment');

        $('#diary_id').val(id);
        $('#diary_text').val(comment);

        $('#diary-btn').text('Update Diary').removeClass('btn-success').addClass('btn-info').data('mode', 'edit');
        $('#modeldiary').modal('show');
      });
</script>

<script>
        // comment form submission commentForm othercommentForm
        $('#diaryForm').on('submit', function(e) {
        e.preventDefault();

        let mode = $('#diary-btn').data('mode');

        $.ajax({
            url: 'model/add_diary.php',
            type: 'POST',
            dataType: 'JSON',
            data: $(this).serialize() + '&mode=' + mode,

            success: function(response) {
            if (response.status) {

                const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000
                });

                Toast.fire({
                icon: "success",
                title: response.success.message
                }).then(() => {
                location.reload();
                });

                $('#modeldiary').modal('hide');
                resetDiaryForm();

            } else {
                $('#errorComment').text(response.errors.comment || '');
            }
            }
        });
        });
        // other comment
        $('#othercommentForm').on('submit', function(e) {
        e.preventDefault();
        let mode = $('#othercomment-btn').data('mode');
        $.ajax({
            url: 'model/add_othercomment.php',
            dataType: 'JSON',
            data: $(this).serialize() + '&mode=' + mode,
            type: 'POST',
            success: function(response) {
            if (response.status) {
                const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000
                });

                Toast.fire({
                icon: "success",
                title: response.success.message
                }).then(() => {
                location.reload(); // refresh page
                });

                $('#modelotherComment').modal('hide');
                resetForm();
            } else {
                // alert('Failed to add expense. Please check your input.');
                $('#errorReason').text(response.errors.reason || '');
                $('#errorComment')
            }
            },
            error: function(xhr, status, error) {
            alert('Error__:' + xhr + status + error);
            }
        });
        });

        $(document).on('click', '.editExpenseBtn', function() {

        let id = $(this).data('id');
        let amount = $(this).data('amount');
        let reason = $(this).data('reason');

        $('#expense_id').val(id);
        $('#Amount').val(amount);
        $('#reason').val(reason);

        $('#action-btn')
            .text('Update')
            .removeClass('btn-primary')
            .addClass('btn-info')
            .attr('data-mode', 'edit');
        });
        //comment
        $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        let mode = $('#comment-btn').data('mode');
        $.ajax({
            url: 'model/add_comment.php',
            dataType: 'JSON',
            data: $(this).serialize() + '&mode=' + mode,
            type: 'POST',
            success: function(response) {
            if (response.status) {
                const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 2000
                });

                Toast.fire({
                icon: "success",
                title: response.success.message
                }).then(() => {
                location.reload(); // refresh page
                });

                $('#modelComment').modal('hide');
                resetForm();
            } else {
                // alert('Failed to add expense. Please check your input.');
                $('#errorReason').text(response.errors.reason || '');
            }
            },
            error: function(xhr, status, error) {
            alert('Error__:' + xhr + status + error);
            }
        });
        });
        // });
</script>

<script>
    $(document).ready(function() {
      
      // Function to completely clear validation messages and reset form
      function resetDiaryForm() {
          $('#diarForm')[0].reset();
          $('#diary_id').val('');
          $('#errorComment').text('');
          
          // Reset save button back to defaults
          $('#diary-btn')
              .text('Save Diary')
              .removeClass('btn-info')
              .addClass('btn-success')
              .data('mode', 'add');
              
          $('.modal-title').html('<i class="fas fa-comment"></i> Add Diary');
      }

      // Reset when modal gets closed
      $('#modeldiary').on('hidden.bs.modal', function () {
          resetDiaryForm();
      });

      // Form Submission (Handles both Add & Edit)
      $('#diarForm').on('submit', function(e) {
          e.preventDefault();
          
          // Clear previous error messages
          $('#errorComment').text('');

          // Get current mode from the button data attribute
          let currentMode = $('#diary-btn').data('mode') || 'add';
          
          // Serialize form values and append the current operation mode
          let formData = $(this).serialize() + '&mode=' + currentMode;

          $.ajax({
              url: 'model/add_diary.php', // *** Make sure this path matches your file structure ***
              type: 'POST',
              dataType: 'JSON',
              data: formData,
              success: function(response) {
                  if (response.status) {
                      // Success notifications via SweetAlert (matching your other views)
                      const Toast = Swal.mixin({
                          toast: true,
                          position: "top-end",
                          showConfirmButton: false,
                          timer: 2000
                      });

                      Toast.fire({
                          icon: "success",
                          title: response.success.message
                      }).then(() => {
                          location.reload(); // Reload page to view updates
                      });

                      $('#modeldiary').modal('hide');
                      resetDiaryForm();
                  } else {
                      // Validation errors returned from PHP handler
                      if (response.errors.comment) {
                          $('#errorComment').text(response.errors.comment);
                      }
                      if (response.errors.general) {
                          alert(response.errors.general);
                      }
                  }
              },
              error: function(xhr, status, error) {
                  console.error("XHR response: ", xhr.responseText);
                  alert('An error occurred while saving the diary: ' + error);
              }
          });
      });

      // Event handler when you click your table/list "Edit" button
      // It populates the inputs and flips the form mode seamlessly
      $(document).on('click', '.edit-diary-btn', function() {
          let diaryId = $(this).data('id');
          let diaryText = $(this).data('reason'); // Adjust data-attributes to whatever you name them in your list row

          // Fill inputs
          $('#diary_id').val(diaryId);
          $('#diary_text').val(diaryText);

          // Transform modal headers & save buttons to edit configuration
          $('.modal-title').html('<i class="fas fa-edit"></i> Edit Diary');
          $('#diary-btn')
              .text('Update Diary')
              .removeClass('btn-success')
              .addClass('btn-info')
              .data('mode', 'edit');

          // Open the modal
          $('#modeldiary').modal('show');
      });
  });
</script>

<script>
  function expenses() {
    var content = document.getElementById('expenses').innerHTML;
    var win = window.open('', '', 'width=900,height=650');

    win.document.write(`
                <html>
                <head>
                  
                    <style>
                        @page { size: A4; margin: 10mm; }

                        body {
                            font-family: Arial, sans-serif;
                            font-size: 14px;
                            padding: 10px;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 10px;
                        }

                        th, td {
                            border: 1px solid #000;
                            padding: 5px;
                            text-align: left;
                        }

                        th {
                            background: #f2f2f2;
                            /*text-align: center;*/
                        }

                        .print-header {
                            /*text-align: center;*/
                            margin-bottom: 10px;
                        }

                        .print-header h3 {
                            margin: 0;
                            font-size: 18px;
                        }

                        .no-print { display:none; }
                        .overpaid { background:#f8d7da; }
                        .table-success { background:#d4edda; }
                    </style>
                </head>
                <body>
                    ${content}
                </body>
                </html>
            `);

    win.document.close();
    win.focus();
    win.print();
  }

  function other_expenses() {
    var content = document.getElementById('other_expenses').innerHTML;
    var win = window.open('', '', 'width=900,height=650');

    win.document.write(`
              <html>
              <head>
                  <style>
                      @page { size: A4; margin: 10mm; }

                      body {
                          font-family: Arial, sans-serif;
                          font-size: 14px;
                          padding: 10px;
                      }

                      table {
                          width: 100%;
                          border-collapse: collapse;
                          margin-bottom: 10px;
                      }

                      th, td {
                          border: 1px solid #000;
                          padding: 5px;
                          text-align: left;
                      }

                      th {
                          background: #f2f2f2;
                          /*text-align: center;*/
                      }

                      .print-header {
                          /*text-align: center;*/
                          margin-bottom: 10px;
                      }

                      .print-header h3 {
                          margin: 0;
                          font-size: 18px;
                      }

                      .no-print { display:none; }
                      .overpaid { background:#f8d7da; }
                      .table-success { background:#d4edda; }
                  </style>
              </head>
              <body>
                  ${content}
              </body>
              </html>
          `);

    win.document.close();
    win.focus();
    win.print();
  }
</script>

<script>
  function resetForm() {
    $('#userForm')[0].reset();
    $('#edit_id').val('');

    $('#action-btn')
      .text('Save')
      .removeClass('btn-info')
      .addClass('btn-primary')
      .data('mode', 'add');

    $('#errorAmount').text('');
    $('#errorReason').text('');
  }

  $(document).ready(function() {
    $('#userForm').on('submit', function(e) {
      e.preventDefault();
      const mode = $('#action-btn').data('mode');
      // const mode = $('#action-btn').data('mode');
      $.ajax({
        url: 'model/expenses.form.php',
        dataType: 'JSON',
        // data: $(this).serialize(),
        data: $(this).serialize() + '&mode=' + mode,
        type: 'POST',
        success: function(response) {
          if (response.status === false) {
            $('#errorAmount').text(response.errors.amount || '');
            $('#errorReason').text(response.errors.reason || '');
          } else {
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
              icon: "success",
              title: response.success.message
            }).then(() => {
              location.reload(); // refresh page
            });

            // $('#usersTable').DataTable().ajax.reload();
            $('#modalUser').modal('hide');
            resetForm();
          }
        },
        error: function(xhr, status, error) {
          alert('Error: ' + xhr.status + ' - ' + error);
        }
      });
    });

    $('#modalUser').on('hidden.bs.modal', function() {
      resetForm();
    });
  });
</script>

<script>
  let existingRows = <?= count($rowmarkets); ?>;
</script>

<script>
  $(document).ready(function() {

    let rowCount = existingRows + 1;

    // Helper definition to initialize Select2 safely 
    function initSelect2(selector) {
      $(selector).select2({
          placeholder: "--select--",
          allowClear: true,
          width: '100%'
      });
    }

    // 1. Initialize Select2 on the baseline row already in the HTML layout
    initSelect2('.market-select, .animal-select');

    // Function to renumber rows
    function renumberRows() {
      let count = existingRows + 1;

      $("#tableBody tr").each(function() {
        $(this).find("td:first").text(count);
        count++;
      });

      rowCount = count - 1;
    }

    // Add Row
    $("#addRow").click(function() {
      // Build row as an un-appended jQuery selector node structure
      let $row = $(`
          <tr>
              <td>
                <input required type="number" name="number[]" class="form-control form-control-alternative">
                            
              </td>
              
              <td>
                  <select name="animal[]" class="form-control animal-select">
                      <option value="">--select--</option>
                      <?php
                        $stmt = $db->conn->prepare("SELECT * FROM department_tbl ORDER BY Department");
                        $stmt->execute();
                        $rows = $stmt->fetchAll();
                        foreach($rows as $row) : ?>
                        <option value="<?= $row['deptID'] ?>"><?= $row['Department'] ?></option>
                      <?php endforeach ?>
                  </select>
              </td>
              <td>
                  <input type="number" name="amount[]" class="form-control pl-1" placeholder="0.00">
              </td>
              <td class="text-center">
                  <button type="button" class="btn btn-outline-danger btn-sm rounded-circle removeRow" title="Remove line row">
                                            <i class="fas fa-times"></i>
                                        </button>
              </td>
          </tr>`);

      rowCount++;

      // Append row directly into the DOM
      $("#tableBody").append($row);

      // 2. Initialize Select2 only within this newly appended row node tree layout
      initSelect2($row.find('.market-select, .animal-select'));
    });

    // Remove Row
    $(document).on("click", ".removeRow", function() {
      // Clear Select2 instances context tracking on destroy before raw element drops out
      $(this).closest("tr").find('.market-select, .animal-select').select2('destroy');
      $(this).closest("tr").remove();
      renumberRows(); // auto renumber
    });

    // Form Submit with Validation
    $("#animalForm").submit(function(e) {
      e.preventDefault();

      $("#animalError").text("");
      let isValid = true;

      // Check at least one row
      if ($("#tableBody tr").length == 0) {
        $("#animalError").text("Please add at least one animal.");
        return;
      }

      let $submitBtn = $("#saveBtn");
      $submitBtn.prop("disabled", true).text("Saving...");

      // If validation passed → AJAX submit
      let isUpdate = $("#edit_id").val() !== "";
      $.ajax({
        url: "model/add_animalrecord.php",
        type: "POST",
        data: $(this).serialize(),
        success: function(response) {

          if (response.trim() == "success") {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Data saved successfully',
              timer: 2000,
              showConfirmButton: false
            }).then(() => location.reload());

          } else if (response.trim() == "updated") {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'info',
              title: 'Data updated successfully',
              timer: 2000,
              showConfirmButton: false
            }).then(() => location.reload());

          } else {
            $("#animalError").text("Failed to save data.");
          }
        },
        error: function() {
          $("#animalError").text("Server error occurred.");
        }
      });
    });
  });
</script>

    <script>
      function resetForm() {
        $('#formUnit')[0].reset();
        $('#errorAmount').text('');
        $('#errorReason').text('');
      }
      $(document).ready(function() {
        $('#formUnit').on('submit', function(e) {
          e.preventDefault();
          $.ajax({
            url: 'model/other_exp.form.php',
            dataType: 'JSON',
            data: $(this).serialize(),
            type: 'POST',
            success: function(response) {
              if (response.status) {
                const Toast = Swal.mixin({
                  toast: true,
                  position: "top-end",
                  showConfirmButton: false,
                  timer: 2000
                });

                Toast.fire({
                  icon: "success",
                  title: response.success.message
                }).then(() => {
                  location.reload(); // refresh page
                });

                $('#modelUnit').modal('hide');
                resetForm();
              } else {
                alert('Failed to add expense. Please check your input.');
                $('#errorAmount').text(response.errors.amount || '');
                $('#errorReason').text(response.errors.reason || '');
              }
            },
            error: function(xhr, status, error) {
              alert('Error__:' + xhr + status + error);
            }
          });
        });
      });
    </script>

    <script>
      $(document).on('click', '.deleteBtn', function() {

        let id = $(this).data('id');

        Swal.fire({
          title: 'Delete this record?',
          text: "This transaction will be permanently deleted.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Yes, Delete It',
          cancelButtonText: 'Cancel'
        }).then((result) => {

          if (result.isConfirmed) {

            $.ajax({
              url: 'model/delete_market_transaction.php',
              type: 'POST',
              data: {
                id: id
              },

              success: function(response) {

                $('#row' + id).remove();

                Swal.fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'success',
                  title: response,
                  timer: 2000,
                  showConfirmButton: false
                });

              },

              error: function() {

                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'Failed to delete record.'
                });

              }
            });

          }

        });

      });
    </script>

    <script>
      $(document).on("click", ".editBtn", function() {

        let id = $(this).data("id");
        let animal = $(this).data("animal");
        let amount = $(this).data("amount");
        let market = $(this).data("market");
        let number = $(this).data("number");
        let dateVal = $(this).data("date");
        $("button[name='save']").text("Update");

        $("#saveBtn")
          .text("Update")
          .removeClass("btn-primary")
          .addClass("btn-info");

          $("#addRow").hide();

        // Clear old rows
        $("#tableBody").html("");

        // Added the helper classes 'market-select' and 'animal-select' to these dropdowns
        let row = `
          <tr>
              <td>
                  <input type="number" name="number[]" value="${number}" class="form-control">
              </td>

              <td>
                  <select name="market_id[]" class="form-control marketSelect market-select">
                      <?php foreach($rowMarket3 as $rowMarket3_1): ?>
                          <option value="<?= $rowMarket3_1['id'] ?>">
                              <?= $rowMarket3_1['name'] ?>
                          </option>
                      <?php endforeach ?>
                  </select>
              </td>

              <td>
                  <select name="animal[]" class="form-control animalSelect animal-select">
                      <?php foreach($rows as $row): ?>
                          <option value="<?= $row['deptID'] ?>">
                              <?= $row['Department'] ?>
                          </option>
                      <?php endforeach ?>
                  </select>
              </td>

              <td>
                  <input type="number" name="amount[]" value="${amount}" class="form-control">
              </td>

              <td>
                  <button type="button" class="btn btn-danger removeRow">X</button>
              </td>
          </tr>
        `;

        $("#tableBody").append(row);

        // 1. Assign the selected options first
        $(".animalSelect").val(animal);
        $(".marketSelect").val(market);
        $("#edit_id").val(id);

        // 2. Initialize Select2 *after* the DOM contains the row and values are set
        $('.market-select, .animal-select').select2({
            placeholder: "--select--",
            allowClear: true,
            width: '100%'
        });

        // Scroll to form
        $('html, body').animate({
          scrollTop: $("#animalFormSection").offset().top - 100
        }, 500);
      });
    </script>

    <script>
      $(document).on('click', '.printGroupBtn', function() {
        let groupName = $(this).data('group');

        // 1. Update the document heading for the print job
        $('#groupTitle').html(
          'BASHIR MADAKI TRANSPORTATION RECORD FOR <strong>' + groupName + '</strong>'
        );
        $('#printHeading').show();

        // 2. Hide everything in the print container that is NOT part of this group
        let clearToPrint = false;

        $('#printArea tbody tr').each(function() {
          let $row = $(this);

          // Check if we hit a group header row
          if ($row.hasClass('groupHeader')) {
            let rowGroup = $row.find('strong').text().trim();

            if (rowGroup === groupName.trim()) {
              clearToPrint = true; // Start printing rows from this group
              $row.addClass('d-none-print'); // Hide the inner group header if redundant with main heading
            } else {
              clearToPrint = false; // Different group header? Stop displaying rows
              $row.addClass('d-none-print');
            }
          } else {
            // It's a standard transaction row, a subtotal row, or a grand total row
            if (!clearToPrint) {
              $row.addClass('d-none-print');
            } else {
              // If it is the Subtotal row right after our group, print it, but stop printing further
              if ($row.text().includes('Subtotal')) {
                clearToPrint = false;
              }
            }
          }
        });

        // Hide the final Grand Total row when printing a single group
        $('#printArea tbody tr').last().addClass('d-none-print');

        // Hide other non-relevant sections entirely (Expenses, Form, Sidebar, etc.)
        $('.form-area, #expenses, #other_expenses, .print-header').addClass('d-none-print');

        // 3. Open system print window
        window.print();

        // 4. Reset layout context back to normal tracking state
        $('#printHeading').hide();
        $('.d-none-print').removeClass('d-none-print');
      });
    </script>

    <script>
      function printDiv() {
        var content = document.getElementById('printArea').innerHTML;
        var win = window.open('', '', 'width=900,height=650');

        win.document.write(`
                    <html>
                    <head>
                      
                        <style>
                            @page { size: A4; margin: 10mm; }

                            body {
                                font-family: Arial, sans-serif;
                                font-size: 14px;
                                padding: 10px;
                            }

                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-bottom: 10px;
                            }

                            th, td {
                                border: 1px solid #000;
                                padding: 5px;
                                text-align: left;
                            }

                            th {
                                background: #f2f2f2;
                                /*text-align: center;*/
                            }

                            .print-header {
                                /*text-align: center;*/
                                margin-bottom: 10px;
                            }

                            .print-header h3 {
                                margin: 0;
                                font-size: 18px;
                            }

                            .no-print { display:none; }
                            .overpaid { background:#f8d7da; }
                            .table-success { background:#d4edda; }
                        </style>
                    </head>
                    <body>
                        ${content}
                    </body>
                    </html>
                `);

        win.document.close();
        win.focus();
        win.print();
      }
    </script>
