function displayTotalNrOfTeams()
{
	var nr_d1 = parseInt(document.getElementById('nr_d1').value);
	var nr_d2 = parseInt(document.getElementById('nr_d2').value);
	var nr_d3 = parseInt(document.getElementById('nr_d3').value);
	var nr_jeunes = parseInt(document.getElementById('nr_jeunes').value);
	var total = nr_d1 + nr_d2 + nr_d3 + nr_jeunes;
	
	document.getElementById('nr_total').innerHTML = total;
}

function setChanged(club_id)
{
	var rowName = 'row' + club_id;
	var row = document.getElementById(rowName);
	var changedName = 'changed' + club_id;
	var inputElementsList = row.getElementsByClassName('changeflag');
	var inputElement = inputElementsList[0];
	inputElement.value = '1';
}