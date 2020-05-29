<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use App\Ability;
use App\Role;
use App\CurrentCompany;
use App\Notifications\CompanyCreated;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('web');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = \Auth::user()->companies()->latest()->get();
        return view('companies.index', ['companies' => $companies]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        try {
            $this->validateCompany();
            $company = new Company(request(['name']));
            $company->code = substr(md5(microtime()), rand(0, 26), 6);
            $company->save();
            $user = \Auth::user();
            $company->employ($user);
            $approveApplication = Ability::firstOrCreate(['name'
                => 'approve_job_application', 'company_id' => $company->id]);
            $admin = Role::firstOrCreate(['name' => 'admin', 'company_id' => $company->id]);
            $admin->allowTo($approveApplication);
            $user->assignRole($admin);
            $recordJournalEntries = Ability::firstOrCreate(['name'
                => 'record_journal_entries', 'company_id' => $company->id]);
            $staff = Role::firstOrCreate(['name' => 'staff', 'company_id' => $company->id]);
            $staff->allowTo($recordJournalEntries);
            $company->save();
            $approveApplication->save();
            $admin->save();
            $currentCompany = new CurrentCompany(['user_id' => $user->id, 'company_id' => $company->id]);
            $currentCompany->save();
            \Notification::send($user, new CompanyCreated($company));
            return redirect(route('home'))
                ->with('status', 'Company created! You may now start adding items through the navigation pane.');
        } catch (\Exception $e) {
            return back()->with('status', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        return view('companies.show', ['company' => $company]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Company $company)
    {
        try {
            $company->update($this->validateCompany());
            return redirect($company->path());
        } catch (\Exception $e) {
            return back()->with('status', $e->getMessage());
        }
    }

    public function validateCompany()
    {
        return request()->validate([
            'name' => 'required'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return redirect(route('companies.index'));
    }
}
