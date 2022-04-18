<?php

/* Transfert des joueurs d'un Club_Player (ou Club_ICN) source vers un Club_ICN destination */

session_start();
	$use_utf8 = false;
	include ("../Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	

if ($_POST['transfert1'])
{
  if (isset($_POST['id_Clb_pro1']))
  {
    if (!empty($_POST['id_Clb_pro1']))
    {
      if (isset($_POST['id_Clb_loc1']))
      {
        if (!empty($_POST['id_Clb_loc1']))
        {
          $query = 'UPDATE i_listeforce SET Club_Icn = ' . $_POST['id_Clb_loc1'] . ' WHERE Club_Player = '
            . $_POST['id_Clb_pro1'];
          $result = mysqli_query($fpdb,$query) or die (mysqli_error());
          $msg = 'Transfert effectué';
        }
        else
        {
          $query = 'UPDATE i_listeforce SET Club_Icn = 0 WHERE Club_Icn = ' . $_POST['id_Clb_pro1'];
          $result = mysqli_query($fpdb,$query) or die (mysqli_error());
          $msg = 'Transfert effectué';
        }
      }
    }
  }
}
elseif ($_POST['transfert2'])
{
  if (isset($_POST['id_Clb_pro2']))
  {
    if (!empty($_POST['id_Clb_pro2']))
    {
      if (isset($_POST['id_Clb_loc2']))
      {
        if (!empty($_POST['id_Clb_loc2']))
        {
          $query = 'UPDATE i_listeforce SET Club_Icn = ' . $_POST['id_Clb_loc2'] . ' WHERE Club_Icn = ' . $_POST['id_Clb_pro2'];
          $result = mysqli_query($fpdb,$query) or die (mysqli_error());
          $msg = 'Transfert effectué';
        }
        else
        {
          $query = 'UPDATE i_listeforce SET Club_Icn = 0 WHERE Club_Icn = ' . $_POST['id_Clb_pro2'];
          $result = mysqli_query($fpdb,$query) or die (mysqli_error());
          $msg = 'Transfert effectué';
        }
      }
    }
  }
}

elseif ($_POST['retour'])
{
  header("location: http://www.frbe-kbsb.be/sites/manager/ICN/LstFrc.php");
}

if ($_POST['Check'])
{

  // Vérification compte users
  //--------------------------
  $mat            = $_POST['Matricule'];
  $pwd            = $_POST['Password'];
  $hash           = "Le guide complet de PHP 5 par Francois-Xavier Bois";
  $_SESSION['ok'] = false;

  if (($mat == 9113) || ($mat == 'RTN') || ($mat == 'Zamparo'))
  {
    $sql = "Select * from p_user where user='" . $mat . "';";
    $res = mysqli_query($fpdb,$sql);
    $num = mysqli_num_rows($res);
    if ($num == 0)
    {
      $msg = "Matricule inconnu. Accès interdit.";
    }
    $usr = mysqli_fetch_array($res);
    $ppp = md5($hash . $pwd);
    if ($ppp != $usr['password'])
    {
      $msg = "Password non valable";
    }
    else
    {
      $msg            = 'Login OK';
      $_SESSION['ok'] = true;
    }
  }
  else
  {
    $msg = "Matricule inconnu. Accès interdit.";
  }
}
?>

<html>
<head>
    <META name="description"
          content="Script de transfert de clubs au sein de la liste de force en Interclubs nationaux FRBE-KBSB.">
    <META name="author" content="Halleux Daniel">
    <META name="keywords" content="chess, rating, elo, belgium, interclubs, FRBE, KBSB, FEFB, VSF">
    <META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
    <META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
    <meta name="date" content="2007-07-01T08:49:37+00:00">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

    <title>Transfert clubs LF en Interclubs nationaux FRBE-KBSB</title>
    <!-- link href="../css/FRBE_EloHist.css" title="FRBE.css" rel="stylesheet" type="text/css" -->
    <link rel="stylesheet" type="text/css" href="styles2.css"/>
</head>

<body>
<div id="tete">
    <!--Bannière-->
    <table width=100% height="99" class=none>
        <tr>
            <td width="66" height="93">
                <div align="left"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                              height="87"/></a>
                </div>
            </td>
            <td width="877" align="center"><h1>Fédération Royale Belge des Echecs FRBE ASBL<br/>
                Koninklijk Belgische Schaakbond KBSB VZW</h1></td>
            <td width="66">
                <div align="right"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                               height="87"/></a>
                </div>
            </td>
        </tr>
    </table>
</div>

<h2 align="center">INTERCLUBS NATIONAUX - NATIONALE INTERCLUBS<br>
    Liste de force<br/></h2>

<form method="post">
    <table class="table2" border="0" align="center">
        <caption align="top">
            <h4>LOGIN</h4>
        </caption>
        <tr>
            <td align="right"><b><?php echo "Matricule"; ?></b></td>
            <td><input name="Matricule" type="text" autocomplete="off" size="12" maxlength="12"></td>
        </tr>
        <tr>
            <td align="right"><b>Password</td>
            <td><input name="Password" type="password" autocomplete="off" size="15" maxlength="15" value=""></td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input type="submit" name="Check" value="Check login">
            </td>
        </tr>
        <tr>
            <td align="center" colspan="2">
                <input type="submit" name="retour" value="Retour LF"></td>
        </tr>
    </table>
    <br>

    <div id="msg"><p><?php echo $msg ?></p></div>
    <br>

    <h3 align="center"><b>ATTENTION !!!</b><br>
        1. Transfert des joueurs d'un <font color="red">Club_PLAYER</font> source vers un <font color="red">Club_ICN</font>
        destination</h3>

    <table class="table2" border="0" align="center">
        <tr>
            <td align="right"><b> <?php echo "Club PLAYER source"; ?></b></td>
            <td><input name="id_Clb_pro1" type="text" <?php if ($_SESSION['ok'])
            {
              echo 'enabled';
            }
            else
            {
              echo 'disabled';
            }?> size="3"/></td>
        </tr>
        <tr>
            <td align="right"><b> <?php echo "Club ICN destination"; ?></b></td>
            <td><input name="id_Clb_loc1" type="text" <?php if ($_SESSION['ok'])
            {
              echo 'enabled';
            }
            else
            {
              echo 'disabled';
            }?> size="3"/></td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input
                  <?php if ($_SESSION['ok'])
                {
                  echo 'enabled';
                }
                else
                {
                  echo 'disabled';
                }?>
                        type="submit"
                        name="transfert"
                        value="TRANSFERT"
                        />
            </td>
        </tr>
    </table>
    <br>
    <h3 align="center">
        2. Transfert des joueurs d'un <font color="red">Club_ICN</font> source vers un <font color="red">Club_ICN</font>
        destination</h3>

    <table class="table2" border="0" align="center">
        <tr>
            <td align="right"><b> <?php echo "Club ICN source"; ?></b></td>
            <td><input name="id_Clb_pro2" type="text" <?php if ($_SESSION['ok'])
            {
              echo 'enabled';
            }
            else
            {
              echo 'disabled';
            }?> size="3"/></td>
        </tr>
        <tr>
            <td align="right"><b> <?php echo "Club ICN destination"; ?></b></td>
            <td><input name="id_Clb_loc2" type="text" <?php if ($_SESSION['ok'])
            {
              echo 'enabled';
            }
            else
            {
              echo 'disabled';
            }?> size="3"/></td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input
                  <?php if ($_SESSION['ok'])
                {
                  echo 'enabled';
                }
                else
                {
                  echo 'disabled';
                }?>
                        type="submit"
                        name="transfert2"
                        value="TRANSFERT"
                        />
            </td>
        </tr>
    </table>
    <h3 align="center"><font color="red">
        Dans les 2 cas, si le champ Club destination ne contient rien, les joueurs sont transférés en club 0!</font></h3>
</form>
</body>
</html>
