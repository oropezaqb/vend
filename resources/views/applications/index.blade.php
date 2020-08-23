@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Applications</div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/applications">
                            @csrf
                            <div class="form-group">
                                <label for="name">Company Code: </label>
                                <input 
                                    class="form-control @error('code') is-danger @enderror" 
                                    type="text" 
                                    name="code" 
                                    id="code" required
                                    value="{{ old('code') }}">
                                @error('code')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                        <p></p>
                        <h6 class="font-weight-bold">Add</h6>
                        <p>Want to file a new application? Click <a href="{{ url('/applications/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($applications as $application)
                            <div id="content">
                                <div id="name">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $application->path() }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/applications/{{ $application->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/applications/{{ $application->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div><div style="display:inline-block;">&nbsp;&nbsp;{!! \App\Company::findOrFail($application->company_id)->name; !!} (Company code: {!! \App\Company::findOrFail($application->company_id)->code; !!})</div>
                                </div>
                            </div>
                        @empty
                            <p>No applications recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
