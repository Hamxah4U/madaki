<style>
    strong{
        font-size: 12pt;
        font-family: Verdana, Geneva, Tahoma, sans-serif;
    }
</style>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/dashboard">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-win"></i>
        </div>
        <div class="sidebar-brand-text mx-3">
            <!-- Smart billing -->
            <img src="../../img/ansar.png" alt="" style="width: 30%;">
        </div>
    </a>  

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <strong>Dashboard</strong></a>
    </li>
   
    <!-- Nav Item - Pages Collapse Menu -->
     <?php  if($_SESSION['role'] == 'Admin'):?>        
                <li class="nav-item active">
                    <a class="nav-link" href="/users">
                        <i class="fas fa-fw fa-user"></i>
                        <strong>Manage Users </strong></a>
                </li>

                <li class="nav-item active">
                    <a class="nav-link" href="/manage-drivers">
                       <i class="fas fa-fw fa-id-card"></i>
                        <strong>Manage Driver </strong></a>
                </li>
              

                <li class="nav-item active">
                    <a class="nav-link" href="/transportation">
                        <i class="fas fa-truck"></i>
                       <strong>Transportation</strong>
                    </a>
                </li>         

            <?php else: ?>
            
              

            <?php endif; ?>

            <hr class="sidebar-divider my-0">
            <li class="nav-item active">
                <a class="nav-link" href="/logout">
                    <i class="fas fa-sign-out-alt text-danger"></i>
                    <strong class="text-danger">Logout</strong>
                </a>
            </li>


    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

            <!-- Sidebar Message -->
</ul>