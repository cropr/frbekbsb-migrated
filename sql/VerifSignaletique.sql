select Matricule,Nom,Prenom,Club,ClubTransfert,ClubOld,
AnneeAffilie,DateAffiliation,DateInscription,LoginModif,DateModif 
from signaletique 
where AnneeAffilie>2008 AND (DateAffiliation is null or DateAffiliation ='0000-00-00') 
order by club,matricule