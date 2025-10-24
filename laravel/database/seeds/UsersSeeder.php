<?php

use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        DB::table('roles')->delete();
        DB::table('users')->delete();

        $admin = new Role;
        $admin->name = 'admin';
        $admin->save();

        $judge = new Role;
        $judge->name = 'judge';
        $judge->save();

        $wall = new Role;
        $wall->name = 'wall';
        $wall->save();

        $adminUser = new User;
        $adminUser->username = 'admin';
        $adminUser->password = Hash::make('admin');
        $adminUser->firstName = 'Admin';
        $adminUser->lastName = 'Admin';
        $adminUser->save();

        $adminUser->attachRole($admin);
        $adminUser->save();

        $judge1 = new User;
        $judge1->username = 'wall';
        $judge1->password = Hash::make('wall');
        $judge1->firstName = 'Wall';
        $judge1->lastName = 'Wall';
        $judge1->save();

        $judge1->attachRole($wall);
        $judge1->save();
    }
}
