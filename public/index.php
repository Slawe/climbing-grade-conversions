<?php

use Climb\Grades\Domain\Service\GradeConversion;
use Climb\Grades\Domain\Value\GradeSystem;

require '../vendor/autoload.php';

$yds = GradeConversion::from('6c+', 'fr')->to(GradeSystem::YDS);
$all = GradeConversion::from('6c+', 'fr')->toAll();

echo '<pre>';

print_r($yds);
print_r($all);