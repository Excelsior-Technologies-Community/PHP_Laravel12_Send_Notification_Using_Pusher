<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

/**
 * CreateAdminUser Seeder
 *
 * This seeder is responsible for creating a default admin user
 * in the application's database.
 *
 * It is useful during:
 * - project setup
 * - testing
 * - development
 *
 * The admin user will have special permissions such as receiving
 * realtime notifications in this project.
 */
class CreateAdminUser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method inserts a new admin user record into the "users" table.
     * Fields:
     * - name: Name of the admin user
     * - email: Admin login email
     * - password: Hashed password using bcrypt()
     * - is_admin: A flag (1 = admin, 0 = normal user)
     *
     * @return void
     */
    public function run(): void
    {
        // Create a default admin user
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@gmail.com',
            'password' => bcrypt('123456'), // Hashing the password for security
            'is_admin' => 1                 // 1 means this user is an admin
        ]);
    }
}
