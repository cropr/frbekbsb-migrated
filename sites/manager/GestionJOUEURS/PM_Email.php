<?php

// CHANGE OF MAILPROCESSING 

use frbekbsb\mail;

require_once "startup.php";
require_once "frbekbsb/mail.php";


// L'exp�diteur est l'administrateur (cc1)
// Envoi au modificateur (to)
// Copies aux responsables des Federation s'il y a un/des adresses Email
//        0=FRBE
//        F=FEFB
//        D=SVDB
//        V=VSF
// Copies aux responsables de Ligues s'il y a un/des adresses Email
//        ligues:100,200,300,....
// Copies suppl�mentaires aux personnes d�sign�es par cc1,cc2,cc3,...
// ----------------------------------------------------------------------------------------------

	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	/* --------------------------------------------------- */
	/* 2021/11/19                                          */
	/* ajout du d�cryptage d'un Username/Password 20211119 */
	/* la clef se trouve dans le fichier lui-m�me          */
	/* donc les appels ne doivent pas donner la clef       */
	/* --------------------------------------------------- */
	/* Supprim� le 16/02/2022                              */ 
	/* require '../include/DecryptUsrPwd.inc.php';         */
	/* --------------------------------------------------- */


function CheckElo($text,$val) {
	$txt="";
	if ((strlen($val)) > 0)
	$txt = "<tr><td bgcolor='red'><b>$text</b></td><td bgcolor='red'>on</td></tr>\n";
	return $txt;
}

function AddInRed($text,$val) {
	$txt="";
	if ((strlen($val)) > 0)
		$txt = "<tr><td bgcolor='red'><b>$text</b></td><td bgcolor='red'>$val</td></tr>\n";
	else
		$txt = "<tr><td bgcolor='red' colspan='2'><b>$text</b></td></tr>\n";
	return $txt;
}

//----------------------------------------------
// what = quoi a �t� modifi� 
// (voir PM_Player.php fonction TestOldValue()
//   1 = nouvelle affiliation
//   2 = date de naissance
//   4 = nom
//   8 = pr�nom
//  16 = sexe
//  32 = nationalit�
//  64 = nationalite FIDE
// 128 = Club
// 256 = Note
// 512 = Federation
//---------------------------------------------------
function NotifyInit($txt,$mat="",&$klu="",&$errAdr,$what) {
	global $cc4Value;
	global $err_mail,$login,$AdminFRBE,$Content,$fpdb,$Federation,$OldValue;
//  echo "GMA: into NotifyInit mat=$mat klu=$klu txt=$txt <br>\n";
	$klu="";
	$Content="";
	$to  = array('');								// Vers le responsable de la modification
	$cc1 = array('laurent.wery@frbe-kbsb-ksb.be'); 			// Copies en plus		
	$cc2 = array('treasurer@frbe-kbsb-ksb.be');	//
	$cc3 = array('jan.vanhercke+admin@frbe-kbsb-ksb.be');		// V�rifier que l'on �crase plus d'autres cc1 cc2 cc3 ou cc4
	$cc4 = array('');
    $cc3Value = "fide@frbe-kbsb-ksb.be";				// Pour les joueurs dont la date de naissance a chang�
    $cc4Value = "luc.cornet@frbe-kbsb-ksb.be";			// Pour les joueurs ayant d�j� eu une cote ELO

	$EmailFRBE = array();
	$EmailFede = array();
	$EmailLigue= array();
	$boundary = md5(uniqid(rand()));				// Cr�ation d'un nombre de codage alm�atoire
	$err_mail="";									// Cette variable est TOUJOURS affich�e au retour.
	$Emails     = array();
	
	//--- Lecture du Signaletique du Matricule modifi�---
	$res=mysqli_query($fpdb,"SELECT * FROM signaletique WHERE Matricule='$mat'");
	$sig=mysqli_fetch_array($res);
	$fed = $sig['Federation'];
	$klu = $sig['Club'];
	if ($sig['Prenom']  == "" ) 	$errAdr |= 1;		// Pas de pr�nom
	if ($sig['Adresse']  == "" ) 	$errAdr |= 2;		// Pas d'adresse
	if ($sig['CodePostal']  == "" ) $errAdr |= 2;
	if ($sig['Localite']  == "" ) 	$errAdr |= 2;
	if ($sig['Pays']  == "" ) 		$errAdr |= 2;
	
//	echo "GMA: PM_Email.php Fede=$Federation OldFede={$OldValue[8]} sig_fede={$sig['Federation']} errAdr=$errAdr <br>\n";
//	echo "DEBUG sig=<pre>";print_r($sig);echo "</pre>\n"; echo "errAdr=$errAdr<br>";
	//--- Lecture de l'Email du responsable dans le signaletique) ---
	if (strcmp($_SESSION['Mail'],"SIGNALETIQUE")==0) {		// Email du matricule dans signaletique.
		$sql="SELECT Email from signaletique WHERE Matricule='$login';";
		$res=mysqli_query($fpdb,$sql);
		if ($res && mysqli_num_rows($res)) {
			$val=mysqli_fetch_array($res);
			$to =array(strtolower($val['Email']));
		}
	}
	else
		$to  = array(strtolower($_SESSION['Mail']));	// Adresse du matricule effectuant la transaction

	//--- Lecture du Mail de la FRBE ---
	$res=mysqli_query($fpdb,"select Email from p_federation where Federation='0';");
	$val=mysqli_fetch_array($res);
	$EmailFRBE=explode(",",strtolower($val['Email']));

	//--- Si AdminFRBE envoi �galement au responsable du club (v�rifier qu'il n'y a pas d'�crasement d'autres cc)
	if ($AdminFRBE) {	
		if (GetMailClub($klu,$Mail))
			$cc3  = array(strtolower($Mail));		// Adresse du responsable du club
	
	}	
	else {
		//--- Lecture du Mail de la Federation ---
		$res=mysqli_query($fpdb,"select Email from p_federation where Federation='$fed';");
		$val=mysqli_fetch_array($res);
		$EmailFede=explode(",",strtolower($val['Email']));
	
		//--- Lecture de la Ligue du Club ---
		$res=mysqli_query($fpdb,"select Ligue from p_clubs where Club='$klu';");
		if ($res && mysqli_num_rows($res)) {
			$val=mysqli_fetch_array($res);
			$Lig=$val['Ligue'];
	
		//--- Lecture du Mail de la Ligue ---
			$res=mysqli_query($fpdb,"select Email from p_ligue where Ligue='$Lig';");
			if ($res && mysqli_num_rows($res)) {
				$val=mysqli_fetch_array($res);
				$EmailLigue=explode(",",strtolower($val['Email']));
			}
		}
	}

	if ($sig['BoitePostale']  == "" ) $sig['BoitePostale']  	= "&nbsp;";
	if ($sig['Email']         == "" ) $sig['Email']         	= "&nbsp;";
	if ($sig['Telephone']     == "" ) $sig['Telephone']     	= "&nbsp;";
	if ($sig['Gsm']           == "" ) $sig['Gsm']           	= "&nbsp;";
	if ($sig['Fax']           == "" ) $sig['Fax']           	= "&nbsp;";
	if ($sig['Cotisation']    == "" ) $sig['Cotisation']    	= "&nbsp;";
	if ($sig['Cotisation']    == "0") $sig['Cotisation']    	= "&nbsp;";
	if ($sig['Note']          == "" ) $sig['Note']          	= "&nbsp;";
	if ($sig['Arbitre']       == "" ){$sig['Arbitre']       	= "&nbsp;";
									  $sig['ArbitreAnnee']  	= "&nbsp;";  }
	if ($sig['ClubOld']       == "" ) $sig['ClubOld']       	= "&nbsp;";
	if ($sig['ClubTransfert'] == "" ) $sig['ClubTransfert'] = "&nbsp;";
	if ($sig['ClubTransfert'] == "0") $sig['ClubTransfert'] = "&nbsp;";

			
	$Content .= "<html><body>\n";
	$Content .= "<h3><font color='red'>$txt</font></h3>\n";
	$Content .= '<b>Date-Datum:</b>'.date('d/m/Y').' - '.date('H:i').'<br>';
	$Content .= "<b><font color='red'>Exp�diteur-Verzender:</font></b>$login<br>";
	$Content .= '<b>Note-Opmerking:</b>' . $_SESSION['Note'] .'<br>';
	$Content .= "<div><font size='-1'><table border='1'>\n";
	$Content .= "<tr><td><b>Matricule-Stamnr</b>               </td><td>$mat                   </td></tr>\n";
	$Content .= "<tr><td><b>Affili� ann�e-Aansluitingsjaar</b> </td><td>{$sig['AnneeAffilie']} </td></tr>\n";
	$Content .= "<tr><td><b>Cotisation-Lidgeld</b>             </td><td>{$sig['Cotisation']}   </td></tr>\n";

// AJOUT des check ELO -----
	if (isset($_POST['CheckEloFide'])) {
		$Content .= CheckElo(Langue("* a une cote FIDE"    	 	,"Cote FIDE")		,$_POST['CheckEloFide']);
		$cc4[0] = $cc4Value;
	}
	if (isset($_POST['CheckEloFrbe'])) {
		$Content .= CheckElo(Langue("* a d�j� eu une cote FRBE"	,"Cote FRBE")		,$_POST['CheckEloFrbe']);
		$cc4[0] = $cc4Value;
	}
	if (isset($_POST['CheckEloEtranger'])) {
		$Content .= CheckElo(Langue("* a une cote �trang�re"	,"Cote Etrang�re")	,$_POST['CheckEloEtranger']);
		$cc4[0] = $cc4Value;
	}
	$Content .= CheckElo(Langue("* n'a pas de cote"     	,"Sans Cote")		,$_POST['CheckEloZero']);
	$Content .= CheckElo(Langue("* Vous avez lu"    		,"Accord")			,$_POST['CheckEloLu']);		
// ----------------------------	
	
		
	// Test si Nom(2) Prenom(4) Date de naissance(16) ont chang�s
	//----------------------------------------------------------
	if (! is_numeric($sig['MatFIDE'])) {
		if ($what & 2)	$Content .= AddInRed("* Nom-Naam",$sig['Nom']);
		else  			$Content .= "<tr><td><b>Nom-Naam</b></td><td>{$sig['Nom']}</td></tr>\n";
		if ($what & 4)	$Content .= AddInRed("* Pr�nom-Voornaam",$sig['Prenom']);
		else			$Content .= "<tr><td><b>Pr�nom-Voornaam</b></td><td>{$sig['Prenom']}</td></tr>\n";
		if ($what & 16) $Content .= AddInRed("* Date Naissance-Geboortedatum",$sig['Dnaiss']);
		else			$Content .= "<tr><td><b>Date Naissance-Geboortedatum</b></td><td>{$sig['Dnaiss']}</td></tr>\n";

		// Message diff�rent si le joueur a un NatFIDE BEL ou autre		
		if ($sig['NatFIDE'] == "BEL") {
			$Content .= AddInRed(Langue("Veuillez faire le changement �galement dans les donn�es fide par la FRS.",
										"Veuillez faire le changement �galement dans les donn�es fide par la FRS."),
										"");
		}
		else {
			$Content .= AddInRed(Langue(" Veuillez informer la f�d�ration �trang�re de ce changement.",
										" Veuillez informer la f�d�ration �trang�re de ce changement."),
										"");
		}
		$cc3[0] = $cc3Value;
	}
	else {
		$Content .= "<tr><td><b>Nom-Naam</b>                       </td><td>{$sig['Nom']}          </td></tr>\n";
		$Content .= "<tr><td><b>Pr�nom-Voornaam</b>                </td><td>{$sig['Prenom']}       </td></tr>\n";
		$Content .= "<tr><td><b>Date Naissance-Geboortedatum</b>   </td><td>{$sig['Dnaiss']}       </td></tr>\n";
}	
	
	
//	$Content .= "<tr><td><b>Nom-Naam</b>                       </td><td>{$sig['Nom']}          </td></tr>\n";
//	$Content .= "<tr><td><b>Pr�nom-Voornaam</b>                </td><td>{$sig['Prenom']}       </td></tr>\n";
//	$Content .= "<tr><td><b>Date Naissance-Geboortedatum</b>   </td><td>{$sig['Dnaiss']}       </td></tr>\n";

	$Content .= "<tr><td><b>Lieu Naissance-Geboorteplaats</b>  </td><td>{$sig['LieuNaiss']}    </td></tr>\n";	
	$Content .= "<tr><td><b>Sexe-Geslacht</b>                  </td><td>{$sig['Sexe']}         </td></tr>\n";
	$Content .= "<tr><td><b>Nationalit�-Nationaliteit</b>      </td><td>{$sig['Nationalite']}  </td></tr>\n";
	$Content .= "<tr><td><b>F�d. FIDE-Fed FIDE</b>     		   </td><td>{$sig['NatFIDE']}  	   </td></tr>\n";	
	$Content .= "<tr bgcolor='grey'><td><b>Id FIDE-FIDE Id</b>     		   </td><td>{$sig['Fide']}  	   </td></tr>\n";	
	$Content .= "<tr><td><b>Club</b>                           </td><td>{$sig['Club']}         </td></tr>\n";
	$Content .= "<tr><td><b>F�d�ration-Federatie</b>           </td><td>{$sig['Federation']}   </td></tr>\n";
	$Content .= "<tr><td><b>Adresse-Adres</b>                  </td><td>{$sig['Adresse']}      </td></tr>\n";
	$Content .= "<tr><td><b>Num�ro-Nummer</b>                  </td><td>{$sig['Numero']}       </td></tr>\n";
	$Content .= "<tr><td><b>Bo�te Postale-Postbus</b>          </td><td>{$sig['BoitePostale']} </td></tr>\n";
	$Content .= "<tr><td><b>Code Postal-Postcode</b>           </td><td>{$sig['CodePostal']}   </td></tr>\n";
	$Content .= "<tr><td><b>Localit�-Plaats</b>                </td><td>{$sig['Localite']}     </td></tr>\n";
	$Content .= "<tr><td><b>Pays-Land</b>                      </td><td>{$sig['Pays']}         </td></tr>\n";
	$Content .= "<tr><td><b>E-mail</b>                         </td><td>{$sig['Email']}        </td></tr>\n";
	$Content .= "<tr><td><b>T�l�phone-Telefoon</b>             </td><td>{$sig['Telephone']}    </td></tr>\n";
	$Content .= "<tr><td><b>Gsm</b>                            </td><td>{$sig['Gsm']}          </td></tr>\n";
	$Content .= "<tr><td><b>Fax</b>                            </td><td>{$sig['Fax']}          </td></tr>\n";
	$Content .= "<tr><td><b>Transfert-Transfers</b>            </td><td>{$sig['ClubTransfert']}</td></tr>\n";
	$Content .= "<tr><td><b>Ancien Club-Voormalige Club</b>    </td><td>{$sig['ClubOld']}      </td></tr>\n";
	$Content .= "<tr><td><b>Arbitre-Arbiter</b>                </td><td>{$sig['Arbitre']} {$sig['ArbitreAnnee']}     </td></tr>\n";
	$Content .= "<tr><td><b>Arbitre Fide-Fide Arbiter</b>      </td><td>{$sig['ArbitreFide']} {$sig['ArbitreAnneeFide']}     </td></tr>\n";
	$Content .= "<tr><td><b>Note-Nota</b>                      </td><td>{$sig['Note']}        </td></tr>\n";
	$Content .= "</table></font></div>\n";
	$Content .= "</body></html>\n";

	//--- Fusion et Unicit� des Emails ---
	// Jan Gooris (VSF) a demand� � ne plus recevoir les mails d'affiliations
	//------------------------------------------------------------------------
	if ($fed <> 'V')
		$Emails = array_merge($to,$cc1,$cc2, $cc3,$cc4, $EmailFRBE,$EmailFede,$EmailLigue);
	else 
		$Emails = array_merge($to,$cc1,$cc2, $cc3,$cc4, $EmailFRBE,           $EmailLigue);
	//------------------------------------------------------------------------
	$Emails = array_unique($Emails);
    
/*
echo "GMA: to        	 <pre>",print_r($to)        ;echo "</pre>\n";
echo "GMA: cc1       	 <pre>",print_r($cc1)       ;echo "</pre>\n";
echo "GMA: cc2       	 <pre>",print_r($cc2)       ;echo "</pre>\n";
echo "GMA: cc3       	 <pre>",print_r($cc3)       ;echo "</pre>\n";
echo "GMA: cc4       	 <pre>",print_r($cc4)       ;echo "</pre>\n";
echo "GMA: EmailFRBE 	 <pre>",print_r($EmailFRBE) ;echo "</pre>\n";
echo "GMA: EmailFede 	 <pre>",print_r($EmailFede) ;echo "</pre>\n";
echo "GMA: EmailLigue	 <pre>",print_r($EmailLigue);echo "</pre>\n";

echo "GMA: <b>Emails</b> <pre>",print_r($Emails)	;echo "</pre>\n";
echo "GMA: Content   	 <pre>",print_r($Content)	;echo "</pre>\n";
*/

  return ($Emails);
}

function NotifyReconduction($txt,$mat,$errAdrGlobal) {
	global $err_mail,$Content,$Serveur;
	$errAdr=0;
	$Content="";
//	echo "GMA Debug NotifyReconduction mat=$mat -txt=$txt- errAdr=$errAdr<br>";
	$Emails=NotifyInit($txt,$mat,$klu,$errAdr,0);

	// CHANGE MAIL PROCESSING	

	$mail = mail\create_mailer();

	$content = "";	
	
	//Destinataires
	$mail->AddAddress($Emails[0]);						// Mail du matricule effectuant la transaction);
	$sessMail=$Emails[0]."<br>\n";
	next($Emails);

	foreach ($Emails as $clef => $val) {		// New
		if ($val) {
			$sessMail .= $val."<br>\n";
			$mail->AddCC(htmlentities($val));
		}
	}
	// Sujet
	$titre=Langue("Reconductions affiliations","Verlenging aansluitingen");
	$titre .= " - $klu";
//	echo "Debug GMA errAdrGlobal=$errAdrGlobal $errAdrGlobal&1<br>\n";
	if ($errAdrGlobal & 1) $titre .= " - !!!!! PAS DE PRENOM";
	if ($errAdrGlobal & 2) $titre .= " - !!!!! PAS D'ADRESSES";
	$titre .= "\n";
	$mail->Subject=$titre;
	$Content=$txt;
	$mail->Body=$Content;

	if ($Serveur == "unix" || $Serveur == "FRBE") {
	  if(!$mail->Send()){
		$err_mail .= Langue("Erreur d'envoi du/des email(s)<br>\n","Fout bij het verzenden van een mail (de mails)<br>\n");
		$err_mail .= $mail->ErrorInfo; 
	  }
	  else{	  
		$err_mail .= Langue("Confirmation envoy�e par Email<br>$sessMail\n","Bevestiging verzonden per E-mail<br>$sessMail\n");
	  }
	}
	else {
		echo "Local Serveur:'$Serveur', pas d'Email envoy�<br>".
		     "<b><u>Subject=</u>$mail->Subject</b><br>".
		     "<b><u>CC:</u> $sessMail</b>".
		     "<b><u>Contenu de l'Email</u></b><br>".
		     "<blockquote>$Content</blockquote><br>\n";
		}
	$mail->SmtpClose();
	unset($mail);
}

function NotifyByMailer($txt,$mat,$what) {
	global $err_mail,$Content,$Serveur;
	global $cc4Value;
	$Content="";
	$errAdr=0;
	
	$Emails=NotifyInit($txt,$mat,$klu,$errAdr,$what);

	// CHANGE MAIL PROCESSING	

	$mail = mail\create_mailer();	

	$content = "";	
	
	//Destinataires
	$mail->AddAddress($Emails[0]);				// Mail du matricule effectuant la transaction);
	$sessMail=strtolower($Emails[0])."<br>\n";
	
	next($Emails);
	
	//-------------------------------------------
	// each has been deprecated from php 7.02
	// replaced by fromeach
	//	while(list($clef,$val) = each($Emails)) {
	//---------------------------------------------
	foreach ($Emails as $clef => $val) {		// New
		if ($val) {
			$sessMail .= $val."<br>\n";
			$mail->AddCC(htmlentities($val));
		}
	}
		// Sujet
	$titre = "$txt - $klu - $mat";
	if ($what & 22)				// Modification Nom(2) Prenom(4) Dnaiss(16)
		$titre .= " - Luc(FIDE)";
//	echo "DEBUG GMA titre=$titre errAdr=$errAdr<br>\n";
	if ($errAdr & 1) $titre .= " - !!! PAS DE PRENOM";	 	//	echo "DEBUG GMA titre=$titre<br>\n";
	if ($errAdr & 2) $titre .= " - !!! PAS D'ADRESSES";	//	echo "DEBUG GMA titre=$titre<br>\n";
	$titre .= "\n";
	$mail->Subject=$titre;
	$mail->Body=$Content;

		// Envoi du Mail si sur le serveur de la FRBE
	if ($Serveur == "unix" || $Serveur == "FRBE") {	
	  if(!$mail->Send()){
		$err_mail .= Langue("Erreur d'envoi du/des email(s)<br>\n","Fout bij het verzenden van een mail (de mails)<br>\n");
		$err_mail .= $mail->ErrorInfo; 
	  }
	  else{	  
		$err_mail .= Langue("Confirmation envoy�e par Email<br>$sessMail\n","Bevestiging verzonden per E-mail<br>$sessMail\n");
	  }
	}
		// Sinon notification sur le local serveur
	else {
		echo "Local Serveur:'$Serveur', pas d'Email envoy�<br>".
		     "<b><u>Subject:</u> $mail->Subject</b><br>".
		     "<b><u>CC:</u> $sessMail".
		     "<b><u>Contenu de l'Email:</u></b><div align='center'>$Content</div><br>\n";
	}
	$mail->SmtpClose();
	unset($mail);
}

// Notification d'un transfert
//----------------------------
function NotifyTransfert($txt,$mat) {
	global $err_mail,$Content,$Serveur,$fpdb;
	$Content="";
	
	$klu="";
	$Content="";
	$cc1 = array(); 		// Copies en plus
	$cc2 = array();			// Copies en plus
	$cc3 = array();			// Copies en plus
	$cc4 = array();			// Copies en plus
	
	$to         = array();
	$EmailFRBE  = array();
	$EmailFede  = array();
	$EmailLigue = array();
	
	$Emails     = array();
	
	$boundary = md5(uniqid(rand()));				// Cr�ation d'un nombre de codage alm�atoire
	$err_mail="";														// Cette variable est TOUJOURS affich�e au retour.

 	$Nomail = 0;														// Pas d'envoi de Mail

	//--- Lecture du Signaletique ---
	$res=mysqli_query($fpdb,"SELECT * FROM signaletique WHERE Matricule='$mat'");
	$sig=mysqli_fetch_array($res);
	$fed = $sig['Federation'];			// Federation
	$klu = $sig['Club'];				// Club actuel
	$nom = $sig['Nom'];					// Nom du joueur	
	$pre = $sig['Prenom'];				// Prenom du joueur
	$trf = $sig['ClubTransfert'];		// Club o� le joueur demande son transfert

	//--- Lecture de l'Email d'un responsable du club
	//---   President OU secretaire
	if (GetMailClub($klu,$Mail))
		$to[0]  = strtolower($Mail);	// Adresse du responsable du club
		
		//--- Lecture du Mail de la FEFB ---
	$res=mysqli_query($fpdb,"select Email from p_federation where Federation='0';");
	$val=mysqli_fetch_array($res);
	$EmailFRBE=explode(",",strtolower($val['Email']));

	//--- Lecture du Mail de la Federation ---
	$res=mysqli_query($fpdb,"select Email from p_federation where Federation='$fed';");
	$val=mysqli_fetch_array($res);
	$EmailFede=explode(",",strtolower($val['Email']));
	
	//--- Lecture de la Ligue du Club ---
	$res=mysqli_query($fpdb,"select Ligue from p_clubs where Club='$klu';");
	if ($res && mysqli_num_rows($res)) {
		$val=mysqli_fetch_array($res);
		$Lig=$val['Ligue'];
	
		//--- Lecture du Mail de la Ligue ---
		$res=mysqli_query($fpdb,"select Email from p_ligue where Ligue='$Lig';");
		if ($res && mysqli_num_rows($res)) {
			$val=mysqli_fetch_array($res);
			$EmailLigue=explode(",",strtolower($val['Email']));
		}
	}
/*
echo "GMA: to        <pre>",print_r($to)        ;echo "</pre>\n";
echo "GMA: cc1       <pre>",print_r($cc1)       ;echo "</pre>\n";
echo "GMA: cc2       <pre>",print_r($cc2)       ;echo "</pre>\n";
echo "GMA: cc3       <pre>",print_r($cc3)       ;echo "</pre>\n";
echo "GMA: cc4       <pre>",print_r($cc4)       ;echo "</pre>\n";
echo "GMA: EmailFRBE <pre>",print_r($EmailFRBE) ;echo "</pre>\n";
echo "GMA: EmailFede <pre>",print_r($EmailFede) ;echo "</pre>\n";
echo "GMA: EmailLigue<pre>",print_r($EmailLigue);echo "</pre>\n";
*/

	//--- Fusion et Unicit� des Emails ---
	$Emails = array_merge($to,$cc1,$cc2,$cc3,$cc4,$EmailFRBE,$EmailFede,$EmailLigue);
	$Emails = array_unique($Emails);
	
//	echo "GMA: Emails:<pre>";print_r($Emails);echo "</pre>\n";

	if ($EmailFRBE == $to[0]) {
		$Nomail = 1;
		$err_mail=Langue("Pas d'envoi d'email (FRBE)","Geen verzending van e-mail (KBSB)");
	}

			
	$Content .= "<html><body>\n";
	$Content .= "<h3><font color='red'>$txt</font></h3>\n";
	$Content .= "<b>Date-Datum:</b>".date('d/m/Y')." - ".date('H:i')."<br>\n";
	$Content .= "<b>".$txt."</b>";
	$Content .= " : $mat - $nom $pre de/van <u>$klu</u> vers/naar <u>$trf</u>\n";
	$Content .= "</body></html>\n";

	// CHANGE MAIL PROCESSING	

	$mail = mail\create_mailer();

	$content = "";	
	
	//Destinataires
	$mail->AddAddress($Emails[0]);						// Mail du matricule effectuant la transaction);
	$sessMail=strtolower($Emails[0])."<br>\n";
	
	next($Emails);
	
	//-------------------------------------------
	// each has been deprecated from php 7.02
	// replaced by formeach
	//	while(list($clef,$val) = each($Emails)) {
	//---------------------------------------------
	foreach ($Emails as $clef => $val) {		// New
		if ($val) {
			$sessMail .= $val."<br>\n";
			$mail->AddCC(htmlentities($val));
		}
	}
		// Sujet
	$mail->Subject="$txt - $mat : $klu --> $trf\n";
	$mail->Body=$Content;

		// Envoi du Mail si sur le serveur de la FRBE
	if ($Serveur == "unix" || $Serveur == "FRBE") {	
	  if(!$mail->Send()){
		$err_mail .= Langue("Erreur d'envoi du/des email(s)<br>\n","Fout bij het verzenden van een mail (de mails)<br>\n");
		$err_mail .= $mail->ErrorInfo; 
	  }
	  else{	  
		$err_mail .= Langue("Confirmation envoy�e par Email<br>$sessMail\n","Bevestiging verzonden per E-mail<br>$sessMail\n");
	  }
	}
		// Sinon notification sur le local serveur
	else {
		echo "Local Serveur:'$Serveur', pas d'Email envoy�<br>".
		     "<b><u>Subject:</u> $mail->Subject</b><br>".
		     "<b><u>CC:</u> $sessMail</b>".
		     "<b><u>Contenu de l'Email:</u></b><div align='center'>$Content</div><br>\n";
	}
	$mail->SmtpClose();
	unset($mail);
}

//--- Obtenir l'Email d'un matricule
//    return: 1=FOUND 0=Not Found
function GetMailClub($klu,&$mai) {
	global $fpdb;
	$mai = "";
	$sql="SELECT PresidentMat,SecretaireMat FROM p_clubs WHERE Club='$klu'";
	$res = mysqli_query($fpdb,$sql);
	$val=mysqli_fetch_array($res);
	$presMat=$val['PresidentMat'];
	$secrMat=$val['SecretaireMat'];
  
  if (!GetMail($presMat,$mai))
       GetMail($secrMat,$mai);
  
	return (strtolower($mai));
}

function GetMail($mat,&$mai) {
	global $fpdb;
	$sql="SELECT Email from signaletique where Matricule='$mat'";
	$res=mysqli_query($fpdb,$sql);
	$mai = "";
	if ($res && mysqli_num_rows($res)) {
		$val=mysqli_fetch_array($res);
		$mai=$val['Email'];
		if ($mai == "") 
			return 0;
	}
		return 1;
}
	
?>