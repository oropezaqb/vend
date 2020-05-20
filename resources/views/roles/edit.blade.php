@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Edit Role Details)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div id="content">
                            <form method="POST" action="/roles/{{ $role->id }}">
                                @csrf
                                @method('PUT')
                                @if (!empty($message))
                                    <p>{{ $message }}</p>
                                @endif
                                <div class="form-group">
                                    <label for="name">Role Name: </label>
                                    <input 
                                        class="form-control @error('name') is-danger @enderror" 
                                        type="text" 
                                        name="name" 
                                        id="name" required
                                        value="{{ $role->name }}">
                                    @error('name')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <p>Abilities:</p>
                                @forelse ($abilities as $ability)
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    {!! Form::checkbox('ability[]', $ability->id, $checkedAbilities->contains('id', $ability->id) ? 'true' : '') !!}
                                    <span style='margin-left:1em;'></span>
                                    {!! Form::label($ability->id, $ability->name) !!}
                                    <br>
                                @empty
                                    <p>No abilities recorded yet.</p>
                                @endforelse
                                <button class="btn btn-primary" type="submit">Save</button>
                                @error('name')
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
