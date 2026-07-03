<?php
  require 'Database.php';
  ob_start();

  function getDeviceDetails() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $os = "Unknown OS";
    $browser = "Unknown Browser";

      //  Operating System
    if (preg_match('/windows|win32/i', $userAgent)) $os = 'Windows';
    elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $os = 'macOS';
    elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) $os = 'iOS (iPhone/iPad)';
    elseif (preg_match('/android/i', $userAgent)) $os = 'Android';
    elseif (preg_match('/linux/i', $userAgent)) $os = 'Linux';

      //  Browser
    if (preg_match('/msie|trident/i', $userAgent)) $browser = 'Internet Explorer';
    elseif (preg_match('/edg/i', $userAgent)) $browser = 'Edge';
    elseif (preg_match('/firefox/i', $userAgent)) $browser = 'Firefox';
    elseif (preg_match('/chrome/i', $userAgent)) $browser = 'Chrome';
    elseif (preg_match('/safari/i', $userAgent)) $browser = 'Safari';

    return "$os - $browser";
  }

  function getIPLocation($ip) {
    // localhost / ::1
    if ($ip === '127.0.0.1' || $ip === '::1') {
        return 'Localhost Dev Environment';
    }
    
    // Call out to a free, lightweight IP API 
    $url = "http://ip-api.com/json/" . $ip;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Do not hang login if API is slow
    $response = curl_error($ch) ? null : curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['status']) && $data['status'] === 'success') {
            // Returns formatted string like: "Lagos, Nigeria" or "New York, United States"
            return $data['city'] . ", " . $data['country'];
        }
    }
    return "Unknown Location";
  }


  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // $db = new Database();
    $conn = $db->conn;
    $errors = [];
    $success = [];

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($email)) {
      $errors['email'] = "Email or Phone is required!";
    }
    if(empty($password)) {
      $errors['password'] = "Password is required!";
    }

    if(empty($errors)){
      $status = 'Active';
      $stmt = $conn->prepare('SELECT `Department`, `Role`, `Email`, `Phone`, `UserPassword`, `userID`, `Fullname`  FROM `users_tbl` WHERE `Status` = :userstatus AND `Email` = :email OR `Phone` = :phone');
      $stmt->execute(['email' => $email, 'phone' => $email, 'userstatus' => $status]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if($user) {
        $userEmail = $user['Email'];
        $userPhone = $user['Phone'];
        $userPassword = $user['UserPassword'];
        $userID = $user['userID'];
        $fname = $user['Fullname'];
        $role = $user['Role'];
        $department = $user['Department'];
        
        if(password_verify($password, $userPassword)){
          if($password === 'password'){
            session_start();
            $_SESSION['email'] = $userEmail;
            $_SESSION['phone'] = $userPhone;
            $_SESSION['userID'] = $userID;
            $_SESSION['fname'] = $fname;
            $_SESSION['role'] = $role;
            $_SESSION['department'] = $department;
            $_SESSION['last_activity'] = time();         

            $success['message'] = 'Login successful, please wait...';
            $success['redirect'] = '/changepassword';
          }else{
            session_start();
            $_SESSION['email'] = $userEmail;
            $_SESSION['phone'] = $userPhone;
            $_SESSION['userID'] = $userID;
            $_SESSION['fname'] = $fname;
            $_SESSION['role'] = $role;
            $_SESSION['department'] = $department;
            $_SESSION['last_activity'] = time();

            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $device_info = getDeviceDetails();
            $physical_location = getIPLocation($ip_address);

            $logStmt = $conn->prepare('INSERT INTO `user_logs_tbl` (`user_id`, `fullname`, `device_info`, `ip_address`, `login_time`, `logout_time`, `logout_reason`)
            VALUES (:user_id, :fullname, :device_info, :ip_address, :login_time, :logout_time, :logout_reason)');
            
            $logStmt->execute([
              'user_id' => $userID,
              'fullname' => $fname,

              'device_info' => $device_info . " (" . $physical_location . ")",
              'ip_address' => $ip_address,
              'login_time' => date('Y-m-d H:i:s'),
              'logout_time' => null,
              'logout_reason' => null
            ]);

            $_SESSION['log_id'] = $conn->lastInsertId();

            $success['message'] = 'Login successful, please wait...';
            $success['redirect'] = '/dashboard';
          }
          
        }else{
          $errors['invalidpass'] = 'Invalid Password!';
        }
      }else{
        $errors['emailPhone'] = 'Email or Phone does not exist!';
      }


    }

      if(count($errors) > 0){
        echo json_encode([
          'status' => false,
          'errors' => $errors,
        ]);
      }else{
        echo json_encode([
          'status' => true,
          'success' => $success,
        ]);
      }
  }
