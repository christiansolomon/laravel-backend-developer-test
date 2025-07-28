<?php

use Illuminate\Support\Collection;

$employees = collect([
    ['name' => 'John', 'city' => 'Dallas'],
    ['name' => 'Jane', 'city' => 'Austin'],
    ['name' => 'Jake', 'city' => 'Dallas'],
    ['name' => 'Jill', 'city' => 'Dallas'],
]);

$offices = collect([
    ['office' => 'Dallas HQ', 'city' => 'Dallas'],
    ['office' => 'Dallas South', 'city' => 'Dallas'],
    ['office' => 'Austin Branch', 'city' => 'Austin'],
]);

$output = [];

foreach ($offices as $office) {
    $city = $office['city'];
    $officeName = $office['office'];

    $employeeNames = $employees
        ->where('city', $city)
        ->pluck('name')
        ->values()
        ->toArray();

    $output[$city][$officeName] = $employeeNames;
}

print_r($output);
