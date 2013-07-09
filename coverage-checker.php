<?php

// Load vendor lib paths from Composer
require_once 'vendor/autoload.php';

// Actual required libs
require_once 'Console/CommandLine.php';

$cmdline_parser = new Console_CommandLine();

$cmdline_parser->addArgument('inputFile', array('description' => 'Clover XML file'));
$cmdline_parser->addArgument('minPercentage', array('description' => 'Minimum percentage required to pass'));

$cmdline_parser->addOption('print_uncovered', array(
    'short_name'  => '-u',
    'long_name'   => '--uncovered',
    'description' => 'Output uncovered lines',
    'action'      => 'StoreTrue'
));

$cmdline_parser->addOption('print_uncovered_verbose', array(
    'short_name'  => '-v',
    'long_name'   => '--uncovered-verbose',
    'description' => 'Output uncovered lines - more verbosely',
    'action'      => 'StoreTrue'
));

$cmdline_parser->addOption('print_uncovered_verbose_whitespace', array(
    'short_name'  => '-w',
    'long_name'   => '--uncovered-verbose-whitespace',
    'description' => 'Output uncovered lines - even if they contain only whitespace',
    'action'      => 'StoreTrue'
));

$cmdline_result = $cmdline_parser->parse();

$inputFile  = $cmdline_result->args['inputFile'];
$percentage = min(100, max(0, (int) $cmdline_result->args['minPercentage']));

if (!file_exists($inputFile)) {
    throw new InvalidArgumentException('Invalid input file provided');
}

if (!$percentage) {
    throw new InvalidArgumentException('An integer checked percentage must be given as second parameter');
}

$xml             = new SimpleXMLElement(file_get_contents($inputFile));
$metrics         = $xml->xpath('//metrics');
$totalElements   = 0;
$checkedElements = 0;

foreach ($metrics as $metric) {
    $totalElements   += (int) $metric['elements'];
    $checkedElements += (int) $metric['coveredelements'];
}

$coverage = ($checkedElements / $totalElements) * 100;

$exit_code = 0;
if ($coverage < $percentage) {
    echo 'Code coverage is ' . $coverage . '%, which is below the accepted ' . $percentage . '%' . PHP_EOL;
    $exit_code = 1;
} else {
    echo 'Code coverage is ' . $coverage . '% - OK!' . PHP_EOL;
}

if ($cmdline_result->options['print_uncovered']) {
    $files = $xml->xpath('//file');
    foreach ($files as $file) {
        print("File: {$file['name']}\n");
        if ($cmdline_result->options['print_uncovered_verbose']) {
            foreach ($file->xpath('metrics') as $file_metrics) {
                print("\tNCLOC: {$file_metrics['ncloc']} of {$file_metrics['loc']} total\n");
                print("\tClasses: {$file_metrics['classes']}\n");
                print("\tCovered methods: {$file_metrics['coveredmethods']} of {$file_metrics['methods']} total\n");
                print("\tCovered conditionals: {$file_metrics['coveredconditionals']} of {$file_metrics['conditionals']} total\n");
                print("\tCovered statements: {$file_metrics['coveredstatements']} of {$file_metrics['statements']} total\n");
                print("\tCovered elements: {$file_metrics['coveredelements']} of {$file_metrics['elements']} total\n");
            }
        }
        $source_code = explode("\n", file_get_contents($file['name']));
        $output_source_code = [];
        foreach ($source_code as $line) {
            $output_source_code[] = array($line, NULL);
        }
        foreach ($file->xpath('line[@count="0"]') as $uncovered_line) {
            $uncovered_line_num = (int) $uncovered_line['num'];
            if (!trim($output_source_code[$uncovered_line_num][0]) and !$cmdline_result->options['print_uncovered_verbose_whitespace']) {
                continue;
            }

            $output_source_code[$uncovered_line_num][1] = "-->";

            if ($cmdline_result->options['print_uncovered_verbose']) {
                $start_line = max(0, $uncovered_line_num - 3);
                $end_line = min(count($source_code) - 1, $uncovered_line_num + 3);
                for ($i = $start_line; $i <= $end_line; $i++) {
                    if ($output_source_code[$i][1] === NULL) {
                        $output_source_code[$i][1] = "...";
                    }
                }
            }
        }
        for ($i=0; $i < count($output_source_code); $i++) {
            $line = $output_source_code[$i];
            if ($line[1] !== NULL) {
                print("{$line[1]}" . sprintf("[%4d]", $i) . "\t{$line[0]}\n");
            }
            if (count($output_source_code) < ($i + 1) and $output_source_code[$i + 1][1] === NULL) {
                print("...\n");
            }
        }
    }
}

exit($exit_code);
