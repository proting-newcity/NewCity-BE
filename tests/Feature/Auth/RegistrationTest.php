<?php

test('new user can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'username' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertNoContent();
});
