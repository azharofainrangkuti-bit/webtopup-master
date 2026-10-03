<?php

use Inertia\Testing\AssertableInertia as Assert;

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
});

test('home page exposes the storefront data used by the Duniagames-inspired sections', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('main/Home')
            ->where('sliders', [])
            ->where('featured_brands', [])
            ->where('categories', [])
        );
});
