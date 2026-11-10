<?php

use Illuminate\Database\Seeder;
use App\Http\Models\User;
use App\Http\Models\Role;

class UsersTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		//factory(App\User::class, 1)->create();

		$adminRole = Role::where('name', 'administrator')->first();
		$agencyRole  = Role::where('name', 'agency')->first();
		$userRole  = Role::where('name', 'user')->first();

		$admin = new User();
		$admin->role_id = $adminRole->id;
		$admin->name = 'test_user';
		$admin->email = 'test@gmail.com';
		$admin->password = bcrypt('test_user');
		$admin->status = 1;
		$admin->first_name = 'User';
		$admin->last_name = 'Test';
		$admin->save();

		$admin = new User();
		$admin->role_id = $adminRole->id;
		$admin->name = 'Admin';
		$admin->email = 'admin@gmail.com';
		$admin->password = bcrypt('secret');
		$admin->status = 1;
		$admin->first_name = 'User';
		$admin->last_name = 'Admin';
		$admin->save();

		$agency = new User();
		$agency->role_id = $agencyRole->id;
		$agency->name = 'Agency 1';
		$agency->email = 'agency_1@gmail.com';
		$agency->password = bcrypt('secret');
		$agency->status = 1;
		$agency->first_name = 'User';
		$agency->last_name = 'Agency 1';
		$agency->save();

		$agency = new User();
		$agency->role_id = $agencyRole->id;
		$agency->name = 'Agency 2';
		$agency->email = 'agency_2@gmail.com';
		$agency->password = bcrypt('secret');
		$agency->status = 1;
		$agency->first_name = 'User';
		$agency->last_name = 'Agency 2';
		$agency->save();

		$user = new User();
		$user->role_id = $userRole->id;
		$user->name = 'User';
		$user->email = 'user@gmail.com';
		$user->password = bcrypt('secret');
		$user->status = 1;
		$user->first_name = 'User';
		$user->last_name = 'Usual';
		$user->save();
	}
}
