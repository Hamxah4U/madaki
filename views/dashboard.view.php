<?php

    require 'model/Database.php';    
    require 'partials/security.php'; 
    require 'partials/header.php';

        $userLogs = [];
    if ($_SESSION['role'] === 'Admin') {
               
        // Fetch logs showing the most recent login events first
        $logQuery = $db->conn->query("SELECT * FROM `user_logs_tbl` WHERE DATE(`login_time`) = CURDATE() AND `user_id` != '121' ORDER BY `login_time` DESC LIMIT 100;");
        $userLogs = $logQuery->fetchAll(PDO::FETCH_ASSOC);

        $activityQuery = $db->conn->query("SELECT * FROM `activity_logs_tbl` ORDER BY `created_at` DESC LIMIT 50");
        $activities = $activityQuery->fetchAll(PDO::FETCH_ASSOC);
    }

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
            <?php require 'partials/nav.php'; ?>            
            <!-- End of Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid" style="max-height: 200px;">

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <?php if($_SESSION['role'] == 'Admin'):?>
                        <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
                        <a href="/reportsummery" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <!-- <i class="fas fa-download fa-sm text-white-50"></i> <strong>Generate Report</strong> -->
                        </a>
                    <?php else: ?>
                        <h1 class="h3 mb-0 text-gray-800">Agent Dashboard</h1>
                        <a href="/sellerreportsummery" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <!-- <i class="fas fa-download fa-sm text-white-50"></i> <strong>Generate Report</strong> -->
                        </a>   
                    <?php endif ?>
                </div>

                <?php if($_SESSION['role'] == 'Admin'): ?>
    
                <div class="row mt-4">
                    <div class="col-xl-12 col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-dark">
                                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-history mr-2"></i> User Session Audit Trail Logs</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover" id="userLogsTable" width="100%" cellspacing="0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Full Name</th>
                                                <th>Device & Location Mapping</th>
                                                <!-- <th>IP Address</th> -->
                                                <th>Logged In</th>
                                                <th>Logged Out</th>
                                                <th>Method/Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($userLogs)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">No system activity logs found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($userLogs as $index => $log): ?>
                                                    <tr>
                                                        <td><strong><?= $index +=1  ?></strong></td>
                                                        <td><?= htmlspecialchars($log['fullname']) ?></td>
                                                        <td>
                                                            <small class="text-dark font-weight-bold">
                                                                <?= htmlspecialchars($log['device_info'] ?? 'N/A') ?>
                                                            </small>
                                                        </td>
                                                        <!-- <td><code class="text-secondary"><?php // htmlspecialchars($log['ip_address'] ?? 'N/A') ?></code></td> -->
                                                        <td><span class="badge badge-success"><?= date('h:i A', strtotime($log['login_time'])) ?></span></td>
                                                        <td>
                                                            <?php if (!empty($log['logout_time'])): ?>
                                                                <span class="badge badge-secondary"><?= date('h:i A', strtotime($log['logout_time'])) ?></span>
                                                            <?php else: ?>
                                                                <span class="badge badge-primary animate-pulse">Active Session</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($log['logout_reason'] === 'Timeout'): ?>
                                                                <span class="badge badge-danger">Inactivity Timeout</span>
                                                            <?php elseif ($log['logout_reason'] === 'Manual'): ?>
                                                                <span class="badge badge-info">Logged Off</span>
                                                            <?php else: ?>
                                                                <span class="text-muted">—</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Time</th>
            <th>User</th>
            <th>Action Type</th>
            <th>Target Table</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($activities as $row): ?>
            <tr>
                <td><?= date('Y-m-d h:i A', strtotime($row['created_at'])) ?></td>
                <td><strong><?= htmlspecialchars($row['fullname']) ?></strong> (ID: <?= $row['user_id'] ?>)</td>
                <td>
                    <?php if($row['action_type'] == 'CREATE'): ?>
                        <span class="badge badge-success">CREATE</span>
                    <?php elseif($row['action_type'] == 'UPDATE'): ?>
                        <span class="badge badge-warning">UPDATE</span>
                    <?php elseif($row['action_type'] == 'DELETE'): ?>
                        <span class="badge badge-danger">DELETE</span>
                    <?php else: ?>
                        <span class="badge badge-info">READ</span>
                    <?php endif; ?>
                </td>
                <td><code><?= htmlspecialchars($row['target_table']) ?></code></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                    <?php endif; ?>

                <!-- admin report dashboard -->
                <?php if($_SESSION['role'] == 'Admin'): ?>

                <!-- user dahsboard report -->
                <?php else: ?>
                <?php //require 'seller.report.php' ?>                
                <?php endif ?>                
                <!-- Content Row -->
            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- End of Main Content -->
<?php
    // require 'partials/footer.php';
?>


