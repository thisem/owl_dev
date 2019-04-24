<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
	
	
	
$method=$_POST['method'];	

$nodo=$_POST['nodo'];

$arrstatus=array(''=>'','0'=>'','1'=>'Disetujui','2'=>'Ditolak');
$arrstpo=array('1'=>'Sedang diajukan','2'=>'Persetujuan selesai','3'=>'Barang sudah ada yang masuk gudang','4'=>'Ditolak');
$optstatus="<option value='1'>Disetujui</option>";
$optstatus.="<option value='2'>Ditolak</option>";




#po1
$nopost1=$_POST['nopost1'];
$pil1=$_POST['pil1'];
//exit("Error:$pil1");
$cat1=$_POST['cat1'];

$nopost2=$_POST['nopost2'];
$pil2=$_POST['pil2'];
$cat2=$_POST['cat2'];

$nopost3=$_POST['nopost3'];
$pil3=$_POST['pil3'];
$cat3=$_POST['cat3'];

$tgl=tanggalsystem($_POST['tgl']);
$txt=$_POST['txt'];
//$numrow=$_POST['numrow'];
$status=$_POST['status']; //this
//exit("Error:$txt");

$hrini=date('Ymd');//exit("error:$hrini");

switch($method)
{
		case'setujupo1'://ind
				recheckData();
				$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
                $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['nopo']."</td>";
                $tab.="<td>:</td><td><input type='text' id=nopost1 class=myinputtext value='".$_POST['nopo']."' style=width:150px; disabled />  </td></tr>";
                $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tandatangan']."</td>";
                $tab.="<td>:</td><td><select id=pil1 style=width:150px;>".$optstatus."</select></td></tr>";
               
			    $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['catatan']."</td>";
                $tab.="<td>:</td><td><textarea id=cat1 ></textarea></td></tr>";
				
                $tab.="<tr class=rowcontent><td colspan=3 align=center>";
                $tab.="<button class=mybutton onclick=savesetujupo1('".$_POST['numrow']."')>".$_SESSION['lang']['save']."</button></td></tr>";
                $tab.="</table>";
                $tab.='<iframe frameborder="0" style="width:795px;height:400px" src="log_slave_print_detail_po_kop.php?table=log_poht&amp;column='.$_POST['nopo'].'&amp;cond=" __idm_frm__="459"></iframe>';
                echo $tab;
        break;
		
		case'savesetujupo1':
		
		
				$str="update ".$dbname.".log_poht set `hasilpersetujuan1`='".$pil1."',tglp1='".$hrini."', catatan1='".$cat1."' where nopo='".$nopost1."' ";
				//exit("Error:$str");
				if(mysql_query($str))
				{
					$i="select persetujuan1,persetujuan2 from ".$dbname.".log_poht where nopo='".$nopost1."'";
					$n=mysql_query($i) or die (mysql_error($conn));
					$d=mysql_fetch_assoc($n);
					
					if($pil1=='1')//setuju
					{	
						if($d['persetujuan2']!='0000000000')
						{
							$to=getUserEmail($d['persetujuan2']);
							$namakaryawan=getNmKaryawan($d['persetujuan1']);
							$subject="[Notifikasi]Persetujuan PO No. ".$nopost1." dari ".$namakaryawan;
							$body="<html>
									 <head>
									 <body>
									   <dd>Dengan Hormat,</dd><br>
									   <br>
									   Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Persetujuan Pembelian Barang
									   kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
									   <br>
									   <br>
									   <br>
									   Regards,<br>
									   Owl-Plantation System.
									 </body>
									 </head>
								   </html>
								   ";
								 //  exit("Error : ".$subject);
							$kirim=kirimEmail($to,$subject,$body);#this has return but disobeying;
							
						}
						else
						{
							//inidra
							$str="update ".$dbname.".log_poht set `stat_release`='3',`statuspo`='2',useridreleasae='".$_SESSION['standard']['userid']."',tglrelease='".$hrini."',tglp1='".$hrini."' where nopo='".$nopost1."' ";
							//exit("Error$str");
							if(mysql_query($str))
							{
							}
							else
							{
								echo " Gagal,".addslashes(mysql_error($conn));
							}
						}
					}
					else //tolak
					{
						$str="update ".$dbname.".log_poht set `statuspo`='4'  , catatan1='".$cat1."' where nopo='".$nopost1."' ";
						if(mysql_query($str))
						{
						}
						else
						{
							echo " Gagal,".addslashes(mysql_error($conn));
						}
					}
				}
				else
				{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
		break;
		
		
		case'setujupo2':
				recheckData();
				$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
                $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['nopo']."</td>";
                $tab.="<td>:</td><td><input type='text' id=nopost2 class=myinputtext value='".$_POST['nopo']."' style=width:150px; disabled /> </td></tr>";
                $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tandatangan']."</td>";
                $tab.="<td>:</td><td><select id=pil2 style=width:150px;>".$optstatus."</select></td></tr>";
                $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['catatan']."</td>";
                $tab.="<td>:</td><td><textarea id=cat2 ></textarea></td></tr>";
                $tab.="<tr class=rowcontent><td colspan=3 align=center>";
                $tab.="<button class=mybutton onclick=savesetujupo2('".$_POST['numrow']."')>".$_SESSION['lang']['save']."</button></td></tr>";
                $tab.="</table>";
                $tab.='<iframe frameborder="0" style="width:795px;height:400px" src="log_slave_print_detail_po_kop.php?table=log_poht&amp;column='.$_POST['nopo'].'&amp;cond=" __idm_frm__="459"></iframe>';
                echo $tab;
        break;
		//po 2
		case'savesetujupo2':
		
		
		
				$str="update ".$dbname.".log_poht set `hasilpersetujuan2`='".$pil2."',tglp2='".$hrini."' , catatan2='".$cat2."' where nopo='".$nopost2."' ";
				if(mysql_query($str))
				{
					$i="select persetujuan2,persetujuan3 from ".$dbname.".log_poht where nopo='".$nopost2."'";
					$n=mysql_query($i) or die (mysql_error($conn));
					$d=mysql_fetch_assoc($n);
					
					if($pil2=='1')
					{	
						if($d['persetujuan3']!='0000000000')
						{
							$to=getUserEmail($d['persetujuan3']);
							$namakaryawan=getNmKaryawan($d['persetujuan2']);
							$subject="[Notifikasi]Persetujuan PO ".$nopost2." dari ".$namakaryawan;
							$body="<html>
									 <head>
									 <body>
									   <dd>Dengan Hormat,</dd><br>
									   <br>
									   Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Persetujuan Pembelian Barang
									   kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
									   <br>
									   <br>
									   <br>
									   Regards,<br>
									   Owl-Plantation System.
									 </body>
									 </head>
								   </html>
								   ";
							$kirim=kirimEmail($to,$subject,$body);#this has return but disobeying;
						}
						else
						{
							$str="update ".$dbname.".log_poht set `stat_release`='3',`statuspo`='2',useridreleasae='".$_SESSION['standard']['userid']."',tglrelease='".$hrini."',tglp2='".$hrini."' where nopo='".$nopost2."' ";
							if(mysql_query($str))
							{
							}
							else
							{
								echo " Gagal,".addslashes(mysql_error($conn));
							}
						}
					}
					else
					{
						$str="update ".$dbname.".log_poht set `statuspo`='4'  , catatan2='".$cat2."' where nopo='".$nopost2."' ";
						if(mysql_query($str))
						{
						}
						else
						{
							echo " Gagal,".addslashes(mysql_error($conn));
						}
					}
				}
				else
				{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
		break;
	
	
		case'setujupo3'://ind
				recheckData();
				$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
                $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['nopo']."</td>";
                $tab.="<td>:</td><td><input type='text' id=nopost3 class=myinputtext value='".$_POST['nopo']."' style=width:150px; disabled /></td></tr>";
                $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tandatangan']."</td>";
                $tab.="<td>:</td><td><select id=pil3 style=width:150px;>".$optstatus."</select></td></tr>";
                $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['catatan']."</td>";
                $tab.="<td>:</td><td><textarea id=cat3 ></textarea></td></tr>";
                $tab.="<tr class=rowcontent><td colspan=3 align=center>";
                $tab.="<button class=mybutton onclick=savesetujupo3('".$_POST['numrow']."')>".$_SESSION['lang']['save']."</button></td></tr>";
                $tab.="</table>";
                $tab.='<iframe frameborder="0" style="width:795px;height:400px" src="log_slave_print_detail_po_kop.php?table=log_poht&amp;column='.$_POST['nopo'].'&amp;cond=" __idm_frm__="459"></iframe>';
                echo $tab;
        break;
		
		case'savesetujupo3':
				$str="update ".$dbname.".log_poht set `hasilpersetujuan3`='".$pil3."',tglp3='".$hrini."' , catatan3='".$cat3."' where nopo='".$nopost3."' ";
				if(mysql_query($str))
				{
					if($pil3=='1')
					{
						$str="update ".$dbname.".log_poht set `stat_release`='3',`statuspo`='2',useridreleasae='".$_SESSION['standard']['userid']."',tglrelease='".$hrini."',tglp3='".$hrini."' where nopo='".$nopost3."' ";
						if(mysql_query($str))
						{
						}
						else
						{
							echo " Gagal,".addslashes(mysql_error($conn));
						}
					}
					else
					{
						$str="update ".$dbname.".log_poht set `statuspo`='4' , catatan3='".$cat3."' where nopo='".$nopost2."' ";
						if(mysql_query($str))
						{
						}
						else
						{
							echo " Gagal,".addslashes(mysql_error($conn));
						}
					}
				
				}
				else
				{
					echo " Gagal,".addslashes(mysql_error($conn));
				}
		break;
	
	
	
	
	
	
	

	case'loadData'://<table class=sortable cellspacing=1 border=2px style=\"border-collapse:collapse\" cellpadding=5px>

		echo"
		
		<table cellspacing='1' border='0' class='sortable'>
		
			<thead>
				<tr class=rowheader>
					<td  align=center rowspan=2>No</td>
					<td  align=center rowspan=2>PO</td>
					<td  align=center rowspan=2>tanggal</td>
					<td  align=center rowspan=2>PT</td>
					<td  align=center rowspan=2>detail</td>
					<td  align=center rowspan=2>Purchaser</td>
					<td  align=center rowspan=2>Status PO</td>
					<td  align=center colspan=2>Persetujuan 1</td>
					<td  align=center colspan=2>Persetujuan 2</td>
					<td  align=center colspan=2>Persetujuan 3</td>
					<td  align=center rowspan=2>Detail</td>
				  </tr>
				  <tr>
					<td  align=center>Nama</td>
					<td  align=center>Status</td>
					<td  align=center>Nama</td>
					<td  align=center>Status</td>
					<td  align=center>Nama</td>
					<td  align=center>Status</td>
				</tr>
			</thead>
		<tbody>";
		
		$limit=20;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		
		if($txt!='')
		{
			$txt=" and nopo like '%".$txt."%'";
		}
		if($tgl!='')
		{
			$tgl=" and tanggal='".$tgl."'";
		}
		
	//	$whereStatus = ($status=="1") ? "statuspo in ('1')" : "statuspo in ('1','2','4','3')";
		//update by this 09/09/2014
		$whereStatus = ($status=="1") ? "statuspo in ('1')" : "statuspo in ('1','2')";
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".log_poht where $whereStatus ".$txt." ".$tgl."  ";// echo $ql2;notran
		$query2=mysql_query($ql2) or die(mysql_error());
		while($jsl=mysql_fetch_object($query2)){
		$jlhbrs= $jsl->jmlhrow;
		}
		
		
		$ha="SELECT * FROM ".$dbname.".log_poht where  $whereStatus ".$txt." ".$tgl."  order by tanggal desc limit ".$offset.",".$limit."";
		
		//$ha="SELECT * FROM ".$dbname.".log_poht where statuspo in ('1','2','4') ".$txt." ".$tgl."  order by tanggal desc limit ".$offset.",".$limit."";
		
		
	//	echo $ha;
		
		$hi=mysql_query($ha) or die(mysql_error());
		$no=$maxdisplay;
		while($hu=mysql_fetch_assoc($hi))
		{
			$no+=1;
			echo"
			<tr class=rowcontent id=tr_$no>
				<td>".$no."</td>
				<td>".$hu['nopo']."</td>
				<td>".tanggalnormal($hu['tanggal'])."</td>
				<td>".$hu['kodeorg']."</td>
				<td align=center><img src=images/pdf.jpg class=resicon title='Print PO: ".$hu['nopo']."' onclick=\"masterPDF('log_poht','".$hu['nopo']."','','log_slave_print_detail_po',event);\">
				<img src=images/pdf.jpg class=resicon title='Print PO: ".$hu['nopo']."' onclick=\"masterPDF('log_poht','".$hu['nopo']."','','log_slave_print_detail_po_kop',event);\">
				</td>
				<td title=\"\">".getNmKaryawan($hu['purchaser'])."</td>
				<td>".$arrstpo[$hu['statuspo']]."</td>
				
				<td>".getNmKaryawan($hu['persetujuan1'])."</td>";
				if($hu['persetujuan1']==$_SESSION['standard']['userid'] && ($hu['hasilpersetujuan1']==0 or $hu['hasilpersetujuan1']=='') )
				{
					echo"<td><img src=images/icons/arrow_right.png class=resicon height='30' title='Release PO: ".$hu['nopo']."' onclick=\"setujupo1('".$hu['nopo']."','".$hu['persetujuan1']."','".$no."');\"></td>";	
				}
				else
				{
					if($hu['tglp1']=='0000-00-00')
					{
						$tgl='';
					}
					else
					{
						$tgl=tanggalnormal($hu['tglp1']);
					}
					echo"<td title='".$hu['catatan1']."'><b>".$arrstatus[$hu['hasilpersetujuan1']]."</b> ".$tgl."</td>";
				}
				
				echo"<td>".getNmKaryawan($hu['persetujuan2'])."</td>";
				if($hu['persetujuan2']==$_SESSION['standard']['userid'] && ($hu['hasilpersetujuan2']==0 or $hu['hasilpersetujuan2']=='') && $hu['hasilpersetujuan1']==1)
				{
					echo"<td><img src=images/icons/arrow_right.png class=resicon height='30' title='Release PO: ".$hu['nopo']."' onclick=\"setujupo2('".$hu['nopo']."','".$hu['persetujuan2']."','".$no."');\"></td>";	
				}
				else
				{
					if($hu['tglp2']=='0000-00-00')
					{
						$tgl='';
					}
					else
					{
						$tgl=tanggalnormal($hu['tglp2']);
					}
					echo"<td title='".$hu['catatan1']."'><b>".$arrstatus[$hu['hasilpersetujuan2']]."</b>  ".$tgl."</td>";
		
				}
				echo"<td>".getNmKaryawan($hu['persetujuan3'])."</td>";
				if($hu['persetujuan3']==$_SESSION['standard']['userid'] && ($hu['hasilpersetujuan3']==0 or $hu['hasilpersetujuan3']=='') && $hu['hasilpersetujuan2']==1)
				{
					echo"<td><img src=images/icons/arrow_right.png class=resicon height='30' title='Release PO: ".$hu['nopo']."' onclick=\"setujupo3('".$hu['nopo']."','".$hu['persetujuan3']."','".$no."');\"></td>";	
				}
				else
				{
					if($hu['tglp3']=='0000-00-00')
					{
						$tgl='';
					}
					else
					{
						$tgl=tanggalnormal($hu['tglp3']);
					}
					echo"<td title='".$hu['catatan1']."'><b>".$arrstatus[$hu['hasilpersetujuan3']]."</b>  ".$tgl."</td>";
				}
				echo "<td align='center'><img class=resicon src='images/info.png' height='20' onclick=cekBarang('".$hu['nopo']."') /></td>";
			echo"</tr>
			
			";
		}
		echo"
		<tr class=rowheader><td colspan=18 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</tbody></table>";

    break;

    case 'cekBarang' : 
    				   $sql="select `a`.`kodebarang` AS `kodebarang`,
    				   				`a`.`nopo` AS `nopo`,
    				   				`a`.`jumlahpesan` AS `jumlahpesan`,
    				   				`a`.`nopp` AS `nopp`,
    				   				`a`.`hargasatuan` AS `hargasatuan`,
    				   				`a`.`satuan` AS `satuan`,
    				   				`b`.`kodesupplier` AS `kodesupplier`,
    				   				`b`.`tanggal` AS `tanggal`,
    				   				`b`.`matauang` AS `matauang`,
    				   				`b`.`subtotal` AS `subtotal`,
    				   				`b`.`diskonpersen` AS `diskonpersen`,
    				   				`b`.`nilaidiskon` AS `nilaidiskon`,
    				   				`b`.`ppn` AS `ppn`,
    				   				`b`.`nilaipo` AS `nilaipo`,
    				   				`b`.`statuspo` AS `statuspo`,
    				   				`b`.`lokalpusat` AS `lokalpusat`,
    				   				`b`.`kurs` AS `kurs`,
    				   				`b`.`kodeorg` AS `kodeorg`,
    				   				`c`.`namasupplier` AS `namasupplier`,
    				   				`d`.`namabarang` AS `namabarang` 
    				   				from (((".$dbname.".`log_podt` `a` left join ".$dbname.".`log_poht` `b` on((`a`.`nopo` = `b`.`nopo`))) 
    				   				left join ".$dbname.".`log_5masterbarang` `d` on((`a`.`kodebarang` = `d`.`kodebarang`))) 
    				   				left join ".$dbname.".`log_5supplier` `c` on((`b`.`kodesupplier` = `c`.`supplierid`)))

    				   				where a.nopo='".$_POST['nopo']."'";

    				   $q=mysql_query($sql) or die(mysql_error());
    				   while($r=mysql_fetch_assoc($q)){
    				   		$po[$r['nopo']]=$r;
    				   		$barang[$r['kodebarang']]=$r['kodebarang'];
    				   		$detail[$r['kodebarang']]=$r;
    				   }

    				   $array = implode("','",$barang);
    				   $sql="select * from ".$dbname.".log_podt a
    				   left join ".$dbname.".log_poht b on a.nopo=b.nopo
    				   where kodebarang in ('".$array."')
    				   order by b.tanggal desc";

    				   $q=mysql_query($sql) or die(mysql_error());
    				   while($d=mysql_fetch_assoc($q)){
    				   		$h[$d['kodebarang']][]=$d;
    				   }


    				   $tbl=
    				   "<table cellspacing='1' border='0' class='sortable'>
							<thead>
								<tr class=rowheader>
									<td  align=center rowspan=2>No</td>
									<td  align=center rowspan=2>Kode</td>
									<td  align=center rowspan=2>Barang</td>
									<td  align=center colspan=2>Riwayat PO</td>
								</tr>
								<tr class=rowheader>
									<td  align=center width=100>Tanggal</td>
									<td  align=center >No. PO</td>
								</tr>
							</thead>
						<tbody>";
							$no=1;
							foreach($barang as $kd){
								foreach($h[$kd] as $list){
									$rowspan=count($h[$kd]);
									$tbl.="<tr class=rowcontent>";
										if($x[$kd]==0){

										$tbl.="<td style='vertical-align: top;' rowspan=".$rowspan.">".$no."
												<td style='vertical-align: top;' rowspan=".$rowspan.">".$detail[$kd]['kodebarang']."
												<td style='vertical-align: top;' rowspan=".$rowspan.">".$detail[$kd]['namabarang'];
										}

										$tbl.=" <td align='center'>".tanggalnormald($list['tanggal'])."
												<td align='right' onclick=masterPDF('log_poht','".$list['nopo']."','','log_slave_print_detail_po_kop',event);>".$list['nopo'];
									$tbl.="	
										</tr>";
									$x[$kd]=1;
								}
								
								$no++;
							}
						$tbl.="
						</tbody>
						</table>";

    				   echo $tbl;

    break;
	

	
	
	

	
	default;	
}

function recheckData(){
    global $dbname;
    $nopo = $_POST['nopo'];

    $sql="select * from ".$dbname.".log_podt where nopo ='".$nopo."'";
    $q = mysql_query($sql) or die(mysql_error());
    while($r = mysql_fetch_assoc($q)){
        $dt += $r['jumlahpesan']*$r['hargasatuan'];
    }


    $sql="select * from ".$dbname.".log_poht where nopo ='".$nopo."'";
    $q = mysql_query($sql) or die(mysql_error());
    while($r = mysql_fetch_assoc($q)){
        $ht  = $r['nilaipo'];
        $pajak = $r['ppn']+$r["pph"]+$r['ppl'];
    }

    
    if($ht!=($dt+$pajak)){
        exit("error : Data Total Belum Sesuai, Mohon hubungi bagian Purchasing!");
    }
}

?>