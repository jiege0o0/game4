<?php
class ArrayUtilClass{
	private $len;
	private $fields;
	private $type;
	
	
	function listSort($a, $b)
	{
		for($i=0;$i<$this->len;$i++)
		{
			$key = $this->fields[$i];
			if($a[$key] < $b[$key])
				return $this->type[$i] == 0 ? -1: 1;
			if($a[$key] > $b[$key])
				return $this->type[$i] == 0 ? 1: -1;
		}
		return 0;
    }
		
		//* @param $type �ֶ��������[0,0,0....] 0��ʾ��С����,�����κ�ֵ���ǴӴ�С
	function mySort($data, $fields, $type){
        $this->type = $type;
		$this->fields = $fields;
		if($data && $fields && $type && count($fields) == count($type))
        {
			$this->len = count($fields);
			// echo $this->listSort(array(),array());
            usort($data,'$this->listSort');
        }
        return $data;
	}
}
$ArrayUtil = new ArrayUtilClass();

?>