<?php

use App\Models\User;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    /**
     * Faker user
     *
     * @return $user
     */

    public function fakerUser()
    {
        factory('App\Models\User')->create(
            [
                "nickname" => "zebrapool",
                "name" => "zebrapool",
                "email" => "tangmingming@nle-tech.com",
                "password" => '$2y$10$VopUZljMszBLVfEtjEfRve.z2u7MXU7XFdj/6Cys.QJ88EJ8a/c2S',
            ]
        );

        $user = User::orderBy("id", "desc")->first();
        app('auth')->guard()->setUser($user);
        return $user;
    }

    /**
     * Faker otheruser
     *
     * @return $otheruser
     */

    public function fakerOtherUser()
    {
        factory('App\Models\User')->create(
            [
                "nickname" => "liuyiming",
                "name" => "liuyiming",
                "email" => "liuyiming@nle-tech.com",
                "password" => '$2y$10$VopUZljMszBLVfEtjEfRve.z2u7MXU7XFdj/6Cys.QJ88EJ8a/c2S',
            ]
        );

        $user = User::orderBy("id", "desc")->first();
        app('auth')->guard()->setUser($user);
        return $user;
    }
}
