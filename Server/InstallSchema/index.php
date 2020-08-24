 <?php
	require "../vendor/autoload.php";

	use Joeybab3\Database as Database;
	
	$devices = 
	"CREATE TABLE `devices` (
	  `id` int(11) NOT NULL,
	  `number` int(11) NOT NULL,
	  `collection` int(11) NOT NULL,
	  `name` varchar(32) NOT NULL,
	  `mac` varchar(64) NOT NULL,
	  `last_station_id` varchar(32) NOT NULL,
	  `last_checked_in` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;
	";
	
	$devicesPrimaryKey = 
	"ALTER TABLE `devices`
	  ADD PRIMARY KEY (`id`);
	";
	
	$devicesAutoIncrement =
	"ALTER TABLE `devices`
	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
	";
	
	$stations = 
	"CREATE TABLE `stations` (
	  `id` int(11) NOT NULL,
	  `station_id` varchar(32) NOT NULL,
	  `last_checked_in` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;
	";
	
	$stationsPrimaryKey = 
	"ALTER TABLE `stations`
	  ADD PRIMARY KEY (`id`);
	";
	
	$stationsAutoIncrement =
	"ALTER TABLE `stations`
	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
	";