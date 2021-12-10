<?php

$ending = "x\n";
$f = fopen('file.txt', 'r');

$line = stream_get_line($f, 1024, $ending);
tell($line);// ending
$line = stream_get_line($f, 1024, $ending);
tell($line);// ending
$line = stream_get_line($f, 1024);
tell($line);// eof

function check_ending($ending) {
  global $f;

  $len = strlen($ending);
  $moved = fseek($f, -1*$len, SEEK_CUR);
  if ($moved === -1)
    throw new Exception("moved back with $len");
  // you moved back with $len bytes
  // but fgets's length parameter is non-inclusive
  // so to read $len bytes, use $len + 1 
  // the pointer is moved by $len + 1, positioned on the next byte
  $check = fgets($f, $len+1);
  if ($check === false)
    throw new Exception("getting ending check string");
  $read = strlen($check);
  // also, fgets read not all expected $len bytes
  // we repeatedly call it, till expected/error 
  while ($read < $len) {
    $diff = $len-$read;
    $moved = fseek($f, -1*$diff, SEEK_CUR);
    if ($moved === -1)
      throw new Exception("moved back with $len");
    $next = fgets($f, $diff);
    if ($next === false)
      throw new Exception("getting ending check string");
    $check .= $next;
    $read = strlen($check);  
  }

  return $check === $ending;
}

function at($echo=true) {
  global $f, $ending;

  $msg = match(true) {
    feof($f) => 'PHP_EOF',
    check_ending($ending) => 'ENDING', 
    default => 'MAX_LEN',
  };

  if($echo) 
    echo $msg;
  else
    return $msg;
}

function tell($msg, $echo=true) {
  global $f;

  $pos = ftell($f);
  if ($pos === false)
    throw new Exception("cannot tell");
  $at = at(echo:false);
  // pointer wii be moved by fgetc
  $char = fgetc($f);
  // so move pointer back
  fseek($f, -1*strlen($char), SEEK_CUR);

  $line = sprintf(
    "%s:: pos:%d char:[%s] char: %s endcase: %s\n",
    $msg, $pos, $char, $char,  $at,
  );

  if($echo) 
    echo $line;
  else
    return $line;
}

