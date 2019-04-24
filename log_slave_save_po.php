<?php
        require_once('master_validation.php');
        require_once('config/connection.php');
        include_once('lib/nangkoelib.php');
        include_once('lib/fpdf.php');
        include_once('lib/zLib.php');

	$supplier_id=$_POST['supplier_id'];
	$proses=$_POST['proses'];
	$nopo=$_POST['nopo'];
	$tgl_po=tanggalsystem($_POST['tglpo']);
	$sub_total=$_POST['subtot'];
	$disc=$_POST['diskon'];
	$nilai_dis=$_POST['nildiskon'];
	$nppn=$_POST['ppn'];

	$npph=$_POST['pph'];
	$ppL=$_POST['ppL'];//exit("Error:$ppL");

	$tanggl_kirim=tanggalsystemd($_POST['tgl_krm']);
	$lokasi_krm=$_POST['lok_kirim'];
	$cr_pembayaran=$_POST['cara_pembayarn'];
	$nilai_po=$_POST['grand_total'];
	$purchaser=$_POST['purchser_id'];
	$lokasi_kirim=$_POST['lokasi_krm'];
	$persetujuan=$_POST['id_user'];
	$comment=$_POST['cm_hasil'];
	$jmlh_realisasi=$_POST['jmlh_realisasi'];
	$jmlh_diminta	=$_POST['jmlh_diminta'];
	$jnopp=$_POST['jnopp'];
	$jkdbrg=$_POST['jkdbrg'];
	$ketUraian=$_POST['ketUraian'];
	$mtUang=$_POST['mtUang'];
	$Kurs=intval($_POST['Kurs']);
        $nmSupplier=$_POST['nmSupplier'];
        $ttd2=$_POST['ttd2'];
        $ongkirim=$_POST['ongkirim'];
        $nomor=$_POST['nomor'];
        $optNmkar=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

  #cek barang double
  foreach($_POST['kdbrg'] as $brg){
    $kdbarang[$brg]+=1;
    if($kdbarang[$brg]>1){
      echo 'error : Data Barang tidak boleh ada yang sama!';
      exit;
    }
  }


	switch($proses)
	{
		case 'cek_supplier':
			$sql="select * from ".$dbname.".log_5supplier where supplierid='".$supplier_id."'";
			$query=mysql_query($sql) or die(mysql_error());
			$res=mysql_fetch_assoc($query);
			echo $res['rekening'].",";
			echo $res['npwp'];

		break;

		case 'insert':

		//exit("Error:MASUK");

		if(($nopo=='')||($tanggl_kirim=='')||($cr_pembayaran=='')||($lokasi_kirim=='')||($mtUang==''))
		{
			echo"warning:Please Complete The Form";
			exit();
		}

                //cek matauang dan kurs
                if($mtUang!='IDR')
                {
                    $Kurs=floatval($Kurs);
                    $sGetKurs="select distinct kurs,kode from ".$dbname.".setup_matauangrate where kode='".$mtUang."' order by daritanggal desc";
                    //exit("Error:".$sGetKurs."__".$Kurs);
                    $qGetKurs=mysql_query($sGetKurs) or die(mysql_error());
                    $rGetKurs=mysql_fetch_assoc($qGetKurs);
                    if($Kurs=='0')
                    {
                      exit("Error:Masukan Kurs Sesuai Dengan Mata Uang,Kurs Untuk ".$rGetKurs['kode']." :".$rGetKurs['kurs']);
                    }
                }
                else
                {
                    $Kurs=1;
                }

		$awl=0;
		$i=1;


		foreach($_POST['kdbrg'] as $row =>$cntn)
		{

	                $kdbrg=$cntn;
                        $b=count($_POST['kdbrg']);
                        $nopp=$_POST['nopp'][$row];
                        $jmlh_pesan=$_POST['rjmlh_psn'][$row];
                        $hrg_satuan=$_POST['rhrg_sat'][$row];
                        $hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
                        $ongangkut=str_replace(',','',$_POST['rongangkut'][$row]);
                        $satuan=$_POST['rsatuan_unit'][$row];
                        $diskon=($hrg_sblmdiskon*$disc)/100;
                        $hrg_diskon=$hrg_sblmdiskon-$diskon;

                        $sqjmlh="select selisih,jlpesan,realisasi,purchaser from ".$dbname.".log_sudahpo_vsrealisasi_vw where nopp='".$nopp."' and kodebarang='".$kdbrg."'";
                        //echo "warning:".$sqjmlh;exit();
                        $qujmlh=mysql_query($sqjmlh) or die(mysql_error());
                        $resjmlh=mysql_fetch_assoc($qujmlh);
                        $jmlh_pesan=$resjmlh['jlpesan']+$jmlh_pesan;
			if(($jmlh_pesan=='')||($hrg_satuan==''))
			{
				echo "warning: Please Complete The Form";
				exit();
			}
			if($purchaser!=$resjmlh['purchaser'])
                        {
                            $purchaser=$resjmlh['purchaser'];
                        }

			if($resjmlh['realisasi']<$jmlh_pesan)
			{
				echo "warning : \nTotal Jumlah Pesan (".$jmlh_pesan.") Dari Kode Barang ".$kdbrg.".(".$jmlh_pesan.") =
				\nJumlah Pesan Sebelumnya (".$resjmlh['jlpesan'].")\nJumlah Pesan Saat Ini (".$_POST['rjmlh_psn'][$row].")
				\nLebih Besar dari Jumlah Yang Disetujui (".$resjmlh['realisasi'].").";
				exit();
            }

		}
                        //$kode_org=substr($nopo,20,4);
                        $sKd="select kodeorg from ".$dbname.".log_prapoht where nopp='".$nopp."'";
                        $qKd=mysql_query($sKd) or die(mysql_error());
                        $rKdorg=mysql_fetch_assoc($qKd);

                        $sql="select nopo from ".$dbname.".log_poht where nopo='".$nopo."'";
                        $query=mysql_query($sql) or die(mysql_error());
                        $res=mysql_fetch_row($query);
						if(intval($lokasi_kirim))
						{
							$field="`idFranco`";
						}
						else
						{
							$field="`lokasipengiriman`";
						}

						$thisDate=date('Y-m-d');
                                                if($nilai_dis=='')
                                                {
                                                    $nilai_dis=0;
                                                }
                                                $Kurs=intval($Kurs);
//                        if($res<1)
//                        {
                                                if($ongkirim=='')
                                                {
                                                    $ongkirim=0;
                                                }

                            //$strx="insert into ".$dbname.".log_poht (`nopo`,`tanggal`,`kodesupplier`,`subtotal`,`diskonpersen`,`nilaidiskon`,`ppn`,`nilaipo`,`tanggalkirim`,".$field.",`syaratbayar`,`uraian`,`purchaser`,`kodeorg`,`lokalpusat`,`matauang`,`kurs`,`persetujuan1`,`hasilpersetujuan1`,`tglp1`,`statuspo`,`persetujuan2`,`hasilpersetujuan2`,`tglp2`) values
                            //('".$nopo."','".$tgl_po."','".$supplier_id."','".$sub_total."','".$disc."','".$nilai_dis."','".$nppn."','".$nilai_po."','".$tanggl_kirim."','".$lokasi_kirim."','".$cr_pembayaran."','".$ketUraian."','".$purchaser."','".$rKdorg['kodeorg']."','0','".$mtUang."','".$Kurs."','".$persetujuan."','1','".tanggalnormal($thisDate)."',2,'".$ttd2."','1','".tanggalnormal($thisDate)."')";
                        /*    $strx="update ".$dbname.".log_poht set `kodesupplier`='".$supplier_id."',`subtotal`='".$sub_total."',`diskonpersen`='".$disc."',`nilaidiskon`='".$nilai_dis."',`ppn`='".$nppn."',`pph`='".$npph."',`ppl`='".$ppL."',`nilaipo`='".$nilai_po."',`tanggalkirim`='".$tanggl_kirim."',
                                  ".$field."='".$lokasi_kirim."',`syaratbayar`='".$cr_pembayaran."',`uraian`='".$ketUraian."',`purchaser`='".$purchaser."',`lokalpusat`='0',`matauang`='".$mtUang."',`kurs`='".$Kurs."',`persetujuan1`='".intval($persetujuan)."',`hasilpersetujuan1`='1',
                                  `tglp1`='0000-00-00',`statuspo`='1',`persetujuan2`='".intval($ttd2)."',`hasilpersetujuan2`='1',`tglp2`='0000-00-00',tgledit='".$thisDate."',ongkosangkutan='".$ongkirim."',`persetujuan3`='".intval($ttd3)."',`hasilpersetujuan1`='1'
                                   where nopo='".$nopo."'";*/

								       $strx="update ".$dbname.".log_poht set `kodesupplier`='".$supplier_id."',`subtotal`='".$sub_total."',`diskonpersen`='".$disc."',`nilaidiskon`='".$nilai_dis."',`ppn`='".$nppn."',`pph`='".$npph."',`ppl`='".$ppL."',`nilaipo`='".$nilai_po."',`tanggalkirim`='".$tanggl_kirim."',
                                  ".$field."='".$lokasi_kirim."',`syaratbayar`='".$cr_pembayaran."',`uraian`='".$ketUraian."',`purchaser`='".$purchaser."',`lokalpusat`='0',`matauang`='".$mtUang."',`kurs`='".$Kurs."',`persetujuan1`='".intval($persetujuan)."',`hasilpersetujuan1`='0',
                                  `tglp1`='0000-00-00',`statuspo`='0',`persetujuan2`='".intval($ttd2)."',`hasilpersetujuan2`='0',`tglp2`='0000-00-00',tgledit='".$thisDate."',ongkosangkutan='".$ongkirim."',`persetujuan3`='".intval($ttd3)."',`hasilpersetujuan1`='0'
                                   where nopo='".$nopo."'";
                        //}
                      //  echo "warning:".$strx; exit();//(`nopo`,`tanggal`,
                        if(!mysql_query($strx))
                        {
                        //echo $sqp;
                            echo "Gagal,".(mysql_error($conn));exit();
                        }
						else
						{
							foreach($_POST['kdbrg'] as $row =>$isi)
							{
								//echo "warning:masuk";exit();
								$kdbrg=$isi;
								$nopp=$_POST['nopp'][$row];
								$jmlh_pesan=$_POST['rjmlh_psn'][$row];
								$hrg_satuan=$_POST['rhrg_sat'][$row];
                                                                $rongank=str_replace(',','',$_POST['rongangkut'][$row]);
								$hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
							//	$mat_uang=$_POST['rmat_uang'][$row];
								$satuan=$_POST['rsatuan_unit'][$row];
								$diskon=($hrg_sblmdiskon*$disc)/100;
								$hrg_diskon=$hrg_sblmdiskon-$diskon;
                                                                $hrgSat=$hrg_diskon+($rongank/$jmlh_pesan);
								$spekBrg=$_POST['spekBrg'][$row];
								$sqjmlh="select selisih,jlpesan,realisasi from ".$dbname.".log_sudahpo_vsrealisasi_vw where nopp='".$nopp."' and kodebarang='".$kdbrg."'";
								//echo "warning:".$sqjmlh;exit();
								$qujmlh=mysql_query($sqjmlh) or die(mysql_error());
								$resjmlh=mysql_fetch_assoc($qujmlh);
                                                                if($rongank=='')
                                                                {
                                                                    $rongank=0;
                                                                }
//								  $sql="insert into ".$dbname.".log_podt (`nopo`,`kodebarang`,`jumlahpesan`,`hargasatuan`,`nopp`,`hargasbldiskon`,`satuan`,`catatan`)
//									values ('".$nopo."','".$kdbrg."','".$jmlh_pesan."','".$hrg_diskon."','".$nopp."','".$hrg_sblmdiskon."','".$satuan."','".$spekBrg."')";
                                                                  $sql="update ".$dbname.".log_podt set `jumlahpesan`='".$jmlh_pesan."',`harganormal`='".$hrg_diskon."',`nopp`='".$nopp."',
                                                                        `hargasbldiskon`='".$hrg_sblmdiskon."',`satuan`='".$satuan."',`catatan`='".$spekBrg."',`hargasatuan`='".$hrgSat."',`ongkangkut`='".$rongank."'
                                                                        where nopo='".$nopo."' and kodebarang='".$kdbrg."'";
														//echo "warning:".$sql;exit();
                                                                 if(!mysql_query($sql))
                                                                 {
                                                                    echo $sql."-----";
                                                                    echo "Gagal,".(mysql_error($conn));exit();
                                                                 }
										$supp="update ".$dbname.".log_prapoht set `nopo`='".$nopo."' where nopp='".$nopp."'";
										//echo"warning:test".$supp;exit();
										if(mysql_query($supp))
										{echo"";}
										else
										{echo "Gagal,".(mysql_error($conn));exit();}


                                                                        $sdpp="update ".$dbname.".log_prapodt set `create_po`='1' where `nopp`='".$nopp."' and `kodebarang`='".$kdbrg."'";
                                                                        if(mysql_query($sdpp))
                                                                        {echo"";}
                                                                        else
                                                                        {echo "Gagal,".$sdpp."__".(mysql_error($conn));exit();	}
							}


						}
		break;
		case 'update_data' :
		echo"
		<table cellspacing='1' border='0' class='sortable'>
        <thead>
            <tr class=rowheader>
				<td>No</td>
                <td>".$_SESSION['lang']['nopo']."</td>
                <td>".$_SESSION['lang']['namasupplier']."</td>
				<td>".$_SESSION['lang']['tgl_po']."</td>
                <td>".$_SESSION['lang']['tgl_kirim']."</td>

                <td>".$_SESSION['lang']['syaratPem']."</td>
				 <td>".$_SESSION['lang']['status']."</td>
                <td>action</td>
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
                                if($_SESSION['empl']['kodejabatan']=='5')
                                {
                                    $sql2="select count(*) as jmlhrow from ".$dbname.".log_poht where lokalpusat='0'  order by tanggal desc ";
                                    $sql="select * from ".$dbname.".log_poht where lokalpusat='0' order by tanggal desc limit ".$offset.",".$limit."";
                                }
                                else
                                {
                                    $sql2="select count(*) as jmlhrow from ".$dbname.".log_poht where lokalpusat='0' and purchaser='".$_SESSION['standard']['userid']."' order by tanggal desc ";
                                    $sql="select * from ".$dbname.".log_poht where lokalpusat='0' and purchaser='".$_SESSION['standard']['userid']."'  order by tanggal desc limit ".$offset.",".$limit."";
                                }
				$query2=mysql_query($sql2) or die(mysql_error());
				while($jsl=mysql_fetch_object($query2)){
				$jlhbrs= $jsl->jmlhrow;
				}
				$no=0;

                $query=mysql_query($sql) or die(mysql_error());
                while ($res = mysql_fetch_object($query)) {
					$no+=1;
                    $sql2="select * from ".$dbname.".log_5supplier where supplierid='".$res->kodesupplier."'";
                    $query2=mysql_query($sql2) or die(mysql_error());
                    $res2=mysql_fetch_object($query2);

                  	$skry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$res->purchaser."'";// echo $skry;
					$qkry=mysql_query($skry) or die(mysql_error());
					$rkry=mysql_fetch_assoc($qkry);

					 if($res->stat_release==0)
					 {
						 $stat_po=$_SESSION['lang']['un_release_po'];
					 }
					 elseif($res->stat_release==3)
					 {
					 	$stat_po=$_SESSION['lang']['release_po'];
					 }
					 elseif($res->stat_release==2)
					 {
						$stat_po=$_SESSION['lang']['ditolak'];
					 }
                   echo"
                        <tr ".($res->stat_release==2?"bgcolor='orange'":"class=rowcontent").">
			    <td>".$no."</td>
                            <td>".$res->nopo."</td>
                            <td>".$res2->namasupplier."</td>
			    <td>".tanggalnormal($res->tanggal)."</td>
                            <td>".tanggalnormal($res->tanggalkirim)."</td>
                            <td>".$res->syaratbayar."</td>
                            <td onclick=getKoreksi('".$res->nopo."') style=cursor:pointer>".$stat_po."</td> ";
                            if(($res->purchaser==$_SESSION['standard']['userid'])||($_SESSION['empl']['kodejabatan']=='5'))
                            {
                                    if($res->stat_release!=3)
                                    {
                                            echo"<td>
                                                 <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$res->nopo."','".tanggalnormal($res->tanggal)."','".$res->kodesupplier."','".$res->subtotal."','".$res->diskonpersen."','".$res->ppn."','".$res->pph."','".$res->ppl."','".$res->nilaipo."','".$res2->rekening."','".$res2->npwp."','".$res->nilaidiskon."','".$stat."','".tanggalnormal($res->tanggalkirim)."','".$res->matauang."','".$res->kurs."','".$res->persetujuan1."','".$res->idFranco."','".$res->persetujuan2."','".$res->ongkosangkutan."','".$res->persetujuan3."');\">";
                                            echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPo('".$res->nopo."','".$res->stat_release."');\" >
                                                 <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">
												 <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po_kop',event);\">
                                                 <img src=images/nxbtn.png class=resicon  title='Persetujuan PO' onclick=\"pertujuanDetail('".$res->nopo."');\">
                                            </td>";
                                    }
                                    else
                                    {
                                            echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">
											<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po_kop',event);\"></td>";
                                    }
                            }
                            else
                            {
                                echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">
								<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po_kop',event);\"></td>";
                            }

                }
				echo"
				 <tr><td colspan=9 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />";
				echo"</tbody> </table>";
		break;
		case 'edit_po':

                $tglSkrng=date("Y-m-d");
		if(($supplier_id=='')||($nopo==''))
		{
			echo"warning:Please Complete The Form";
			exit();
		}
                //cek matauang dan kurs
                if($mtUang!='IDR')
                {
                    $sGetKurs="select distinct kurs,kode from ".$dbname.".setup_matauangrate where kode='".$mtUang."' order by daritanggal desc";
                    //exit("Error:".$sGetKurs."__".$Kurs);
                    $qGetKurs=mysql_query($sGetKurs) or die(mysql_error());
                    $rGetKurs=mysql_fetch_assoc($qGetKurs);
                    if($Kurs<$rGetKurs['kurs'])
                    {
                      exit("Error:Masukan Kurs Sesuai Dengan Mata Uang,Kurs Untuk ".$rGetKurs['kode']." :".$rGetKurs['kurs']);
                    }
                }
                else
                {
                    $Kurs=1;
                }


		foreach($_POST['kdbrg'] as $row =>$isi)
		{

			$kdbrg=$isi;
			$nopp=$_POST['nopp'][$row];
			$jmlh_pesan=$_POST['rjmlh_psn'][$row];
                        $hrg_satuan=$_POST['rhrg_sat'][$row];
                        $hrg_sblmdiskon=str_replace(',','',$hrg_satuan);

			$diskon=($hrg_sblmdiskon*$disc)/100;
			$hrg_diskon=$hrg_sblmdiskon-$diskon;
			$mat_uang=$_POST['rmat_uang'][$row];
			$satuan=$_POST['rsatuan_unit'][$row];
			$spekBrg=$_POST['spekBrg'][$row];
                        $rongank=str_replace(',','',$_POST['rongangkut'][$row]);
			$hrgSat=$hrg_diskon;
			if(($jmlh_pesan=='')||($hrg_satuan=='')||($tanggl_kirim=='')||($cr_pembayaran=='')||($lokasi_kirim==''))
			{
				echo "warning: Please Complete The Form";
				exit();
			}
		else
		{
		$scek="select stat_release from ".$dbname.".log_poht where nopo='".$nopo."'";
		$qcek=mysql_query($scek) or die(mysql_error($conn));
		$rcek=mysql_fetch_assoc($qcek);
		if($rcek['stat_release']==1)
		{
			echo"warning : No. PO : ".$nopo." Sudah Release";
			exit();
		}

			if(intval($lokasi_kirim))
			{
			$field="`idFranco`";
			}
			else
			{
			$field="`lokasipengiriman`";
			}
                        if($ongkirim==''){
                            $ongkirim=0;
                        }
			$strx="update ".$dbname.".log_poht set
			`kodesupplier`='".$supplier_id."',`subtotal`='".$sub_total."',tgledit='".$tglSkrng."',`diskonpersen`='".$disc."',`nilaidiskon`='".$nilai_dis."',`ppn`='".$nppn."',`pph`='".$npph."',`ppl`='".$ppL."',`nilaipo`='".$nilai_po."',
                        `tanggalkirim`='".$tanggl_kirim."',".$field."='".$lokasi_kirim."',`syaratbayar`='".$cr_pembayaran."',`uraian`='".$ketUraian."',matauang='".$mtUang."',kurs='".$Kurs."',
                         persetujuan1='".intval($persetujuan)."',persetujuan2='".intval($ttd2)."',ongkosangkutan='".$ongkirim."',persetujuan3='".intval($ttd3)."',nomor='".$nomor."',statuspo='1'
                         where nopo='".$nopo."'";
			 //echo "warning:".$strx; exit();
			if(!mysql_query($strx))
			{
				//echo $sqp;
				echo "Gagal,".(mysql_error($conn));exit();
			}
			else
			{

				foreach($_POST['kdbrg'] as $row =>$isi)
				{

					$kdbrg=$isi;
					$nopp=$_POST['nopp'][$row];
					$jmlh_pesan=$_POST['rjmlh_psn'][$row];
									$hrg_satuan=$_POST['rhrg_sat'][$row];
									$hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
					$diskon=($hrg_sblmdiskon*$disc)/100;
					$hrg_diskon=$hrg_sblmdiskon-$diskon;
					$mat_uang=$_POST['rmat_uang'][$row];
					$satuan=$_POST['rsatuan_unit'][$row];
					$spekBrg=$_POST['spekBrg'][$row];
                                        $rongank=str_replace(',','',$_POST['rongangkut'][$row]);
                                        $hrgSat=$hrg_diskon;
                                        if($rongank==''){
                                            $rongank=0;
                                        }
					$sql="update ".$dbname.".log_podt
                                              set `jumlahpesan`='".$jmlh_pesan."',`hargasatuan`='".$hrgSat."',`matauang`='".$mat_uang."',`hargasbldiskon`='".$hrg_sblmdiskon."',
                                              `satuan`='".$satuan."',catatan='".$spekBrg."',harganormal='".$hrg_sblmdiskon."',`ongkangkut`='".$rongank."'
                                              where nopo='".$nopo."' and kodebarang='".$kdbrg."' and nopp='".$nopp."'";
						//echo "warning:".$sql; exit();
						if(!mysql_query($sql))
						{
							//echo $sqp;
							echo "Gagal,".(mysql_error($conn));exit();
						}
                                                else
                                                {
                                                   // $sCek="select distinct create_po from ".$dbname.".log_prapodt where nopp='".$_POST['nopp'][$row]."' and kodebarang='".$isi."'";
                                                   // $qCek=mysql_query($sCek) or die(mysql_error());
                                                   // $rCek=mysql_fetch_assoc($qCek);
                                                   // if($rCek['create_po']==''||$rCek['create_po']=='0')
                                                   // {
                                                        $sUpdate="update ".$dbname.".log_prapodt set create_po=1 where nopp='".$_POST['nopp'][$row]."' and kodebarang='".$isi."'";
                                                        if(!mysql_query($sUpdate))
                                                        {
                                                        echo "Gagal,".(mysql_error($conn));exit();
                                                        }
                                                    //}
                                                }
				}
			}
		 }
		}
		break;
		case 'delete_all':
		$scek="select stat_release from ".$dbname.".log_poht where nopo='".$nopo."'";
		$qcek=mysql_query($scek) or die(mysql_error($conn));
		$rcek=mysql_fetch_assoc($qcek);
		if($rcek['stat_release']==2)
		{
			echo"warning : No. PO : ".$nopo." Sedang Dalam Proses Koreksi";
			exit();
		}
		else
		{
                    $sCekGdng="select distinct nopo from ".$dbname.".log_transaksi_vw where nopo='".$nopo."'";
                    $qCekGdng=mysql_query($sCekGdng) or die(mysql_error($conn));
                    //exit("Error:".$sCekGdng);
                    $rCekGdng=mysql_num_rows($qCekGdng);
                    if($rCekGdng>0)
                    {
                    exit("Error: Nopo : ".$nopo." Sudah diterima di gudang tidak dapat di hapus");
                    }

                    $sListPP="select distinct nopp,kodebarang from ".$dbname.".log_podt where nopo='".$nopo."'";
                    $qListPP=mysql_query($sListPP) or die(mysql_error());
                    $row=mysql_num_rows($qListPP);

                        while($rListPP=mysql_fetch_assoc($qListPP))
                        {
                            $sUpd="update ".$dbname.".log_prapodt set create_po=0 where kodebarang='".$rListPP['kodebarang']."' and nopp='".$rListPP['nopp']."'";
                            if(mysql_query($sUpd))
                            {
                                $sql="delete from ".$dbname.".log_podt where kodebarang='".$rListPP['kodebarang']."' and nopp='".$rListPP['nopp']."' and nopo='".$nopo."'"; //echo "warning:".$sql;exit();
                                if(!mysql_query($sql))
                                {
                                echo "Gagal,".(mysql_error($conn));exit();
                                }
                            }
                             $row--;
                        }
                    if($row==0)
                    {
			$sql2="delete from ".$dbname.".log_poht where nopo='".$nopo."'";
			if(!mysql_query($sql2))
			{
				echo "Gagal,".(mysql_error($conn));exit();
			}
                    }
		}

		break;

		case 'insert_forward_po' :

		if($persetujuan==$_SESSION['standard']['userid'])
		{
			echo "Warning: Nama Tidak Boleh Sama Dengan User Id yang Mengajukan";
		}
		else
		{
			$tgl=date("Y-m-d");
			$sql="update ".$dbname.".log_poht set persetujuan1='".$persetujuan."',statuspo='2',tglp1='0000-00-00',hasilpersetujuan1='1' where nopo='".$nopo."'";
			//$sql="update ".$dbname.".log_poht set persetujuan1='".$persetujuan."',statuspo='1' where nopo='".$nopo."'";
			//echo "warning".$sql; exit();
			if(!mysql_query($sql))
			{
				echo "Gagal,".(mysql_error($conn));exit();
			}
		}
		break;
         case 'get_form_approval' :
	$sql="select nopo from ".$dbname.".log_poht where nopo='".$nopo."' and lokalpusat='0'";
	$query=mysql_query($sql) or die(mysql_error());
	$rCek=mysql_num_rows($query);
	if($rCek>0)
	{
					$rest=mysql_fetch_assoc($query);
					echo"<br />
					<div id=test style=display:block>
					<fieldset>
					<legend><input type=text readonly=readonly name=rnopp id=rnopp value=".$nopo."  /></legend>
					<table cellspacing=1 border=0>
					<tr>
					<td colspan=3>
					Diajukan Untuk Verifikasi Berikutnya :</td>
					</tr>
					<td>".$_SESSION['lang']['namakaryawan']."</td>
					<td>:</td>
					<td valign=top>";

					$optPur='';
					$klq="select namakaryawan,karyawanid,bagian,lokasitugas from ".$dbname.".`datakaryawan` where tipekaryawan='0' and karyawanid!='".$user_id."' and lokasitugas!='' and (kodejabatan<6 or kodejabatan=11) order by namakaryawan asc";
					//echo $klq;
					$qry=mysql_query($klq) or die(mysql_error());
					while($rst=mysql_fetch_object($qry))
					{
						$sBag="select nama from ".$dbname.".sdm_5departemen where kode='".$rst->bagian."'";
						$qBag=mysql_query($sBag) or die(mysql_error());
						$rBag=mysql_fetch_assoc($qBag);

						$optPur.="<option value='".$rst->karyawanid."'>".$rst->namakaryawan." [".$rst->lokasitugas."] [".$rBag['nama']."]</option>";
					}

					echo"
						<select id=persetujuan_id name=persetujuan_id>
							$optPur;
						</select></td></tr>
						<tr>
						<td colspan=3 align=center>
						<button class=mybutton onclick=forward_po() title=\"Diajukan Kembali Untuk Verivikasi\" >".$_SESSION['lang']['diajukan']."</button>
						<button class=mybutton onclick=cancel_po() title=\"Menutup Form Ini\">".$_SESSION['lang']['cancel']."</button>
						</td></tr></table><br />
						<input type=hidden name=proses id=proses  />
						</fieldset></div>

						<div id=close_po style=\"display:none;\">
						<fieldset><legend><input type=text id=snopo name=snopo disabled value='".$nopo."' /></legend>
						<p align=center>Apakah Anda Yakin Ingin Memproses PO Ini !!</p><br />
						<button class=mybutton onclick=proses_release_po() title=\"Untuk Memproses PO Ini\" >".$_SESSION['lang']['approve']."</button>
						<button class=mybutton onclick=cancel_po() title=\"Menutup Form Ini\">".$_SESSION['lang']['cancel']."</button>
						</fieldset></div>
						";
	}
	else
	{
		echo"warning:Data Belum Terinput";
		exit();
	}
		break;
		case 'proses_release_po':
		$sql="update ".$dbname.".log_poht set statuspo='2',hasilpersetujuan1='1' where nopo='".$nopo."'";
		mysql_query($sql) or die(mysql_error());

		break;
		case 'cari_nopo':
		echo"<div style=\"overflow:auto;height:400px;\">
		<table cellspacing='1' border='0'>
        <thead>
            <tr class=rowheader>
                <td>".$_SESSION['lang']['nopo']."</td>
                <td>".$_SESSION['lang']['namasupplier']."</td>
		<td>".$_SESSION['lang']['tgl_po']."</td>
                <td>".$_SESSION['lang']['tgl_kirim']."</td>
		<td>".$_SESSION['lang']['purchaser']."</td>
                <td>".$_SESSION['lang']['syaratPem']."</td>
                <td>".$_SESSION['lang']['status']."</td>
                <td>action</td>
            </tr>
         </thead>
	 <tbody>";


		//$sql2="select count(*) as jmlhrow from ".$dbname.".log_poht order by nopo desc ";

		if(isset($_POST['txtSearch']))
				{
					$txt_search=$_POST['txtSearch'];
					$txt_tgl=tanggalsystem($_POST['tglCari']);
					$txt_tgl_t=substr($txt_tgl,0,4);
					$txt_tgl_b=substr($txt_tgl,4,2);
					$txt_tgl_tg=substr($txt_tgl,6,2);
					$txt_tgl=$txt_tgl_t."-".$txt_tgl_b."-".$txt_tgl_tg;
					//echo "warning:".$txt_tgl;
				}
				else
				{
					$txt_search='';
					$txt_tgl='';
				}
				if($txt_search!='')
			{
				$where=" nopo LIKE  '%".$txt_search."%'";
			}
			elseif($txt_tgl!='')
			{
				$where.=" tanggal LIKE '".$txt_tgl."'";
			}
			elseif(($txt_tgl!='')&&($txt_search!=''))
			{
				$where.=" nopo LIKE '%".$txt_search."%' and tanggal LIKE '%".$txt_tgl."%'";
			}

			if(($txt_search=='')&&($txt_tgl==''))
			{
				$strx="SELECT * FROM ".$dbname.".log_poht where lokalpusat='0' order by nopo desc ";//echo $str;
				$sql2="SELECT count(*) as jmlhrow FROM ".$dbname.".log_poht where lokalpusat='0' order by nopo desc";
			}
			else
			{
				$strx="SELECT * FROM ".$dbname.".log_poht where lokalpusat='0' and ".$where." order by nopo desc";//echo $strx;
				$sql2="SELECT count(*) as jmlhrow FROM ".$dbname.".log_poht where lokalpusat='0' and ".$where." order by nopo desc";
			}
			//echo "warning:".$strx;exit();
			if(mysql_query($strx))
			{
                            $query=mysql_query($strx);
			$numrows=mysql_num_rows($query);
			if($numrows<1)
			{
				echo"<tr class=rowcontent><td colspan=10>Not Found</td></tr>";
			}
			else
			{
				//echo $sql2;
				$query2=mysql_query($sql2) or die(mysql_error());
				while($jsl=mysql_fetch_object($query2)){
				$jlhbrs= $jsl->jmlhrow;
				}
			  while ($res = mysql_fetch_object($query)) {
                    $sql2="select * from ".$dbname.".log_5supplier where supplierid='".$res->kodesupplier."'";
                    $query2=mysql_query($sql2) or die(mysql_error());
                    $res2=mysql_fetch_object($query2);

					$skry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$res->purchaser."'";// echo $skry;
					$qkry=mysql_query($skry) or die(mysql_error());
					$rkry=mysql_fetch_assoc($qkry);

					if($res->tglp1=='0000-00-00')
					{
						$stat=0;
					}
					elseif($res->tglp1!='0000-00-00')
					{
						$stat=1;
					}
					if($res->stat_release==0)
					{
					$stat_po=$_SESSION['lang']['un_release_po'];
					}
					elseif($res->stat_release==3)
					{
					$stat_po=$_SESSION['lang']['release_po'];
					}
					elseif($res->stat_release==2)
					{
						$stat_po=$_SESSION['lang']['ditolak'];
					}
                      echo"
                        <tr ".($res->stat_release==2?"bgcolor='orange'":"class=rowcontent").">
                        <td>".$res->nopo."</td>
                        <td>".$res2->namasupplier."</td>
                        <td>".tanggalnormal($res->tanggal)."</td>
                        <td>".tanggalnormal($res->tanggalkirim)."</td>
                        <td>".$rkry['namakaryawan']."</td>
                        <td>".$res->syaratbayar."</td>
                        <td onclick=getKoreksi('".$res->nopo."') style=cursor:pointer>".$stat_po."</td>";
                        if($res->stat_release !=3){
                            if(($res->purchaser==$_SESSION['standard']['userid'])||($_SESSION['empl']['kodejabatan']=='5'))
                            {
                                echo"<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$res->nopo."','".tanggalnormal($res->tanggal)."','".$res->kodesupplier."','".$res->subtotal."','".$res->diskonpersen."','".$res->ppn."','".$res->pph."','".$res->ppl."','".$res->nilaipo."','".$res2->rekening."','".$res2->npwp."','".$res->nilaidiskon."','".$res->stat_release."','".tanggalnormal($res->tanggalkirim)."','".$res->matauang."','".$res->kurs."','".$res->persetujuan1."','".$res->idFranco."','".$res->persetujuan2."','".$res->ongkosangkutan."');\">";
                                echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPo('".$res->nopo."','".$stat."');\" >
                                     <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">
									 <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po_kop',event);\">
                                     <img src=images/nxbtn.png class=resicon  title='Persetujuan PO' onclick=\"pertujuanDetail('".$res->nopo."');\">
                                </td>";
                            }
                            else
                            {
                                echo"
                                <td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">
								<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po_kop',event);\">
                                </td>";
                            }
                        }else {
                            echo"
                                <td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">
								<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po_kop',event);\">
                                </td>";
                        }
                        echo"</tr>";

                }
				echo"<tbody></table></div>";
			}
		}
		break;

		case 'cek_pembuat_po':
		//echo "warning:Please See Your Username";
			$user_id=$_SESSION['standard']['userid'];
			$skry="select purchaser from ".$dbname.".log_poht where nopo='".$nopo."'";
			$qkry=mysql_query($skry) or die(mysql_error());
			$rkry=mysql_fetch_assoc($qkry);
			if($rkry['purchaser']!=$user_id)
			{
				echo "warning:Please See Your Username";
				exit();
			}
		break;
		case'getKurs':
		$sGet="select kurs from ".$dbname.".setup_matauangrate where kode='".$mtUang."' and daritanggal='".$tgl_po."'";
		$qGet=mysql_query($sGet) or die(mysql_error());
		$rGet=mysql_fetch_assoc($qGet);
		//echo "warning:".$rGet['kurs'];
		if($mtUang=='IDR')
		{
			$rGet['kurs']=1;
		}
		else
		{
			$rGet['kurs']=$rGet['kurs'];
		}
		echo intval($rGet['kurs']);
		break;
		case'getKoreksi':
		$sql="select  distinct * from ".$dbname.".log_poht where nopo='".$nopo."'";
		//echo $sql;
                $query=mysql_query($sql) or die(mysql_error());
                $rCek=mysql_num_rows($query);
                if($rCek>0)
                {
                        $arrData=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak']);
                        echo"<br />
                        <div id=test>
                        <fieldset>
                        <legend><input type=text readonly=readonly name=rnopp id=rnopp value=".$nopo."  /></legend>
                        <table class=sortable border=0 cellspacing=1 width=\"100%\">
                        <thead>
                        <tr class=rowheader>
                        <td>".$_SESSION['lang']['namakaryawan']."</td>
                        <td>".$_SESSION['lang']['approval_status']."</td>
                        <td>".$_SESSION['lang']['catatan']."</td>
                        </tr>
                        </thead>
                        <tbody>";
                        $rdata=mysql_fetch_assoc($query);
                        for($ared=1;$ared<4;$ared++){
                            if(intval($rdata['persetujuan'.$ared])!=0){
                            echo"<tr class=rowcontent>
                            <td>".$optNmkar[$rdata['persetujuan'.$ared]]."</td>
                            <td>".$arrData[$rdata['hasilpersetujuan'.$ared]]."</td>
                            <td>".$rdata['catatan'.$ared]."</td>
                            </tr> ";
                            }
                        }
                        echo"</tbody>
                        </table></fieldset></div>";
                }
                else
                {
                        echo"warning:Data Belum Terinput";
                        exit();
                }
		break;
		case'updateKoreksi':
		$sUpd="update ".$dbname.".log_poht set stat_release='0' where nopo='".$nopo."'";
		if(!mysql_query($sUpd))
		{
			echo $sUpd."Gagal,".(mysql_error($conn));
		}
		break;
		case'getNotifikasi':
		$Sorg="select kodeorganisasi from ".$dbname.".organisasi where char_length(kodeorganisasi)=4";
		$qOrg=mysql_query($Sorg) or die(mysql_error());
		while($rOrg=mysql_fetch_assoc($qOrg))
		{
                    $scari="select distinct right(nopp,4) as unit from ".$dbname.".log_prapoht where nopp like '%".$rOrg['kodeorganisasi']."%'";
                    //exit("error:".$scari);
                    $qcari=mysql_query($scari) or die(mysql_error($conn));
                    $rcari=mysql_num_rows($qcari);
                    if($rcari!=0){
                        $dafUnit[]=$rOrg['kodeorganisasi'];
                    }
                }

                echo"<table border=0>";
                foreach($dafUnit as $lstKdOrg){
                    $ared+=1;
                    if($ared==1)
                    {
                        echo"<tr>";
                    }
		if($_SESSION['empl']['kodejabatan']=='5')
                {
                    $sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where nopp like '%".$lstKdOrg."%' and (lokalpusat='0' and status!='3') and (selisih>0 or selisih is null)";
                }
                else
                {
                   $sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where  nopp like '%".$lstKdOrg."%' and (purchaser='".$_SESSION['standard']['userid']."' and lokalpusat='0' and status!='3') and (selisih>0 or selisih is null)";
                }
                //exit("error:".$sList);
		//$sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$rOrg['kodeorganisasi']."'  and purchaser='".$_SESSION['standard']['userid']."' and lokalpusat='0' and status!='3') and (selisih>0 or selisih is null) group by kodept";
		$qList=mysql_query($sList) or die(mysql_error());
                $rBaros=mysql_num_rows($qList);
                $rList=mysql_fetch_assoc($qList);
                    if(intval($rList['jmlhJob'])!=0)
                    {
                        if($rList['jmlhJob']=='')
                        {
                            $rList['jmlhJob']=0;
                        }
                            if($_POST['status']==1)
                            {
                                echo"<td>".$lstKdOrg."</td><td>: ".$rList['jmlhJob']."</td>";
                            }
                            else
                            {
                                echo"<td>".$lstKdOrg."</td><td>: <a href='#' onclick=\"cek_pp_pt('".$lstKdOrg."')\">".$rList['jmlhJob']."</a></td>";
                            }
                    }
                    if($ared==9){
                        echo"</tr>";
                        $ared=0;
                    }
                }
                echo"</table>";
		break;
                case'getSupplierNm':
                    echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 class=sortbale>
                        <thead>
                        <tr class=rowheader>
                        <td>No.</td>
                        <td>".$_SESSION['lang']['kodesupplier']."</td>
                        <td>".$_SESSION['lang']['namasupplier']."</td>
                        </tr><tbody>
                        ";
                 $sSupplier="select namasupplier,supplierid from ".$dbname.".log_5supplier where namasupplier like '%".$nmSupplier."%' and kodekelompok='S001'";
                 $qSupplier=mysql_query($sSupplier) or die(mysql_error($conn));
                 while($rSupplier=mysql_fetch_assoc($qSupplier))
                 {
                     $no+=1;
                     echo"<tr class=rowcontent onclick=setData('".$rSupplier['supplierid']."')>
                         <td>".$no."</td>
                         <td>".$rSupplier['supplierid']."</td>
                         <td>".$rSupplier['namasupplier']."</td>
                    </tr>";
                 }
                    echo"</tbody></table></div>";
                break;


		case'getTandaTangan':

					$sGetApp="select karyawanid from ".$dbname.".setup_approval where applikasi like '%PO%'";
					$qGetApp = mysql_query($sGetApp);
					$i=1;
					$c = mysql_num_rows($qGetApp);
					while ($arrApp=mysql_fetch_array($qGetApp)){
						if ($i==$c)
						$id .= "'".$arrApp['karyawanid']."'";
						else
						$id .= "'".$arrApp['karyawanid']."',";
						$i++;
						}
						//exit("Error : ".$id);
					$arrNmJ=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
                    $snilpo="select (nilaipo*kurs) as nilaipo from ".$dbname.".log_poht where nopo='".$_POST['nopo']."'";
                    $qnilpo=mysql_query($snilpo) or die(mysql_error($conn));
                    $rnilpo=mysql_fetch_assoc($qnilpo);
                    $opt.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                    $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
                    $dtKar="select distinct namakaryawan,karyawanid,kodejabatan from ".$dbname.".datakaryawan
                            where tipekaryawan=0 and karyawanid in (".$id.") and tanggalkeluar='0000-00-00' and lokasitugas in (select distinct kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING') order by kodejabatan";
                    $qdtKar=mysql_query($dtKar) or die(mysql_error($conn));
                    while($rdtKar=  mysql_fetch_assoc($qdtKar)){
                        $opt.="<option value='".$rdtKar['karyawanid']."'>".getNmKaryawan($rdtKar['karyawanid'])." [ ".$arrNmJ[$rdtKar['kodejabatan']]." ]</option>";
                    }
                    $dr=0;
                    $sapll="select distinct * from ".$dbname.".setup_parameterappl
                            where kodeaplikasi='PO' order by kodeparameter asc";
                    $qapll=mysql_query($sapll) or die(mysql_error($conn));
                    while($rapll=  mysql_fetch_assoc($qapll)){
                        $dr+=1;
                        $nildt[$dr]=$rapll['nilai'];
                    }
                    $art="##nopodt";
                    $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['nopo']."</td>";
                    $tab.="<td>:</td><td><input type='text' id=nopodt class=myinputtext value='".$_POST['nopo']."' style=width:150px; disabled /></td></tr>";
                    if($rnilpo['nilaipo']<$nildt[1]){
                        $art.="##sign1";
                        $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tandatangan']."</td>";
                        $tab.="<td>:</td><td><select id=sign1 style=width:150px;>".$opt."</select></td></tr>";
                    }
                    if(($rnilpo['nilaipo']>=$nildt[1])&&(($rnilpo['nilaipo']<$nildt[2]))){
                        $art.="##sign1##sign2";
                        $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tandatangan']."</td>";
                        $tab.="<td>:</td><td><select id=sign1 style=width:150px;>".$opt."</select></td></tr>";
                        $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tandatangan']." 2</td>";
                        $tab.="<td>:</td><td><select id=sign2 style=width:150px;>".$opt."</select></td></tr>";
                    }
                    //if(($rnilpo['nilaipo']>=$nildt[2])&&(($rnilpo['nilaipo']<$nildt[3]))){
						if(($rnilpo['nilaipo']>=$nildt[2])){
                         $art.="##sign1##sign2##sign3";
                        $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tandatangan']."</td>";
                        $tab.="<td>:</td><td><select id=sign1 style=width:150px;>".$opt."</select></td></tr>";
                        $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tandatangan']." 2</td>";
                        $tab.="<td>:</td><td><select id=sign2 style=width:150px;>".$opt."</select></td></tr>";
                        $tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tandatangan']." 3</td>";
                        $tab.="<td>:</td><td><select id=sign3 style=width:150px;>".$opt."</select></td></tr>";
                    }
                    $tab.="<tr class=rowcontent><td colspan=3 align=center>";
                    $tab.="<button class=mybutton onclick=savePersetujuan('log_slave_save_po','".$art."')>".$_SESSION['lang']['save']."</button></td></tr>";
                    $tab.="</table>";
                    echo $tab;
                break;


                case'savePersetujuan':
/*                    $supd="update ".$dbname.".log_poht set persetujuan1='".intval($_POST['sign1'])."',persetujuan2='".intval($_POST['sign2'])."',
                           persetujuan3='".intval($_POST['sign3'])."',hasilpersetujuan1=0,hasilpersetujuan2=0,hasilpersetujuan3=0
                           where nopo='".$_POST['nopodt']."'";*/

						   //update by ikhsan statuspo=1

						                       $supd="update ".$dbname.".log_poht set persetujuan1='".intval($_POST['sign1'])."',persetujuan2='".intval($_POST['sign2'])."',
                           persetujuan3='".intval($_POST['sign3'])."',hasilpersetujuan1=0,hasilpersetujuan2=0,
						   hasilpersetujuan3=0,statuspo=1
                           where nopo='".$_POST['nopodt']."'";
                    //exit("error:".$supd);
                    if(intval($_POST['sign1'])!=0){
                        $dr[]=$_POST['sign1'];
                    }
                    if(intval($_POST['sign2'])!=0){
                        $dr[]=$_POST['sign2'];
                    }
                    if(intval($_POST['sign3'])!=0){
                        $dr[]=$_POST['sign3'];
                    }
                    /*$snilpo="select distinct purchaser from ".$dbname.".log_poht where nopo='".$_POST['nopodt']."'";
					//exit("error:$snilpo");
                    $qnilpo=mysql_query($snilpo) or die(mysql_error($conn));
                    $rnilpo=mysql_fetch_assoc($qnilpo);
                    $drop=count($dr);*/
				//	exit("Error:$drop");
                    /*if($drop==0){
                        exit("error: Pilih salah satu untuk persetujuan");
                    }*/
                    if(mysql_query($supd)){
						$snilpo="select  nopo,purchaser,persetujuan1,persetujuan2,persetujuan3,nilaipo from ".$dbname.".log_poht where nopo='".$_POST['nopodt']."'";
                 	  	$qnilpo=mysql_query($snilpo) or die(mysql_error($conn));
                    	$rnilpo=mysql_fetch_assoc($qnilpo);

						if($rnilpo['persetujuan1']!='0000000000')
						{

							$to=getUserEmail($rnilpo['persetujuan1']);
                            $namakaryawan=getNamaKaryawan($rnilpo['purchaser']);
                            $nmpnlk=getNamaKaryawan($rnilpo['persetujuan1']);
                            $subject="[Notifikasi] ".$_SESSION['lang']['nopo']." :".$rnilpo['nopo']."";
                            $body="<html>
                                     <head>
                                     <body>
                                       <dd>Dengan Hormat, Mr./Mrs. ".$nmpnlk."</dd><br>
                                       Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Persertujuan atas ".$_SESSION['lang']['nopo']." : ".$_POST['nopodt']."
                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                       <br>
                                       Regards,<br>
                                       Owl-Plantation System.
                                     </body>
                                     </head>
                                   </html>
                                   ";
                           //email tidak dikirim
						   $x=kirimEmail($to,$subject,$body);#this has return but disobeying;
						}


						/*if($rnilpo['persetujuan2']!='0000000000')
						{

							$to=getUserEmail($rnilpo['persetujuan2']);
                            $namakaryawan=getNamaKaryawan($rnilpo['purchaser']);
                            $nmpnlk=getNamaKaryawan($rnilpo['persetujuan2']);
                            $subject="[Notifikasi] ".$_SESSION['lang']['nopo']." :".$rnilpo['nopo']."";
                            $body="<html>
                                     <head>
                                     <body>
                                       <dd>Dengan Hormat, Mr./Mrs. ".$nmpnlk."</dd><br>
                                       Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Persertujuan atas ".$_SESSION['lang']['nopo']." : ".$_POST['nopodt']."
                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                       <br>
                                       Regards,<br>
                                       Owl-Plantation System.
                                     </body>
                                     </head>
                                   </html>
                                   ";
                           $x=kirimEmail($to,$subject,$body);
						}

						if($rnilpo['persetujuan3']!='0000000000')
						{

							$to=getUserEmail($rnilpo['persetujuan3']);
                            $namakaryawan=getNamaKaryawan($rnilpo['purchaser']);
                            $nmpnlk=getNamaKaryawan($rnilpo['persetujuan3']);
                            $subject="[Notifikasi] ".$_SESSION['lang']['nopo']." :".$rnilpo['nopo']."";
                            $body="<html>
                                     <head>
                                     <body>
                                       <dd>Dengan Hormat, Mr./Mrs. ".$nmpnlk."</dd><br>
                                       Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Persertujuan atas ".$_SESSION['lang']['nopo']." : ".$_POST['nopodt']."
                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                       <br>
                                       Regards,<br>
                                       Owl-Plantation System.
                                     </body>
                                     </head>
                                   </html>
                                   ";
                           $x=kirimEmail($to,$subject,$body);
						}*/


						/*for($aert=0;$aret<=$drop;$aert++){


                            $to=getUserEmail($_POST['sign'.$aert]);
                            $namakaryawan=getNamaKaryawan($rnilpo['purchaser']);
                            $nmpnlk=getNamaKaryawan($_POST['sign'.$aert]);
                            $subject="[Notifikasi] ".$_SESSION['lang']['nopo']." :".$_POST['nopodt']."";
                            $body="<html>
                                     <head>
                                     <body>
                                       <dd>Dengan Hormat, Mr./Mrs. ".$nmpnlk."</dd><br>
                                       Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namakaryawan." mengajukan Persertujuan atas ".$_SESSION['lang']['nopo']." : ".$_POST['nopodt']."
                                       kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                                       <br>
                                       Regards,<br>
                                       Owl-Plantation System.
                                     </body>
                                     </head>
                                   </html>
                                   ";
                           $x=kirimEmail($to,$subject,$body);#this has return but disobeying;
                       }*/

                   }
                break;
	}



	 ?>
