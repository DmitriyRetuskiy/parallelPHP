<?php

$out = fopen("php://output", "w"); //output handler
fwrite($out, "your output string.\n");
fclose($out);