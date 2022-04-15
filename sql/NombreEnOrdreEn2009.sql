-- Nombre de joueurs par club qui ont été reconduits pour 2009
--

SELECT Club,Count(Club) AS Nb FROM `signaletique` WHERE AnneeAffilie=2009  Group By Club order by Club