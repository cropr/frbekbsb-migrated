<?php
session_start();


    /* ===== v5.2.21 ==================================
	require '../phpmailer/PHPMailerAutoload.php';
	===================================================
	*/

	/* ===== v6.0.3 =================================== */
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	
include ("../include/DecryptUsrPwd.inc.php");
	
	require '../phpmailer/src/Exception.php';
	require '../phpmailer/src/PHPMailer.php';
	require '../phpmailer/src/SMTP.php';
	/* ================================================ */

function actions($msg)
{
    $handle = fopen("actions.log", "a+");
    fwrite(
        $handle, date("d/m/Y H:i:s")
        . ' - IP: ' . $_SERVER["REMOTE_ADDR"] . ' - User: ' . $_SESSION['id_manager'] . ' - ' . $_SESSION['nom_manager'] . ' ' . $_SESSION['prenom_manager']
         . ' - ' . $msg
        . "\r\n");

    fclose($handle);
}

function specialXML($schaine)
{
    // return str_replace(array('<', '\'', '&', '"', '>'), array('.', '.', '-', '.', '.'), $schaine);
    return str_replace(array('&'), array('-'), $schaine);
}

function supp_accents ($schaine)
{
    return strtr($schaine, '�����������������������������������������������������', 'AAAAAACEEEEIIIINOOOOOUUUUYaaaaaaceeeeiiiinooooouuuuyy');
}

//------------------------------------------------------------------
// Envoi d'un mail
//------------------------------------------------------------------

function email($mail_destinataire, $sujet, $body, $mail_copie_1, $mail_copie_2, $mail_copie_3, $mail_copie_4, $mail_copie_5)
{

	// CHANGED START

	$mail = new PHPMailer(true);                                                                                                     
	$mail->SetLanguage('fr', 'phpmailer/language/');                                                                                 
	$mail->IsSMTP();                                                                                                                 
	$mail->IsHtml(true);                                                                                                             
	$mail->SMTPAuth   = true;        			// enable SMTP authentication                                                        
	$mail->SMTPSecure = "ssl";      			// sets the prefix to the server                                                     
	$mail->From       = 'noreply@frbe-kbsb.be';                                                                                      
	$mail->FromName   = 'Mail server GOOGLE';                                                                                        
	$mail->Host       = 'smtp.gmail.com';						//'smtp.gmail.com'; // sets GMAIL as the SMTP server                 
	$mail->Port       = 465; 									// set the SMTP port for the GMAIL server                            
	$mail->Username   = "No username / passwords params in source";
	$mail->Password   = "No username / passwords params in source";

	// CHANGED END    

    // Destinataire
    //$mail->AddAddress('kbsb.frbe@gmail.com');
    //$mail->AddBCC('Halleux.Daniel@gmail.com');
    $mail->AddBCC($mail_destinataire);
    $mail->AddBCC($mail_copie_1);
    $mail->AddBCC($mail_copie_2);
    $mail->AddBCC($mail_copie_3);
    $mail->AddBCC($mail_copie_4);
    $mail->AddBCC($mail_copie_5);
    $mail->AddBCC('jan.vanhercke+licenseg@frbe-kbsb-ksb.be');
    //$mail->AddCC('kbsb.frbe.archive@gmail.com');
    //$mail->addBCC('Halleux.Daniel@yahoo.fr');


    // Objet
    $mail->Subject = $sujet;
    $mail->Body = $body;

    // Ajouter une pi�ce jointe
    //$mail->AddAttachment('fichier.txt');

    // Envoi du mail avec gestion des erreurs
    $mail->Send();
}

//------------------------------------------------------------------
// NewMat: g�n�re des matricules � partir de 20000.
//------------------------------------------------------------------
function GenereNewMat()
{
    global $fpdb;
    $sqlM = "SELECT Matricule,Locked,DateModif FROM signaletique WHERE Matricule > '20000' ORDER by Matricule";
    $resM = mysqli_query($fpdb, $sqlM);
    $sigM = mysqli_fetch_array($resM);
    $NewMat = $sigM['Matricule'];

    $OldMat = $NewMat;        // Memorisation du NewMat
    $n = 1;
    while ($sigM = mysqli_fetch_array($resM)) {

        $NewMat = $sigM['Matricule'];

        $Modif = $sigM['DateModif'];
        $CurrD = date("Y-m-d");

        if ($NewMat == ($OldMat + 1)) {            // Il n'y a pas de trou
            if ($sigM['Locked'] != "1") {        // Pas locked == PRIS
                $OldMat++;
                continue;
            }
            // Le matricule est Locked, il faut tester la DateModif et CurrentDate
            // Si CurrentDate est > que DateModif, le record est libre
            if ($CurrD <= $Modif) {
                $OldMat++;
                continue;
            }
            // Le record est libre on le prend mais on y met des valeurs par default
            $NewMat = $OldMat + 1;
            $sql = "UPDATE signaletique SET Nom=NULL,DateInscription=CURDATE(),DateModif=CURDATE(),LoginModif=" . $_SESSION['id_manager'] . ",Locked='1' WHERE Matricule='$NewMat'";
            mysqli_query($fpdb, $sql);
            return ($NewMat);
        }
        $NewMat = $OldMat + 1;
        $sql = "INSERT INTO signaletique SET Matricule='$NewMat',Locked='1',DateInscription=CURDATE(), DateModif=CURDATE(),LoginModif=" . $_SESSION['id_manager'];
        mysqli_query($fpdb, $sql);
        return ($NewMat);
    }
    return (-1);
}

//------------------------------------------------------------------

//------------------------------------------------------------------
function StripQuotes(&$field)
{
    $field = str_replace("\"", "", $field);
    $field = stripslashes($field);
}


//--------------------------------------------------------
// Affichage d'un texte avec la langue donn�e dans la page de Login
// La langue est enregistr�e dans un COOKIE
//--------------------------------------------------------
function Langue($FR, $NL)
{
    if ($_SESSION['langue'] == "ned") {
        return $NL;
    } else {
        return $FR;
    }
}

//------------------------------------------------------------------

/*
Id �tape/F�d�	FR	        NL	                Codes postaux	Note
1	FV	Bruxelles-Capitale	Brussels-Capital	1000-1299
2	F	Brabant wallon	    Waals-Brabant	    1300-1499
3	V	Brabant flamand	    Vlaams-Brabant	    1500-1999	arrondissement de Hal-Vilvorde, sauf Overijse
                                                3000-3499	arrondissement de Louvain, plus Overijse
4	V	Anvers	            Antwerpen	        2000-2999
5	V	Limbourg	        Limburg	            3500-3999
6	F	Li�ge	            Luik	            4000-4999
7	F	Namur	            Namen	            5000-5999
8	F	Hainaut-1	        Henegouwen-1	    6000-6599
        Hainaut-2	        Henegouwen-2	    7000-7999
9	F	Luxembourg	        Luxemburg	        6600-6999
10	V	Flandre-Occidentale	West-Vlaanderen	    8000-8999
11	V	Flandre-Orientale	Oost-Vlaanderen	    9000-9999

100	F	FEFB		        Finales r�gionales
101	V	VSF
102	D	SVDB
110	FVD	FRBE-KBSB-KSB		Finale nationale

*/


// ------------------------------------------------------------------------------------------
// Pour mettre le premier caract�re en majuscule
// ------------------------------------------------------------------------------------------
// cette fonction 'splite le nom s�par� par un separateur
// Le premier caract�re du 'split' est mis en majuscule
// Ensuite on v�rifie que le premier caract�re se trouve dans les accentu�s minuscules
// Si c'est le cas on le remplace par le caract�re MAJUSCULE
//-------------------------------------------------------------------------------------------
function ucname2($sep, $nom)
{
    //$ASCII_SPC_MIN = "�����������������������������";
    //$ASCII_SPC_MAX = "����������������������������?";

    $ASCII_SPC_MIN = "�����������������������������??�";
    $ASCII_SPC_MAX = "����������������������������???�";

    $arr = explode($sep, $nom);
    $total = count($arr);
    $newnom = "";
    for ($i = 0; $i < $total; $i++) {
        $arr[$i][0] = strtoupper($arr[$i][0]);
        $pos = strpos($ASCII_SPC_MIN, $arr[$i][0]);

        if ($pos !== false) {
            $arr[$i][0] = $ASCII_SPC_MAX[$pos];
        }
        $newnom .= $arr[$i];
        if ($i < ($total - 1))
            $newnom .= $sep;
    }
    return $newnom;
}

// Cette fonction appelle ucname2 avec les s�parateurs tiret, espace, simple quote
function ucname($nom)
{
    if (strlen($nom) == 0)
        return "";

    $nom = trim(strtolower($nom));
    $SEPARATEURS = "- '";
    $tot = strlen($SEPARATEURS);
    for ($i = 0; $i < $tot; $i++) {
        $nom = ucname2($SEPARATEURS[$i], $nom);
    }
    return $nom;
}


function replaceAccentsUmlauts($str)
{
    $search = explode(",", "�,�,�,�,�,�,�,�,�,�,�,�,�,�,�,�,�,�,�,�,�");
    $replace = explode(",", "c,a,e,e,ae,e,i,oe,ue,a,e,i,o,u,Ae,E,I,Oe,Ue,C,ss");
    return str_replace($search, $replace, $str);
}
