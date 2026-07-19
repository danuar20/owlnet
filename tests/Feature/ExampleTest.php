<?php

declare(strict_types=1);

it('redirects guests from the root to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
