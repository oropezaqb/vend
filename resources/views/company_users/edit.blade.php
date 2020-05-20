@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Edit Company User Details)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div id="content">
                            <form method="POST" action="/company_users/{{ $user->id }}">
                                @csrf
                                @method('PUT')
                                @if (!empty($message))
                                    <p>{{ $message }}</p>
                                @endif
                                <p>User Name: {!! $user->name !!}</p>
                                <p>Roles:</p>
                                @forelse ($roles as $role)
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    {!! Form::checkbox('role[]', $role->id, $checkedRoles->contains('id', $role->id) ? 'true' : '') !!}
                                    <span style='margin-left:1em;'></span>
                                    {!! Form::label($role->id, $role->name) !!}
                                    <br>
                                @empty
                                    <p>No roles recorded yet.</p>
                                @endforelse
                                <input type="hidden" id="id" name="id" value="{{ $user->id }}">
                                <button class="btn btn-primary" type="submit">Save</button>
                                @error('')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
