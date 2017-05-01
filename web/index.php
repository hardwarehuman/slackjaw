<?php
//Requirement U1, U2 Start

//Requirement S3, S4 Start
session_start();
$logged = "no";
if (isset($_SESSION["is_logged"])) {
    $logged = "yes";
    $userid = $_SESSION["userid"];
} else {
    header("Location: http://slackjaw.me/login.php");
}
//Requirement S3, S4 End

        //Requirement S2 Start
        if (isset($_FILES['UploadFileField'])) {
            // Creates the Variables needed to upload the file
            $UploadName = $_FILES['UploadFileField']['name'];
            $UploadTmp = $_FILES['UploadFileField']['tmp_name'];
            $UploadType = $_FILES['UploadFileField']['type'];
            $FileSize = $_FILES['UploadFileField']['size'];

            //Optional Parameters
            $customerID = $userid;
            $Return = 'silent';//$_POST['return'];

            // Removes Unwanted Spaces and characters from the files names of the files being uploaded
            $UploadName = preg_replace("#[^a-z0-9.]#i", "", $UploadName);
            // Upload File Size Limit
            if (($FileSize > 125000)) {
                die("Error - File too Big");
            }
            // Checks a File has been Selected and Uploads them into a Directory on your Server
            if (!$UploadTmp) {
                die("No File Selected, Please Upload Again");
            } else {
                move_uploaded_file($UploadTmp, "$UploadName");
                    #echo "<pre>./preproc_runner.sh $UploadName $customerID $Return</pre>";
                    $execute = "<pre>./preproc_runner.sh $UploadName $customerID $Return</pre>";
                    #echo $execute;
                    echo shell_exec("./preproc_runner.sh $UploadName $customerID $Return");
            }
        }
        //Requirement S2 End

            //Requirement S1 Start
            if (isset($_GET["search"])) {
                $search = $_GET["search"];
                if (isset($_GET["dateFrom"])) {
                    $dateFrom = $_GET["dateFrom"];
                }
                if (isset($_GET["dateTo"])) {
                    $dateTo = $_GET["dateTo"];
                }

                $scriptLocation = "search_archive.sh";

                //Requirement S4
                $customerID = $userid;

                $dateRange = $dateFrom . ":". $dateTo;

                $script = "sh $scriptLocation $customerID $dateRange $search";
                $return = shell_exec($script);


                //$customerID = "cust-1234"; //later will get from login info
                $root = "";
                $path = trim("$customerID/$return");
                $file = "$path";
                $queryResults = "";
                clearstatcache();
                $results = file($path);
                if ($results === false) {
                    echo "<p>An Error Has Occured</p>";
                } else {
                    foreach ($results as $match) {
                        $queryResults .= "<tr>\n";
                        $match = str_getcsv($match);
                        $stamp = explode(':', $match[0]);
                        $stamp = end($stamp);
                        $stamp = explode('.', $stamp);
                        $stamp = date('Y-m-d', $stamp[0]);
                        $queryResults .= "<td>$stamp</td>\n";
                        $channel = $match[2];
                        $channel =  explode(':', $channel);
                        $channel = $channel[1];
                        $queryResults .= "<td>$channel</td>\n";
                        $user = $match[1];
                        $queryResults .= "<td>$user</td>\n";
                        $content = $match[3];
                        $queryResults .= "<td>$content</td>\n";
                    }
                }
            }
            //Requirement S1 End
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>SlackJaw</title>
		<link rel="icon" type="image/gif" href="hashbrush.png" />
		<link rel="stylesheet" type="text/css" href="style.css" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
		<div id="header">
			<div id="headcontainer">
				<div id="logo">
					<A HREF="http://www.slackjaw.me"><img src="slackjaw_me.png" alt="Logo" style="width:auto; height:100%;"></A>
				</div>
			</div>
			<div class="text">

			</div>
			<div class="menu">

			</div>
		</div>
		<div class="spacer">
			&nbsp;
		</div>

		<div id="content">
			<div id="title">
				<p1><A HREF="../..">Home  </A></p1>
				<p1>You are logged in as <?php echo $userid; ?>, do you wish to <a href="logout.php">logout</a>?</p1>
			</div>

<!--[Upload Box]-->

				<div id="upload">
					<div class="title">
						<h3>Upload Archive</br></br></h3>
					</div>
					<div class="fileuploadholder">
						<form action="index.php" method="post" enctype="multipart/form-data" name="FileUploadForm" id="FileUploadForm">
							<label for="UploadFileField"></label>
								<!--<label for="CustomerId">Customer ID: </label>
								<input type="text" class="tftextinput" name="CustomerId" size="15" maxlength="150">
              </br>
								<input type="radio" name="return" value="debug" checked> Debug<br>
								<input type="radio" name="return" value="silent"> Silent<br>`-->
								<label for="UploadFileField">.zip, .tar</label>
								<input type="file" name="UploadFileField" id="UploadFileField" accept="zip tar.gz tar.bz"/>
								<input type="submit" name="UploadButton" id="UploadButton" value="Upload" />
						</form>
					</div>
				</div>
<!--[End of Upload Box]-->
				<div id="main">
					<article>
						<h2 align="center">Slackjaw</h2>
							<p>Processes exported Slack transcripts for search</p>
						<hr width="95%" size="2" align="center">
						<div>
							<h3>Expected to encompass three basic functions:</h3>
							<p>* Ingest slack transcripts</br>* Perform search across all chat streams</br>* Present results in a webpage</br></p>
						</div>
					</article>
				</div>


			<!--[Search Box]-->
			<div id="nav">
				<div class="title">
					<h3>Search</br></br></h3>
				</div>
				<div class="formWrapper">
					<form id="searchbar" method="get" action="index.php">
						<table>
							<!--<tr>
								<td>
									<div class="details">
										<label for="custID">Customer ID</label>
										<input type="text" name="custID" <?php //if(isset($customerID))echo "value='$customerID'";?>>
									</div>
								</td>
							</tr>-->
							<tr>
								<td>
									<div class="details">
										<label for="dateFrom">Date From:</label>
										<input type="date" name="dateFrom" <?php if (isset($dateFrom)) {
    echo "value='$dateFrom'";
} ?>>
									</div>
								</td>
								<td>
									<div class="details">
										<label for="dateTo">Date To:</label>
										<input type="date" name="dateTo" <?php if (isset($dateTo)) {
    echo "value='$dateTo'";
} ?>>
									</div>
								</td>
							</tr>
						</table>
						<div class="details">
							<label for="search">Search String:</label>
							<input type="text" class="tftextinput" name="search" size="50" maxlength="200" <?php if (isset($search)) {
    echo "value='$search'";
} ?>>
						</div>
						<p>
						<fieldset>
							<input class="btn" name="submit" type="submit" value="search"/>
							<input class="btn" name="reset" type="reset" value="clear form">
						</fieldset>
					</form>
					<br clear="left">
					<div id="results">
					<?php
                            if (isset($queryResults) && $queryResults != "") {
                                echo "<table border='1'>";
                                echo        "<tr>";
                                echo        "<th>Date</th><th>Channel</th><th>User</th><th>Content</th>";
                                echo    "</tr>";
                                echo $queryResults;
                                echo "</table>";
                            } elseif (isset($queryResults)) {
                                echo "<p>No Results Found</p>";
                            }
                    ?>
					</div>
				</div>
			</div>

<!--[End of Search Box]-->

	<div id="footer">
			Not associated with Slack Technologies
	</div>
</div>
</body>
</html>
<?php //Requirement U1, U2 End?>
