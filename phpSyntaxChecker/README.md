# README.MD
This a basic deployer script that deploys data from git, installs or updates dependencies using composer and import database from 0 or update it.

## Installation
Download from github and execute composer install and create and script that instantiates the class miniDeployer.php and call to the "deployProject()" method.

## MySQL importation
There are two ways to import mysql:

-The whole database: The sql file must contain the "CREATE DATABASE" sentence.
-If the database already exists just add the sentence "USE <databaseName>" at the beginning of sql file.
-If database does not be to update don't include the mySql section.
