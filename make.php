<?php
if (!isset($argv[1])) {
	usage();
}

$command_function = 'command_' . $argv[1];
if (!function_exists($command_function)) {
	error_and_die("Command '".$argv[1]." doesn't exist.");
}
call_user_func_array($command_function, array_slice($argv, 2));

function command_pdfs() {
	generate_front_pdfs();
	generate_back_pdf();
}

function command_clean() {
	system('rm -rf pdf/*');
	system('rm -rf html/*');
}

function generate_front_pdfs($file_name_template = 'pdf/{number}.pdf') {
	$people = parse_people_file('people.csv');
	$file_number = 0;
	foreach($people as $person) {
		$output_file_name = get_output_filename($file_name_template, $file_number);
		$file_number++;
		generate_pdf_from_person($person, $output_file_name);
	}
}

function generate_back_pdf() {
	generate_pdf_from_html_file('back.html', 'pdf/back.pdf');
}

function command_reg_html() {
	$people = parse_people_file('people.csv');
	usort( $people, function( $a, $b ) { return strcmp(trim($a->first), trim($b->first)); });
	include 'list.php';
}

function parse_people_file($file_name) {
	$people = array();
	$f = fopen($file_name, 'r');
	while( false !== ( $line = fgetcsv( $f ) ) ) {
		if ( !array_filter( $line ) ) {
			continue;
		}
		if ( $line[0] == 'Attendee ID' ) {
			continue;
		}
		$person = new stdClass();
		$person->first = $line[2];
		$person->last = $line[3];
		$person->twitter = parse_twitter($line[9]);
		$person->id = $line[0];
		$person->email = $line[4];
		$person->coupon = $line[8];
		$person->size = $line[10];
		$person->telerik = in_array($person->id, array(831, 830));

		$people[]= $person;
	}
	return $people;
}

function parse_twitter($twitter) {
	$twitter = preg_replace('%(https?://)?twitter.com/|@%', '', $twitter );
	return $twitter? "@$twitter" : '';
}

function generate_pdf_from_person($person, $output_file_name) {
		$html = get_html_for_person($person);
		$html_file_name = str_replace('pdf', 'html', $output_file_name);
		file_put_contents($html_file_name, $html);
		return generate_pdf_from_html_file($html_file_name, $output_file_name);
}

function generate_pdf_from_html_file($html_file_name, $output_file_name) {
		system("wkhtmltopdf -s A6 -B 0 -L 0 -R 0 -T 0 $html_file_name $output_file_name");
}

function get_html_for_person($person) {
	ob_start();
	require 'template.php';
	return ob_get_clean();
}

function get_output_filename($template, $number) {
	$template = str_replace('%', '%%', $template);
	$template = str_replace('{number}', '%03d', $template);
	return sprintf($template, $number);
}

function get_commands() {
	$user_functions = get_defined_functions()['user'];
	$command_functions = array_filter( $user_functions, function($f) {
		return preg_match( '/^command_/', $f );
	});
	$commands = array_map( function($f) {
		preg_match( '/^command_(.*)/', $f, $m );
		return $m[1];
	}, $command_functions);
	return $commands;
}

function usage() {
	global $argv;
	stderr_line(basename($argv[0]) . ' <command> <args>');
	stderr_line("Available commands: " . implode(', ', get_commands()));
	exit(1);
}

function stderr_line($line) {
	fwrite(STDERR, "$line\n");
}

function error_and_die($message, $exit_code = 1) {
	stderr_line($message);
	exit($exit_code);
}
