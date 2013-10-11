<?php

/*
Bachelor project : Quiz generator for Cryptography and Security course
Author : François Farquet
Date : Feb - June 2013
*/

// max length allowed for strings such as the question title or an answer
define("MAX_STRING_LENGTH", 2000);

// max string length for categories and course titles
define("MAX_LENGTH_COURSES", 255);

// state if it is allowed to save a question with errors remaining
define("SAVE_QUESTIONS_WITH_ERRORS", false);

// Set this to true if you want that each getters return content in HTML format instead of Latex
// doesn't affect functions starting with html_ : html_form() or html_correction()
define("HTML_MODE", false);

// where we stores the two files (tex2html.txt and default.txt) from the root of the project
define("TEX2HTML_FOLDER", "lib/tex2html/");

// Time in seconds before redirection when we display a message and then redirect
define("REDIRECT_TIME", 2);

// Number of questions in a quiz
define("QUIZ_SIZE", 12);

// max saved statistics by user
define("MAX_SAVED_STATS", 300);

// time in months after which we delete quizzes statistics by users
define("MAX_QUIZ_SAVED_DURATION", 18);

// if we want to show the percentage statistics of correctness when we take a quiz
define("SHOW_STATS", false);

// the chosen basis to encode the integer id to a string id
define("ID_ENCODER_BASIS", "123456789abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ");

// database name
define("DB_BASE", "quiz_generator");

// table names in this database
define("TBL_QUESTIONS", "questions");
define("TBL_CATEGORIES", "categories");
define("TBL_COURSES", "courses");
define("TBL_USERS_STATS", "users_stats");

?>