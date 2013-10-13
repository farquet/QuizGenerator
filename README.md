#QuizGenerator

**Goal** : Parse Latex files to extract particular \question command. Storing and organizing questions in a MySQL database. Display random quizzes with many options.

___

This was a BSc. project in Computer Science at EPFL. It has been done for in the [Security and Cryptography lab](http://lasecwww.epfl.ch/ "LASEC EPFL") (LASEC) at EPFL.

The goal of this project is to exploit a large amount of Latex quiz archives. A quiz is a sequence of N multiple choices questions with exactly one right answer among M.


This project has two main parts. The first one consists in parsing a Latex file submitted
by an admin and extracting quiz questions (title, answers and solution index) and organizing them in courses and chapters. The second
one aims to display random quizzes to anyone who want to practice this material. A quiz is generated loading random questions from the database.

#### Demo
*	This is actually running on the LASEC website. [Try the version used by EPFL students](http://lasec.epfl.ch/quiz_generator/choices.php "Quiz generator at LASEC, EPFL").
*	There exist a demo version on a private server (Warning : may be slow or down) with admin zone not protected. Feel free to have a look [here](http://home.farquet.com/quiz_generator/ "Quiz generator Farquet").

#### Dependencies :

*	Apache web server
*	PHP5.0 or later
*	magic_quotes_gpc = off (PHP option)
*	MySQL server
*	MathJax 2.1 or newer (v2.1 included in project)

### Important remarks
*	The authentication used is named Tequila. It has been developped by EPFL and is and open source authentication system under GNU GPL v2 license. You can learn more about it on [the official website](http://tequila.epfl.ch "Tequila EPFL"). If you are not hosting this project on the EPFL subnet (\*.epfl.ch), the authentication will not work and give you a fake EPFL id (999999) without prompting any login or password. Feel free to modify the *login.php* page which is the only file creating and deleting PHP sessions.

___

## INSTALLATION INSTRUCTIONS 

#### Step 1

Be sure to have an Apache server with PHP5 or later with magic_quotes_gpc option set
to off. You also need a MySQL server with login and password.

#### Step 2

Copy the folder ***web*** on your server.

Note : you can optionally remove the tests folder which isn't required to let the project
work.

#### Step 3

Modify the file *web/lib/param.inc.php* by setting your username and password to access the MySQL server and set the right access to this file as low as possible (i.e. using chmod).

#### Step 4

Create a MySQL database named *quiz_generator* and execute in it the *tables.sql* file from this folder.

Note : you can choose another name if you change it in the options (see step 5).

#### Step 5

Choose the options as you want by adjusting values in *web/lib/config.inc.php*

#### Step 6

Protect your admin folder with any mechanism you want (i.e. using a *.htaccess* file)

#### Step 7

Delete or rename the index.php page in the web folder (link to user and admin part of the
project).

Everything is now set to work properly, you can :

*	access the user part by going, with a browser, on *choices.php*

*	access the admin part by going, with a browser, on *admin/*

___
Project done by François Farquet in June 2013. &copy; All rights reserved.
