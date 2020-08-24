<?php

require "vendor/autoload.php";
require "credentials.php";

use Joeybab3\Database as Database;

$D = new Database($username,$password,$database);
$D->init("PS Init");

if(isset($_GET['station']))
{
	$stationid = $_GET['station'];

	$query = $D->prepare("SELECT * FROM `stations` WHERE `station_id` = ?;");
	$query->bindValue(1, $stationid);
	$query->execute();
	
	$station = $query->fetch(\PDO::FETCH_ASSOC);
	
	if(!$station)
	{
		$query = $D->prepare("INSERT INTO `stations` (`id`, `station_id`, `last_checked_in`) VALUES (NULL, ?, CURRENT_TIMESTAMP);");
		$query->bindValue(1, $stationid);
		$query->execute();
	}
	else
	{
		$query = $D->prepare("UPDATE `stations` SET `last_checked_in` = CURRENT_TIME() WHERE `station_id` = ?;");
		$query->bindValue(1, $stationid);
		$query->execute();
		
		if(isset($_GET['mac']))
		{
			$mac = $_GET['mac'];
			
			$query = $D->prepare("SELECT * FROM `devices` WHERE `mac` = ?;");
			$query->bindValue(1, $mac);
			$query->execute();
			
			$scooter = $query->fetch(\PDO::FETCH_ASSOC);
			
			if(!$scooter)
			{
				$query = $D->prepare("INSERT INTO `stations` (`id`, `number`, `collection`, `name`, `mac`, `last_station_id`, `last_checked_in`) VALUES (NULL, '99', '1', 'EGHD-#', ?, ?, CURRENT_TIMESTAMP);");
				$query->bindValue(1, $mac);
				$query->bindValue(2, $stationid);
				$query->execute();
			}
			else
			{
				if(isset($_GET['name']) && $_GET['name'] != "")
				{
					$query = $D->prepare("UPDATE `stations` SET `last_station_id` = ?, `last_checked_in` = CURRENT_TIME(), `name` = ? WHERE `mac` = ?;");
				}
				else
				{
					$query = $D->prepare("UPDATE `stations` SET `last_station_id` = ?, `last_checked_in` = CURRENT_TIME() WHERE `mac` = ?;");
				}
				
				$query->bindValue(1, $stationid);
				
				if(isset($_GET['name']) && $_GET['name'] != "")
				{
					$query->bindValue(2, $_GET['name']);
					$query->bindValue(3, $mac);
				}
				else
				{
					$query->bindValue(2, $mac);
				}
				
				$result = $query->execute();
			}
		}
	}
}
else
{
	var_dump($_GET);
}