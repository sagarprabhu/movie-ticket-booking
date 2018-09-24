<?php 
require 'db_conn.php';
require 'template.php';
session_start();

function check($conn,$theatre_id,$movie_id,$time,$booked) // booking synchronization function
{
	$sql="SELECT seats FROM booking WHERE theatre_id='$theatre_id' && movie_id='$movie_id' && time='$time'";
	$result=mysqli_query($conn,$sql);
	$disp=array();
	if (mysqli_num_rows($result) > 0) 
	{
		$booked=explode(",",$booked);
		
		while($row = mysqli_fetch_assoc($result))
		{	
			$temp=$row["seats"];
			$temp=explode(",",$temp);
			
			
			for ($i = 0; $i < count($temp); $i++)
				for ($j = 0; $j < count($booked); $j++)
					if( ($temp[$i]==$booked[$j] ) && (!in_array($booked[$j], $disp)) )
					{	
						array_push($disp,$booked[$j]);
					}
			
		}
		if(count($disp)!=0)
		{
			echo "<span style='font: 24px Verdana, Geneva, sans-serif;color:black;'>Seat ".implode(",",$disp)." was not booked.<br></span>";
			echo "<span style='font: bold 34px Georgia, serif;color:red;'><a href='index.php'>Please select your seats again</a><br><br></span>";
			die("Page was killed");	// if booked seats overlap then kill page
			
		}
	}
}

$fname=$_POST["fname"];
$lname=$_POST["lname"];
$email=$_POST["email"];
$cardName=$_POST["cardName"];
$cardNo=$_POST["cardNo"];
$exMonth=$_POST["expiryMonth"];
$exYear=$_POST["expiryYear"];
$cvv=$_POST["cvv"];

$theatre_id=$_SESSION["theatre"];
$movie_id=$_SESSION["movie"];
$booked=$_SESSION["booked"];
$amount=$_SESSION["amount"];
$time=$_SESSION["time"];
$final=$_SESSION["final"];
$final="[".$final."]";


check($conn,$theatre_id,$movie_id,$time,$booked);

$sql="INSERT INTO unreg_user (fname,lname,email)
		values ('$fname','$lname','$email') ";
mysqli_query($conn,$sql);
$last_id = mysqli_insert_id($conn);


$sql="INSERT INTO booking (user_id,theatre_id,movie_id,seats,amount,time) 
		values('$last_id','$theatre_id','$movie_id','$booked','$amount','$time') ";
mysqli_query($conn,$sql);
$last_id = mysqli_insert_id($conn);



$sql="INSERT INTO payment values('$last_id','$cardNo','$cardName','$exMonth','$exYear','$cvv')";
mysqli_query($conn,$sql);

$sql="UPDATE theatre_movie SET seats='$final' 
	WHERE theatre_id='$theatre_id' && movie_id='$movie_id' && time='$time' ";
mysqli_query($conn,$sql);
	
echo "<span style='font: 24px Verdana, Geneva, sans-serif;color:black;'>Seats ".$booked." booked successfully!<br>Show the key sent to your email at the theatre to get tickets.</span>";

$subject = "Key";
$txt = uniqid();
$headers = "From: bookmytickets@gmail.com" ;


//mail($email,$subject,$txt,$headers);		

?>

<!DOCTYPE html>
<html>
<head>
</head>
<body>
</body>
</html>