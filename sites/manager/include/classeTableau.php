<?php	

// version 1.05 du 14/05/2007

require_once('_classePath.php');

// Chargement du code de couleur par défaut du site
require_once('_classeSkin.php');

// PARAMETRAGE :
DEFINE('CHEMINRESSOURCES_CT',INCLUDEPATH.'classeTableau/');

# Tabs :
# ------------------------------------------
#   ->tab_nouveau()          :	Définition d'un nouveau tableau
#   ->tab_ajoutcolonneadd()  :	Définition d'une colonne de commande d'ajout doit etre devant la 1ere ->tab_ajoutcolonne() 
#   ->tab_ajoutcolonne()     :	Définition d'une nouvelle colonne
#                             	title,width,
#								align (left,center,right), 
#                               sort  (Number,String,CaseInsensitiveString,Date,None)
#                               url   ( de la forme : "nom_de_la_page.php?ID=" )
#                               style ( de la colonne )

#   ->tab_trier()            :  Trier selon colonne particulière
#   ->tab_ouvrir()           :	Affichage de l'entete de colonne
#   ->tab_remplircellule()   :	Remplir une case par une valeur
#   ->tab_alleralaligne()    :	Aller a la ligne suivante quelque soit la colonne en cours
#   ->tab_sautligne()        :	Insérer une ligne blanche
#   ->tab_fermer()           :	Fermer le tableau
#   ->tab_boutons()          :	Afficher les boutons et Fermer le formulaire si ouvert
#   ->tab_recalc()           :	Recalculer tous les champs
#   ->tab_nbrelignes()       :	Retourne le nombre de ligne dans le tableau

#   ->tab_recuppost()        :  Pour facilité l'exploitation des SELECT retourne un tableau id => valeur
#	->tab_ActiverBtnValider() : Pour  laisser activé le bouton "Valider"
#	->tab_LibBoutons()
#	->tab_TimeOut()           : peut être utilisé pour rafraichir automatiquement le tableau ou sortir au bout d'un certain temps
#   ->tab_TitreInit()

class Tabs
{
	var $htmlparamcol;  	// tableau de tableau de paramètres
	var $taburl;            // tableau des URL de branchement

	var $titredutableau;	// titre du tableau
	var $nomdutableau = -1;	// nom du tableau (utilisé par le javascript c'est le compteur de tableau)
	var $nbrecolonnes;
	var $ligneencours;
	var $colencours;
	var $cpttitre;      	// compteur de titre de colonne
	var $cptinput;
	var $cptinputmasks;
	var $cptinputscript;
	
	var $masks_deja_sortis=false;
	var $numskin = 0;
	var $formulaireouvert=false;
	var $sequenceJS='';     // Sequence de code appelée par la validation sur le bouton
	var $codeenvoye = false;  // le code JS est a envoyer qu'une seule fois

	var $libbtnValider      = "Valider";
	var $libbtnAnnuler      = "Rétablir";
	var $libbtnQuitter      = "Quitter";

	var $timeout_nomobj     = 'CTtimeout';  // nom de l'objet javascript qui gere le timeout
	var $timeout_fctstop    = '';           // desarmement du timeout
	var $timeout_url        = '';                 
	var $timeout_tempo      = -1;                 
	var $timeout_idcounteur = '';  		
	var $timeout_dejasorti = false;  		




	function tab_nouveau( $titre_du_tableau="", $tabparam=array() )
	{

		$this->nomdutableau++;
		$this->htmlparamcol   = array();
		$this->taburl         = array();

		$this->class_col      = array();	   
		$this->style_col      = array();	   
		
		$this->nbrecolonnes   = 0;
		$this->celluleencours = 1;
		$this->ligneencours   = 0;
		$this->pair_impair    = 0;
		$this->parametragefin = 0;
		$this->colencours     = 0;
		$this->cptcolurl      = 0;
		$this->cpttitre       = 0;
		$this->btnajouter     = false;
		$this->btnquitter     = false;
		$this->urlquitter     = "";
		$this->ActiverBtnValider = false;

		$this->tooltips_cpt       = 0;
		$this->tooltips_js        = false;
		$this->tooltips_cssbody   = "tooltipsb";
		$this->tooltips_cssheader = "tooltipsh";
		$this->tooltips_width     = "150px";
		$this->tooltips_opacity   = "85";

		// Titre automatique du formulaire
		$this->tab_titre         = '';
		$this->tab_titre_icone   = '';
		$this->tab_titre_class   = '';

		
		$this->defaultsort    = -1;         // OPTION DE TRI = n°colonne
		$this->descending     = false;
		
		$this->cptcheckbox    = 0;
		$this->colcheckbox    = array();
		$this->InitToggleCheckBox = 0;      
		
		$this->largeurtab     = '500px';
		$this->largeurtabcalcule = 0;
		$this->cptselect      = 0;
		$this->cptinput       = 0; // compteur de champ de saisie INPUT
		$this->cptinputmasks  = 0;

		$this->style = "";
		// SI DES PARAMETRES ONT ETE PASSE ON LES ANALYSE
		if ( count($tabparam)>0 ) {
			if ( !empty($tabparam['style']) ) {
				$this->style = $tabparam['style'];
				$this->cptparam++;
			}
			if ( !empty($tabparam['tooltipsheaderstyle']) ) {
				$this->tooltips_cssheader = $tabparam['tooltipsheaderstyle'];
				$this->cptparam++;
			}
			if ( !empty($tabparam['tooltipsbodystyle']) ) {
				$this->tooltips_cssbody = $tabparam['tooltipsbodystyle'];
				$this->cptparam++;
			}
			if ( !empty($tabparam['tooltipswidth']) ) {
				$this->tooltips_width = $tabparam['tooltipswidth'];
				$this->cptparam++;
			}
			if ( !empty($tabparam['tooltipsopacity']) ) {
				$this->tooltips_opacity = $tabparam['tooltipsopacity'];
				$this->cptparam++;
			}
		}

		// CHARGEMENT DES PARAMETRES DU THEME DE COULEURS

		/* ORDRE DE PRIORITE DES COULEURS DU THEME :
		   1) LE COOKIE 'DEFAULT_SKIN'
		   2) LA VARIABLE DE SESSION 'DEFAULT_SKIN'
		   3) LA FONCTION frm_InitPalette DEFINIE DANS LE CODE
		   4) LA CONSTANTE 'DEFAULT_SKIN' DANS _classeSkin.php
		*/
		if ( isset($_COOKIE['DEFAULT_SKIN']) ) {
			$this->numskin = $_COOKIE['DEFAULT_SKIN'];
		} elseif ( isset($_SESSION['DEFAULT_SKIN']) ) {
			$this->numskin = $_SESSION['DEFAULT_SKIN'];
		} elseif ( defined('DEFAULT_SKIN') ) {
			$this->numskin = DEFAULT_SKIN;
		} else {
			$this->numskin = 0;
		}

		$this->titredutableau = $titre_du_tableau;

	}


	function tab_InitTimeOut($tempo=60,$url='',$idcounter='') {
		$this->timeout_tempo = $tempo;                 
		$this->timeout_url   = $url;                 
		$this->timeout_idcounteur = $idcounter;  
		$this->timeout_fctstop = $this->timeout_nomobj . '.AutoRedirection_stop();';   
	}	
	
	function tab_skin($numero=0) {
		// INITIALISE LE NUMERO DU SKIN
		$this->numskin  = $numero;
	}
	
	
	// DEFINITION ET AFFICHAGE DU TITRE
	function tab_TitreInit($tabattributs=array()) {

		$this->tab_titre         = '';
		$this->tab_titre_icone   = '';
		$this->tab_titre_class   = '';
				
		// le texte du message est le seul champ obligatoire
		if (!empty($tabattributs['text'])) {
			$this->tab_titre = $tabattributs['text'];
		}		
		$this->tab_titre_class = 'classetableautitre';
		if (!empty($tabattributs['class'])) {
			$this->tab_titre_class = $tabattributs['class'];
		}
		if (isset($tabattributs['maskedifmessage'])) {
			$this->tab_titre_masquer = $tabattributs['maskedifmessage'];
		}
		if (empty($tabattributs['icon'])) {
			$this->tab_titre_icone = '';
		} else {
			$this->tab_titre_icone = $tabattributs['icon'];
		}		
		
		// sortie effective du titre au moment de l'ouverture du tableau

		
	}


	
			// SI UNE CONSTANTE EST DEFINIE POUR LE SKIN TOUT LE SITE EN PROFITE

	// CREATION D'UNE COLONNE DE COMMANDE EN TETE DE TABLEAU : BOUTON AJOUTER ENTETE ET PIED DE TABLEAU
	function tab_ajoutcolonneadd($parametresprecis)
	{		
		if ($this->nbrecolonnes>0) {
			print "ATTENTION tab_ajoutcolonneadd() doit devancer le 1er appel a tab_ajoutcolonne()";
			return;
		}
		if ( is_array($parametresprecis) ) {
			if ( !empty($parametresprecis['help']) ) {
				// LE TAG HTML "ALT" EST ALLERGIQUE AU CARACTERE '
				$libhelp = str_replace("'","&#8217;",$parametresprecis['help']);
			} else	
				$libhelp = "Ajouter un nouvel enregistrement";
			if ( !empty($parametresprecis['icon']) ) {
				$iconadd = $parametresprecis['icon'];
			} else {
				$iconadd = CHEMINRESSOURCES_CT."img/add.gif";
			}

			$parametresprecis['sort'] = "none";
			$parametresprecis['width'] = "20px";
			$parametresprecis['title'] = "<a href='".$parametresprecis['addurl']."'><img src='".$iconadd."' border='0' alt='".$libhelp ."'></a>";
			$this->tab_ajoutcolonne($parametresprecis);
		}
		$this->btnajouter = true;
	}
	
	function tab_ajoutcolonne($parametresimpleouprecis)
	{		
		// AVANT ON AJOUTE LE CODE
		if ($this->nbrecolonnes==0) {
			if ($this->nomdutableau == 0) {
				$this->tab_skin_init();
				$this->tab_js_init();
			}
		}
		// Le paramètre est une chaine ou bien un tableau
		if ( is_array($parametresimpleouprecis) ) {
			// décodage des paramètres
			$this->parametragefin++;
			if ( !empty($parametresimpleouprecis['title']) ) {
				$this->cpttitre++;
			}
			array_push($this->htmlparamcol, $parametresimpleouprecis);
			if ( !empty($parametresimpleouprecis['url'])) {
				$this->taburl[$this->nbrecolonnes]  = $parametresimpleouprecis['url'];
				$this->cptcolurl++;
			} else {
				$this->taburl[$this->nbrecolonnes]  = "";
			}
			if ( !empty($parametresimpleouprecis['checkbox']) ) {
				$this->cptcheckbox++;
				$this->colcheckbox[$this->nbrecolonnes] = 0;
			} elseif ( !empty($parametresimpleouprecis['select']) ) {
				$this->cptselect++;
			} elseif ( !empty($parametresimpleouprecis['input']) ) {		
				$parametresimpleouprecis['input'] = strtoupper($parametresimpleouprecis['input']);
				$this->cptinput++;
				if ( !empty($parametresimpleouprecis['mask']) ) $this->cptinputmasks++;
				if ( !empty($parametresimpleouprecis['script']) ) $this->cptinputscript++;
				
			}
			if ( !empty($parametresimpleouprecis['width']) ) {
				$strlargeur = $parametresimpleouprecis['width'];
				// On efface les "PX" pour mieux les cumuler
				str_replace('px','',$strlargeur);
				$this->largeurtabcalcule+= (integer) $strlargeur;
			}
			if (!empty($parametresimpleouprecis['style'])) {
				$this->style_col[$this->nbrecolonnes] = $parametresimpleouprecis['style'];
			} else {
				$this->style_col[$this->nbrecolonnes] = '';
			}
			
		} else {
		    // dans le cas d'un seul parametre c'est automatiquement le titre
			$this->cpttitre++;
			array_push($this->htmlparamcol, array('title' => $parametresimpleouprecis) );
		}
		$this->nbrecolonnes++;
	}


	

	function tab_ouvrir($largeurtableau='')
	{
		if (!empty($largeurtableau)) {
			$this->largeurtab = $largeurtableau;
		} else {
			$this->largeurtab = (string) $this->largeurtabcalcule;
		}
		$this->pair_impair = 0;
		print "\n\n<!-- DEBUT DE TABLEAU GENERE PAR LA CLASSE Tableau (" . $this->nbrecolonnes . " colonnes, ".$this->cpttitre." titre(s) ) -->\n";
		if ($this->cptinputmasks>0 && !$this->masks_deja_sortis) {
				print "\n<!-- CODE JAVASCRIPT EXTERNE necessaire pour les masques -->\n";
				print "<script type=\"text/javascript\" src=\"".CHEMINRESSOURCES_CT."masks/masks.js\"></script>\n";
				$this->masks_deja_sortis = true;
		}
		// sortie effective du titre si defini
		if ( !empty($this->tab_titre) ) {
			print "\n<!-- Titre defini par la fonction tab_TitreInit() -->";
			print "\n<p>";			
			if ( !empty($this->tab_titre_icone) ) {
				print "<img src=\"".$this->tab_titre_icone."\" border=\"0\">&nbsp;";
			}
			print "<span class=\"".$this->tab_titre_class."\">".$this->tab_titre."</span>";			
			print "</p>";			
		}
		// SI AU MOINS UNE URL A ETE DEFINIE
		if ($this->cptcolurl>0) {
			print "\n<!-- NBRE D'URL DE BRANCHEMENT POUR CE TABLEAU = ". $this->cptcolurl . " -->";
			print "\n<script language=\"JavaScript\" type=\"text/JavaScript\">\n\n";

			// POUR TOUTES LES COLONNES QUI ONT UNE URL DEFINIE
			for ($i=0; $i<$this->nbrecolonnes; $i++) {
				if ( !empty($this->taburl[$i]) ) {
					print "\n<!-- FONCTION DE BRANCHEMENT DE LA COLONNE n°".$i." -->";
					print "\nfunction st".$this->nomdutableau."_".$i."_url(id) {";
					print "\n\tvar sortcol = '';";
					print "\n\tif (st_".$this->nomdutableau.".sortColumn!=null) sortcol='&SORT_COL='+st_".$this->nomdutableau.".sortColumn;";
					print "\n\tif (st_".$this->nomdutableau.".descending!=null) sortcol+='&SORT_DESC='+(st_".$this->nomdutableau.".descending ? '1' :'0');";
					print "\n\tlocation.href='".$this->taburl[$i]."'+id+sortcol+'&CALLEDBY=".$_SERVER['PHP_SELF']."';";
					print "\n\treturn false;";
					print "\n}";
				} 
			}
			print "\n//-->\n</script>\n";
		}
		// SI AU MOINS UN LISTE A ETE DEFINIE
		if ($this->cptselect>0) {
			print "\n<!-- NBRE DE LISTES POUR CE TABLEAU = ". $this->cptselect . " -->";
			print "\n<script language=\"JavaScript\" type=\"text/JavaScript\">\n<!--\n";
			// POUR TOUTES LES COLONNES QUI ONT UNE URL DEFINIE
			for ($i=0; $i<$this->nbrecolonnes; $i++) {
				// SI LA COLONNE CONTIENT UNE LISTE
				if ( !empty($this->htmlparamcol[$i]['select']) ) {
					$this->_tab_jsselect($i);			
				}
			}
			print "\n//-->\n</script>\n";
		}
		
		
		// SI AU MOINS UN CHAMP INPUT A ETE DEFINI
		if ($this->cptinput>0) {
			print "\n<!-- NBRE DE CHAMPS \"INPUT\" POUR CE TABLEAU = ". $this->cptinput . " -->";
			print "\n<script language=\"JavaScript\" type=\"text/JavaScript\">\n<!--\n";

			print "\tvar InputFieldsList = new Array();\n";
			// POUR TOUTES LES COLONNES 
			for ($i=0; $i<$this->nbrecolonnes; $i++) {
				// SI LA COLONNE CONTIENT UN CHAMP "Input"
				if ( !empty($this->htmlparamcol[$i]['input']) ) {
					print "\n";
					$nomobjet = strtoupper($this->htmlparamcol[$i]['input']);
					if ( !empty($InputFieldsList) ) $InputFieldsList .= ","; 
					$masque = '';
					// SI LE CHAMP INPUT CONTIENT UN MASQUE
					if ( !empty($this->htmlparamcol[$i]['mask']) ) {
						$masque = $this->htmlparamcol[$i]['mask'];
						$typemasque = "";
						if (!empty($this->htmlparamcol[$i]['attrib'])) 
							$attrib = strtoupper($this->htmlparamcol[$i]['attrib']);
							switch ($attrib) {
								case 'N':
									$typemasque = 'number';
									$nombrepositif = ',false';
									$aligner = 'R';
									break;
								case 'P':
									$typemasque = 'number';
									$nombrepositif = ',true';
									$aligner = 'R';
									break;
								case 'D':
									$typemasque = 'date';
									$nombrepositif = '';
									$aligner = 'L';
									break;
								default:
									$typemasque = 'string';
									$nombrepositif = '';
									$aligner = 'L';
									break;
									
							}
							print "\tMsk".$nomobjet." = new Mask(\"".$masque."\",\"".$typemasque."\"".$nombrepositif.");\n";
							print "\tInputFieldsList[InputFieldsList.length] = { _name:\"".$nomobjet."\", _masque:\"".$masque."\" };\n";

					}

					if ( !empty($this->htmlparamcol[$i]['width'])) {
						$style = "width:".$this->htmlparamcol[$i]['width'].";";
					} else {
						$style = "";
					}
					$nombre = strtoupper($this->htmlparamcol[$i]['attrib']);
					if ($nombre=='N' || $nombre=='P') {
						$style .= "text-align: right;";
					}
					// CREATION DE L'OBJET JAVASCRIPT
					$avecmasque = ( !empty($masque) ) ? 'true' : 'false';
					print "\to".$nomobjet." = new InputText('".$nomobjet."','".$style."',".$avecmasque.");\n";


					// SI UN TIMEOUT A ETE DEFINI IL FAUT LE NEUTRALISER
					if ( !empty($this->timeout_fctstop)) {		
						print "\to".$nomobjet.".IT_onChange('".$this->timeout_fctstop."');\n";
					}	
					// AJOUT DU SCRIPT SI IL EXISTE
					if ( !empty($this->htmlparamcol[$i]['script'])) {		
						print "\to".$nomobjet.".IT_script('".str_replace('()','',$this->htmlparamcol[$i]['script'])."');\n";
					}	
					// SI LECTURE SEULE
					if ( isset($this->htmlparamcol[$i]['readonly'])) {		
						print "\to".$nomobjet.".IT_readOnly();\n";
					}	
					print "\to".$nomobjet.".imgdirectory = '".CHEMINRESSOURCES_CT."img/';\n";
					
				}
			}
			print "\n//-->\n</script>\n";
		}
		
		
		
		
		if (!$this->formulaireouvert && ($this->cptcheckbox>0 || $this->cptselect>0)) {
			print "\n<!-- Le formulaire ci-dessous est motive par la présence de CHECKBOX ou de LISTES -->";
			print "\n<form name=\"classeTableauForm\" id=\"classeTableauForm\" method=\"post\" action=\"\">\n";
			$this->formulaireouvert = true;
		}

		// AFFICHE UNE CASE TITRE SI IL EXISTE
		if (!empty($this->titredutableau)) {
			print '<table class="sort-table" style="border:	1px solid; border-color: ButtonHighlight ButtonShadow ButtonShadow ButtonHighlight; height: 20px" cellspacing="0" width="'.$this->largeurtab."\">\n";
			print "<thead><tr><th scope=\"row\"><div align=\"center\">".$this->titredutableau."</div></th></tr></thead> \n";
			print "</table>\n";
		}
		// LIGNE DES TITRES DE COLONNES
	   $this->tab_style_init();
	   print '<table class="sort-table" id="'.$this->nomdutableau.'" cellspacing="0" width="'.$this->largeurtab."\">\n";

	   // ALIGNEMENT DES COLONNES	   
	   for ($i=0; $i<$this->nbrecolonnes; $i++) {
	   		print "\t<col";
			$style_align = "";
			$style_width = "";
			if (!empty($this->htmlparamcol[$i]['align'])) $style_align= $this->htmlparamcol[$i]['align'];
			if (!empty($this->htmlparamcol[$i]['width'])) $style_width= $this->htmlparamcol[$i]['width'];
			// ouverture du style
			if ( !empty($style_align) || !empty($style_width) ) print " style=\"";
			if (!empty($style_align)) print "text-align: ".$style_align.";";
			if (!empty($style_width)) print " width: ".$style_width.";";
			// fermeture du style
			if ( !empty($style_align) || !empty($style_width) ) print '"';
			print " />\n";
	   }
	   // SI IL EXISTE AU MOINS UN TITRE ALORS ON PLACE LES ENTETES DE COLONNE
	   if ($this->cpttitre > 0) {
		   print "\t<thead> \n";
		   print "\t\t<tr valign=\"baseline\"> \n";
		   // TITRE DES COLONNES (OBLIGATOIRE POUR TRI)
		   for ($i=0; $i<$this->nbrecolonnes; $i++) {
			   $modedetri = "";
		   		if (!empty($this->htmlparamcol[$i]['sort']))
					$modedetri = strtoupper($this->htmlparamcol[$i]['sort']);
				switch ($modedetri) {
					case "STRING" :
					case "CASEINSENSITIVESTRING" :
				    case "DATE" :
				    case "NUMBER" :
					   		$gras_avant = "<b>";
					   		$gras_apres = "</b>";
							break;
					default :
					   		$gras_avant = "";
				   			$gras_apres = "";
							break;
				}
	   		
		   		print "\t\t\t<td>";
				
				// SI UNE COCHE "TOGGLE ALL" EST DEFINIE ALORS
				if ( !empty($this->htmlparamcol[$i]['toggle']) ) {
					if (strtolower($this->htmlparamcol[$i]['toggle'])=='true') {
						$nomtog = "c".$this->htmlparamcol[$i]['checkbox']."_";
						print "<input id=\"". $nomtog ."\" type=\"checkbox\" onClick=\"".$this->timeout_fctstop."oGC.ReadyToValid();st_".$this->nomdutableau.".toggleCheckBox('".$nomtog."')\" />&nbsp;";

					}
				}

				// SI "SELECTALL" EST DEFINIE ALORS
				if ( !empty($this->htmlparamcol[$i]['selectall']) ) {
					$nomselect = $this->htmlparamcol[$i]['select'];
					if (empty($this->htmlparamcol[$i]['title'])) 
						$titreselect = '';
					else
						$titreselect = $this->htmlparamcol[$i]['title'];
					print " <script> o".$nomselect.".writeSelectAll('".$titreselect."','background: ".$this->couleurchampobligatoire."','st_".$this->nomdutableau.".selectAll(\'".$nomselect."\')'";
					if ( strtolower($this->htmlparamcol[$i]['selectall'])=='true' ) {
						$msgconfirm = (!empty($this->htmlparamcol[$i]['selectconfirmmsg'])) ? $this->htmlparamcol[$i]['selectconfirmmsg'] : 'Vous allez modifier toute cette colonne ?';
						print ",'".str_replace("'","%27",$msgconfirm)."'";
					}
					print "); </script> ";
				} else {
					// SINON ET SI IL EXISTE, LE TITRE DE LA COLONNE
					if (empty($this->htmlparamcol[$i]['title'])) 
						print "&nbsp";
					else
						print $gras_avant . $this->htmlparamcol[$i]['title'] . $gras_apres;
				}
		   		print "</td> \n";
		   }	   
		   print "\t\t</tr> \n";
		   print "\t</thead> \n";
		}

	   print "\t<tbody> \n";
	   print "\n\t<!-- FIN DE DEFINITION DE LA LIGNE TITRE DE COLONNE -->\n";
       $this->colencours = 0;
	}


	function tab_recalc() {
		// SI AU MOINS UN CHAMP INPUT A ETE DEFINI
		if ($this->cptinputscript>0) {
			print "\n<!-- RECALCUL DE TOUS LES CHAMPS -->";
			print "\n<script language=\"JavaScript\" type=\"text/JavaScript\">\n<!--\n";
			print "\tfunction RecalculateAllFields() {\n";

			// POUR TOUTES LES COLONNES 
			for ($i=0; $i<$this->nbrecolonnes; $i++) {
				// SI LA COLONNE CONTIENT UN CHAMP "Input"
				if ( !empty($this->htmlparamcol[$i]['input']) ) {
					if ( !empty($this->htmlparamcol[$i]['script'])) {
						print "\t\to".$this->htmlparamcol[$i]['input'].".IT_recalc();\n";
					}	
				}
			}
			print "\t}\n\tRecalculateAllFields();\n";
			print "\n//-->\n</script>\n";
		}		
	}


	function tab_trier($colonneatrier=-1, $descending=false)
	{
		$this->defaultsort = $colonneatrier;
		$this->descending  = $descending;		
	}


	function tab_select($valeur,$id='')
	{
		$selectname = $this->htmlparamcol[$this->colencours]['select'];
		return '<script> o'.$selectname.'.writeSelect('.$valeur.','.$id.'); </script>';	
	}


	function tab_input($valeur,$id='')
	{		
		$inputname = $this->htmlparamcol[$this->colencours]['input'];
		return "<script> o".$inputname.".IT_write('".$valeur."','".$id."'); </script>";	
	}

	function tab_input_incdec($valeur,$id='',$readonly=false)
	{		
		$inputname = $this->htmlparamcol[$this->colencours]['input'];
		return "<script> o".$inputname.".IT_write_incdec('".$valeur."','".$id."'); </script>";	
	}

	
	function tab_sautligne()
	{
	   $this->pair_impair++;
	   print "\n\n\t<!-- saut de ligne genere par la classe Tableau ->tab_sautligne() -->";
	   if (($this->pair_impair % 2) == 0) {
	       print "\n\t<tr class=\"even\">";
	   } else {
	 	   print "\n\t<tr class=\"odd\">";
	   }
	   for ($i=0; $i<$this->nbrecolonnes; $i++) {
	        print "\n\t\t<td>&nbsp;</td>";
	   }
	   print "\n\t</tr>\n";
	}

	function tab_nbrelignes()
	{
	   return $this->pair_impair;
	}

	function tab_alleralaligne()
	{
	   print "\n\n\t<!-- saut de ligne genere par la classe Tableau ->tab_alleralaligne() -->";
	   for ($i=$this->colencours; $i<$this->nbrecolonnes; $i++) {
	        print "\n\t\t<td>&nbsp;</td>";
	   }
	   print "\n\t</tr>\n";
	   $this->colencours = 0;
	}


	function tab_remplircellule($valeur="",$idpoururl="",$attrib=array())	
	{
		$couleur_avant   = "";
		$couleur_apres   = "";
		$couleurbg       = "";
		$colspanlib      = "";
		$colspanval      = 1;
		$tooltips_avant  = "";
		$tooltips_apres  = "";
		if ( count($attrib)>0 ) {
			if ( !empty($attrib['color']) ) {
				$couleur_avant = '<span style="color: '.$attrib['color'].'">';
				$couleur_apres = '</span>';
			}
			if ( !empty($attrib['bgcolor']) ) {
				$couleurbg = ' bgcolor="'.$attrib['bgcolor'].'"';
			}
			if ( !empty($attrib['colspan']) ) {
				$colspanval = (integer) $attrib['colspan'];
				$colspanlib = ' colspan="'.$attrib['colspan'].'"';
			}
			if ( !empty($attrib['tooltipsheader']) ) {
				$tooltipsheader = $this->tab_formatertooltips($attrib['tooltipsheader']);
				$this->tooltips_cpt++;
			}
			if ( !empty($attrib['tooltipsbody']) ) {
				$tooltipsbody = $this->tab_formatertooltips($attrib['tooltipsbody']);
				$this->tooltips_cpt++;
			}
			if ( !empty($tooltipsheader) || !empty($tooltipsbody) ) {
				if ( !empty($attrib['tooltipsheaderstyle']) ) {
					$tooltipsheaderstyle = $attrib['tooltipsheaderstyle'];
				} else {
					$tooltipsheaderstyle = $this->tooltips_cssheader;
				}
				if ( !empty($attrib['tooltipsbodystyle']) ) {
					$tooltipsbodystyle = $attrib['tooltipsbodystyle'];
				} else {
					$tooltipsbodystyle = $this->tooltips_cssbody;
				}
				$tooltips_avant  = '<div title="cssheader=['.$tooltipsheaderstyle.'] cssbody=['.$tooltipsbodystyle.'] header=['.$tooltipsheader.'] body=['.$tooltipsbody.'] hideselects=[on]">';
				$tooltips_apres  = '</div>';
			}
			
		}
		# QUAND ON CHANGE DE LIGNE
	
		if ( $this->colencours == 0 ) {
	    	$this->ligneencours++;
			$this->pair_impair++;
			# SI CE N'EST PAS LA 1ERE LIGNE ON FINIT LA PRECEDENTE
			if ($this->pair_impair > 1) {
	   	    	print "\n\t</tr>";
			}
		   
			if (($this->pair_impair % 2) == 0) {
				print "\n\t<tr class=\"even$this->nomdutableau \"";
			} else {
				print "\n\t<tr class=\"odd$this->nomdutableau \"";
			}
			print ">";

	   } 
	   # IMPRESSION DE LA CELLULE
		print "\n\t\t<td".$couleurbg.$colspanlib.$this->class_col[$this->colencours].">".$couleur_avant.$tooltips_avant;
		
		// EDITION PARTICULIERE DES CHECKBOX
		if ( !empty($this->htmlparamcol[$this->colencours]['checkbox']) ) {
			print "<input id=\"c". $this->htmlparamcol[$this->colencours]['checkbox'] ."_".$idpoururl."\" type=\"checkbox\" ";
			// SI LA VALEUR EN PARAMETRE CORRESPOND A LA VALEUR ValueCheck
			if ($this->htmlparamcol[$this->colencours]['valuecheck'] == $valeur) {
				$this->colcheckbox[$this->colencours]++;
				print "checked=\"checked\"";
			}
			print " onClick=\"".$this->timeout_fctstop."oGC.ReadyToValid()\" />";
		} else {   
		   if ( empty($idpoururl) ) {
		      if (empty($valeur)) print "&nbsp;"; else print $valeur;	   
		   } else {
		   	  if ( !empty($this->taburl[$this->colencours]) ) {
				print "<a href='' onClick=\"return st".$this->nomdutableau."_".$this->colencours ."_url('".$idpoururl."');\">".$valeur."</a>";
			  }	else {
			    if (empty($valeur)) print "&nbsp;"; else print $valeur;	   
			  }	  	
		   }
		}
		print $tooltips_apres.$couleur_apres."</td>";
		$this->celluleencours+=$colspanval;
		$this->colencours+=$colspanval;
		if ( $this->colencours >= $this->nbrecolonnes ) {
			$this->colencours = 0;
		}
	}


	function tab_fermer($libelleducompteur="")	
	{
	   print "\n\t</tr>";
	   print "\n\t</tbody> \n";
	   // SI LE LIBELLE EST RENSEIGNE	   
	   if ( !empty($libelleducompteur) || ($this->btnajouter) ) {
			print "\t<thead>\n";
			print "\t\t<tr> \n";
			if ($this->btnajouter) {
				print "\t\t<td align=\"left\">".$this->htmlparamcol[0]['title'];
				print "</td> \n";
				$ncol = $this->nbrecolonnes-1;
			} else {
				$ncol = $this->nbrecolonnes;
			}
			print "\t\t<td align=\"left\" colspan=\"" . $ncol ."\">";
			print "&nbsp;".$libelleducompteur;
			print "</td> \n\t\t</tr> \n";
			print "\t</thead> \n";
		}

		print "\n</table>";
		print "\n<!-- FIN DE TABLEAU GENERE PAR LA CLASSE Tableau -->\n";

		if ($this->cptcheckbox>0) {
			print "\n<!-- CHAMP CACHE POUR SAUVEGARDER LES ID DES COCHES ET UN AUTRE LES NON-COCHES -->";
			for ($i=0; $i<$this->nbrecolonnes; $i++) {
				if (!empty($this->htmlparamcol[$i]['checkbox'])) {
					print "\n";
					print '<input name="'.$this->htmlparamcol[$i]['checkbox'].'" type="hidden" id="'.$this->htmlparamcol[$i]['checkbox'].'" value="" />';
					print "\n";
					print '<input name="NOT_'.$this->htmlparamcol[$i]['checkbox'].'" type="hidden" id="NOT_'.$this->htmlparamcol[$i]['checkbox'].'" value="" />';		
					print "\n";
				}
			}
			print "\n";
			print "\n<!-- POUR LES COLONNES DE CHECKBOX  ENTIEREMENT COCHEES SI LA COCHE \"toggle\" EXISTE ELLE EST AUSSI COCHEE -->";
			print "\n<script>\n\tfunction InitToggleCheckBox() {";
			for ($i=0; $i<$this->nbrecolonnes; $i++) {
				if (!empty($this->htmlparamcol[$i]['checkbox'])) {
					if ( !empty($this->htmlparamcol[$i]['toggle']) ) {
						if (strtolower($this->htmlparamcol[$i]['toggle'])=='true') {
							if ( $this->colcheckbox[$i]==$this->pair_impair ) {
								print "\n\tvar obj = st_MM_findObj('c".$this->htmlparamcol[$i]['checkbox']."_'); if (obj) obj.checked = true;";
								$this->InitToggleCheckBox++;
							}
						}
					}
				}
			}
			print "\n\t}\n\tInitToggleCheckBox();\n</script>\n\n";
		}
	
		if ($this->cpttitre > 0) {
			$this->tab_js_apresdescription();
		}
		// POUR TOUTES LES COLONNES DE CHECKBOX OU DE SAISIE INPUT ON MEMORISE LA SEQUENSE JS
		for ($i=0; $i<$this->nbrecolonnes; $i++) {
			if (!empty($this->htmlparamcol[$i]['checkbox'])) {
				$nomchk = $this->htmlparamcol[$i]['checkbox'];
				$this->sequenceJS .= "st_".$this->nomdutableau.".getAllCheckBox('c".$nomchk."_','".$nomchk."');";
			} elseif (!empty($this->htmlparamcol[$i]['input'])) {
				// SI DES MASQUES ONT ETE POSITIONNES ALORS AVANT LE SUBMIT ON REFORMATE LES VALEURS (ELIMINATION DES BLANCS PAR EXEMPLE)
				if ( !empty($this->htmlparamcol[$i]['mask']) && !isset($this->htmlparamcol[$i]['readonly']) ) {
					$this->sequenceJS .='o'.$this->htmlparamcol[$i]['input'].'.IT_ready2submit();';
				}
			}
		}
		if ($this->tooltips_cpt>0) {
			$this->tab_js_tooltips();
		}
		

	}


	function tab_boutons($message_gauche='')	{
		if ($this->formulaireouvert) {
			$this->tab_afficherbtns($message_gauche);
			print "\n\n</form>\n\n";
			$this->formulaireouvert = false;
		}
	}

	function tab_quitter($urlquitter='',$libquitter='') {
		$this->btnquitter = true;
		$this->urlquitter = $urlquitter;
		if ( !empty($libquitter) ) $this->libbtnQuitter = $libquitter;
	}

	function tab_LibBoutons($libbtnValider='',$libbtnQuitter='',$libbtnAnnuler='') {
		if (!empty($libbtnValider)) $this->libbtnValider = $libbtnValider;
		if (!empty($libbtnAnnuler)) $this->libbtnAnnuler = $libbtnAnnuler;
		if (!empty($libbtnQuitter)) $this->libbtnQuitter = $libbtnQuitter;
	}

	function tab_ActiverBtnValider() {
		$this->ActiverBtnValider = true;
	}


# -----------------------------------------------------------------------------------------------



		function tab_afficherbtns($message='')
		{
			print "\n<!-- AFFICHAGE DU CHAMP CACHE QUI MEMORISE L'ACTION DE VALIDATION -->\n";
			print "\n<input type=\"hidden\" name=\"COMMIT\" disabled=\"true\" value=\"YES\">";
			print "\n<input type=\"hidden\" name=\"CANCEL\" disabled=\"true\" value=\"YES\">";

			print "\n<!-- AFFICHAGE DES BOUTONS -->\n";
			print '<table width="'.$this->largeurtab.'" border="0" cellpadding="0"><tr>';
			print '<td align="left">';					
			print $message;
			print '<td>';
			print '<td width="200" align="right" nowrap>';		

			print "\n<input type=\"submit\" class=\"classetableaubouton\" id=\"BTN_COMMIT\" style=\"width:80px\" value=\"".$this->libbtnValider."\" ";
			print "onClick=\"".$this->sequenceJS."oGC.Validation()\">\n\n";

			print "\n<input type=\"button\" class=\"classetableaubouton\" id=\"BTN_CANCEL\" style=\"width:80px\" ";
			print 'value="'.$this->libbtnQuitter.'"';		
			print ' onclick="'.$this->sequenceJS.'oGC.ResetOrQuit()';
			if ($this->InitToggleCheckBox>0) print ";InitToggleCheckBox()";
			if ($this->cptinputscript>0) print ";RecalculateAllFields();oGC.FindField();";
			print '" >';
			print "\n\t<script>";
			if ($this->btnquitter) $libbtn='true'; else $libbtn='false';
			$urlquit = ( !empty($this->urlquitter) ) ? "'".$this->urlquitter."'" : "''";
			$libtruefalse = ($this->ActiverBtnValider) ? "true" : "false";
			
			print "\n\t\tvar oGC = new CommandGroup('classeTableauForm',".$libbtn.",".$urlquit.",".$libtruefalse.");";
			// CHANGEMENT DE L'INTITULE DES BOUTONS
			print "\n\t\toGC.libbtnValider='".$this->libbtnValider."';";
			print "\n\t\toGC.libbtnRetablir='".$this->libbtnAnnuler."';";
			print "\n\t\toGC.libbtnQuitter='".$this->libbtnQuitter."';";
			print "\n\t\toGC.InitButtons();";
			print "\n\t\toGC.FindField();";
			
 
			print "\n\t</script>\n";
			print '</td></tr></table>';
		}

		function tab_js_tooltips() 
		{
			if ($this->tooltips_js) return;
			$this->tooltips_js = true;
			print "\n<!-- CODE JAVASCRIPT necessaire a l'affichage des bulles -->\n";
			print "<script type=\"text/javascript\" src=\"".CHEMINRESSOURCES_CT."boxover/boxover.js\"></script>\n";
		}
		
		function tab_js_apresdescription() 
		{
				print "\n<!-- CODE JAVASCRIPT necessaire pour le tri dynamique du tableau ci-dessus -->\n";
				print "<script type=\"text/javascript\"> \n";
				print "<!-- \n";
				print "var st_".$this->nomdutableau." = new SortableTable(document.getElementById(\"".$this->nomdutableau."\"), \n";
				print "	[";
			    for ($i=0; $i<$this->nbrecolonnes; $i++) {
					if ($i>0)  print ",";
					$modedetri = "";
					if (!empty($this->htmlparamcol[$i]['sort'])) $modedetri = strtoupper($this->htmlparamcol[$i]['sort']);
					switch ($modedetri) {
						case "STRING" :
							$modedetri = "String";
							break;
						case "CASEINSENSITIVESTRING" :
							$modedetri = "CaseInsensitiveString";
							break;
						case "DATE" :
							$modedetri = "Date";
							break;
						case "NUMBER" :
							$modedetri = "Number";
							break;
						default :
							$modedetri = "None";

					}
					print "\"". $modedetri . "\"";
				}
				print "],'".CHEMINRESSOURCES_CT."img/'); \n";
				print "	 \n";
				print "st_".$this->nomdutableau.".onsort = function () { \n";
				print "	var rows = st_".$this->nomdutableau.".tBody.rows; \n";
				print "	var l = rows.length; \n";
				print "	for (var i = 0; i < l; i++) { \n";
				print "		removeClassName(rows[i], i % 2 ? \"odd".$this->nomdutableau."\" : \"even".$this->nomdutableau."\"); \n";
				print "		addClassName(rows[i], i % 2 ? \"even".$this->nomdutableau."\" : \"odd".$this->nomdutableau."\"); \n";
				print "	} \n";
				print "}; \n";

				if ($this->cptcheckbox>0) {
					$this->_tab_triercheckbox();
				}

				// SI LE TABLEAU EST A TRIER				
				if ($this->defaultsort>=0) {
					print "st_".$this->nomdutableau.".sort(".$this->defaultsort;
					if ($this->descending) print ",true";
					print "); \n";
				}
				print " \n";
				print "--> \n";
				print "</script> \n";
		}

		function tab_js_init() 
		{	
				if ($this->codeenvoye) return;
				print "\n<!-- CODE JAVASCRIPT EXTERNE necessaire pour les Tableaux -->\n";
				print "<script type=\"text/javascript\">\n<!--\n\t var SortableTablePath = '".CHEMINRESSOURCES_CT."';";
				print "\n-->\n</script>\n";
				print "<script type=\"text/javascript\" src=\"".CHEMINRESSOURCES_CT."sortabletable.js\"></script>\n";
				print "<script type=\"text/javascript\" src=\"".CHEMINRESSOURCES_CT."sortabletable_ext.js\"></script>\n";
				// DEFINITION DU TIMEOUT SI EXISTANT
				if ($this->timeout_tempo>0)	{
					$this->js_timeout();
				}
				print "<link href=\"".CHEMINRESSOURCES_CT."sortabletable.css\" rel=\"stylesheet\" type=\"text/css\">\n";

				print "<style type=\"text/css\"> \n";

				print ".sort-table { \n";
				print "	font:		Icon; \n";
				print "	border:		1px Solid ".$this->couleurchampobligatoire."; \n";
				print "	background:	".$this->couleurchampnormal."; \n";
				print "	color:		WindowText; \n";
				print "} \n";

				print ".sort-table thead { \n";
				print "	background:	".$this->couleurchampobligatoire."; \n";			
				print "} \n";

				print ".sort-arrow.descending { \n";
				print "	background-image:		url(\"".CHEMINRESSOURCES_CT."img/downsimple.png\"); \n";
				print "} \n";

				print ".sort-arrow.ascending { \n";
				print "	background-image:		url(\"".CHEMINRESSOURCES_CT."img/upsimple.png\"); \n";
				print "} \n";


				print ".sort-table a:link { \n";
				print "	font: Icon;  \n";
				print "	color: ".$this->couleurtitre."; \n";
				print "	text-decoration: none; \n";
				print "} \n";
				
				print ".sort-table a { \n";
				print "	font: Icon;  \n";
				print "	font-size: 12px;  \n";
				print "	color: ".$this->couleurtitre."; \n";
				print "	text-decoration: none; \n";
				print "} \n";

				print ".sort-table  td, th { \n";
				print "	line-height: normal;  \n";
				print "} \n";

				print ".sort-table a:visited { \n";
				print "	font: Icon;  \n";
				print "	font-size: 12px;  \n";
				print "	color: ".$this->couleurtitre."; \n";
				print "	text-decoration: none; \n";
				print "} \n";
				
				print ".sort-table a:hover { \n";
				print "	font: Icon;  \n";
				print "	font-size: 12px;  \n";
				print " text-decoration: underline; \n";
				print "} \n";
				
				print ".classetableaubouton { \n";
				print "	font-family: Verdana, Arial, Helvetica, sans-serif;  \n";
				print "	font-size: 10px;  \n";
				print "	cursor:hand;  \n";
				print "} \n";

				print ".classetableautitre { \n";
				print "	font-family: Verdana, Arial, Helvetica, sans-serif;  \n";
				print "	font-size: 18px;  \n";
				print "	font-style: normal;  \n";
				print "	font-weight: bold;  \n";
				print "	color: ".$this->couleurtitre.";  \n";
				print "} \n";

				print ".classeselecteur { \n";
				print "	font-family: Verdana, Arial, Helvetica, sans-serif;  \n";
				print "	font-size: 9px;  \n";
				print "} \n";

				print ".classeinput { \n";
				print "	font-family: Verdana, Arial, Helvetica, sans-serif;  \n";
				print "	font-size: 9px;  \n";
				print "	background:	".$this->couleurchampnormal."; \n";
				print "} \n";

				print ".classeoutput { \n";
				print "	font-family: Verdana, Arial, Helvetica, sans-serif;  \n";
				print "	font-size: 9px;  \n";
				print "	background-color:	#FFFFFE; \n";
				print "} \n";

				print ".classedelete { \n";
				print "	font-family: Verdana, Arial, Helvetica, sans-serif;  \n";
				print "	font-size: 9px;  \n";
				print "	color: white; \n";
				print "	background-color:	".$this->couleurtitre."; \n";
				print "} \n";


				print ".tooltipsh { \n";
				print "	background:".$this->couleurchampnormal.";\n";
				print "	font-family: Verdana, Arial, Helvetica, sans-serif;\n";
				print "	font-size:10px;\n";
				print "	font-weight:bold;\n";
				print "	border:1px solid ".$this->couleurchampobligatoire.";\n";
				print "	padding:5px;\n";
				print "	width:".$this->tooltips_width.";\n";
				print "	filter:alpha(opacity=85);\n";
				print "	opacity:0.".$this->tooltips_opacity.";\n";
				print "} \n";
         
				print ".tooltipsb { \n";
				print "	background:#FFFFFF;\n";
				print "	font-family: Verdana, Arial, Helvetica, sans-serif;\n";
				print "	font-size:10px;\n";
				print "	border-left:1px solid ".$this->couleurchampobligatoire.";\n";
				print "	border-right:1px solid ".$this->couleurchampobligatoire.";\n";
				print "	border-bottom:1px solid ".$this->couleurchampobligatoire.";\n";
				print "	padding:5px;\n";
				print "	width:".$this->tooltips_width.";\n";
				print "} \n";

				print "</style> \n";
				$this->codeenvoye = true;
		}


		function tab_style_init() 
		{	
			print "\n<!-- STYLE SPECIFIQUE DU TABLEAU : ".$this->nomdutableau." -->\n";
			print "<style type=\"text/css\"> \n";

			print "/* extra rules for even and odd rows */ \n";
			print ".even$this->nomdutableau { \n";
			print "	background:	".$this->couleurchampnormal."; \n";
			if (!empty($this->style)) {
				print "	".$this->style."; \n";
			}
			print "} \n";

			print ".odd$this->nomdutableau { \n";
			print "	background:	#FFFFFF; \n";
			if (!empty($this->style)) {
				print "	".$this->style."; \n";
			}
			print "} \n";

			for ($i=0; $i<$this->nbrecolonnes; $i++) {
				if (!empty($this->style_col[$i])) {
					$nomclasse = 'CT'.$this->nomdutableau.'_C'.$i;
					$this->class_col[$i] = ' class="'.$nomclasse.'"';
					print '.'.$nomclasse." { ".$this->style_col[$i] . " }\n";
				}
			}
			print "</style>\n\n";
		}


		// GENERATION DU CODE JS QUI GENERE LA LISTE 'SELECT'
		function _tab_jsselect($i) {
			$nameselect = $this->htmlparamcol[$i]['select'];
				
			print "\nvar o".$nameselect." = new initSelect('".$nameselect."', [ ";
			$affichernull = false;
			$cptlignes = 0;
			if ( !empty($this->htmlparamcol[$i]['defaultvalue']) ) {
				$affichernull = (strtolower($this->htmlparamcol[$i]['defaultvalue'])=='true');
			}
			// AFFICHER UNE VALEUR NULLE			
			if ($affichernull) {
				print "{value:\"\",text:\"&#8212;\"}";
				$cptlignes++;
			}

			foreach ($this->htmlparamcol[$i]['data'] as $valeur => $libelle) {
				if ($cptlignes>0) print ",";
				print " {value:\"".$valeur."\",text:\"".str_replace('"','\\"',$libelle)."\"}";
				$cptlignes++;
			}
			print " ] );";
			
			// SI UN TIMEOUT A ETE POSITIONNE
			if (!empty($this->timeout_fctstop)) { 
				print "\no".$nameselect.".onChangeSelect('".$this->timeout_fctstop."');";
			}

		}

		function _tab_triercheckbox() {

				print "// restore the class names \n";
				print "st_".$this->nomdutableau.".onsort = function () { \n";
				print "	var rows = st_".$this->nomdutableau.".tBody.rows; \n";
				print "	if (/MSIE/.test(navigator.userAgent)) this.onsort_(); \n";
				print "	var l = rows.length; \n";
				print "	for (var i = 0; i < l; i++) { \n";
				print "		removeClassName(rows[i], i % 2 ? \"odd".$this->nomdutableau."\" : \"even".$this->nomdutableau."\"); \n";
				print "		addClassName(rows[i], i % 2 ? \"even".$this->nomdutableau."\" : \"odd".$this->nomdutableau."\"); \n";
				print "	} \n";
				print "}; \n";
				print " \n";
				
				print "function getCheckBoxValue (oRow, nColumn) { \n";
				print "	alert(oRow + ' / '+ nColumn); \n";
				print "	return oRow.cells[nColumn].firstChild.checked ? 1 : 0; \n";
				print "}; \n";
				
				print "// add new sort type and use the default compare \n";
				print "// also use custom getRowValue since the text content is not enough \n";
				print "st_".$this->nomdutableau.".addSortType(\"CheckBox\", null, null, getCheckBoxValue); \n";
				print " \n";
				print "if (/MSIE/.test(navigator.userAgent)) { \n";
				print "	st_".$this->nomdutableau.".onbeforesort = function () { \n";
				print "		var table = st_".$this->nomdutableau.".element; \n";
				print "		var inputs = table.getElementsByTagName(\"INPUT\"); \n";
				print "		var l = inputs.length; \n";
				print "		for (var i = 0; i < l; i++) { \n";
				print "			inputs[i].parentNode._checked = inputs[i].checked; \n";
				print "		} \n";
				print "	}; \n";
				print "	st_".$this->nomdutableau.".onsort_ = function () { \n";
				print "		var table = st_".$this->nomdutableau.".element; \n";
				print "		var inputs = table.getElementsByTagName(\"INPUT\"); \n";
				print "		var l = inputs.length; \n";
				print "		for (var i = 0; i < l; i++) { \n";
				print "			inputs[i].checked = inputs[i].parentNode._checked; \n";
				print "		} \n";
				print "	}; \n";
				print "} \n";
	}

	// INITIALISE UNE PALETTE PRE-DEFINIE
	function tab_skin_init() {
		// DU + CLAIR AU + FONCE
		switch ($this->numskin) {
			// PALETTE BLEUE
			case 1:
				$this->couleurchampnormal      = "#E8F3FD";
				$this->couleurchampobligatoire = "#C1DEF9";   
				$this->couleurchamperreur      = "#9CCCF8"; 
				$this->couleurtitre            = "#146DB6";
				break;

			// PALETTE GRISE
			case 2:
				$this->couleurchampnormal      = "#F4F3EA";
				$this->couleurchampobligatoire = "#E2DFC7";   
				$this->couleurchamperreur      = "#D7D2B0"; 
				$this->couleurtitre            = "#333333";
				break;
				
			// PALETTE JAUNE
			case 3:
				$this->couleurchampnormal      = "#FFFFCC";
				$this->couleurchampobligatoire = "#F7EAAE";   
				$this->couleurchamperreur      = "#F0DC7B"; 
				$this->couleurtitre            = "#7C690E";
				break;

			// PALETTE VERTE
			case 4:
				$this->couleurchampnormal      = "#EAFFE1";
				$this->couleurchampobligatoire = "#ACFF8C";   
				$this->couleurchamperreur      = "#66CC00"; 
				$this->couleurtitre            = "#009900";
				break;

			// PALETTE ORANGE
			case 5:
				$this->couleurchampnormal      = "#FFE1C4";
				$this->couleurchampobligatoire = "#FFD5AA";   
				$this->couleurchamperreur      = "#FFC58A"; 
				$this->couleurtitre            = "#400000";
				break;

			// PALETTE MAUVE
			case 6:
				$this->couleurchampnormal      = "#e7c3ff";
				$this->couleurchampobligatoire = "#dea9ff";   
				$this->couleurchamperreur      = "#d289ff"; 
				$this->couleurtitre            = "#070040";
				break;
												
			// PALETTE ROUGE PAR DEFAUT
			default:
				$this->couleurchampnormal      = "#FAF0ED";
				$this->couleurchampobligatoire = "#F5DED6";   
				$this->couleurchamperreur      = "#E9BDAD";   // UTILISE POUR LES CHAMPS EN ERREUR
				$this->couleurtitre            = "#9C0000";
		}
	}

	function tab_recuppost($nomselectachercher) {
		$tabret = array();
		$longclef = strlen($nomselectachercher);
		foreach ($_POST as $POSTclef => $POSTvaleur) {
			list($nomselect,$id) = explode("_",$POSTclef);
			if ( (strlen($nomselect)==$longclef) && ($nomselect==$nomselectachercher) ) {
				$tabret[$id] = $POSTvaleur;
			}
		}
		return $tabret;
	}

	function tab_formatertooltips($chaine) {
		$chaine_out = '';
		$chaine_out = str_replace('"','&#34;',$chaine);
		$chaine_out = str_replace('[','&#91;',$chaine_out);
		$chaine_out = str_replace(']','&#93;',$chaine_out);
		return $chaine_out;
	}

	function formulaireouvert() {
		return $this->formulaireouvert;
	}



	function js_timeout() {
		print "\n<!-- Definition du TIMEOUT -->";
		print "\n<script language=\"JavaScript\" type=\"text/javascript\" src=\"".CHEMINRESSOURCES_CT."timeout/timeout.js\"></script>";			

		print "\n<script language=\"JavaScript\" type=\"text/JavaScript\">";
		print "\n<!--";
		print "\n\t".$this->timeout_nomobj." = new AutoRedirection('".$this->timeout_url."',".$this->timeout_tempo.",'".$this->timeout_nomobj."');";
		if (!empty($this->timeout_idcounteur)) {
			print "\n\t".$this->timeout_nomobj.".AutoRedirection_idcounter('".$this->timeout_idcounteur."');";
		}
		print "\n\t".$this->timeout_nomobj.".AutoRedirection_init();";
		print "\n-->";
		print "\n</script>\n";
		$this->timeout_dejasorti = true;
	}

} // Fin de la classe TABLEAU


?>