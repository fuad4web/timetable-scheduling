 <?php session_start();
if(empty($_SESSION['id'])):
header('Location:../index.php');
endif;include("../dist/includes/dbcon.php");
$id=$_REQUEST['id'];
$result=mysqli_query($con,"DELETE FROM cys WHERE cys_id ='$id'")
	or die(mysqli_error($con));
	echo "<script type='text/javascript'>alert('Successfully deleted a class!');</script>";	
		
	echo "<script>document.location='class.php'</script>";  
?>