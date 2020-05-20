<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;
use App\Application;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class ApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('web');
    }
    public function index()
    {
        if (empty(request('code'))) {
            $applications = \Auth::user()->applications()->latest()->get();
        } else {
            $company = Company::where('code', request('code'))->firstOrFail();
            $applications = \Auth::user()->applications()->where('company_id', $company->id)->get();
        }
        \Request::flash();
        return view('applications.index', ['applications' => $applications]);
    }
    public function show(Application $application)
    {
        return view('applications.show', ['application' => $application]);
    }
    public function create()
    {
        return view('applications.create');
    }
    public function store()
    {
        $this->validateApplication();
        $company = Company::where('code', request('code'))->firstOrFail();
        $user = \Auth::user();
        if ($user->applications()->where('company_id', $company->id)->count() == 0) {
            if ($user->companies()->where('company_id', $company->id)->count() == 0) {
                $application = new Application(['user_id' => $user->id, 'company_id' => $company->id]);
                $application->save();
                return redirect(route('applications.index'));
            } else {
                \Request::flash();
                $message = "You are already an authorized user in this company.";
                return view('applications.create', ['message' => $message]);
            }
        } else {
            \Request::flash();
            $message = "An application has already been filed with this company.";
            return view('applications.create', ['message' => $message]);
        }
    }
    public function edit(Application $application)
    {
        return view('applications.edit', compact('application'));
    }
    public function update(Application $application)
    {
        $application->update($this->validateApplication());
        return redirect($application->path());
    }
    public function validateApplication()
    {
        return request()->validate([
            'code' => 'required'
        ]);
    }
    public function destroy(Application $application)
    {
        $application->delete();
        return redirect(route('applications.index'));
    }
}
