<?php 
	$useObj = new stdClass();
	$num5 = 0;
	$num10 = 0;
	foreach($playerData->list as $key=>$value)
	{
		$id = $value["mid"];
		if($useObj->{$id})
			$useObj->{$id} +=1;
		else
			$useObj->{$id} = 1;
		
		if($monster_base[$id]['cost'] >= 10)
			$num10 ++;
		else if($monster_base[$id]['cost'] <= 5)
			$num5 ++;
	}

	for($i=0;$i<4;$i++)
	{
		$type = $task->list[$i]->type;
		switch($type)
		{
			case 1://����5������
				$task->list[$i]->current ++;
				break;
			case 2://���3ʤ
				if($isPKWin)
					$task->list[$i]->current ++;
				break;
			case 3://ʹ��XX N��
				$num = $useObj->{$task->list[$i]->mid};
				if($num)
					$task->list[$i]->current += $num;
				break;
			case 4://10�Ѽ�����
				$task->list[$i]->current += $num10;
				debug($num10);
				break;
			case 5://5�Ѽ�����
				$task->list[$i]->current += $num5;
				break;
		}
	}			
?> 