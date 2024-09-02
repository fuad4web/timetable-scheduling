
 <?php session_start();
if(empty($_SESSION['id'])):
header('Location:../index.php');
endif;
error_reporting(0);
if($_POST)
{
include('../dist/includes/dbcon.php');

	$member = $_POST['teacher'];
	$subject = $_POST['subject'];
	$room = $_POST['room'];
	$cys = $_POST['cys'];
	$remarks = $_POST['remarks'];
	
	$m = $_POST['m'];
	$w = $_POST['w'];
	$f = $_POST['f'];
	$t = $_POST['t'];
	$th = $_POST['th'];
	
	$set_id=$_SESSION['settings'];
	$program=$_SESSION['id'];
	// exit(var_dump($m));
					
	//monday sched
	foreach ($m as $daym) {
		// Check conflict for member
	// exit(var_dump($member, $daym));
		$query_member = mysqli_query($con, "SELECT COUNT(*) as count
		FROM `schedule`
		NATURAL JOIN `member`
		NATURAL JOIN `time`
		WHERE `member_id` = '$member' 
		AND `schedule`.`time_id` = '$daym'
		AND `day` = 'm'");
	
		// Fetch the result
		$row = mysqli_fetch_array($query_member);
		// exit(var_dump());
		$count_t = $row['count'];
	
		if($row['count'] > 0) {
			// If row is valid, process the data
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
            WHERE `member_id` = '$member'
            AND `time_id` = '$daym'
            AND `day` = 'm'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$detail_row = mysqli_fetch_array($detail_result);

			$time1 = date("h:i a", strtotime($detail_row['time_start'])) . "-" . date("h:i a", strtotime($detail_row['time_end']));
			$member1 = $detail_row['member_last'] . ", " . $detail_row['member_first'];

			$error_t = "
				<span class='text-danger'>
				<table width='100%'>
					<tr>
						<td>monday</td>
						<td>$time1</td> 
						<td>$member1</td>
						<td class='text-danger'><b>Not Available</b></td>                    
					</tr>
				</table>
				</span>";
			
			echo $error_t . "<br>";
		}
	
		// conflict for room
		$query_room = mysqli_query($con, "SELECT COUNT(*) as count
		FROM `schedule`
		NATURAL JOIN `member`
		NATURAL JOIN `time`
		WHERE `room` = '$room' 
		AND `schedule`.`time_id` = '$daym'
		AND `day` = 'm'") or die(mysqli_error($con));
	
		$rowr = mysqli_fetch_array($query_room);
		$count_r = $rowr['count'];
		if($count_r > 0) {
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
			WHERE `room` = '$room' 
            AND `schedule`.`time_id` = '$daym'
            AND `day` = 'm'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$rowr = mysqli_fetch_array($detail_result);


			$timer = date("h:i a", strtotime($rowr['time_start'])) . "-" . date("h:i a", strtotime($rowr['time_end']));
			$roomr = $rowr['room'];
		
			$error_r = "
			<span class='text-danger'>
			<table width='100%'>
				<tr>
					<td>monday</td>
					<td>$timer</td> 
					<td>Room $roomr</td>
					<td class='text-danger'><b>Not Available</b></td>                    
				</tr>
			</table>
			</span>";
		}
	
		// Check conflict for class
		$query_class = mysqli_query($con, "SELECT COUNT(*) as count FROM `schedule` 
			NATURAL JOIN `member` NATURAL JOIN `time` 
			WHERE `cys` = '$cys' 
			AND `schedule`.`time_id` = '$daym' 
			AND `day` = 'm'") or die(mysqli_error($con));
	
		$rowc = mysqli_fetch_array($query_class);
		$count_c = $rowc['count'];

		if($count_c > 0) {
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
			WHERE `cys` = '$cys' 
            AND `schedule`.`time_id` = '$daym'
            AND `day` = 'm'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$rowc = mysqli_fetch_array($detail_result);

			$cysc = $rowc['cys'];
			$timec = date("h:i a", strtotime($rowc['time_start'])) . "-" . date("h:i a", strtotime($rowc['time_end']));
		
			$error_c = "
				<span class='text-danger'>
				<table width='100%'>
					<tr>
						<td>monday</td>
						<td>$timec</td> 
						<td>$cysc</td>
						<td class='text-danger'><b>Not Available</b></td>                    
					</tr>
				</table>
				</span>";
		}
	
		// Fetch member details
		$queryt = mysqli_query($con, "SELECT * FROM `member` WHERE `member_id` = '$member'") or die(mysqli_error($con));
		$rowt = mysqli_fetch_array($queryt);
		$membert = $rowt['member_last'] . ", " . $rowt['member_first'];
		// exit(var_dump($membert));
	
		// Fetch time details
		$querytime = mysqli_query($con, "SELECT * FROM `time` WHERE `time_id` = '$daym'") or die(mysqli_error($con));
		$rowt = mysqli_fetch_array($querytime);
		$timet = date("h:i a", strtotime($rowt['time_start'])) . "-" . date("h:i a", strtotime($rowt['time_end']));
	
		// Insert into schedule if no conflicts found
		if (($count_t == 0) && ($count_r == 0) && ($count_c == 0)) {
			mysqli_query($con, "INSERT INTO `schedule` (`time_id`, `day`, `member_id`, `subject_code`, `cys`, `room`, `remarks`, `settings_id`, `encoded_by`) 
				VALUES ('$daym', 'm', '$member', '$subject', '$cys', '$room', '$remarks', '$set_id', '$program')") 
				or die(mysqli_error($con));
	
			echo "<span class='text-success'>
			<table width='100%'>
				<tr>
					<td>monday</td>
					<td>$timet</td> 
					<td>Success</td>                    
				</tr>
			</table>
			</span><br>";
		} else {
			// Output the error messages based on conflicts
			if ($count_t > 0) echo $error_t . "<br>";
			if ($count_r > 0) echo $error_r . "<br>";
			if ($count_c > 0) echo $error_c . "<br>";
		}
	}
	
	//------------------------------------------------
	//wednesday sched
	foreach ($w as $daym){
		$query_member = mysqli_query($con, "SELECT COUNT(*) as count
		FROM `schedule`
		NATURAL JOIN `member`
		NATURAL JOIN `time`
		WHERE `member_id` = '$member' 
		AND `schedule`.`time_id` = '$daym'
		AND `day` = 'w'");
	
		// Fetch the result
		$row = mysqli_fetch_array($query_member);
		// exit(var_dump());
		$count_t = $row['count'];
	
		if($row['count'] > 0) {
			// If row is valid, process the data
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
            WHERE `member_id` = '$member'
            AND `time_id` = '$daym'
            AND `day` = 'w'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$detail_row = mysqli_fetch_array($detail_result);

			$time1 = date("h:i a", strtotime($detail_row['time_start'])) . "-" . date("h:i a", strtotime($detail_row['time_end']));
			$member1 = $detail_row['member_last'] . ", " . $detail_row['member_first'];

			$error_t = "
				<span class='text-danger'>
				<table width='100%'>
					<tr>
						<td>Wednesday</td>
						<td>$time1</td> 
						<td>$member1</td>
						<td class='text-danger'><b>Not Available</b></td>                    
					</tr>
				</table>
				</span>";
			
			echo $error_t . "<br>";
		}
		
		// Check conflict for room
		$query_room = mysqli_query($con, "SELECT COUNT(*) as count
		FROM `schedule`
		NATURAL JOIN `member`
		NATURAL JOIN `time`
		WHERE `room` = '$room' 
		AND `schedule`.`time_id` = '$daym'
		AND `day` = 'w'") or die(mysqli_error($con));
	
		$rowr = mysqli_fetch_array($query_room);
		$count_r = $rowr['count'];
		if($count_r > 0) {
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
			WHERE `room` = '$room' 
            AND `schedule`.`time_id` = '$daym'
            AND `day` = 'w'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$rowr = mysqli_fetch_array($detail_result);


			$timer = date("h:i a", strtotime($rowr['time_start'])) . "-" . date("h:i a", strtotime($rowr['time_end']));
			$roomr = $rowr['room'];
		
			$error_r = "
			<span class='text-danger'>
			<table width='100%'>
				<tr>
					<td>Wednesday</td>
					<td>$timer</td> 
					<td>Room $roomr</td>
					<td class='text-danger'><b>Not Available</b></td>                    
				</tr>
			</table>
			</span>";
		}
	
		// Check conflict for class
		$query_class = mysqli_query($con, "SELECT COUNT(*) as count FROM `schedule` 
			NATURAL JOIN `member` NATURAL JOIN `time` 
			WHERE `cys` = '$cys' 
			AND `schedule`.`time_id` = '$daym' 
			AND `day` = 'w'") or die(mysqli_error($con));
	
		$rowc = mysqli_fetch_array($query_class);
		$count_c = $rowc['count'];

		if($count_c > 0) {
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
			WHERE `cys` = '$cys' 
            AND `schedule`.`time_id` = '$daym'
            AND `day` = 'w'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$rowc = mysqli_fetch_array($detail_result);

			$cysc = $rowc['cys'];
			$timec = date("h:i a", strtotime($rowc['time_start'])) . "-" . date("h:i a", strtotime($rowc['time_end']));
		
			$error_c = "
				<span class='text-danger'>
				<table width='100%'>
					<tr>
						<td>Wednesday</td>
						<td>$timec</td> 
						<td>$cysc</td>
						<td class='text-danger'><b>Not Available</b></td>                    
					</tr>
				</table>
				</span>";
		}
		
		// Fetch member details
		$queryt = mysqli_query($con, "SELECT * FROM `member` WHERE `member_id` = '$member'") 
			or die(mysqli_error($con));
		$rowt = mysqli_fetch_array($queryt);
		$membert = $rowt['member_last'] . ", " . $rowt['member_first'];
		
		// Fetch time details
		$querytime = mysqli_query($con, "SELECT * FROM `time` WHERE `time_id` = '$daym'") 
			or die(mysqli_error($con));
		$rowtime = mysqli_fetch_array($querytime);
		$timet = date("h:i a", strtotime($rowtime['time_start'])) . "-" . date("h:i a", strtotime($rowtime['time_end']));
		
		// Check if no conflicts exist
		if ($count_t == 0 && $count_r == 0 && $count_c == 0) {
			mysqli_query($con, "INSERT INTO `schedule` (`time_id`, `day`, `member_id`, `subject_code`, `cys`, `room`, `remarks`, `settings_id`, `encoded_by`) 
				VALUES ('$daym', 'w', '$member', '$subject', '$cys', '$room', '$remarks', '$set_id', '$program')") 
				or die(mysqli_error($con));
				
			echo "<span class='text-success'>
			<table width='100%'>
				<tr>
					<td>Wednesday</td>
					<td>$timet</td> 
					<td>Success</td>					
				</tr>
			</table></span><br>";
		} else if ($count_t > 0) {
			echo $error_t . "<br>";
		} else if ($count_r > 0) {
			echo $error_r . "<br>";
		} else {
			echo $error_c . "<br>";
		}
	}
	
	
	// //-------------------------------------------------------------
	// //friday sched
	foreach ($f as $daym) {
		// Check conflict for member
		$query_member = mysqli_query($con, "SELECT COUNT(*) as count
		FROM `schedule`
		NATURAL JOIN `member`
		NATURAL JOIN `time`
		WHERE `member_id` = '$member' 
		AND `schedule`.`time_id` = '$daym'
		AND `day` = 'f'");
	
		// Fetch the result
		$row = mysqli_fetch_array($query_member);
		// exit(var_dump());
		$count_t = $row['count'];
	
		if($row['count'] > 0) {
			// If row is valid, process the data
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
            WHERE `member_id` = '$member'
            AND `time_id` = '$daym'
            AND `day` = 'f'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$detail_row = mysqli_fetch_array($detail_result);

			$time1 = date("h:i a", strtotime($detail_row['time_start'])) . "-" . date("h:i a", strtotime($detail_row['time_end']));
			$member1 = $detail_row['member_last'] . ", " . $detail_row['member_first'];

			$error_t = "
				<span class='text-danger'>
				<table width='100%'>
					<tr>
						<td>Friday</td>
						<td>$time1</td> 
						<td>$member1</td>
						<td class='text-danger'><b>Not Available</b></td>                    
					</tr>
				</table>
				</span>";
			
			echo $error_t . "<br>";
		}
		
		// Check conflict for room
		$query_room = mysqli_query($con, "SELECT COUNT(*) as count
		FROM `schedule`
		NATURAL JOIN `member`
		NATURAL JOIN `time`
		WHERE `room` = '$room' 
		AND `schedule`.`time_id` = '$daym'
		AND `day` = 'f'") or die(mysqli_error($con));
	
		$rowr = mysqli_fetch_array($query_room);
		$count_r = $rowr['count'];
		if($count_r > 0) {
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
			WHERE `room` = '$room' 
            AND `schedule`.`time_id` = '$daym'
            AND `day` = 'f'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$rowr = mysqli_fetch_array($detail_result);


			$timer = date("h:i a", strtotime($rowr['time_start'])) . "-" . date("h:i a", strtotime($rowr['time_end']));
			$roomr = $rowr['room'];
		
			$error_r = "
			<span class='text-danger'>
			<table width='100%'>
				<tr>
					<td>Friday</td>
					<td>$timer</td> 
					<td>Room $roomr</td>
					<td class='text-danger'><b>Not Available</b></td>                    
				</tr>
			</table>
			</span>";
		}
	
		// Check conflict for class
		$query_class = mysqli_query($con, "SELECT COUNT(*) as count FROM `schedule` 
			NATURAL JOIN `member` NATURAL JOIN `time` 
			WHERE `cys` = '$cys' 
			AND `schedule`.`time_id` = '$daym' 
			AND `day` = 'f'") or die(mysqli_error($con));
	
		$rowc = mysqli_fetch_array($query_class);
		$count_c = $rowc['count'];

		if($count_c > 0) {
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
			WHERE `cys` = '$cys' 
            AND `schedule`.`time_id` = '$daym'
            AND `day` = 'f'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$rowc = mysqli_fetch_array($detail_result);

			$cysc = $rowc['cys'];
			$timec = date("h:i a", strtotime($rowc['time_start'])) . "-" . date("h:i a", strtotime($rowc['time_end']));
		
			$error_c = "
				<span class='text-danger'>
				<table width='100%'>
					<tr>
						<td>Friday</td>
						<td>$timec</td> 
						<td>$cysc</td>
						<td class='text-danger'><b>Not Available</b></td>                    
					</tr>
				</table>
				</span>";
		}
	
		// Fetch member details for success output
		$query_member_info = mysqli_query($con, "SELECT * FROM `member` WHERE `member_id`='$member'") or die(mysqli_error($con));
		$row_member_info = mysqli_fetch_array($query_member_info);
		$member_full_name = $row_member_info['member_last'] . ", " . $row_member_info['member_first'];
	
		// Fetch time details for success output
		$query_time = mysqli_query($con, "SELECT * FROM `time` WHERE `time_id` = '$daym'") or die(mysqli_error($con));
		$row_time = mysqli_fetch_array($query_time);
		$time_range = date("h:i a", strtotime($row_time['time_start'])) . " - " . date("h:i a", strtotime($row_time['time_end']));
	
		// Insert schedule if no conflicts exist
		if ($count_member == 0 && $count_room == 0 && $count_class == 0) {
			mysqli_query($con, "INSERT INTO `schedule` (`time_id`, `day`, `member_id`, `subject_code`, `cys`, `room`, `remarks`, `settings_id`, `encoded_by`) 
				VALUES ('$daym', 'f', '$member', '$subject', '$cys', '$room', '$remarks', '$set_id', '$program')") or die(mysqli_error($con));
	
			echo "
			<span class='text-success'>
				<table width='100%'>
					<tr>
						<td>Friday</td>
						<td>$time_range</td>
						<td>Success</td>
					</tr>
				</table>
			</span><br>";
		}
		// Handle conflict outputs
		else if ($count_member > 0) {
			echo $error_member . "<br>";
		} else if ($count_room > 0) {
			echo $error_room . "<br>";
		} else {
			echo $error_class . "<br>";
		}
	}
	
	// //------------------------------------------------
	// //tuesday sched
	foreach ($t as $daym) {
		// Check conflict for member
		$query_member = mysqli_query($con, "SELECT COUNT(*) as count
		FROM `schedule`
		NATURAL JOIN `member`
		NATURAL JOIN `time`
		WHERE `member_id` = '$member' 
		AND `schedule`.`time_id` = '$daym'
		AND `day` = 't'");
	
		// Fetch the result
		$row = mysqli_fetch_array($query_member);
		// exit(var_dump());
		$count_t = $row['count'];
	
		if($row['count'] > 0) {
			// If row is valid, process the data
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
            WHERE `member_id` = '$member'
            AND `time_id` = '$daym'
            AND `day` = 't'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$detail_row = mysqli_fetch_array($detail_result);

			$time1 = date("h:i a", strtotime($detail_row['time_start'])) . "-" . date("h:i a", strtotime($detail_row['time_end']));
			$member1 = $detail_row['member_last'] . ", " . $detail_row['member_first'];

			$error_t = "
				<span class='text-danger'>
				<table width='100%'>
					<tr>
						<td>Tuesday</td>
						<td>$time1</td> 
						<td>$member1</td>
						<td class='text-danger'><b>Not Available</b></td>                    
					</tr>
				</table>
				</span>";
			
			echo $error_t . "<br>";
		}
		
		// Check conflict for room
		$query_room = mysqli_query($con, "SELECT COUNT(*) as count
		FROM `schedule`
		NATURAL JOIN `member`
		NATURAL JOIN `time`
		WHERE `room` = '$room' 
		AND `schedule`.`time_id` = '$daym'
		AND `day` = 't'") or die(mysqli_error($con));
	
		$rowr = mysqli_fetch_array($query_room);
		$count_r = $rowr['count'];
		if($count_r > 0) {
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
			WHERE `room` = '$room' 
            AND `schedule`.`time_id` = '$daym'
            AND `day` = 't'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$rowr = mysqli_fetch_array($detail_result);


			$timer = date("h:i a", strtotime($rowr['time_start'])) . "-" . date("h:i a", strtotime($rowr['time_end']));
			$roomr = $rowr['room'];
		
			$error_r = "
			<span class='text-danger'>
			<table width='100%'>
				<tr>
					<td>Tuesday</td>
					<td>$timer</td> 
					<td>Room $roomr</td>
					<td class='text-danger'><b>Not Available</b></td>                    
				</tr>
			</table>
			</span>";
		}
	
		// Check conflict for class
		$query_class = mysqli_query($con, "SELECT COUNT(*) as count FROM `schedule` 
			NATURAL JOIN `member` NATURAL JOIN `time` 
			WHERE `cys` = '$cys' 
			AND `schedule`.`time_id` = '$daym' 
			AND `day` = 't'") or die(mysqli_error($con));
	
		$rowc = mysqli_fetch_array($query_class);
		$count_c = $rowc['count'];

		if($count_c > 0) {
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
			WHERE `cys` = '$cys' 
            AND `schedule`.`time_id` = '$daym'
            AND `day` = 't'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$rowc = mysqli_fetch_array($detail_result);

			$cysc = $rowc['cys'];
			$timec = date("h:i a", strtotime($rowc['time_start'])) . "-" . date("h:i a", strtotime($rowc['time_end']));
		
			$error_c = "
				<span class='text-danger'>
				<table width='100%'>
					<tr>
						<td>Tuesday</td>
						<td>$timec</td> 
						<td>$cysc</td>
						<td class='text-danger'><b>Not Available</b></td>                    
					</tr>
				</table>
				</span>";
		}
	
		// Fetch member information
		$queryt = mysqli_query($con, "SELECT * FROM `member` WHERE `member_id`='$member'") or die(mysqli_error($con));
		$rowt = mysqli_fetch_array($queryt);
		$membert = $rowt['member_last'] . ", " . $rowt['member_first'];
	
		// Fetch time details
		$querytime = mysqli_query($con, "SELECT * FROM `time` WHERE `time_id`='$daym'") or die(mysqli_error($con));
		$rowt = mysqli_fetch_array($querytime);
		$timet = date("h:i a", strtotime($rowt['time_start'])) . " - " . date("h:i a", strtotime($rowt['time_end']));
	
		// Insert schedule if no conflicts exist
		if ($count_t == 0 && $count_r == 0 && $count_c == 0) {
			mysqli_query($con, "INSERT INTO `schedule`(`time_id`, `day`, `member_id`, `subject_code`, `cys`, `room`, `remarks`, `settings_id`, `encoded_by`) 
				VALUES('$daym', 't', '$member', '$subject', '$cys', '$room', '$remarks', '$set_id', '$program')") or die(mysqli_error($con));
	
			echo "
			<span class='text-success'>
				<table width='100%'>
					<tr>
						<td>Tuesday</td>
						<td>$timet</td> 
						<td>Success</td>                    
					</tr>
				</table>
			</span><br>";
		}
		// Handle conflict outputs
		else if ($count_t > 0) {
			echo $error_t . "<br>";
		} else if ($count_r > 0) {
			echo $error_r . "<br>";
		} else {
			echo $error_c . "<br>";
		}
	}
	
	
	// //--------------------------------------------------
	// //thursday sched
	foreach ($th as $daym) {
		// Check conflict for member
		$query_member = mysqli_query($con, "SELECT COUNT(*) as count
		FROM `schedule`
		NATURAL JOIN `member`
		NATURAL JOIN `time`
		WHERE `member_id` = '$member' 
		AND `schedule`.`time_id` = '$daym'
		AND `day` = 'th'");
	
		// Fetch the result
		$row = mysqli_fetch_array($query_member);
		// exit(var_dump());
		$count_t = $row['count'];
	
		if($row['count'] > 0) {
			// If row is valid, process the data
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
            WHERE `member_id` = '$member'
            AND `time_id` = '$daym'
            AND `day` = 'th'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$detail_row = mysqli_fetch_array($detail_result);

			$time1 = date("h:i a", strtotime($detail_row['time_start'])) . "-" . date("h:i a", strtotime($detail_row['time_end']));
			$member1 = $detail_row['member_last'] . ", " . $detail_row['member_first'];

			$error_t = "
				<span class='text-danger'>
				<table width='100%'>
					<tr>
						<td>Thursday</td>
						<td>$time1</td> 
						<td>$member1</td>
						<td class='text-danger'><b>Not Available</b></td>                    
					</tr>
				</table>
				</span>";
			
			echo $error_t . "<br>";
		}
		
		// Check conflict for room
		$query_room = mysqli_query($con, "SELECT COUNT(*) as count
		FROM `schedule`
		NATURAL JOIN `member`
		NATURAL JOIN `time`
		WHERE `room` = '$room' 
		AND `schedule`.`time_id` = '$daym'
		AND `day` = 'th'") or die(mysqli_error($con));
	
		$rowr = mysqli_fetch_array($query_room);
		$count_r = $rowr['count'];
		if($count_r > 0) {
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
			WHERE `room` = '$room' 
            AND `schedule`.`time_id` = '$daym'
            AND `day` = 'th'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$rowr = mysqli_fetch_array($detail_result);


			$timer = date("h:i a", strtotime($rowr['time_start'])) . "-" . date("h:i a", strtotime($rowr['time_end']));
			$roomr = $rowr['room'];
		
			$error_r = "
			<span class='text-danger'>
			<table width='100%'>
				<tr>
					<td>Thursday</td>
					<td>$timer</td> 
					<td>Room $roomr</td>
					<td class='text-danger'><b>Not Available</b></td>                    
				</tr>
			</table>
			</span>";
		}
	
		// Check conflict for class
		$query_class = mysqli_query($con, "SELECT COUNT(*) as count FROM `schedule` 
			NATURAL JOIN `member` NATURAL JOIN `time` 
			WHERE `cys` = '$cys' 
			AND `schedule`.`time_id` = '$daym' 
			AND `day` = 'th'") or die(mysqli_error($con));
	
		$rowc = mysqli_fetch_array($query_class);
		$count_c = $rowc['count'];

		if($count_c > 0) {
			$detail_query = "
            SELECT `schedule`.*, `member`.*, `time`.*
            FROM `schedule`
            NATURAL JOIN `member`
            NATURAL JOIN `time`
			WHERE `cys` = '$cys' 
            AND `schedule`.`time_id` = '$daym'
            AND `day` = 'th'";
			$detail_result = mysqli_query($con, $detail_query) or die(mysqli_error($con));
			$rowc = mysqli_fetch_array($detail_result);

			$cysc = $rowc['cys'];
			$timec = date("h:i a", strtotime($rowc['time_start'])) . "-" . date("h:i a", strtotime($rowc['time_end']));
		
			$error_c = "
				<span class='text-danger'>
				<table width='100%'>
					<tr>
						<td>Thursday</td>
						<td>$timec</td> 
						<td>$cysc</td>
						<td class='text-danger'><b>Not Available</b></td>                    
					</tr>
				</table>
				</span>";
		}
	
		// Fetch member details
		$queryt = mysqli_query($con, "SELECT * FROM `member` WHERE `member_id` = '$member'") or die(mysqli_error($con));
		$rowt = mysqli_fetch_array($queryt);
		$membert = $rowt['member_last'] . ", " . $rowt['member_first'];
	
		// Fetch time details
		$querytime = mysqli_query($con, "SELECT * FROM `time` WHERE `time_id` = '$daym'") or die(mysqli_error($con));
		$rowt = mysqli_fetch_array($querytime);
		$timet = date("h:i a", strtotime($rowt['time_start'])) . "-" . date("h:i a", strtotime($rowt['time_end']));
	
		// Insert schedule if no conflicts
		if (($count_t == 0) and ($count_r == 0) and ($count_c == 0)) {
			mysqli_query($con, "INSERT INTO `schedule`(`time_id`, `day`, `member_id`, `subject_code`, `cys`, `room`, `remarks`, `settings_id`, `encoded_by`) 
				VALUES('$daym', 'th', '$member', '$subject', '$cys', '$room', '$remarks', '$set_id', '$program')") or die(mysqli_error($con));
	
			echo "<span class='text-success'>
				<table width='100%'>
					<tr>
						<td>Thursday</td>
						<td>$timet</td>
						<td>Success</td>
					</tr>
				</table>
				</span><br>";
		} else if ($count_t > 0) {
			echo $error_t . "<br>";
		} else if ($count_r > 0) {
			echo $error_r . "<br>";
		} else {
			echo $error_c . "<br>";
		}
	}
	
		
}					  
