<?php
	
	function get_biks ()
	{
		$bik = file_get_contents("https://www.cbr.ru/vfs/mcirabis/BIKNew/20230807ED01OSBR.zip");
		file_put_contents("biks.zip", $bik);
		
		$zip = new ZipArchive;
		$res = $zip->open('biks.zip');
		if ($res === TRUE) {
			$zip->extractTo(__DIR__);
			$zip->close();
			unlink('biks.zip');
			} else {
			echo 'Archive opening error!';
		}
		
		$dir = __DIR__ ;
		
		$files = array();
		foreach(glob($dir . '/*') as $file) 
		{
			$files[] = basename($file);	
		} 
		
		foreach ($files as $file)
		{
			if (substr($file, -4) == '.xml') break;
		}
		
		$xml = simplexml_load_file($file, "SimpleXMLElement", LIBXML_NOCDATA);
		unlink($file);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		
		$objs = array();
		
		foreach ($array['BICDirectoryEntry'] as $bde)
		{
			if (array_key_exists('Accounts', $bde)) 
			{
				foreach($bde['Accounts'] as $account)
				{
					if(count($bde['Accounts']) == 1) $account = $bde['Accounts'];
					$obj = new stdClass();
					$obj->bic = $bde['@attributes']['BIC'];
					$obj->name = $bde['ParticipantInfo']['@attributes']['NameP'];
					$obj->corrAccount = $account['@attributes']['Account'];
					$objs[] = $obj;
				}
			}
		}
		return $objs;
	}
	
	echo '<pre>';
	print_r(get_biks ());
	echo '</pre>';
	
?>