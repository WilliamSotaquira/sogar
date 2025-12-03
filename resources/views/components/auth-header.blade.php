@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <h1 class="text-2xl font-semibold text-heading">{{ $title }}</h1>
    <p class="text-body">{{ $description }}</p>
</div>
