## Secrets directory

This directory contains sensitive information stored locally (as opposed to secrets 
stored in the Google cloud).

It used connection parameters of the development environment.  The sensitive 
parameters the test and production environment are stored in the Google Cloud Secret 
Manager 

The source code for accessing the secrets is found in sites/frbekbsb/secrets.php  
The code supports reading json and yaml files.

Although technically this directory should only contains sensitive informnation 
in the development environment, all json and yaml files in this directory
are anyway  put in .gitignore, avoiding the development secrets to be stored in the 
git repo.
