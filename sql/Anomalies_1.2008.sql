-- Liste des joueurs affili�s apr�s la date de 2008-01-01
-- mais qui ont une ann�e d'affiliation de 2008
-- -----------------------------------------------------

SELECT AnneeAffilie as Aff, Matricule,Nom,Prenom,Club,Federation AS Fed,ClubTransfert as Trf,DateAffiliation as dAff,LoginModif as Login FROM `signaletique` WHERE AnneeAffilie=2008 AND  DateAffiliation>DATE('2008-00-00') Order By Club,Nom,Prenom