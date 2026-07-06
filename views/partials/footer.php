<!-- Footer -->

<!-- <footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; Tikvaah Tech Solutions <?= date('Y') ?></span>
        </div>
    </div>
</footer> -->

            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; Tikvaah Tech Solutions <?= date('Y') ?></span>
        </div>
    </div>
</footer>

    <!-- Bootstrap core JavaScript-->
    <!-- <script src="vendor/jquery/jquery.min.js"></script> -->
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>
    <script>
        // $(document).ready(function(){
        //     $('.table-striped').DataTable();
        // });
    </script>

    <script src="js/chart.js"></script>

    

    <script>
        const idleLimit = 20 * 60 * 1000; // 20 minutes in milliseconds
        let idleTimer;

        function resetIdleTimer() {
            clearTimeout(idleTimer);
            idleTimer = setTimeout(triggerLogout, idleLimit);
        }

        function triggerLogout() {
            // If the user is online, redirect to a dedicated timeout trigger or logout page
            if (navigator.onLine) {
                window.location.href = "/logout?reason=Timeout"; 
            } else {
                // If offline, they can't reach the server right now. 
                // We force a local wipe and redirect once they come back online.
                alert("You have been logged out due to inactivity, and your network is disconnected.");
                window.location.reload(); 
            }
        }

        // Listen for actual user activity to prove they aren't idle
        window.onload = resetIdleTimer;
        document.onmousemove = resetIdleTimer;
        document.onkeypress = resetIdleTimer;
        document.onclick = resetIdleTimer;
        document.onscroll = resetIdleTimer;

        // --- HANDLE NETWORK SWITCHES / DISCONNECTIONS ---
        window.addEventListener('offline', function() {
            console.warn("Network disconnected. Tracking idle time locally...");
            // Optional: Show a subtle UI banner warning the user: "You are offline."
        });

        window.addEventListener('online', function() {
            console.log("Network restored.");
            // If they were offline for hours and suddenly reconnect, 
            // this immediately triggers the backend check to see if they timed out.
            window.location.reload(); 
        });
    </script>
    

</body>
</html>