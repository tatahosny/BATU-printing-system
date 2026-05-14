<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;

$subjects = Subject::withCount('students')->get();
foreach ($subjects as $subject) {
    echo $subject->name . ': ' . $subject->students_count . PHP_EOL;
}
