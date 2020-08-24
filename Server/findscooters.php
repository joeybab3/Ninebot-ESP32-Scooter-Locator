<?php
date_default_timezone_set('America/Los_Angeles');
require "autoloader.php";

use Joeybab3\Database as Database;

$D = new Database("username","password","database");
$D->init("FS Init");

$collection = 1;

header('Content-Type: application/json');

$query = $D->prepare("SELECT * FROM `locations` WHERE `collection` = ?;");
$query->bindValue(1, $collection);
$query->execute();
$scooters = $query->fetchAll(\PDO::FETCH_ASSOC);

$query2 = $D->prepare("SELECT * FROM `stations`;");
$query2->execute();
$stations = $query2->fetchAll(\PDO::FETCH_ASSOC);

foreach($scooters as &$scooter)
{
	$lastcheckin = strtotime($scooter['last_checked_in']);
	$times = time() - $lastcheckin;
	
	unset($scooter['id']);
	
	$unit = "second";
	if($times > 59)
	{
		// minutes
		$times = floor($times / 60);
		$unit = "minute";
		if($times > 59)
		{
			//hours
			$times = floor($times / 60);
			$unit = "hour";
			if($times > 23)
			{
				//days
				$unit = "day";
				$times = floor($times / 24);
				if($times > 7)
				{
					unset($scooter);
				}
			}
		}
	}
	
	if($times > 1 || $times < 1)
	{
		$unit .= "s";
	}
	
	$scooter['lastseen'] = $times . " " . $unit . " ago";
	$scooter['howlong'] = time() - $lastcheckin;
}

foreach($stations as &$station)
{
	$lastcheckin = strtotime($station['last_checked_in']);
	$times = time() - $lastcheckin;
	
	unset($station['id']);
	
	$unit = "second";
	if($times > 59)
	{
		// minutes
		$times = floor($times / 60);
		$unit = "minute";
		if($times > 59)
		{
			//hours
			$times = floor($times / 60);
			$unit = "hour";
			if($times > 23)
			{
				//days
				$unit = "day";
				$times = floor($times / 24);
				if($times > 7)
				{
					unset($station);
				}
			}
		}
	}
	
	if($times > 1 || $times < 1)
	{
		$unit .= "s";
	}
	
	$station['lastseen'] = $times . " " . $unit . " ago";
	$station['howlong'] = time() - $lastcheckin;
}

$return['scooters'] = $scooters;
$return['stations'] = $stations;

echo json_encode($return);