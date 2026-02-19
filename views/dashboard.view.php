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


