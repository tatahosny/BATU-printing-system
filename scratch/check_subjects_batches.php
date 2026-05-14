<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\Batch;

$subjects = Subject::all();
foreach ($subjects as $subject) {
    echo 'ID: ' . $subject->id . ', Name: ' . $subject->name . ', Batch ID: ' . $subject->batch_id . PHP_EOL;
}

$batches = Batch::all();
foreach ($batches as $batch) {
    echo 'Batch ID: ' . $batch->id . ', Name: ' . $batch->name . PHP_EOL;
}
