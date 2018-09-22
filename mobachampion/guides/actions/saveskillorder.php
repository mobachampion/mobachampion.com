<?php 
require_once('../../forum/SSI.php');
require('../../rb/rb.php');
require('../../rb/connect.php');

$guideid = $_POST['guideid'];
$id = $_POST['skillorder'];
$basic = $_POST['basic'];
$adv = $_POST['adv'];
$choice = $_POST['choice'];
$desc = $_POST['desc'];

$sectionType = 'Skill Order';
$sectionName = 'guidev2skillorder';

function ValidateLength($str, $len)
{
	$myLength = strlen($str);
	if ($myLength > $len)
	{
		return false;
	}
	
	return true;
}

function CheckForBadStuff($badstuff)
{
	if (strpos($badstuff,'<script>') !== false ||
		strpos($badstuff,'</script>') !== false ||
		strpos($badstuff,'iframe') !== false) 
	{
		return true;
	}
	
	return false;
}

if ($context['user']['is_logged'])
{	
	if ($_SESSION['ban']['cannot_access'] != NULL)
	{
		$reason = 'Unknown error saving guide (code: 39). Please try again later.';
		$data = array('fail'=> true,'message'=>$reason, 'id' => 0);
		echo json_encode($data);
		exit(0);
	}
	
	if (CheckForBadStuff($guideid) || 
		CheckForBadStuff($id) ||
		CheckForBadStuff($basic) || 
		CheckForBadStuff($adv) ||
		CheckForBadStuff($desc))
	{
		$reason = 'Javascript injection detected.';
		$data = array('fail'=> true,'message'=>$reason, 'id' => 0);
		echo json_encode($data);
		exit(0);
	}
	
	$section = null;
	if (is_null($id) || $id < 0)
	{
		$section = R::dispense($sectionName);
	}
	else
	{
		$section = R::load($sectionName, $id);
		$guide = R::load('guidev2', $guideid);
		
		// Check
		if ($guide->author != $context['user']['name'] ||
			$section->owner != $context['user']['name'])
		{
			$reason = 'You tried to save to a ' . $sectionType . ' that does not belong to you (' . $guide->author . ',' . $section->owner . ')';
			$data = array('fail'=> true,'message'=>$reason, 'id' => 0);
			echo json_encode($data);
			exit(0);
		}
	}

	if ($section->id >= 0)
	{
		$success = true;
		$reason = $sectionType . 's saved successfully';
	
		// All sections musth ave this variable
		$section->owner = $context['user']['name'];

		// Section Length Validation
		if (!ValidateLength($desc, 10000))
		{
			$reason = $sectionType . ' has a description that is too long (max 10000 characters)';
			$data = array('fail'=> true,'message'=>$reason, 'id' => 0);
			echo json_encode($data);
			exit(0);
		}

		// Section specific variables
		$section->basic = $basic;
		$section->adv = $adv;
		$section->choice = $choice;
		$section->desc = $desc;
		
		// Store
		$id = R::store($section);
	}
}
else
{
	$success = false;
	$reason = "Not logged in";
}

$data = array('success'=> true,'message'=>$reason, 'id' => $id);
echo json_encode($data);

?>
