#!/usr/bin/php
<?php

set_time_limit(5);
error_reporting(0);


$logging		= true;		// Global logging
$debug_logging		= false;	// Logging debug messages
$dump_requests		= false;	// Save requests
$request_buffer_size	= 1024;		// 1024 Bytes - Maximum request size

$log_file		= '/var/log/smart-console-utility/smart-console-utility.log';
$req_id			= preg_replace(array('/\./', '/(\-)([0-9]{1}$)/', '/(\-)([0-9]{2}$)/', '/(\-)([0-9]{3}$)/'), array('-', '$1---$2', '$1--$2', '$1-$2'), array_sum(explode(' ', microtime())));

$dump_dir		= '/tmp/smart-console-utility';
$dump_request_file	= $dump_dir.'/'.$req_id.'.request';

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
function write_log($message)
{
	global $log_file, $logging, $req_id;
	if ($logging)
	{
		$logging = (file_put_contents($log_file, date('Y-m-d H:i:s  ').'['.$req_id.' '.sprintf('%-14s', $_SERVER['REMOTE_HOST']).'] '.$message."\n", FILE_APPEND) !== false);
	}
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
if ($debug_logging) 
{
	write_log('Start');
}

if ($read_handle = fopen('php://stdin', 'r'))
{
	if (($dump_requests) && (!is_dir($dump_dir))) 
	{
		mkdir($dump_dir, 644, true);
		$dump_requests = is_dir($dump_dir);
	}
	// Read client request
	if ($request = fread($read_handle, $request_buffer_size))
	{
		// Get trap message
		preg_match('/[\w\d\s\-\(\)\.]+$/', $request, $matches);
		if ($matches)
		{
			$trap_message =
				preg_replace(array('/\([\d]+\)/', '/[\.]+$/'), '', 
					preg_replace('/[ ]{2,}/', ' ',
						preg_replace('/^.DES/', 'DES',
							trim(end($matches))
						)
					)
				);
		} 
		else 
		{
			$trap_message = '';
		}
	
		if ($logging) {
			write_log($trap_message);
		}

		if ($debug_logging) {
			write_log('Handle request, size('.strlen($request).")\t-> ".$trap_message);
		}

		if ($dump_requests) {
			file_put_contents($dump_request_file, $request, FILE_APPEND);
		}
		// Parse trap message
	}
	else
	{
		write_log('Null request');
	}
	fclose($read_handle);
}
else
{
	write_log('Unable to open STDIN!');
}

if ($debug_logging) 
{
	write_log('End');
}

?>