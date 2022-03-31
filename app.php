#!/usr/bin/env php
<?php

# ! NO PACKAGES, NO CLASSES
# [X] list - show all todos
# [x] add  <description> - add a new todo item
# [x] delete <id> - delete a todo item
# Homework
# [ ] search <query> find todo items
# [ ] edit <id> <content> update todo item
# [ ] set-status <id> <status> (check if status is new, in-progrss, done or rejected)
# [ ] *Task 3 - add due-date to todo item (if due-date is in past, then show status 'outdated'
use ToDoApp\Application;

require __DIR__ . '/vendor/autoload.php';
//require_once __DIR__ . "/core.php";
/*
$script = array_shift($argv); // app.php
$command = array_shift($argv); // list
$args = $argv; // []
main($command, $args);
*/
$application = new Application(getcwd() . '/todo.json', "TODO-");
$application -> run();