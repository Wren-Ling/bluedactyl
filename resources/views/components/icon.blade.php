@props(['name' => '', 'class' => ''])

@php
    $iconPath = base_path('node_modules/lucide-static/icons/' . $name . '.svg');
    $svg = file_exists($iconPath) ? file_get_contents($iconPath) : '';

    if ($svg) {
        $svg = preg_replace(
            '/<svg([^>]*)>/',
            '<svg$1 class="lucide lucide-' . e($name) . ' ' . e($class) . '">',
            $svg
        );
    }
@endphp

{!! $svg !!}