
function validatename(thisform) {
	if (thisform.nom.value != '') {
		thisform.matricule.value=''
		thisform.club.value=''
		thisform.nom.value=thisform.nom.value.toUpperCase()
	}
	return true
}

function validatemat(thisform) {
	var returnval=false

	re=/^\d{0,5}$/
	if (!re.test(thisform.matricule.value)) {
		thisform.nom.value=''
		alert("matricule " + thisform.matricule.value + " non valable")
		thisform.matricule.value=''
		thisform.matricule.focus()
	} else {
		returnval=true
		if (thisform.matricule.value != 0 && thisform.matricule.value != '') {
			thisform.nom.value=''
			thisform.club.value=''
		}
	}
	return returnval
}
 
function validateclub(thisform) {
	var returnval=false
	re=/^\d{0,3}$/
	if (!re.test(thisform.club.value)) {
		thisform.nom.value=''
		alert("club " + thisform.club.value + " non valable")
		thisform.club.value=''
		thisform.club.focus()
	} else {
		returnval=true
		if (thisform.club.value != 0 && thisform.club.value != '') {
			thisform.nom.value=''
			thisform.matricule.value=''
		}
	}
	return returnval
} 
 
function validateform(theform) {
	validatename(theform)
	if (validatemat(theform)==false) {
		return false
	}
	if (theform.nom.value=='' && theform.matricule.value=='')  {
		alert ("Entrez un matricule OU un nom")
		return false
	}
	return true
}
