<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
if (!isset($_SESSION['GesClub'])) {
    header("location: ../GestionCOMMON/GestionLogin.php");
}
$use_utf8 = false;
header("Content-Type: text/html; charset=iso-8889-1");

if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
    $url = "../GestionCOMMON/Gestion.php";
    header("location: $url");
}

$contents = file_get_contents($_SERVER['DOCUMENT_ROOT']."/database.json");
$json = json_decode($contents, true);

function showDate($file) {
	return date('d/m/y',filemtime($file));
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">

<HTML lang="fr">
<Head>
    <META name="Author" content="Halleux Daniel">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>Database</title>
    <link href="../ICN/styles.css" rel="stylesheet">
    <link href="../ICN/styles2.css" rel="stylesheet">
</Head>

<Body>
<div id="tete">
    <!--Bannière-->
    <table width=100% class=none>
        <tr>
            <td width="66" height="93">
                <div align="left"><a href="https://www.frbe-kbsb.be/"><img src="../logos/Logo FRBE.png" alt=""
                                                                           width="66"
                                                                           height="87"/></a></div>
            </td>
            <td width="auto" align="center"><h1>Database FRBE-KBSB-KSB</h1></td>
            <td width="66">
                <div align="right"><a href="https://www.frbe-kbsb.be/"><img src="../logos/Logo FRBE.png" alt=""
                                                                            width="66"
                                                                            height="87"/></a></div>
            </td>
        </tr>
    </table>
</div>
<br>

<form action="">
    <p align="center">
        <input type="submit" name="Exit" value="EXIT">
    </p>
</form>



    <table id="table1" class="tbl_icn_arch" style="width: 95%;" align="center">
        <tbody>
        <tr>
            <td style="text-align: left; padding:2ex;">

		<h4><strong>Results</strong></h4>

		<p>
		   <a href="/rating.php#/result-overview">Checklist</a>
		</p>

		<h4><strong>Player databases</strong></h4>

                <p>
			<a href="<?php echo $json['players-sqlite-file']?>">SQLite format</a> (<?php echo showdate($json['players-sqlite-file']) ?>)<br/>
			<a href="<?php echo $json['player-dbf-file']?>">DBASE DBF format</a> (<?php echo showdate($json['player-dbf-file']) ?>)<br/>
			<a href="<?php echo $json['swissmanager-file']?>">Swiss Manager / Excel format</a> (<?php echo showdate($json['swissmanager-file']) ?>)<br/>
			<a href="<?php echo $json['swisschess-file']?>">Swiss Chess / DBF format</a> (<?php echo showdate($json['swisschess-file']) ?>)<br/>
			<a href="<?php echo $json['sevilla-file']?>">Sevilla CSV</a> (<?php echo showdate($json['sevilla-file']) ?>)<br/>
                </p> 

                <h4><strong>Fide database</strong></h4>
                <p>
		 	<a href="fide.sqlite.zip">SQLite format</a> (<?php echo showdate('fide.sqlite.zip') ?>)<br/>
			<a href="Fide.zip">DBASE DBF format</a> (<?php echo showdate('Fide.zip') ?>)<br/>
                </p>

                <h4><strong><strong>ARCHIVES</strong></strong></h4>
                <p>
                     2019-10: <a href="players_201910.zip">Player SQLite</a> -
					 <a href="PLAYER_201910.ZIP">Player</a> -  
					 <a href="Tournois%20ELO%202019-10.pdf">Tournois</a><br>
                     2019-07: <a href="players_201907.zip">Player SQLite</a> -
					 <a href="PLAYER_201907.ZIP">Player</a> -  
					 <a href="Tournois%20ELO%202019-07.pdf">Tournois</a><br>
                     2019-04: <a href="players_201904.zip">Player SQLite</a> -
					 <a href="PLAYER_201904.ZIP">Player</a> -  
                    <a href="PLAYER_201904-v3.ZIP">Player-v3</a> - <a
                            href="Tournois%20ELO%202019-04.pdf">Tournois</a><br>
                     2019-01: <a href="players_201901.zip">Player SQLite</a> -
					 <a href="PLAYER_201901.ZIP">Player</a> -  
                    <a href="PLAYER_201901-v3.ZIP">Player-v3</a> - <a
                            href="Tournois%20ELO%202019-01.pdf">Tournois</a><br>
                     2018-10: <a href="players_201810.zip">Player SQLite</a> -
					 <a href="PLAYER_201810.ZIP">Player</a> -  
                    <a href="PLAYER_201810-v3.ZIP">Player-v3</a> - <a
                            href="Tournois%20ELO%202018-10.pdf">Tournois</a><br>
                     2018-07: <a href="PLAYER_201807.ZIP">Player</a> - 
                    <a href="PLAYER_201807-v3.ZIP">Player-v3</a> - <a
                            href="Tournois%20ELO%202018-07.pdf">Tournois</a><br>
					2018-04: <a href="PLAYER_201804.ZIP">Player</a> - 
							<a href="PLAYER_201804-v3.ZIP">Player-v3</a> - <a
                            href="Tournois%20ELO%202018-04.pdf">Tournois</a><br>
                    2018-01: <a href="PLAYER_201801.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202018-01.pdf">Tournois</a><br>
                    2017-10: <a href="PLAYER_201710.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202017-10.pdf">Tournois</a><br>
                    2017-07: <a href="PLAYER_201707.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202017-07.pdf">Tournois</a><br>
                    2017-04: <a href="PLAYER_201704.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202017-04.pdf">Tournois</a><br>
                    2017-01: <a href="PLAYER_201701.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202017-01.pdf">Tournois</a><br>
                    2016-10:&nbsp;<a href="PLAYER_201610.ZIP">Player</a>
                    -&nbsp;<a href="Tournois%20ELO%202016-10.pdf"
                              target="_blank">Tournois</a><br>
                    2016-07: <a href="PLAYER_201607.ZIP">Player</a>
                    -&nbsp;<a href="Tournois%20ELO%202016-07.pdf">Tournois</a><br>
                    2016-04: <a href="PLAYER_201604.ZIP">Player</a>
                    -&nbsp;<a href="Tournois%20ELO%202016-04.pdf">Tournois</a><br>
                    2016-01: <a href="PLAYER_201601.ZIP">Player</a>
                    -&nbsp;<a href="Tournois%20ELO%202016-01.pdf">Tournois</a><br>
                    2015-10: <a href="PLAYER_201510.ZIP">Player</a>
                    -&nbsp;<a href="Tournois%20ELO%202015-10.pdf">Tournois</a><br>
                    2015-07: <a href="PLAYER_201507.ZIP">Player</a>
                    -&nbsp;<a href="Tournois%20ELO%202015-07.pdf">Tournois</a><br>
                    2015-04: <a href="PLAYER_201504.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202015-04.pdf" target="_blank">Tournois</a><br>
                    2015-01: <a href="PLAYER_201501.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202015-01.pdf" target="_blank">Tournois</a><br>
                    2014-10: <a href="PLAYER_201410.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202014-10.pdf" target="_blank">Tournois</a><a
                            href="Tournois%20ELO%202014-10.pdf"
                            target="_blank"><br/></a>
                    2014-07: <a href="PLAYER_201407.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202014-07.pdf" target="_blank">Tournois</a><br/>2014-04:
                    <a href="PLAYER_201404.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202014-04.pdf" target="_blank">Tournois</a><br/>
                    2014-01: <a href="PLAYER_201401.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202014-01.pdf" target="_blank">Tournois</a><br/>
                    2013-10: <a href="PLAYER_201310.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202013-10.pdf">Tournois</a><br/>
                    2013-07: <a href="PLAYER_201307.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202013-07.pdf">Tournois</a><br/>
                    2013-01: <a href="PLAYER_201301.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202013-01.pdf">Tournois</a><br/>
                    2012-07: <a href="PLAYER_201207.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202012-07.pdf">Tournois</a><br/>
                    2012-01: <a href="PLAYER_201201.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202012-01.pdf">Tournois</a><br/>
                    2011-07: <a href="PLAYER_201107.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202011-07.pdf">Tournois</a><br/>
                    2011-01: <a href="PLAYER_201101.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202011-01.pdf">Tournois</a><br/>
                    2010-07: <a href="PLAYER_201007.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202010-07.pdf">Tournois</a><br/>
                    2010-01: <a href="PLAYER_201001.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202010-01.pdf">Tournois</a><br/>
                    2009-07: <a href="PLAYER_200907.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202009-07.pdf">Tournois</a><br/>
                    2009-01: <a href="PLAYER_200901.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202009-01.pdf">Tournois</a><br/>
                    2008-07: <a href="PLAYER_200807.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202008-07.pdf">Tournois</a><br/>
                    2008-01: <a href="PLAYER_200801.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202008-01.pdf">Tournois</a><br/>
                    2007-07: <a href="PLAYER_200707.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202007-07.pdf">Tournois</a><br/>
                    2007-01: <a href="PLAYER_200701.ZIP">Player</a> - <a
                            href="Tournois%20ELO%202007-01.pdf">Tournois</a>
                </p>
                <br>
            </td>
        </tr>
        </tbody>
    </table>
</Body>
