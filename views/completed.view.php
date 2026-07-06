<?php	
		require 'model/Database.php';    
        require 'partials/security.php'; 
        require 'partials/header.php';
?>

    <div id="wrapper">
    <?php require 'partials/sidebar.php' ?>

    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            <?php
                require 'partials/nav.php';
            ?>
            <div class="container-fluid">                     
						<div class="row mb-4">
							<div class="col-md-12 col-lg-12">
								<div class="table-responsive">
                  <p><strong class="text-primary">List of on Completed Motors</strong></p>
                      <table class="table table-bordered text-nowrap" width="100%" id="driverTable">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Driver Name</th>
                            <!-- <th>Agent</th> -->
                            <th>Yan waju</th>                           
                            <th>Other Paid Bal. (₦)</th>   
                            <th>Other Cost (₦)</th>   
                            <th>GGT (₦)</th>                      
                            <th>Action</th>
                          </tr>
                        </thead>
                       
                        <?php
                            /* $stmt = $db->query("SELECT 
                                COALESCE(e_total.total_other_exp, 0) AS total_other_exp, t.other_cost, 
                                t.amount_per_animal, 
                                COALESCE(SUM(tx.surviving_animal), 0) AS total_surving_animal, 
                                t.id AS TID, 
                                t.status_date, 
                                COALESCE(SUM(tx.total), 0) AS total_paid,  
                                u.Fullname, 
                                tx.transportation_id, 
                                t.driver_name, 
                                t.yan_waju 
                            FROM transportation_expenses tx 
                            LEFT JOIN transportation t ON t.id = tx.transportation_id 
                            LEFT JOIN users_tbl u ON u.userID = t.agent
                            LEFT JOIN (
                                SELECT driver_id, SUM(amount) AS total_other_exp
                                FROM expenses 
                                WHERE status = 'other_exp'
                                GROUP BY driver_id
                            ) e_total ON e_total.driver_id = t.id
                            WHERE t.status_id = 3
                            GROUP BY tx.transportation_id, t.status_date
                            ORDER BY u.Fullname ASC, t.status_date DESC;"); */
                            $stmt = $db->query("SELECT                            
                                t.other_cost, 
                                t.driver_amount,
                                COALESCE(e_other.total_other_exp, 0) AS total_other_exp, 
                                COALESCE(e_exp.total_exp, 0) AS total_exp, -- Now available cleanly if you need to display it
                                t.amount_per_animal, 
                                COALESCE(SUM(tx.surviving_animal), 0) AS total_surving_animal, 
                                t.id AS TID, 
                                t.status_date, 
                                COALESCE(SUM(tx.total), 0) AS total_paid,  
                                u.Fullname, 
                                tx.transportation_id,
                                t.driver_name, 
                                t.yan_waju 
                            FROM transportation_expenses tx 
                            LEFT JOIN transportation t ON t.id = tx.transportation_id 
                            LEFT JOIN users_tbl u ON u.userID = t.agent
                            -- Changed alias name here to e_other
                            LEFT JOIN (
                                SELECT driver_id, SUM(amount) AS total_other_exp
                                FROM expenses 
                                WHERE status = 'other_exp'
                                GROUP BY driver_id
                            ) e_other ON e_other.driver_id = t.id
                            -- Changed alias name here to e_exp
                            LEFT JOIN (
                                SELECT driver_id, SUM(amount) AS total_exp
                                FROM expenses 
                                WHERE status = 'exp'
                                GROUP BY driver_id
                            ) e_exp ON e_exp.driver_id = t.id
                            WHERE t.status_id = 3
                            GROUP BY 
                                tx.transportation_id, 
                                t.id, 
                                t.other_cost, 
                                t.driver_amount, 
                                e_other.total_other_exp, 
                                e_exp.total_exp,
                                t.amount_per_animal, 
                                t.status_date, 
                                u.Fullname, 
                                t.driver_name, 
                                t.yan_waju
                            ");
                            
                            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <tbody>
                            <?php
                                $currentCategory = ""; 
                                $serialNumber = 1; 
                                $subTotalOPB = 0; // Tracks the subtotal calculation per group
                                $isFirstRow = true;
                                $otheExp = 0;
                                $otherCost = 0;

                                foreach($drivers as $driver):
                                    $formattedDate = !empty($driver['status_date']) ? date('Y-m-d', strtotime($driver['status_date'])) : 'No Date';
                                    $categoryKey = $driver['Fullname'] . " - " . $formattedDate;

                                    // Row Math Calculations
                                    $totalOtherExp = $driver['total_other_exp'];
                                    $totalPaid = $driver['total_paid'];
                                    $OPB =  $totalPaid - $totalOtherExp;
                                    $GGTF = 0;

                                    // Check if we are changing groups
                                    if ($currentCategory !== $categoryKey): 
                                        
                                        // If it's not the first record, print the sub-total for the PREVIOUS group
                                        if (!$isFirstRow): 
                                ?>
                            <tr class="table-light font-weight-bold text-right text-dark">
                                <td colspan="4">Sub-total:</td>
                                <td class="text-left text-danger"><strong>₦ <?= number_format($subTotalOPB); ?></strong></td>
                            </tr>
                                <?php 
                                        // Reset subtotal counter for the new category block
                                        $subTotalOPB = 0; 
                                    endif;

                                    $currentCategory = $categoryKey;
                                    $isFirstRow = false;
                                ?>
                            <tr class="table-secondary font-weight-bold">
                                <td colspan="6" class="text-dark py-2">
                                    <i class="fas fa-user-tie mr-1"></i> <?= htmlspecialchars($driver['Fullname']) ?> 
                                    <span class="badge badge-primary ml-2"><i class="fas fa-calendar-alt"></i> <?= $formattedDate ?></span>
                                </td>
                            </tr>
                                <?php 
                                        endif; 

                                        // Add to active group running sub-total
                                        $subTotalOPB += $OPB;
                                        $otherCost +=  $driver['other_cost'];

                                ?>

                            <tr class="clickable-row" data-href="/transportationexp?id=<?= $driver['TID'] ?>">
                                <td><?= $serialNumber++ ?></td>                            
                                <td><?= htmlspecialchars($driver['driver_name']) ?></td>
                                <!-- <td><?php // htmlspecialchars($driver['Fullname']) ?></td> -->
                                <td><?= htmlspecialchars($driver['yan_waju']) ?></td>                                
                                <td>
                                    <strong><?= number_format($OPB); ?></strong>
                                </td>
                                <td><?= $driver['other_cost'] ? number_format($driver['other_cost'], 2) : '0.00' ?></td>
                                <td>
                                    <?php
                                        $cost = $driver['driver_amount'] ? $driver['driver_amount'] : '0';
                                        $ex = $driver['total_exp'] ? $driver['total_exp'] : '0';
                                        $GGT = $cost + $ex;
                                        $GGTF += $GGT;
                                    ?>
                                    <strong><?= number_format($GGT, 2) ?></strong>
                                </td>
                                <td>
                                <button 
                                        type="button"
                                        class="btn btn-warning btn-sm"
                                        data-toggle="modal"
                                        data-target="#modelStatus"
                                        data-id="<?= $driver['TID'] ?>"
                                        >
                                        <strong>Status</strong>
                                    </button> |
                                    
                                    <a href="/transportationexp?id=<?= $driver['TID'] ?>" class="btn btn-primary btn-sm">View</a> |
                                    <a href="/edittransportation?id=<?= $driver['TID'] ?>" class="btn btn-info btn-sm">Edit</a> 
                                </td>
                                
                            </tr>
                                <?php endforeach; ?>

                            <?php 
                                // Render the final trailing sub-total row for the very last category in the array
                                if (!empty($drivers)): 
                            ?>
                            <tr class="table-light font-weight-bold text-right text-dark">
                                <td colspan="3"><strong>Sub-total:</strong></td>
                                <td class="text-left text-danger"><strong>₦<?= number_format($subTotalOPB); ?></strong></td>
                                <td>₦<?= number_format($otherCost) ?></td>
                               <td></td>
                               <td></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>                      
                      </table>
                    </div>
								</div>
						</div>    
					</div>       
	 </div>
        <?php
    require 'partials/footer.php';
?>

<script>
    document.querySelectorAll(".clickable-row").forEach(row => {

        row.addEventListener("click", function(e) {

            // Ignore clicks inside buttons, links, or modal triggers
            if (
                e.target.closest('button') ||
                e.target.closest('a')
            ) {
                return;
            }

            window.location = this.dataset.href;

        });

    });
</script>

<?php
    $statusList = $db->conn->prepare("SELECT * FROM `status`");
    $statusList->execute();

?>

<div class="modal fade" id="modelStatus" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Transportation Status</h5>

                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form action="" id="statusForm">
                   <input type="text" name="driverID" id="driverID" hidden>
					<div class="form-group">
						<label for="my-input">Status</label>
                        <select name="status" id="change" class="form-control">
                            <option value="">--select status--</option>
                            <?php
                                foreach($statusList as $row): ?>
                                <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                            <?php endforeach ?>
                        </select>
						<small class="text-danger" id="errorStatus"></small>
					</div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function(){

        // Open modal and set driver ID
        $('#driverTable').on('click', '.btn-warning', function(){

            let driverID = $(this).data('id');

            $('#driverID').val(driverID);

        });

        // Submit form
        $('#statusForm').submit(function(e){

            e.preventDefault();

            // clear old errors
            $('#errorStatus').text('');

            $.ajax({

                url: 'model/updatestatus.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',

                success: function(response){

                    if(response.status == true){

                        Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            }
                            }).fire({
                            icon: "success",
                            title: response.success.message
                        });

                        $('#modelStatus').modal('hide');

                        $('#statusForm')[0].reset();

                        setTimeout(() => {

                            location.reload();

                        }, 3000);

                    }else{

                        if(response.errors.status){

                            $('#errorStatus').text(response.errors.status);

                        }

                    }

                },

                error: function(xhr){

                    console.log(xhr.responseText);

                }

            });

        });

    });
</script>