<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use Illuminate\Support\Facades\DB;

$results = Course::select('intended_semester', 'course_type', DB::raw('count(*) as c'))
    ->groupBy('intended_semester','course_type')
    ->orderBy('intended_semester')
    ->get();

$bySem = [];
foreach ($results as $r) {
    $bySem[$r->intended_semester][$r->course_type] = $r->c;
}

for ($i=1;$i<=12;$i++) {
    $the = $bySem[$i]['theory'] ?? 0;
    $lab = $bySem[$i]['lab'] ?? 0;
    echo "Sem $i: theory=$the lab=$lab\n";
}
