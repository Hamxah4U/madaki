
<?php	
	require 'partials/security.php';
    require 'partials/header.php';
	require 'model/Database.php';

    if (!isset($_GET['id']) || empty($_GET['id'])) {
      require 'controllers/404.php';
      exit();
    }

    $transport_id = (int)$_GET['id'];

    // $stmt = $db->conn->prepare("SELECT * FROM transportation_expenses WHERE transportation_id = :id");
    // $stmt = $db->conn->prepare("SELECT * FROM transportation_expenses
    //     JOIN transportation ON transportation.id = transportation_expenses.transportation_id 
    //     WHERE transportation_id = :id");
    $stmt = $db->conn->prepare("SELECT te.*, t.amount_per_animal FROM transportation_expenses te JOIN transportation t ON t.id = te.transportation_id WHERE transportation_id = :id");
    $stmt->execute(['id' => $transport_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);


    //fetch transportation table
    $transport_id = (int)$_GET['id'];
    $stmt = $db->conn->prepare("SELECT t.balance, t.first_payment, t.second_payment, t.third_payment, yan_waju, amount_per_animal, t.driver_name, t.bossno, t.driver_amount FROM transportation_expenses te
        JOIN transportation t ON t.id = te.transportation_id 
        WHERE transportation_id = :id LIMIT 1");
    $stmt->execute(['id' => $transport_id]);
    $driverInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    //total exp
    
    $stmt_exp = $db->conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total_exp FROM expenses WHERE `status` = 'exp' AND `driver_id` = :id");
    $stmt_exp->execute(['id' => $transport_id]);
    $expenses = $stmt_exp->fetch(PDO::FETCH_ASSOC);

    //Other exp
    $stmt_exp_other = $db->conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total_other_exp FROM expenses WHERE `status` = 'other_exp' AND `driver_id` = :id");
    $stmt_exp_other->execute(['id' => $transport_id]);
    $expenses_other = $stmt_exp_other->fetch(PDO::FETCH_ASSOC);

    $grandTotal = ($expenses['total_exp'] ?? 0) + ($driverInfo['driver_amount'] ?? 0);

   
    
    $subGrandTotal =  ($grandTotal) - (($expenses_other['total_other_exp'] ?? 0));
    
    //$driverInfo['driver_amount'] + $expenses['total_exp'] ?? 0 + $otherExpenses['total_other_exp'] ?? 0;
    //edit info 
    $editMode = false;
    $editData = null;

    if (isset($_GET['edit'])) {
        $edit_id = (int)$_GET['edit'];

        $stmt = $db->conn->prepare("
            SELECT * FROM transportation_expenses
            WHERE id = :id
        ");
        $stmt->execute(['id' => $edit_id]);
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($editData) {
            $editMode = true;
        } else {
            echo "<script>alert('Record not found for editing');</script>";
        }
    }

?>

<?php
    // if (isset($_POST['save'])) {
    //     $stmt = $db->conn->prepare("INSERT INTO transportation_expenses (transportation_id, fullname, death_animal, surviving_animal, amount,
    //     first_payment, second_payment, third_payment, total)
    //     VALUES
    //     (:tid, :fullname, :death, :survive, :amount, :first, :second, :third, :total)");

    //   foreach ($_POST['fullname'] as $key => $name) {
        
    //     if (empty($name)) continue;

    //       $death   = $_POST['death_animal'][$key] ?? 0;
    //       $survive = $_POST['surviving_animal'][$key] ?? 0;
    //       $amount  = $_POST['amount'][$key] ?? 0;
    //       $first   = $_POST['first_payment'][$key] ?? 0;
    //       $second  = $_POST['second_payment'][$key] ?? 0;
    //       $third   = $_POST['third_payment'][$key] ?? 0;
    //       $total   = $_POST['total'][$key] ?? 0;

    //       $stmt->execute([
    //           'tid'      => $transport_id,
    //           'fullname' => $name,
    //           'death'    => $death,
    //           'survive'  => $survive,
    //           'amount'   => $amount,
    //           'first'    => $first,
    //           'second'   => $second,
    //           'third'    => $third,
    //           'total'    => $total
    //       ]);
    //   }

    //   echo "<script>alert('Saved successfully');</script>";
    // }

    if (isset($_POST['save'])) {
      // UPDATE (single row edit)
      if (!empty($_POST['edit_id'])) {

          $stmt = $db->conn->prepare(" UPDATE transportation_expenses SET
            fullname = :fullname,
            total_animal = :total_animal,
            death_animal = :death_animal,
            first_payment = :first_payment,
            second_payment = :second_payment,
            third_payment = :third_payment
            WHERE id = :id
          ");

          $stmt->execute([
              'fullname' => $_POST['fullname'][0],
              'total_animal' => $_POST['total_animal'][0],
              'death_animal' => $_POST['death_animal'][0],
              'first_payment' => $_POST['first_payment'][0],
                'second_payment' => $_POST['second_payment'][0],
                'third_payment' => $_POST['third_payment'][0],
              
              'id'       => $_POST['edit_id']
            //   'total_animal' => $_POST['total_animal'][0]
          ]);

      } else {

          $stmt = $db->conn->prepare("
            INSERT INTO transportation_expenses 
            (total_animal, transportation_id, fullname, death_animal, first_payment, second_payment, third_payment)
            VALUES 
            (:total_animal, :tid, :fullname, :death, :first, :second, :third)
        ");
        
                foreach ($_POST['fullname'] as $key => $name) {
        
            if (empty($name)) continue;
        
            $stmt->execute([
                'total_animal' => $_POST['total_animal'][$key] ?? 0,
                'tid'          => $transport_id,
                'fullname'     => $name,
                'death'        => $_POST['death_animal'][$key] ?? 0,
                'first'        => $_POST['first_payment'][$key] ?? 0,
                'second'       => $_POST['second_payment'][$key] ?? 0,
                'third'        => $_POST['third_payment'][$key] ?? 0
            ]);
        }
        
      }
        echo "<script>alert('Saved successfully');</script>";
        echo "<script>location.href='?id=".$transport_id."';</script>";
    }

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
            <div class="table-responsive">
            <div class="form-area no-print">
                <form action="" method="POST">
                <input type="hidden" name="edit_id" value="<?= $editData['id'] ?? '' ?>">            
                <table class="table table-striped text-nowrap" id="peopleTable">  
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>No. of Animal</th>
                            <th>Death</th>
                            <th>Surviving</th>
                            <!-- <th>Amount</th> -->
                            <th>1st</th>
                            <th>2nd</th>
                            <th>3rd</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td>1</td>
                            <td><input type="text" name="fullname[]" class="form-control" value="<?= $editData['fullname'] ?? '' ?>"></td>
                            <td><input type="number" name="total_animal[]" style="width: 66px;" value="<?= $editData['total_animal'] ?? '' ?>" class="form-control"></td>
                            <td><input type="number" name="death_animal[]" style="width: 66px;" value="<?= $editData['death_animal'] ?? '' ?>" class="form-control"></td>
                            <td><input type="number" name="surviving_animal[]" style="width: 66px;" value="<?= $editData['surviving_animal'] ?? '' ?>" class="form-control"></td>
                            <!-- <td><input type="number" name="amount[]" value="<?php // $editData['amount'] ?? '' ?>" class="form-control"></td> -->
                            <td><input type="number" name="first_payment[]" style="width: 85px;" value="<?= $editData['first_payment'] ?? '' ?>" class="form-control"></td>
                            <td><input type="number" name="second_payment[]" style="width: 85px;" value="<?= $editData['second_payment'] ?? '' ?>" class="form-control"></td>
                            <td><input type="number" name="third_payment[]" style="width: 85px;" value="<?= $editData['third_payment'] ?? '' ?>" class="form-control"></td>
                            <td><input type="number" name="total[]" style="width: 86px;" value="<?= $editData['total'] ?? '' ?>" class="form-control"></td>
                            <td><button style="width: 32px;" type="button" class="btn btn-danger removeRow">X</button></td>
                        </tr>
                    </tbody>
                </table>

                <?php if (!$editMode): ?>
                    <button type="button" class="btn btn-success" id="addRow">Add Person</button>
                    <br><br>
                <?php endif; ?>
                
                <button type="submit" name="save"
                    class="btn <?= $editMode ? 'btn-info' : 'btn-primary' ?>">
                    <?= $editMode ? 'Update' : 'Submit' ?>
                </button>
                </form>
            </div>
            <div class="mb-3">
                <br>
                <button class="btn btn-info" onclick="printDiv('printArea')">
                    Print
                </button>
                <!-- <a href="/export-excel?tid=<?php // $transport_id ?>" class="btn btn-success no-print">
                    Export to Excel
                </a> -->
                <button class="btn btn-primary" type="button" data-target="#modalUser" data-toggle="modal"><strong>Expenses</strong></button>

                <button type="button" data-target="#modelUnit" data-toggle="modal" class="btn btn-primary"><strong>Other Expenses</strong></button>  
                <button type="button" data-target="#modelComment" data-toggle="modal" class="btn btn-primary"><strong>Comments</strong></button>  
                <button type="button" data-target="#modelotherComment" data-toggle="modal" class="btn btn-primary"><strong>Other Comments</strong></button>              
                <button type="button" data-target="#modeldiary" data-toggle="modal" class="btn btn-primary"><strong>Diary</strong></button>              

            </div>

    <?php
        $currentEditId = $_GET['edit'] ?? null;

        /* ===============================
        STEP 1: Calculate totals first
        =================================*/
        $totalDeath = 0;
        $totalSurviving = 0;
        $totalFirst = 0;
        $totalSecond = 0;
        $totalThird = 0;
        $totalPaid = 0;
        $total_animal = 0;

        foreach ($records as $row) {
            $totalDeath += (int)$row['death_animal'];
            $totalSurviving += (int)$row['surviving_animal'];
            $totalFirst += (float)$row['first_payment'];
            $totalSecond += (float)$row['second_payment'];
            $totalThird += (float)$row['third_payment'];
            $totalPaid += (float)$row['total'];
            $total_animal += (int)$row['total_animal'];
        }

        /* ===============================
        STEP 2: Cost per animal
        =================================*/
        $costPerAnimal = 0;
        if ($totalSurviving > 0) {
            $costPerAnimal = $driverInfo['driver_amount'] / $totalSurviving;
        }
    ?>
    <div class="print-container" id="printArea">
        <!-- Header -->
        <div class="print-header">
            <h3>BASHIR MADAKI TRANSPORTATION RECORD</h3>
            <small><?= date('d M Y') ?></small>
        </div>

        <!-- Driver Info -->
        <table class="table table-bordered text-nowrap mb-3">
            <tr>
                <th>Driver</th>
                <td><?= !empty($driverInfo['driver_name']) ? $driverInfo['driver_name'] : '' ?></td>
                <th>Yan waju</th>
                <td><?= !empty($driverInfo['yan_waju']) ? $driverInfo['yan_waju'] : '' ?></td>
                <!-- <th>Motor No.</th> -->
                <!-- <td><?php // !empty($driverInfo['bossno']) ? $driverInfo['bossno'] : 'N/A' ?></td> -->
                <th>General Grand Total </th>
                <td>&#8358;<?= number_format($grandTotal) ?></td>
            </tr>
            <tr>
                <th>Total Transportation Cost</th>
                <td>&#8358;<?= !empty($driverInfo['driver_amount']) ? number_format($driverInfo['driver_amount']) : '0.00' ?></td>
                <th>Cost Per Animal</th>
                <td>&#8358;<?= !empty($driverInfo['amount_per_animal']) ? number_format($driverInfo['amount_per_animal']) : '0.00' ?></td>
                
                <th>Expenses</th>
                <td>&#8358;<?= number_format($expenses_other['total_other_exp'] ?? 0) ?></td>
            </tr>
            <tr>
                <th>Total Surviving Animals</th>
                <td><?= $totalSurviving ?></td>
                <th>Total Death</th>
                <td><?= number_format($totalDeath) ?></td>
                <th>Expected Return</th>
                <td>&#8358;<?= number_format($subGrandTotal) ?></td>
            </tr>
            <tr>
                <th>Deposits</th>
                <td colspan="5">
                    <div class="d-flex justify-content-between">
                        <span><strong>1st:</strong> ₦<?= number_format($driverInfo['first_payment'] ?? 0) ?></span>
                        <span><strong>2nd:</strong> ₦<?= number_format($driverInfo['second_payment'] ?? 0) ?></span>
                        <span><strong>3rd:</strong> ₦<?= number_format($driverInfo['third_payment'] ?? 0) ?></span>
                        <span><strong>Remaining Bal.</strong> ₦<?= number_format($driverInfo['balance'] ?? 0) ?></span>
                    </div>
                </td>
            </tr>

        </table>

        <!-- Records Table -->
        <table class="table table-bordered text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Total Animal</th>
                    <th>Death</th>
                    <th>Surviving</th>
                    <th>Expected Amount</th>
                    <th>1st</th>
                    <th>2nd</th>
                    <th>3rd</th>
                    <th>Total Paid</th>
                    <th>Balance</th>
                    <th class="no-print">Action</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($records as $i => $row): 
            
                // Expected payment for this person
                $expectedAmount = $row['amount_per_animal'] * $row['surviving_animal']; //$costPerAnimal

                // Overpayment check
                $isOverpaid = $row['total'] > $expectedAmount;

                // ramaining balance
                $balance = round($expectedAmount - $row['total']);

                // Row classes
                $rowClass = '';
                if ($currentEditId == $row['id']) {
                    $rowClass = 'table-danger';
                }elseif($balance == 0) {
                    $rowClass = 'table-success';
                }elseif($isOverpaid){
                    $rowClass = 'overpaid';
                }
                // elseif ($isOverpaid) {
                //     $rowClass = 'overpaid';
                // }
            ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= $i + 1 ?></td>
                    <td><?= $row['fullname'] ?></td>
                    <td><?= $row['total_animal'] ?></td>
                    <td><?= $row['death_animal'] ?></td>
                    <td><?= $row['surviving_animal'] ?></td>
                    <td><strong><?= number_format($expectedAmount) ?></strong></td>
                    <td><?= number_format($row['first_payment']) ?></td>
                    <td><?= number_format($row['second_payment']) ?></td>
                    <td><?= number_format($row['third_payment']) ?></td>
                    <td><strong><?= number_format($row['total']) ?></strong></td>
                    <td>
                        <?php if($balance > 0): ?>
                            <span class="text-warning">
                                <?= number_format($balance) ?> Remaining
                            </span>
                        <?php elseif($balance < 0): ?>
                            <span class="text-success">
                                <?= number_format(abs($balance)) ?> Over
                            </span>
                        <?php elseif($balance == 0): ?>
                            <span class="text-primary">Cleared</span>
                        <?php endif; ?>
                    </td>
                    <td class="no-print">                    
                        <button 
                            class="btn btn-sm btn-info receiptBtn"
                            data-id="<?= $row['id'] ?>"
                            data-phone="<?= $row['phone'] ?>"
                            data-tid="<?= $transport_id ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#receiptModal">
                            Receipt
                        </button>
                        <a href="?id=<?= $transport_id ?>&edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit </a>
                        <a href="/delete-exp?id=<?= $row['id'] ?>&tid=<?= $transport_id ?>"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Delete this record?')">
                        Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

            <!-- Footer Totals -->
            <tfoot>
                <tr style="background:#f1f1f1; font-weight:bold;">
                    <td colspan="2">TOTAL</td>
                    <td><?= $total_animal ?></td>
                    <td><?= $totalDeath ?></td>
                    <td><?= $totalSurviving ?></td>
                    <td>&#8358;<?= !empty($driverInfo['driver_amount']) ? number_format($driverInfo['driver_amount']) : '0.00' ?></td>
                    <td><?= number_format($totalFirst) ?></td>
                    <td><?= number_format($totalSecond) ?></td>
                    <td><?= number_format($totalThird) ?></td>
                    <td><?= number_format($totalPaid) ?></td>
                    <td colspan="2"></td>
                    <td class="no-print"></td>
                </tr>
            </tfoot>
        </table>

        <div class="row">
            <div class="col-sm-6">
                <table class="table table-bordered text-nowrap">
                    <thead>
                        <tr><strong>Expenses</strong></tr>
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
                            $exp = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'exp' AND driver_id = :id");
                            $exp->execute(['id' => $transport_id]);
                            $row_exps = $exp->fetchALL();
                            foreach($row_exps AS $index => $row_exp) : 
                                $exponly  += $row_exp['amount'];
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= $row_exp['reason'] ?></td>
                            <td><?= number_format($row_exp['amount']) ?></td>
                            <td><?= $row_exp['daterecorded'] ?></td>
                            <td><?= $row_exp['timerecorded'] ?></td>
                            <td class="no-print">
                                <a href="/delete-only-exp?id=<?= $row_exp['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                                <!-- <a href="/edit">Edit</a> -->
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#f1f1f1; font-weight:bold;">
                            <td colspan="2">Total</td>
                            <th colspan="5">₦<?= number_format($exponly) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        
            <div class="col-sm-6">
                <table class="table table-bordered text-nowrap">
                    <thead>
                        <tr><strong>Other Expenses</strong></tr>
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
                            $exp = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'other_exp' AND driver_id = :id");
                            $exp->execute(['id' => $transport_id]);
                            $row_exps = $exp->fetchALL();
                            foreach($row_exps AS $index => $row_exp) : 
                                $exponly  += $row_exp['amount'];
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= $row_exp['reason'] ?></td>
                            <td><?= number_format($row_exp['amount']) ?></td>
                            <td><?= $row_exp['daterecorded'] ?></td>
                            <td><?= $row_exp['timerecorded'] ?></td>
                            <td class="no-print">
                                <a href="/delete-other-expenses?id=<?= $row_exp['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger no-print" onclick="return confirm('Delete this record?')">Delete</a>
                                <!-- <a href="/edit">Edit</a> -->
                            </td>
                        </tr>
                        <?php endforeach ?>
                        </tbody>
                        <tfoot>
                        <tr style="background:#f1f1f1; font-weight:bold;">
                            <td colspan="2">Total</td>
                            <th colspan="4">₦<?= number_format($exponly) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap">
                        <thead>
                            <tr><strong>Comments</strong></tr>
                            <tr>
                                <th>#</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th class="no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $comment = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'comment' AND driver_id = :id");
                                $comment->execute(['id' => $transport_id]);
                                $row_comments = $comment->fetchALL();
                                foreach($row_comments AS $index => $row_comment) : 
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= $row_comment['reason'] ?></td>
                                <td><?= date('d M Y', strtotime($row_comment['daterecorded'])) ?></td>
                                <td><?= date('h:i A', strtotime($row_comment['timerecorded'])) ?></td>
                                <td class="no-print">
                                    <a href="/delete-comment?id=<?= $row_comment['id'] ?>&tid=<?= $transport_id ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete this record?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap">
                        <thead>
                            <tr><strong>OtherComments</strong></tr>
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
                                $comment = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'other_comment' AND driver_id = :id");
                                $comment->execute(['id' => $transport_id]);
                                $row_comments = $comment->fetchALL();
                                foreach($row_comments AS $index => $row_comment) : 
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= $row_comment['reason'] ?></td>
                                <td><?= date('d M Y', strtotime($row_comment['daterecorded'])) ?></td>
                                <td><?= date('h:i A', strtotime($row_comment['timerecorded'])) ?></td>
                                <td class="no-print">
                                    <a href="/delete-comment?id=<?= $row_comment['id'] ?>&tid=<?= $transport_id ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete this record?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="table table-responsive">
                    <table class="table table-bordered text-nowrap">
                        <thead>
                            <tr><strong>Diary</strong></tr>
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
                            $diary = $db->conn->prepare("SELECT * FROM `expenses` WHERE `status` = 'diary' AND driver_id = :id");
                            $diary->execute(['id' => $transport_id]);
                            $row_diarys = $diary->fetchALL();
                            foreach($row_diarys AS $index => $row_diary) : 
                               
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= $row_diary['reason'] ?></td>
                            <td><?= $row_diary['daterecorded'] ?></td>
                            <td><?= $row_diary['timerecorded'] ?></td>
                            <td class="no-print">
                                <a href="/delete-other-expenses?id=<?= $row_diary['id'] ?>&tid=<?= $transport_id ?>" class="btn btn-sm btn-danger no-print" onclick="return confirm('Delete this record?')">Delete</a>
                                <!-- <a href="/edit">Edit</a> -->
                            </td>
                        </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
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

    <!-- Modal -->

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
                        
                        <div class="form-group">
                            <label><strong>Diary</strong></label>
                            <textarea name="comment" class="form-control" rows="4" required></textarea>
                            <small class="text-danger" id="errorComment"></small>
                        </div>

                        <input type="hidden" name="transport_id" value="<?= $transport_id ?? '' ?>">
                    
                        <div class="modal-footer p-0 pt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Diary
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                Close
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
                        
                        <div class="form-group">
                            <label><strong>Other Comment</strong></label>
                            <textarea name="comment" class="form-control" rows="4" required></textarea>
                            <small class="text-danger" id="errorComment"></small>
                        </div>

                        <input type="hidden" name="transport_id" value="<?= $transport_id ?? '' ?>">
                    
                        <div class="modal-footer p-0 pt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Comment
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                Close
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
                        
                        <div class="form-group">
                            <label><strong>Comment</strong></label>
                            <textarea name="comment" class="form-control" rows="4" required></textarea>
                            <small class="text-danger" id="errorComment"></small>
                        </div>

                        <input type="hidden" name="transport_id" value="<?= $transport_id ?? '' ?>">
                    
                        <div class="modal-footer p-0 pt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Comment
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                Close
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>

 
    <div class="modal fade" id="modalUser" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
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
                        <input type="text" name="userID" id="userID" value="<?= $transport_id ?>" hidden>
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
                        <button type="submit" class="btn btn-primary" id="action-btn" data-mode='add'><strong>Save</strong></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modelUnit" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-primary"><strong>Other Expenses</strong></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-danger">&times;</span>
                        </button>
                </div>
                <div class="modal-body">
                    <form id="formUnit">
                        <input type="text" name="userID" id="userID" value="<?= $transport_id ?>" hidden>
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
                        <button type="submit" class="btn btn-primary" id="action-btn" data-mode='add'><strong>Save</strong></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require 'partials/footer.php'; ?>

<script>
    // comment form submission commentForm othercommentForm
     $(document).ready(function(){
        // diary
        $('#diarForm').on('submit', function(e){
			e.preventDefault();
			$.ajax({
				url: 'model/add_diary.php', 
				dataType: 'JSON',
				data: $(this).serialize(),
				type: 'POST',
				success: function(response){
                    if(response.status){
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

                        $('#modeldiary').modal('hide');
                        resetForm();
                    }else{
                        // alert('Failed to add expense. Please check your input.');
                        $('#errorReason').text(response.errors.reason || '');
                    }
                }
                ,
				error: function(xhr, status, error){
					alert('Error__:' + xhr + status + error);
				}
			});
		});

        // other comment
		$('#othercommentForm').on('submit', function(e){
			e.preventDefault();
			$.ajax({
				url: 'model/add_othercomment.php', 
				dataType: 'JSON',
				data: $(this).serialize(),
				type: 'POST',
				success: function(response){
                    if(response.status){
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
                    }else{
                        // alert('Failed to add expense. Please check your input.');
                        $('#errorReason').text(response.errors.reason || '');
                    }
                }
                ,
				error: function(xhr, status, error){
					alert('Error__:' + xhr + status + error);
				}
			});
		});
        //comment
        $('#commentForm').on('submit', function(e){
			e.preventDefault();
			$.ajax({
				url: 'model/add_comment.php', 
				dataType: 'JSON',
				data: $(this).serialize(),
				type: 'POST',
				success: function(response){
                    if(response.status){
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
                    }else{
                        // alert('Failed to add expense. Please check your input.');
                        $('#errorReason').text(response.errors.reason || '');
                    }
                }
                ,
				error: function(xhr, status, error){
					alert('Error__:' + xhr + status + error);
				}
			});
		});
	});
</script>

<script>
    function resetForm(){
    $('#formUnit')[0].reset();
    $('#errorAmount').text('');
    $('#errorReason').text('');
}
    $(document).ready(function(){
		$('#formUnit').on('submit', function(e){
			e.preventDefault();
			$.ajax({
				url: 'model/other_exp.form.php', 
				dataType: 'JSON',
				data: $(this).serialize(),
				type: 'POST',
				success: function(response){
                    if(response.status){
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
                    }else{
                        alert('Failed to add expense. Please check your input.');
                        $('#errorAmount').text(response.errors.amount || '');
                        $('#errorReason').text(response.errors.reason || '');
                    }
                }
                ,
				error: function(xhr, status, error){
					alert('Error__:' + xhr + status + error);
				}
			});
		});
	});
</script>


<script>
    $(document).ready(function () {
        $('#userForm').on('submit', function (e) {
            e.preventDefault();
            const mode = $('#action-btn').data('mode');
            $.ajax({
                url: 'model/expenses.form.php',
                dataType: 'JSON',
                data: $(this).serialize(),
                type: 'POST',
                success: function (response) {
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
                error: function (xhr, status, error) {
                    alert('Error: ' + xhr.status + ' - ' + error);
                }
            });
        });

        $('#modalUser').on('hidden.bs.modal', function () {
                resetForm();
        });
	});
</script>

<script>
    let currentId = '';
    let currentPhone = '';
    let currentTid = '';

    document.querySelectorAll('.receiptBtn').forEach(btn => {
        btn.addEventListener('click', function () {

            currentId = this.dataset.id;
            currentPhone = this.dataset.phone;
            currentTid = this.dataset.tid;

            // Show phone in modal
            document.getElementById('phoneLabel').innerText = currentPhone;

            // Load receipt content via AJAX
            fetch('/receipt?id=' + currentId + '&tid=' + currentTid)
                .then(res => res.text())
                .then(data => {
                    document.getElementById('receiptContent').innerHTML = data;
                });
        });
    });

    // WhatsApp PDF button
    document.getElementById('whatsappPdfBtn').addEventListener('click', function () {
        sendWhatsAppPDF(currentId, currentPhone, currentTid);
    });

    function sendWhatsAppPDF(id, phone, tid) {

    let pdfUrl = window.location.origin + 
        '/receipt_pdf?id=' + id + '&tid=' + tid;

    let message = 
        "Your transportation payment receipt is ready.\n" +
        "Download here:\n" + pdfUrl;

    let whatsappUrl = "https://wa.me/" + phone + "?text=" + encodeURIComponent(message);

    window.open(whatsappUrl, '_blank');
}
</script>

<script>
    function sendWhatsAppPDF(id, phone, tid) {

        // Remove leading 0 and add Nigeria code
        if(phone.startsWith('0')){
            phone = '234' + phone.substring(1);
        }

        // Open PDF in new tab
        let pdfUrl = `/receipt-pdf?id=${id}&tid=${tid}`;
        window.open(pdfUrl, '_blank');

        // Open WhatsApp chat
        let message = "Your transportation payment receipt is ready.";
        let waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
        window.open(waUrl, '_blank');
    }
</script>

<script>
    function shareWhatsApp() {
            let name = document.querySelector('#receiptPrintArea table tr:nth-child(1) td').innerText;
            let total = document.querySelector('#receiptPrintArea table tr:nth-child(10) td').innerText;

            let message = 
        `BASHIR MADAKI TRANSPORTATION RECEIPT

        Name: ${name}
        Total Paid: ${total}

        Thank you.`;

            let url = "https://wa.me/?text=" + encodeURIComponent(message);
            window.open(url, '_blank');
    }

</script>

<script>
     function printReceipt() {
        let content = document.getElementById('receiptPrintArea').innerHTML;

        let win = window.open('', '', 'width=700,height=700');
        win.document.write(`
            <html>
            <head>
                <title>Receipt</title>
                <style>
                    body { font-family: Arial; padding:20px; }
                    table { width:100%; border-collapse: collapse; }
                    th, td { border:1px solid #000; padding:6px; }
                </style>
            </head>
            <body onload="window.print();window.close();">
                ${content}
            </body>
            </html>
        `);
        win.document.close();
    }

</script>   


<script>
    document.querySelectorAll('.receiptBtn').forEach(button => {
        button.addEventListener('click', function() {
            let id = this.dataset.id;
            let tid = this.dataset.tid;

            fetch(`/receipt-modal?id=${id}&tid=${tid}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('receiptContent').innerHTML = data;
                let modal = new bootstrap.Modal(document.getElementById('receiptModal'));
                modal.show();
            });
        });
    });

    // Print function
    
</script>


<script>
    let rowCount = 1;

    document.getElementById('addRow').addEventListener('click', function () {
        rowCount++;
        // <td><input type="number" name="amount[]" class="form-control"></td>

        let row = `
        <tr>
            <td>${rowCount}</td>
            <td><input type="text" name="fullname[]" class="form-control"></td>        
            <td><input type="number" style="width: 66px;" name="total_animal[]" class="form-control"></td>
            <td><input type="number" style="width: 66px;" name="death_animal[]" class="form-control"></td>
            <td><input type="number" style="width: 66px;" name="surviving_animal[]" class="form-control"></td>
        
            <td><input type="number" name="first_payment[]" style="width: 85px;" class="form-control"></td>
            <td><input type="number" name="second_payment[]" style="width: 85px;" class="form-control"></td>
            <td><input type="number" name="third_payment[]" style="width: 85px;" class="form-control"></td>
            <td><input type="number" name="total[]" class="form-control" style="width: 86px;"></td>
            <td><button type="button" style="width: 32px;" class="btn btn-danger removeRow">X</button></td>
        </tr>`;
        document.getElementById('tableBody').insertAdjacentHTML('beforeend', row);
    });

    // Remove row
    document.addEventListener('click', function(e){
        if(e.target.classList.contains('removeRow')){
            e.target.closest('tr').remove();
        }
    });
</script>

<script>
// Auto calculate total for each row
document.addEventListener('input', function(e) {

    if (
        e.target.name === 'first_payment[]' ||
        e.target.name === 'second_payment[]' ||
        e.target.name === 'third_payment[]'
    ) {
        let row = e.target.closest('tr');

        let first  = parseFloat(row.querySelector('[name="first_payment[]"]').value) || 0;
        let second = parseFloat(row.querySelector('[name="second_payment[]"]').value) || 0;
        let third  = parseFloat(row.querySelector('[name="third_payment[]"]').value) || 0;

        let total = first + second + third;

        row.querySelector('[name="total[]"]').value = total;
    }

});
</script>

<script>
    function printDiv() {
        var content = document.getElementById('printArea').innerHTML;
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



