@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Add Company Users)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <h6 class="font-weight-bold">Pending Applications</h6>
                        @forelse ($applications as $application)
                            <div id="content">
                                <div id="name">
                                    <div style="display:inline-block;">
                                        <button class="btn btn-link" onclick="location.href = '/company_users/{{ $application->user->id }}';">
                                            View
                                        </button>
                                    </div>
                                    <div style="display:inline-block;">
                                        <button class="btn btn-link" onclick="location.href = '/company_users/{{ $application->user->id }}/edit';">
                                            Edit
                                        </button>
                                    </div>
                                    <div style="display:inline-block;">
                                        <form method="POST" action="/company_users">
                                            @csrf
                                            <button class="btn btn-link" type="submit" name="application_id" value="{{ $application->id }}">
                                                Approve
                                            </button>
                                        </form>
                                    </div>
                                    <div style="display:inline-block;">&nbsp;&nbsp;{!! $application->user->name !!}</div>
                                </div>
                            </div>
                        @empty
                            <p>None.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
