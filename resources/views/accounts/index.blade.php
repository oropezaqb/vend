@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Account Titles)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/accounts">
                            @csrf
                            <div class="form-group">
                                <label for="name">Account Title: </label>
                                <input 
                                    class="form-control @error('title') is-danger @enderror" 
                                    type="text" 
                                    name="title" 
                                    id="title" required
                                    value="{{ old('title') }}">
                                @error('title')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                        <p></p>
                        <h6 class="font-weight-bold">Add</h6>
                        <p>Want to add a new account title? Click <a href="{{ url('/accounts/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($accounts as $account)
                            <div id="content">
                                <div id="title">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $account->path() }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/accounts/{{ $account->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/accounts/{{ $account->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div><div style="display:inline-block;">&nbsp;&nbsp;{{ $account->title }}</div>
                                </div>
                            </div>
                        @empty
                            <p>No account titles recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
