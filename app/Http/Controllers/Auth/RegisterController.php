<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RedirectsUsers;
use User;
use Role;
use Email;
use BaseModel;
use Profession;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */
    use RedirectsUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest');
		$this->middleware('web', ['only' => ['register', 'showRegistrationForm']]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $auth = Auth::user();
        if($auth && $auth->isAdmin()) {
            return Validator::make($data, [
                'email' => 'required|string|email|max:190|unique:users',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'role_id' => 'required|int',
            ])->setAttributeNames([
                'email' => trans('auth.email'),
                'first_name' => trans('auth.first_name'),
                'last_name' => trans('auth.last_name'),
                'role_id' => trans('auth.role'),
            ]);
        } else {
            return Validator::make($data, [
                'name' => 'required|string|max:190|unique:users',
                'email' => 'required|string|email|max:190|unique:users',
                'password' => 'required|string|min:4|confirmed',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'role_id' => 'required|int',
            ])->setAttributeNames([
                'name' => trans('auth.name'),
                'email' => trans('auth.email'),
                'password' => trans('auth.password'),
                'first_name' => trans('auth.first_name'),
                'last_name' => trans('auth.last_name'),
                'role_id' => trans('auth.role'),
            ]);
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Http\Models\User
     */
    protected function create(array $data, $additional = [])
    {
        return User::createUser($data, $additional);
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        $data = $this->controlParams();

        if(!$data) return redirect(url('/'));
        $onlyrole = ($data === true ? null : $data['role']);

        $rolesList = Role::query();
        if(is_null($onlyrole)) {
            $rolesList->where('name', '!=', 'administrator')->where('name', '!=', 'franchise') ;
        } elseif ($onlyrole != 'all') {
            $rolesList->where('name', $onlyrole);
        }
        $rolesList = $rolesList->get()->toArray();
        $rolesList = array_filter($rolesList, function($item){
            if (in_array($item['name'],$this->_getAvailableRoles())) {
                return $item;
            }
        });

		$roles = json_encode(collect($rolesList));
        $params['professions'] = Profession::getAllList();
        $params['professions_sort'] = array_keys($params['professions']);
        $params = json_encode(collect($params));

		$siteName = config('app.name');

        return view('auth.register', compact('roles', 'onlyrole', 'params', 'siteName'));
    }

    public function controlParams()
    {
        $user = Auth::user();
        $onlyRole = request()->role;

        if($user) {
            if($user->isAdmin()) return ['role' => 'all'];
            if(is_null($onlyRole)) return false;

            $role = $user->role()->first()->name;
            if(!User::_isAgencyWithAgents($role)) return false;
            $agent_role = User::getAgencyAgents($role, 'role');
            if($agent_role != $onlyRole) return false;
            return ['role' => $agent_role, 'agency_id' => $user->id];
        }
        return is_null($onlyRole);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $data = $this->controlParams();
        if(!$data) return redirect(url('/'));

        $params = $request->all();
        if(isset($data['role'])) {
            if($data['role'] != 'all') {
                $role = Role::where('name', $data['role'])->first();
                $params['role_id'] = $role->id;
                $data['role_name'] = $role->title;
            }
            $status = 0;
        } else {
           $status = 0;
        }
        //dd($data, $params);
        $validator = $this->validator($params);
        if($validator->fails()) {
            return redirect()->back()->with('errors', json_encode($validator->errors()->toArray()))->withInput();
        }

        $params['status'] = $status;
        $auth = Auth::user();
        if($auth && $auth->isAdmin()) {
            if(empty($params['name'])) {
                $params['name'] = $params['email'];
            }
            if(empty($params['password'])) {
                $params['password'] = 'OIfgio3$';
            }
        }
        event(new Registered($user = $this->create($params, $data)));

        //$this->guard()->login($user);

        return $this->registered($request, $user, $data)
            ? (isset($data['role']) ? ($data['role'] == 'all' ? redirect(route('user.edit.admin', $user->id)) : redirect(route('user.profile.agents'))) : redirect()->back()->with('message', json_encode(__('Thank you for registering. Wait for approval.'))))
            : redirect($this->redirectPath());
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user, $data)
    {
        if(!$user) return false;
        $auth = Auth::user();
        if($auth && $auth->isAdmin()) return true;

        $attributes = array();
        $user = $user->toArray();
        foreach($user as $key => $value) {
            if(!is_null($value) && !is_array($value)) {
                $attributes['user_' . $key] = $value;
            }
        }
        if($user['status'] == 0) {
            Email::send('new_user', $attributes);
        } else if (isset($data['agency_id'])) {
            $attributes['user_role'] = $data['role_name'];
            $attributes['password'] = $request->password;

            $agency = User::getUserById($data['agency_id']);
            foreach($agency as $key => $value) {
                if(!is_null($value) && !is_array($value)) {
                    $attributes['agency_' . $key] = $value;
                }
            }
            $attributes['agency_name'] = isset($agency['relation']) && isset($agency['relation']['company_name']) ? $agency['relation']['company_name'] : $agency['first_name'] . ' ' . $agency['last_name'];

            Email::send('new_agent', $attributes);
        }
        $attributes['send_to_user'] = 1;
        Email::send('signup_user', $attributes);
        return true;
    }
}
