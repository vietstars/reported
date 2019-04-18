<?php 
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 9999); 
set_time_limit(0);
class timeLogs
{
	public $ls;
	public $log=array();
	public $url='https://redmine.lampart-vn.com/';
	public $timeTotal=0;
	public $total=array();
	public $list=array();
	public $startDate='';
	public $endDate='';
	public $caching=false;

	function __construct()
	{
		$this->total['JPSE'] = 0;
		$this->total['BSE'] = 0;
		$this->total['ITC'] = 0;
		$this->total['PG'] = 0;
		$this->total['QC'] = 0;
		$this->total['BA'] = 0;
	}
	public function report()
	{
		echo '<table class="table table-hover table-bordered mt-3" width="100%">';
		echo '<thead><tr class="table-primary"><td align="center" width="22%">PROJECT</td><td align="center" width="5%">TASK</td><td align="center" width="28%">TASK NAME</td><td align="center" width="6%">JPSE</td><td align="center" width="6%">BSE</td><td align="center" width="6%">ITC</td><td align="center" width="6%">PG</td><td align="center" width="6%">QC</td><td align="center" width="6%">BA</td><td align="center" width="9%">TOTAL</td></tr></thead>';
		echo "<tbody>";
		if(file_exists('./assets/'.date('m-Y',strtotime($this->startDate)).'/log.json')&&file_get_contents('./assets/'.date('m-Y',strtotime($this->startDate)).'/log.json') ){
			$this->log = json_decode(file_get_contents('./assets/'.date('m-Y',strtotime($this->startDate)).'/log.json'));
			$this->caching=true;
		}else{
			$this->simpleLog();
		}
		foreach ( $this->log as $k => $logs ) {
			foreach ( $logs as $k1 => $task ) {
				list($task_id,$task_Name) = explode('::', $k1);
				$_apart = array('JPSE', 'BSE', 'ITC', 'PG', 'QC', 'BA');
				echo '<tr><td align="left">'.$k.'</td><td align="center">'.$task_id.'</td><td align="left">'.$task_Name.'</td>';
				$total = 0;
				if($this->caching){
					foreach ($_apart as $key) {
						if(isset($task->$key)) {
							echo '<td align="center">'.$task->$key.'</td>';
							$total += $task->$key;
							$this->total[$key] += $task->$key; 
						} else {
							echo '<td align="center">0</td>';
						}
					}
				}else{
					foreach ($_apart as $key) {
						if(isset($task[$key])) {
							echo '<td align="center">'.$task[$key].'</td>';
							$total += $task[$key];
							$this->total[$key] += $task[$key]; 
						} else {
							echo '<td align="center">0</td>';
						}
					}
				}
				$this->caching?$this->timeTotal+=$total:null;
				echo '<td align="center">'.$total.'</td>';
			}
		};
		echo '<tr><td colspan=3 align="right">Sumary </td><td align="center">'.$this->total['JPSE'].'</td><td align="center">'.$this->total['BSE'].'</td><td align="center">'.$this->total['ITC'].'</td><td align="center">'.$this->total['PG'].'</td><td align="center">'.$this->total['QC'].'</td><td align="center">'.$this->total['BA'].'</td><td align="center">'.$this->timeTotal.'</td></tr>';
		echo '<tr><td colspan=8 align="right">Check </td><td align="center">'.array_sum($this->total).'</td><td align="center">'.$this->timeTotal.'</td></tr>';
		echo "</tbody></table>";
		!$this->caching?$this->log('./assets/'.date('m-Y',strtotime($this->startDate)).'/log.json',json_encode($this->log),'w'):null;
	}
	public function getApi($url=false)
	{
		is_numeric($url)?$this->url = 'https://redmine.lampart-vn.com/issues/'.$url.'.xml':$this->url = $url;
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $this->url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 400,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_POSTFIELDS => "",
		  CURLOPT_HTTPHEADER => array(
		    "Postman-Token: d3f137fb-d0a8-4eeb-80e8-29be5fceca86",
		    "cache-control: no-cache"
		  ),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  return json_encode(simplexml_load_string($response));		  
		} 
	}
	public function simpleTask($json=false)
	{
		if (empty(json_decode($json)->time_entry)) {
			return array();
		}
		$sum = json_decode($json)->time_entry;
		$time = 0;
		$pro_name = "";$taskTime=0;
		foreach($sum as $total){
			$pro_name = current($total->project)->name;
			$task_root =  $this->getTaskRoot(current($total->issue)->id);
			$name = current($total->user)->name;
			if(strpos($name, '[') === false){
			    $tmp = 'JPSE';
			}else{
				$name = explode('[', $name);
				$tmp = rtrim(end($name),']');
			}
			if(isset($this->log[$pro_name][$task_root.'::'.$this->getTaskName($task_root)][$tmp])){
				$this->log[$pro_name][$task_root.'::'.$this->getTaskName($task_root)][$tmp] += (float) $total->hours;
			}else{
				$this->log[$pro_name][$task_root.'::'.$this->getTaskName($task_root)][$tmp] = (float) $total->hours;
			}
			$this->timeTotal += (float) $total->hours;
		}
		return $this->log;
	}
	public function simpleLog()
	{
		$url = "https://redmine.lampart-vn.com/time_entries.xml?c[]=project&c[]=spent_on&c[]=user&c[]=activity&c[]=issue&c[]=comments&c[]=hours&f[]=spent_on&f[]=project_id&f[]=&op[project_id]==&op[spent_on]=><&utf8=✓";
		foreach ($this->ls as $pro) {
			$url .= "&v[project_id][]=".$pro;
		}
		if( $this->startDate&&$this->endDate ){
			$url .= "&v[spent_on][]=".$this->startDate."&v[spent_on][]=".$this->endDate;
		}else{
			$url .= "&v[spent_on][]=2019-03-01&v[spent_on][]=2019-03-31";
		}
		$first = $url.'&offset=0&limit=100';
		$log=$this->getApi($first);
		$logs=current(json_decode($log));
		$total=ceil($logs->total_count/100);
		$offset=$logs->offset;
		$this->simpleTask($log);
		if($total*100>100){
			for($i=1;$i<=$total;$i++){
				$this->simpleTask($this->getApi($url.'&offset='.($i*100).'&limit=100'));
			}
		}
	}
	public function getTaskName($id=0)
	{
		$res=$this->getApi($id);
		return isset(json_decode($res)->subject)?json_decode($res)->subject:null;
	}
	public function getProName($id=0)
	{
		$res=$this->getApi($id);
		return isset(current(json_decode($res)->project)->name)?current(json_decode($res)->project)->name:null;
	}
	public function getTaskRoot($id=0)
	{
		$res=$this->getApi($id);
		if(isset(json_decode($res)->parent)){
			return $this->getTaskRoot(current(json_decode($res)->parent)->id);
		}else{
			return $id;
		}
	}
	public function log($log='./assets/project.json',$data=false,$type="a+")
	{
		if($log&&$data){
			if(file_exists($log)){
				$file=fopen($log,$type) or die("Unable to open file!"); 
				fwrite($file,$data);
				fclose($file);
				chmod($log,0777);
			}else{
				if($log!='./assets/project.json'){
					if(!is_dir('./assets/'.date('m-Y',strtotime($this->startDate)))){
						mkdir('./assets/'.date('m-Y',strtotime($this->startDate)), 0777);
						chmod('./assets/'.date('m-Y',strtotime($this->startDate)), 0777);
					}
				}
				$file=fopen($log,$type) or die("Unable to open file!"); 
				fwrite($file,$data);
				fclose($file);
				chmod($log,0777);
			}
			return $this;
		}
	}
	public function simpleProject()
	{
		$projectSumary = json_decode($this->getApi('https://redmine.lampart-vn.com/projects.xml?offset=0&limit=100'));
		foreach ($projectSumary->project as $v) {
			if(isset($v->parent))
				$this->list[$v->id]=$this->getParent(current($v->parent)->id,'-- '.$v->name);
			else
				$this->list[$v->id]=$v->name;
		}
		$total=ceil(current($projectSumary)->total_count/100);
		for($i=1;$i<$total;$i++){
			$prSumary=json_decode($this->getApi('https://redmine.lampart-vn.com/projects.xml?offset='.($i*100).'&limit=100'));
			if(isset($prSumary->project)){
				foreach ($prSumary->project as $v){
					if(isset($v->parent))
						$this->list[$v->id]=$this->getParent(current($v->parent)->id,'-- '.$v->name);
					else
						$this->list[$v->id]=$v->name;
				}
			}
		}
		$this->log('./assets/project.json',json_encode($this->list),"w");
	}
	public function getParent($id='',$name='')
	{
		if($id){
			$info = json_decode($this->getApi('https://redmine.lampart-vn.com/projects/'.$id.'.xml'));
			if(isset($info->parent)&&current($info->parent)->id){
				$name='--'.$name;
				return $this->getParent(current($info->parent)->id,$name);
			}else{
				return $name;	
			}
		}
	}
	public function setRanger($start='',$end='')
	{
		if( $start&&$end ){
			$this->startDate=$start;
			$this->endDate=$end;
			return $this;
		}
	}
	public function setProject($projects='')
	{
		if( $projects ) $this->ls = $projects;
		return $this;
	}
	public function checkTotal($data='')
	{
		if( $data['projects']&&$data['startDate']&&$data['endDate'] ){
			$url = "https://redmine.lampart-vn.com/time_entries.xml?c[]=project&c[]=spent_on&c[]=user&c[]=activity&c[]=issue&c[]=comments&c[]=hours&f[]=spent_on&f[]=project_id&f[]=&op[project_id]==&op[spent_on]=><&utf8=✓";
			foreach ($data['projects'] as $pro) {
				$url .= "&v[project_id][]=".$pro;
			}
			$url .= "&v[spent_on][]=".$data['startDate']."&v[spent_on][]=".$data['endDate'];
			$first = $url.'&offset='.$data['offset']*$data['limit'].'&limit='.$data['limit'];
			$log=$this->getApi($first);
			$logs=current(json_decode($log));
			$total=ceil($logs->total_count/$data['limit']);
			$report = $this->simpleTask($log);
			if(!is_dir('./assets/'.date('m-Y',strtotime($data['startDate'])))){
				mkdir('./assets/'.date('m-Y',strtotime($data['startDate'])), 0777);
				chmod('./assets/'.date('m-Y',strtotime($data['startDate'])), 0777);
			}
			$_aparts = array('JPSE', 'BSE', 'ITC', 'PG', 'QC', 'BA');
			if(file_exists('./assets/'.date('m-Y',strtotime($data['startDate'])).'/log.json')&&file_get_contents('./assets/'.date('m-Y',strtotime($data['startDate'])).'/log.json')){
				$reported = json_decode(file_get_contents('./assets/'.date('m-Y',strtotime($data['startDate'])).'/log.json'));
				foreach ($report as $project => $tasks) {
					foreach ($tasks as $task => $aparts) {
						if(!isset($reported->$project->$task)){
							foreach ($_aparts as $ap) {
								$reported->$project->$task->$ap=0;
							}
						}
					}
				}
				foreach ($report as $project => $tasks) {
					foreach ($tasks as $task => $aparts) {
						foreach ($aparts as $k=>$v) {
							$reported->$project->$task->$k+=$v;
						}
					}
				}
				$report=$reported;

			}else{
				foreach ($report as $project => $tasks) {
					foreach ($tasks as $task => $aparts) {
						$diff = array_diff($_aparts,array_keys($aparts));
						foreach ($diff as $d) {
							$report[$project][$task][$d]=0;
						}
					}
				}
			}
			$this->log('./assets/'.date('m-Y',strtotime($data['startDate'])).'/log.json',json_encode($report),'w');
			$res = array(
				'total' => $total,
				'limit' => $data['limit']
			);
			echo json_encode($res);
			exit;
		}
	}
}
$app = new timeLogs();
if(isset($_GET['act']) && $_GET['act']=='caching'){
	header("Content-type: application/json;charset=utf-8");
	if(!isset($_POST['offset'])||$_POST['offset']==0){
		if(file_exists('./assets/'.date('m-Y',strtotime($_POST['startDate'])).'/log.json')){
			unlink('./assets/'.date('m-Y',strtotime($_POST['startDate'])).'/log.json');
		}
	}
	isset($_POST['projects'])?$app->log('./assets/projectSelected.json',json_encode($_POST['projects']),"w"):null;
	$_POST['offset']=isset($_POST['offset'])?$_POST['offset']:0;
	$_POST['limit']=isset($_POST['limit'])?$_POST['limit']:25;
	$app->checkTotal($_POST);
	exit;
}
$projects=file_get_contents('./assets/project.json')?json_decode(file_get_contents('./assets/project.json')):$app->simpleProject();
$selected=file_get_contents('./assets/projectSelected.json')?json_decode(file_get_contents('./assets/projectSelected.json')):[210,228,115,237,87,194,179,106,104,33,221,235];
if(isset($_GET['act']) && $_GET['act']=='export'){
	header("Content-type: application/json;charset=utf-8");
	if( isset($_POST['date']) ){
		if(file_exists('./assets/'.date('m-Y',strtotime($_POST['date'])).'/log.json')){
			$data = json_decode(file_get_contents('./assets/'.date('m-Y',strtotime($_POST['date'])).'/log.json'));
			$export = array();
			foreach ($data as $project => $tasks) {
				foreach ($tasks as $task => $aparts) {
					$aparts->project = $project;
					list($aparts->task_id,$aparts->task_name)=explode('::',$task);
					$export[]=$aparts;
				}
			}
            $row=1;
			include './Classes/PHPExcel.php';			
			$Excel = new PHPExcel();
			$Excel->createSheet();
            $activeSheet = $Excel->setActiveSheetIndex($row);
            $activeSheet->setTitle('Report');
            $activeSheet->setCellValue('A'.$row, 'Premium (ALL)')
                ->setCellValue('B'.$row, date('m',strtotime($_POST['date'])).' 月工数');
            $activeSheet->mergeCells('B'.$row.':K'.$row);
            $Excel->getActiveSheet()
            ->getStyle('B'.$row)
            ->getAlignment()
            ->applyFromArray(['horizontal' => 'center']);
            $Excel->getActiveSheet()
            ->getStyle('B'.$row)->getFont()->setSize(16)->setBold(true);
            $row++;
            $options=array(
            	'A'=>'project',
            	'B'=>'task_id',
            	'C'=>'task_name',
            	'D'=>'JPSE',
            	'E'=>'BSE',
            	'F'=>'ITC',
            	'G'=>'PG',
            	'H'=>'QC',
            	'I'=>'BA',
            	'J'=>'TOTAL',
            	'K'=>'備考',
            );
            foreach ($options as $k => $v) {
            	$activeSheet->setCellValue($k.$row, strtoupper(str_replace("_"," ",$v)));
            }
            $first=current(array_keys($options)).$row;
            $last=end(array_keys($options)).$row;
            $Excel->getActiveSheet()->getStyle($first.':'.$last)->getFill()
		    ->setFillType('solid')
		    ->getStartColor()->setARGB('FF008000');
		    $Excel->getActiveSheet()->getStyle($first.':'.$last)->getAlignment()
		    ->applyFromArray(['horizontal' => 'center']);
		    $Excel->getActiveSheet()->getStyle($first.':'.$last)->getFont()->setBold(true);
            $row++;
            $color='FFCCFFCC';
            $currentpr='';
            $colors = array('FFFFFFCC','FFCCFFFF','FFCCFFCC','FFFFFF99','FF99CCFF');
            $lastest=0;
            foreach ($export as $cells) {
            	if(!$lastest)$lastest=$row;
            	foreach ($options as $col => $cell) {
            		if( !in_array($col,array('J','K')) )
            	 		$activeSheet->setCellValue($col.$lastest, $cells->$cell);
            	 	if( $col == 'J')
            	 		$activeSheet->setCellValue($col.$lastest, '=SUM(D'.$lastest.':I'.$lastest.')');
            	} 
            	if($cells->project!==$currentpr){
            		$currentpr=$cells->project;
            		$color = $colors[array_rand(array_diff($colors, array($color)))];
            	}
            	$Excel->getActiveSheet()->getStyle('A'.$lastest.':K'.$lastest)->getFill()
			    ->setFillType('solid')->getStartColor()->setARGB($color);
            	$lastest++;
            }
            $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
            $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
            // $Excel->getActiveSheet()->getStyle('A'.$row.':A'.$lastest)->getAlignment()->setWrapText(true); 
            // $Excel->getActiveSheet()->getStyle('C'.$row.':C'.$lastest)->getAlignment()->setWrapText(true);
            $Excel->getActiveSheet()->getStyle('J'.$row.':J'.$lastest)->getFont()->setColor( new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_RED) )->setBold(true); 
            $activeSheet->setCellValue('A'.$lastest, 'Sumary: '); 
            foreach (array('D','E','F','G','H','I','J') as $k) {
        	 	$activeSheet->setCellValue($k.$lastest, '=SUM('.$k.$row.':'.$k.($lastest-1).')');
            }
            // $activeSheet->setCellValue('K'.$lastest, '=SUM(D'.$lastest.':I'.$lastest.')');
            $Excel->getActiveSheet()->getStyle('J'.$lastest)
            ->getNumberFormat()->setFormatCode('#,##0.00');
            $Excel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
            $activeSheet->mergeCells('A'.$lastest.':C'.$lastest);
            $Excel->getActiveSheet()
            ->getStyle('A'.$lastest)
            ->getAlignment()
            ->applyFromArray(['horizontal' => 'right']);
            $Excel->getActiveSheet()
            ->getStyle('A'.$lastest.':K'.$lastest)->getFont()->setSize(13)->setColor( new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_RED) )->setBold(true);
            $styleArray = array(
				'borders' => array(
					'allborders' => array(
						'style' => 'thin',
						'color' => array('argb' => 'FF000000'),
					),
				),
			);
			$Excel->getActiveSheet()->getStyle('A1:K'.$lastest)->applyFromArray($styleArray);
			$note=3;
			$activeSheet->setCellValue('A'.($lastest+$note),'Danh sách project cần tính:');
			$Excel->getActiveSheet()->getStyle('A'.($lastest+$note))->getFont()->setSize(13)->setBold(true);
			$note++;
			$_pw=array();
			foreach ($selected as $k) {
				if (strpos($projects->$k, '------ ') !== false) {
				    $_pw[] =  str_replace('------ ', '-- ', $projects->$k);
				}else if (strpos($projects->$k, '---- ') !== false) {
				    $_pw[] =  str_replace('---- ', '-- ', $projects->$k);
				}else if (strpos($projects->$k, '-- ') !== false) {
				    $_pw[] =  $projects->$k;
				}else{
					$note++;
					$activeSheet->setCellValue('A'.($lastest+$note),$projects->$k);
					$activeSheet->mergeCells('A'.($lastest+$note).':C'.($lastest+$note));
				}
			}
			$note++;
			$activeSheet->setCellValue('A'.($lastest+$note),'Premium Water');
			$activeSheet->mergeCells('A'.($lastest+$note).':C'.($lastest+$note));
			foreach ($_pw as $k) {
				$note++;
				$activeSheet->setCellValue('A'.($lastest+$note),$k);
				$activeSheet->mergeCells('A'.($lastest+$note).':C'.($lastest+$note));
			}
            if(file_exists('./export/report-'.date('m-Y',strtotime($_POST['date'])).'.xlsx'))unlink(('./export/report-'.date('m-Y',strtotime($_POST['date'])).'.xlsx'));
            $writer = \PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        	$writer->save('./export/report-'.date('m-Y',strtotime($_POST['date'])).'.xlsx');
			// echo json_encode($export);
			echo 'export finish';
			exit;
		}else{
			echo 'caching this month before export';
			exit;
		}
	}else{
		echo 'select startDate to check month export';
		exit;
	}
}
include './header.php';
if($_POST&&$_POST['startDate']&&$_POST['endDate']){
	isset($_POST['projects'])?$app->log('./assets/projectSelected.json',json_encode($_POST['projects']),"w"):null;
	$app->setRanger($_POST['startDate'],$_POST['endDate'])
	->setProject($_POST['projects'])
	->report();
}
include './footer.php';