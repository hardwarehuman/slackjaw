<?php
session_start();
$error = "";

if(isset($_SESSION["is_logged"]) && $_SESSION["is_logged"] == "potato"){
  //is already logged in
  header("Location: http://slackjaw.me");
}

if(isset($_POST["user"]) && isset($_POST["password"])){
  echo "<p>Submitted Form</p>";
  //login form submitted
  $user = $_POST["user"];
  $pw = $_POST["password"];


  $servername = "localhost";
  $username = "admin";
  $password = "\$LackJaw!";
  $db = "customers";

  $conn = new mysqli($servername, $username, $password,$db);
  if (!$conn->set_charset("utf8")) {
      printf("Error loading character set utf8: %s\n", $conn->error);
  }

  if($conn->connect_error){
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
  }
  echo "<p>Starting Query: $query</p>";
  $query = "Select `customerID` , `password` from logins where customerID = '$user'";
  $matches = $conn->query($query);
  echo "<p>Completed Query</p>";
  if($matches->num_rows > 0){
    echo "<p>Building Matches</p>";
    $match = $matches->fetch_assoc();
    $passhash = $match["password"];
    $userid = $match["customerID"];
  } else{
    echo "<p>No matches</p>";
    $error .= "Invalid Username or Password";
  }
  echo "<p>Checking Password</p>";
  if($pw === $passhash){
    echo "<p>Password Verified</p>";
    //we matched so create session vars
    $_SESSION["is_logged"] = "potato";
    $_SESSION["userid"] = $userid;

    //thank them for logging in  and redirect
    header("Location: http://slackjaw.me");
  } else {
    echo "<p>Password Not Matching</p>";
    $error = "Invalid Username or Password";

  }

  $conn->close();
}

?>


<!doctype html />
<html>
<head>
  <title>Login</title>
  <style>
    #box{
      width:400px;
      margin:auto;
      border:black thin solid;
      background:#eee;
      display:block;
      padding:15px;
    }
  </style>
</head>
<body>

  <div id="box">
    <form action="login.php" method="post">
      <h1>Login</h1>
      <?php if($error != ""){echo "<p style='color:red'>".$error."</p>";} ?>
      <label>Customer ID:</label><input type="text" id="user" name="user" /><br />
      <label>Password:<label><input type="password" id="password" name="password" /><br />
      <input type="submit" value="Submit" name="submit" />
    </form>
  </div>
</body>
</html>
