<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use App\Ability;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
    }
    public function index()
    {
        if (empty(request('name'))) {
            $company = \Auth::user()->current_company->company()->firstOrFail();
            $roles = Role::where('company_id', $company->id)->get();
        } else {
            $company = \Auth::user()->current_company->company()->firstOrFail();
            $roles = Role::where('company_id', $company->id)->where('name', 'like', '%' . request('name') . '%')->get();
        }
        \Request::flash();
        return view('roles.index', compact('roles'));
    }
    public function show(Role $role)
    {
        $abilities = $role->abilities()->get();
        return view('roles.show', compact('role', 'abilities'));
    }
    public function create()
    {
        $abilities = Ability::latest()->get();
        return view('roles.create', compact('abilities'));
    }
    public function store(Request $request)
    {
        $this->validateRole();
        $company = \Auth::user()->current_company->company()->firstOrFail();
        $role = new Role(['name' => request('name'), 'company_id' => $company->id]);
        $role->save();
        $abilities = $request->input('ability');
        if (isset($abilities)) {
            foreach ($abilities as $ability) {
                $role->allowTo(Ability::find($ability));
            }
        }
        return redirect(route('roles.index'));
    }
    public function edit(Role $role)
    {
        $abilities = Ability::latest()->get();
        $checkedAbilities = $role->abilities()->latest()->get();
        return view('roles.edit', compact('role', 'abilities', 'checkedAbilities'));
    }
    public function update(Role $role, Request $request)
    {
        $role->update($this->validateRole());
        $abilities = $request->input('ability');
        $role->abilities()->detach();
        if (isset($abilities)) {
            foreach ($abilities as $ability) {
                $role->allowTo(Ability::find($ability));
            }
        }
        return redirect(route('roles.show', compact('role')));
    }
    public function validateRole()
    {
        return request()->validate([
            'name' => 'required'
        ]);
    }
    public function destroy(Role $role)
    {
        $role->delete();
        return redirect(route('roles.index'));
    }
}
