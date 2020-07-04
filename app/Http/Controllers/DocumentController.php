<?php

namespace App\Http\Controllers;

use App\Document;
use Illuminate\Http\Request;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.NumberOfChildren)
     */

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
    }
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('name'))) {
            $documents = Document::where('company_id', $company->id)->latest()->get();
        } else {
            $documents = Document::where('company_id', $company->id)
                ->where('name', 'like', '%' . request('name') . '%')->get();
        }
        if (\Route::currentRouteName() === 'documents.index') {
            \Request::flash();
        }
        return view('documents.index', compact('documents'));
    }
    public function show(Document $document)
    {
        return view('documents.show', compact('document'));
    }
    public function create()
    {
        if (\Route::currentRouteName() === 'documents.create') {
            \Request::flash();
        }
        return view('documents.create');
    }
    public function store()
    {
        $this->validateDocument();
        $company = \Auth::user()->currentCompany->company;
        $document = new Document([
            'company_id' => $company->id,
            'name' => request('name')
        ]);
        $document->save();
        return redirect(route('documents.index'));
    }
    public function edit(Document $document)
    {
        if (\Route::currentRouteName() === 'documents.edit') {
            \Request::flash();
        }
        return view('documents.edit', compact('document'));
    }
    public function update(Document $document)
    {
        $document->update($this->validateDocument());
        return redirect($document->path());
    }
    public function validateDocument()
    {
        return request()->validate([
            'name' => 'required'
        ]);
    }
    public function destroy(Document $document)
    {
        try {
            $document->delete();
            return redirect(route('documents.index'));
        } catch (\Illuminate\Database\QueryException $ex) {
            return back()->with('status', $ex->getMessage());
        }
    }
}
