<?php

/**********************************************************************

timely, a webbased calender
Copyright (C) 2013  Markus Klein <m@mklein.co.at>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.


Made with <3

***********************************************************************/


if((isset($_GET['month']) && isset($_GET['year'])) || isset($_GET['calender_id'])){
	include('../include.php');
}
//if the user is not logged in return error
if($_SESSION['logged_in'] != true){
	echo('error');
	exit();
}
//get the request information
elseif(isset($_GET['month']) && isset($_GET['year'])){
	$month = $_GET['month'];
	$year = $_GET['year'];
	$first_day = date('N', strtotime(1 . date("F", mktime(0, 0, 0, $month, 10)) . $year));
	$_SESSION['current_date_month'] = $month;
	$_SESSION['current_date_year'] = $year;
}
elseif(isset($_GET['calender_id'])){
	$_SESSION['active_calender_id'] = $_GET['calender_id'];
	$date = getdate();
	$month = $date['mon'];
	$year = $date['year'];
	$first_day = date('N', strtotime(1 . $date['month'] . $year));
	$_SESSION['current_date_month'] = $month;
	$_SESSION['current_date_year'] = $year;
}
//if the script is called from the server, render the default calender
else{
	echo('<div class="container" id="calender-content">');
	if(isset($_SESSION['current_date_month']) && isset($_SESSION['current_date_year'])){
		$month = $_SESSION['current_date_month'];
		$year = $_SESSION['current_date_year'];
		$first_day = date('N', strtotime(1 . date("F", mktime(0, 0, 0, $month, 10)) . $year));
	}
	else{
		$date = getdate();
		$month = $date['mon'];
		$year = $date['year'];
		$first_day = date('N', strtotime(1 . $date['month'] . $year));
		$_SESSION['current_date_month'] = $month;
		$_SESSION['current_date_year'] = $year;
	}
}
if($first_day == 7){
	$first_day = 1;
}
?>
<div class="calender-select">
	<p>
    	Active Calender: 
        <select>
        <?php
		//set the calenders
		$query = "SELECT id, name"
			. " FROM timely_calender"
			. " WHERE owner_id = " . $_SESSION['ID'];
		
		$get_calenders = mysql_query($query);
		if(!$get_calenders){
			die("Database error: " . mysql_error());
		}
		while($row = mysql_fetch_assoc($get_calenders)){
			echo('<option' . ($row['id'] == $_SESSION['active_calender_id'] ? ' selected="selected"' : '') . ' value="' . $row['id'] . '">' . "\n" . $row['name'] . "\n" . '</option>' . "\n");
		}
		?>
        </select>
    </p>
</div>
<div class="div-center">
	<h2 id="year"><button class="btn btn-link" id="prev_year">&larr;</button><?php echo $year ?><button class="btn btn-link" id="next_year">&rarr;</button></h2>
</div>
<div class="pagination pagination-centered pagination-small">
	<ul>
    	<?php
		$months = array("January","February","March","April","May","June","July","August","September","October","November","December");
		for($i = 0; $i < 12; $i++){
			echo '<li ' . ($i == $month-1 ? 'class="active"' : '') . '><a class="month-pagination" onclick="changeMonth(' . ($i+1) .')">' . $months[$i] . '</a></li>' . "\n";
		}
		?>
    </ul>
</div>

<table class="table table-bordered table-condensed calender-table">
<tr class="calender-table-header-row">
  <th>Sunday</th>
  <th>Monday</th>
  <th>Tuesday</th>
  <th>Wednesday</th>
  <th>Thursday</th>
  <th>Friday</th>
  <th>Saturday</th>
</tr>

<?php
//get the appointments from database
$query = "SELECT content, calender_id, date_day"
	. " FROM timely_appointment"
	. " WHERE date_year = '" . $year . "' and date_month = '" . $month . "'";

$appointments_query = mysql_query($query);	
if(!$appointments_query){
	die("Database error: " . mysql_error());
}

if(mysql_num_rows($appointments_query)){
	while($row = mysql_fetch_assoc($appointments_query)){
		if($row['calender_id'] == $_SESSION['active_calender_id']){
			$appointments[$row['date_day']] = $row['content'];
		}
	}
}

//render the calender
$day=1;
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
for($j = 0; $j<10; $j++){
	echo('<tr>' . "\n");
	for($i = 0; $i < 7; $i++){
		if(($i < $first_day && $j == 0) || $day > $daysInMonth){
			echo ('<td class="calender-inactive"></td>' . "\n");
		}
		else{
			echo('<td class="appointment">' . "\n" . '<span class="badge badge-inverse calender-day-header">' . $day . '</span>' . "\n" . '<textarea class="appointment-textarea">' . "\n");
			if(isset($appointments[$day])){
				echo($appointments[$day]);
			}
			echo('</textarea>' . "\n" . '<input type="hidden" class="calender-day" value="' . $day . '">' . "\n" . '</td>' . "\n");
			$day++;
		}
	}
	echo('</tr>' . "\n");
	if($day > $daysInMonth){
		break;
	}
}
?>
</table>
<input type="hidden" id="calender-month" value="<?php echo $month ?>">
<input type="hidden" id="calender-year" value="<?php echo $year ?>">
<input type="hidden" id="calender-id" value="<?php echo $_SESSION['current_calender_id'] ?>">
</div>