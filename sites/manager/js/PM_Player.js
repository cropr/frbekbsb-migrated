// Assignation d'un string
//------------------------
function js_FillString(nam, val) { 
document.forms.titulaire[nam].value = val;
}

// Assignation d'une date AAAA-MM-JJ en JJJJ-MM-AA
//------------------------------------------------
function js_FillDate(nam, val) {
	if (val.length > 4) {
		var aa = val.substring(0,4)
		var mm = val.substring(5,7);
		var jj = val.substring(8,10);
		var dat = jj+"/"+mm+"/"+aa;
	}
	else
		dat="";
	document.forms.titulaire[nam].value = dat;
}

// Assignation d'un Radio bouton
//------------------------------
function js_FillRadio(nam, val) {
document.forms.titulaire[nam][val].checked = true;
}

// Assignation d'un CheckBox
//------------------------------
function js_FillCheck(nam, val) {
document.forms.titulaire[nam].checked = -val;
}

// Assignation d'un groupe de Boxes
//---------------------------------
function js_FillBoxes(nam, i, val) {
document.forms.titulaire[nam][i].checked = -val;
}

function js_setBoxesEnable(name,status) {
	var radios = document.forms.titulaire[name];
	for (var i=0, iLen=radios.length; i<iLen; i++) {
 		 radios[i].disabled = !status;
	} 
}

// Assignation de la Nationalité
//------------------------------
function js_FillOption(nam,val) {
	for(i=0;i<document.forms.titulaire[nam].length;++i) {
  		if(document.forms.titulaire[nam].options[i].value == val) {
			document.forms.titulaire[nam].options[i].selected = true;
			return;
		}
	}
}
// Verification d'une date
//------------------------
function js_VerifDate(dat,lan) {
	var jj = dat.value.substring(0,2);
	var s1 = dat.value.substring(2,3);
	var mm = dat.value.substring(3,5);
	var s2 = dat.value.substring(5,6);
	var aa = dat.value.substring(6,10);
	var dt = new Date(aa,mm-1,jj);
	var dj = new Date();
	
	if (dat.value == "") {
		if (lan == "NL")
				alert("Datum ongeldig "+dat.value);
			else
				alert("Date non valable "+dat.value);
		return false;
	}
	
	if (dat.value == ""        ||
		s1 != "/" ||
	    s2 != "/" ||
	    dt.getDate()     != jj ||
	    dt.getFullYear() != aa ||
	    dt.getMonth()    != (mm-1)) {
	    	if (lan == "NL")
				alert("Datum ongeldig "+dat.value);
			else
				alert("Date non valable "+dat.value);
			dat.className = "inputer";
			document.forms.titulaire[dat].focus();
			return false;
	}

/* -----------------------------------------------	
 * Test de la validité d'une date de naissance 
 * année du jour <= annéé donnée
 * age doit être > 3  ans
 * age doit être < 80 ans
 *-------------------------------------------------
 */
 if ( dt.getFullYear() >= dj.getFullYear()) {
 	if (lan == "NL")
		alert("Jaar ongeldig "+dat.value);
	else
		alert("Année non valable "+dat.value);
	dat.className = "inputer";
	document.forms.titulaire[dat].focus();
	return false;
}


if ((dj.getFullYear() - dt.getFullYear()) < 3) {
	if (lan == "NL")
		alert("Jaar ongeldig "+dat.value);
	else
		alert("Année non valable, Age < 3 ans "+dat.value);
	dat.className = "inputer";
	document.forms.titulaire[dat].focus();
	return false
}
 
 if ((dj.getFullYear() - dt.getFullYear()) > 100) {
	if (lan == "NL")
		alert("Jaar ongeldig "+dat.value);
	else
		alert("Année non valable, Age > 100 ans "+dat.value);
	dat.className = "inputer";
	document.forms.titulaire[dat].focus();
	return false
}
 
	dat.className = "inputup";
	return true;
}


function js_VerifEmail(mail) {  
if (mail.value == "") {
	mail.className = "inputup";
	return true;
}
if (mail.value.indexOf("@") != "-1" &&
    mail.value.indexOf(".") != "-1" &&
    mail.value != "") {
    	mail.className = "inputup";
    	return true;
    }

	alert("Email "+mail.value+" non valable\n");
	mail.className = "inputer";
	return false;

}

function js_setError(field) {
	document.forms.titulaire[field].className = "inputer";
	document.forms.titulaire[field].focus();
}

function js_resetError(field) {
	document.forms.titulaire[field].className = "inputup";
}

function js_setReading(field) {
	alert("inputer field=");
	document.forms.titulaire[field].className="inputer";
}
function js_setColor(field) {
	document.forms.titulaire[field].className="inputcol";
}
// Encription d'une adresse émail
//-------------------------------
function decrypt(mail,text)
{
 	coded = mail
	cipher = "aZbYcXdWeVfUgThSiRjQkPlOmNnMoLpKqJrIsHtGuFvEwDxCyBzA1234567890"
	shift=coded.length
	link=""
	for (i=0; i<coded.length; i++){
		if (cipher.indexOf(coded.charAt(i))==-1){
			ltr=coded.charAt(i)
			link+=(ltr)
		}
		else {     
			ltr = (cipher.indexOf(coded.charAt(i))-shift+cipher.length) % cipher.length
			link+=(cipher.charAt(ltr))
		}				
	}
	if (text == "")
	document.write("<a href='mailto:"+link+"'>"+link+"</a>")
	else
	document.write("<a href='mailto:"+link+"'>"+text+"</a>")
}

