<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ability;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class AbilityController extends Controller
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
            $abilities = Ability::latest()->get();
        } else {
            $abilities = Ability::where('name', 'like', '%' . request('name') . '%')->get();
        }
        \Request::flash();
        return view('abilities.index', compact('abilities'));
    }
    public function show(Ability $ability)
    {
        return view('abilities.show', compact('ability'));
    }
    public function create()
    {
        return view('abilities.create');
    }
    public function store()
    {
        $this->validateAbility();
        $company = \Auth::user()->current_company->company;
        $ability = new Ability([
            'company_id' => $company->id,
            'name' => request('name')
        ]);
        $ability->save();
        return redirect(route('abilities.index'));
    }
    public function edit(Ability $ability)
    {
        return view('abilities.edit', compact('ability'));
    }
    public function update(Ability $ability)
    {
        $ability->update($this->validateAbility());
        return redirect($ability->path());
    }
    public function validateAbility()
    {
        return request()->validate([
            'name' => 'required'
        ]);
    }
    public function destroy(Ability $ability)
    {
        $ability->delete();
        return redirect(route('abilities.index'));
    }
}
